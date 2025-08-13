<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Listeners;

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
            
            // Only update status if invoice is currently in sending status
            if ($invoice->getStatus()->value === 'sending') {
                $invoice->markAsSentToClient();
                $this->invoiceRepository->save($invoice);
                
                $this->logger->info('Invoice status updated to sent-to-client', [
                    'invoice_id' => $event->resourceId->toString(),
                ]);
            } else {
                $this->logger->warning('Invoice delivery event received but invoice is not in sending status', [
                    'invoice_id' => $event->resourceId->toString(),
                    'current_status' => $invoice->getStatus()->value,
                ]);
            }
        } catch (\Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException $e) {
            $this->logger->warning('Invoice delivery event received for non-existent invoice', [
                'invoice_id' => $event->resourceId->toString(),
                'error' => $e->getMessage(),
            ]);
            // Don't re-throw for non-existent invoices - this is expected in some cases
        } catch (\Exception $e) {
            $this->logger->error('Failed to process invoice delivery event', [
                'invoice_id' => $event->resourceId->toString(),
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw the exception to ensure proper error handling
            throw $e;
        }
    }
}
