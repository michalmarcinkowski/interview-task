<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Psr\Log\LoggerInterface;

final readonly class InvoiceDeliveredListener
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private LoggerInterface $logger,
    ) {}

    public function handle(ResourceDeliveredEvent $event): void
    {
        try {
            $invoice = $this->invoiceRepository->findOrFail($event->resourceId);

            // This is an idempotency check: "Is the state already where the event says it should be?"
            if ($invoice->isSentToClientAlready()) {
                $this->logger->info("Invoice {$invoice->getId()} is already marked as sent. Ignoring event.");

                return;
            }

            if (! $invoice->canBeMarkedAsSentToClient()) {
                $this->logger->warning('Invoice delivery event received but invoice is not in sending status', [
                    'invoice_id' => $event->resourceId->toString(),
                    'current_status' => $invoice->getStatus()->value,
                    'action' => 'acknowledge_message_no_retry',
                ]);

                return; // Acknowledge message, do not retry
            }

            $invoice->markAsSentToClient();
            $this->invoiceRepository->save($invoice);
        } catch (InvoiceNotFoundException $e) {
            // Invoice Not Found: resourceId points to an invoice that doesn't exist
            $this->logger->warning('Invoice delivery event received for non-existent invoice', [
                'invoice_id' => $event->resourceId->toString(),
                'error' => $e->getMessage(),
                'action' => 'acknowledge_message_no_retry',
            ]);
            // Acknowledge message, do not retry
        } catch (\Exception $e) {
            // Infrastructure Down / Transient Error: Database unavailable, concurrency issues, network blips, etc.
            $this->logger->error('Error while processing invoice delivery event', [
                'invoice_id' => $event->resourceId->toString(),
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'action' => 'rethrow_for_retry',
            ]);
            // Re-throw to trigger retry by message queue (TODO: implement retry)
            throw $e;
        }
    }
}
