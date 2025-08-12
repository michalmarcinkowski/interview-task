<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Data;

use Modules\Invoices\Presentation\Http\Request\CreateInvoiceRequest;

final readonly class CreateInvoiceData
{
    private function __construct(
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $productLines = [],
    ) {}

    public static function fromValues(string $customerName, string $customerEmail, array $productLines = []): self
    {
        return new self($customerName, $customerEmail, $productLines);
    }

    public static function fromRequest(CreateInvoiceRequest $request): self
    {
        return new self(
            $request->customerName,
            $request->customerEmail,
            $request->productLines ?? []
        );
    }
}
