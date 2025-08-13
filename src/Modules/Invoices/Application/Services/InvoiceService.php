<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Commands\SendInvoiceCommand;
use Modules\Invoices\Application\Factories\InvoiceFactoryInterface;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private InvoiceFactoryInterface $factory,
        private NotificationServiceInterface $notificationService
    ) {}

    public function create(CreateInvoiceCommand $data): Invoice
    {
        $invoice = $this->factory->create($data);

        $this->repository->save($invoice);

        return $invoice;
    }

    public function findOrFail(UuidInterface $id): Invoice
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * Sends an invoice to the customer.
     *
     * This method orchestrates the invoice sending process following a fail-fast approach
     * and ensuring data consistency even when external services fail.
     *
     * ## Implementation Strategy
     *
     * **Fail-Fast Validation**: We validate business rules here even though the domain model
     * also validates them. This provides early failure detection and clear error messages
     * before any database operations or external service calls.
     *
     * **Order of Operations**:
     * 1. Validate invoice can be sent (fail-fast)
     * 2. Update invoice status to SENDING
     * 3. Persist status change to database
     * 4. Call external notification service
     *
     * **Why Save Before Notify?**
     * - If notification fails, we can retry without losing the status change (additional logic needed)
     * - If database commit fails after notification call, we cannot rollback the external service call
     * - This approach ensures we can always retry notifications for invoices in SENDING state
     *
     * **Additional Retry Logic Considerations**:
     * - Implement anomaly detection for invoice stuck in SENDING state for too long (2h/24h)
     * - Stuck invoices can also be retried by sending new notifications
     *
     * @param  SendInvoiceCommand  $command  The send invoice command
     *
     * @throws InvoiceNotFoundException When invoice is not found
     * @throws \InvalidArgumentException When invoice cannot be sent (business rules violation)
     */
    public function send(SendInvoiceCommand $command): void
    {
        // Step 1: Retrieve and validate invoice exists
        $invoice = $this->repository->findOrFail($command->invoiceId);

        // Step 2: Fail-fast validation - ensure invoice meets all business rules
        // Note: We validate here even though domain model validates in markAsSending()
        // This provides early failure detection and clear error messages
        Assert::true($invoice->canBeSent(), 'Invoice cannot be sent. Make sure it fulfills the business rules.');

        // Step 3: Update invoice status to SENDING (domain model validates again)
        $invoice->markAsSending();

        // Step 4: Persist status change BEFORE calling external service
        // This ensures we can retry notifications if they fail, without losing the status change
        // If we called notify() first and it succeeded but DB commit failed, we couldn't rollback the external call
        // What is more, with current workflow if we would receive ResourceDeliveredEvent before the invoice is saved,
        // we would not be able to update the invoice status to SENT_TO_CLIENT.
        $this->repository->save($invoice);

        // Step 5: Call external notification service
        // If this fails, the invoice remains in SENDING state and can be retried
        // Implement retry logic and anomaly detection for stuck invoices
        $this->notificationService->notify(
            $invoice->getId(),
            $invoice->getCustomerEmail()->value(),
            'New invoice is now available',
            $this->generateInvoiceEmailMessage($invoice)
        );
    }

    /**
     * Generates the email message for the invoice.
     *
     * @param  Invoice  $invoice  The invoice to generate message for
     * @return string The email message
     */
    private function generateInvoiceEmailMessage(Invoice $invoice): string
    {
        $message = "Dear {$invoice->getCustomerName()},\n\n";
        $message .= "New invoice is now available.\n\n";
        $message .= "Thank you for your business!\n\n";
        $message .= "Best regards,\nCompany Name";

        return $message;
    }
}
