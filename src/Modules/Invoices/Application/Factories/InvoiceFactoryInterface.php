<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Domain\Models\Invoice;

/**
 * Defines the contract for creating Invoice domain objects from application commands.
 * Part of the Application layer in hexagonal architecture.
 *
 * ## Purpose
 * - Converts CreateInvoiceCommand data into Invoice domain objects
 * - Encapsulates complex invoice creation logic
 * - Ensures proper construction of Invoice aggregates with ProductLines
 *
 * ## Design Benefits
 * - Enables dependency injection and loose coupling
 * - Easy to mock in tests
 * - Supports multiple implementations if needed
 *
 * @see CreateInvoiceCommand
 * @see Invoice
 */
interface InvoiceFactoryInterface
{
    /**
     * Creates an Invoice domain object from command data.
     *
     * @param  CreateInvoiceCommand  $data  The invoice creation data
     * @return Invoice A fully constructed Invoice domain object
     *
     * @throws \InvalidArgumentException When command data is invalid
     */
    public function create(CreateInvoiceCommand $data): Invoice;
}
