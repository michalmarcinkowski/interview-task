<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Data;

use Spatie\LaravelData\Data;
use Modules\Invoices\Domain\Models\Invoice;

final class InvoiceData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $customerName,
        public readonly string $customerEmail,
    ) {}

    public static function fromDomainModel(Invoice $invoice): self
    {
        return new self(
            id: $invoice->getId()->toString(),
            status: $invoice->getStatus()->value,
            customerName: $invoice->getCustomerName(),
            customerEmail: $invoice->getCustomerEmail()->value(),
        );
    }
}
