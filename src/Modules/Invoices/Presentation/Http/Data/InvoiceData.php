<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Data;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Spatie\LaravelData\Data;

final class InvoiceData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $productLines,
        public readonly int $total,
    ) {}

    public static function fromDomainModel(Invoice $invoice): self
    {
        $productLines = array_map(function (InvoiceProductLine $line) {
            return [
                'id' => $line->getId()->toString(),
                'productName' => $line->getProductName(),
                'quantity' => $line->getQuantity()->value(),
                'unitPrice' => $line->getUnitPrice()->value(),
                'totalUnitPrice' => $line->getTotalUnitPrice(),
            ];
        }, $invoice->getProductLines()->toArray());

        return new self(
            id: $invoice->getId()->toString(),
            status: $invoice->getStatus()->value,
            customerName: $invoice->getCustomerName(),
            customerEmail: $invoice->getCustomerEmail()->value(),
            productLines: $productLines,
            total: $invoice->getTotal()
        );
    }
}
