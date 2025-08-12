<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repositories;

use Modules\Invoices\Domain\Models\Invoice;

interface InvoiceFactoryInterface
{
    public function create(string $customerName, string $customerEmail, array $productLines = []): Invoice;
}
