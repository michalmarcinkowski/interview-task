<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;

/**
 * Invoice Factory Implementation
 *
 * Creates Invoice domain objects from CreateInvoiceCommand data.
 * Handles the construction of Invoice aggregates with ProductLines.
 *
 * ## Responsibilities
 * - Transforms command data into domain objects
 * - Creates InvoiceProductLine entities from raw data
 * - Ensures proper value object construction
 *
 * @see InvoiceFactoryInterface
 * @see CreateInvoiceCommand
 * @see Invoice
 */
class InvoiceFactory implements InvoiceFactoryInterface
{
    /**
     * Creates an Invoice with ProductLines from command data.
     *
     * @param  CreateInvoiceCommand  $command  The invoice creation command
     * @return Invoice A fully constructed Invoice domain object
     */
    public function create(CreateInvoiceCommand $command): Invoice
    {
        $productLines = $this->createProductLines($command->productLines);

        return Invoice::create(
            $command->customerName,
            Email::fromString($command->customerEmail),
            ProductLines::fromArray($productLines),
        );
    }

    /**
     * Creates InvoiceProductLine entities from raw data.
     *
     * @param  array<array{productName: string, quantity: int, unitPrice: int}>  $productLinesData
     * @return InvoiceProductLine[]
     *
     * @throws \InvalidArgumentException When data is invalid
     */
    private function createProductLines(array $productLinesData): array
    {
        return array_map(function ($lineData) {
            return InvoiceProductLine::create(
                $lineData['productName'],
                Quantity::fromInteger($lineData['quantity']),
                UnitPrice::fromInteger($lineData['unitPrice'])
            );
        }, $productLinesData);
    }
}
