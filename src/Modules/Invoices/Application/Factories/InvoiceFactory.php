<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;

final class InvoiceFactory implements InvoiceFactoryInterface
{
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
     * @param array<array{productName: string, quantity: int, unitPrice: int}> $productLinesData
     * 
     * @return InvoiceProductLine[]
     * @throws InvalidArgumentException
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
