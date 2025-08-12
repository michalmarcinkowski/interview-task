<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Presentation\Http\Data\CreateInvoiceData;

final class InvoiceFactory implements InvoiceFactoryInterface
{
    public function create(CreateInvoiceData $data): Invoice
    {
        $productLines = array_map(function ($line) {
            return InvoiceProductLine::create(
                $line['productName'],
                Quantity::fromInteger($line['quantity']),
                UnitPrice::fromInteger($line['unitPrice'])
            );
        }, $data->productLines);

        return Invoice::create(
            $data->customerName,
            Email::fromString($data->customerEmail),
            ProductLines::fromArray($productLines),
        );
    }
}
