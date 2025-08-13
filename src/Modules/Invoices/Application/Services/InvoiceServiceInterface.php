<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Commands\SendInvoiceCommand;
use Modules\Invoices\Domain\Models\Invoice;
use Ramsey\Uuid\UuidInterface;

/**
 * Invoice Service Interface
 *
 * Defines the contract for invoice business operations in the application layer.
 * Part of the Application layer in hexagonal architecture.
 *
 * ## Purpose
 * - Orchestrates invoice creation and retrieval business processes
 * - Coordinates between domain models, repositories, and factories
 * - Provides application-level business logic for invoice operations
 *
 * ## Design Benefits
 * - Enables dependency injection and loose coupling
 * - Easy to mock in tests
 * - Supports multiple implementations if needed
 * - Separates business logic from infrastructure concerns
 *
 * ## Architecture Context
 * This interface sits between the Presentation layer (controllers) and the Domain layer,
 * orchestrating the business processes for invoice management. For the purpose of the project
 * decided on single service class to keep it simple and avoid complexity. For bigger projects
 * it would be better to have a separate service class for each business process
 * (e.g. CreateInvoiceService, SendInvoiceService, etc.).
 *
 * @see CreateInvoiceCommand
 * @see Invoice
 */
interface InvoiceServiceInterface
{
    /**
     * Creates a new invoice using the provided command data.
     *
     * This method orchestrates the invoice creation process.
     *
     * @param  CreateInvoiceCommand  $data  The invoice creation command
     * @return Invoice The newly created invoice
     */
    public function create(CreateInvoiceCommand $data): Invoice;

    /**
     * Retrieves an invoice by its unique identifier.
     *
     * This method handles the business logic for finding invoices by id.
     *
     * @param  UuidInterface  $id  The unique identifier of the invoice
     * @return Invoice The found invoice
     *
     * @throws InvoiceNotFoundException When invoice is not found
     */
    public function findOrFail(UuidInterface $id): Invoice;

    /**
     * Sends an invoice to the customer.
     *
     * This method orchestrates the invoice sending process.
     *
     * @param  SendInvoiceCommand  $command  The send invoice command
     * @return Invoice The updated invoice with SENDING status
     *
     * @throws InvoiceNotFoundException When invoice is not found
     * @throws \InvalidArgumentException When invoice cannot be sent
     */
    public function send(SendInvoiceCommand $command): void;
}
