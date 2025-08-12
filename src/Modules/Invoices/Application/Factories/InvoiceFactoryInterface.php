<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;

interface InvoiceFactoryInterface
{
    public function create(CreateInvoiceCommand $data): Invoice;
}
