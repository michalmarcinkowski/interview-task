<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Data;

use Modules\Invoices\Presentation\Http\Request\CreateInvoiceRequest;

final readonly class CreateInvoiceData
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $productLines = [],
    ) {}

    public static function fromRequest(CreateInvoiceRequest $request): self
    {
        return new self(
            customerName: $request->customerName,
            customerEmail: $request->customerEmail,
            productLines: $request->productLines ?? []
        );
    }
}
