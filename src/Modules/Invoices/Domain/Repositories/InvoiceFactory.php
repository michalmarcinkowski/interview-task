<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;

final class InvoiceFactory implements InvoiceFactoryInterface
{
    public function create(string $customerName, string $customerEmail, array $productLines = []): Invoice
    {
        return Invoice::create($customerName, Email::fromString($customerEmail), ProductLines::fromArray($productLines));
    }
}
