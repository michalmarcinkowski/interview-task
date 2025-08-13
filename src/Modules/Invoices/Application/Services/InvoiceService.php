<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Commands\SendInvoiceCommand;
use Modules\Invoices\Application\Factories\InvoiceFactoryInterface;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Ramsey\Uuid\UuidInterface;

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

    public function send(SendInvoiceCommand $command): Invoice
    {
        $invoice = $this->repository->findOrFail($command->invoiceId);

        $invoice->markAsSending();

        $this->notificationService->notify(
            $invoice->getId(),
            $invoice->getCustomerEmail()->value(),
            'New invoice is now available',
            $this->generateInvoiceEmailMessage($invoice)
        );

        $this->repository->save($invoice);

        return $invoice;
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
