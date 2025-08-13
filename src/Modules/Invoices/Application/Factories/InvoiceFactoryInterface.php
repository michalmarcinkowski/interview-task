<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Domain\Models\Invoice;

interface InvoiceFactoryInterface
{
    public function create(CreateInvoiceCommand $data): Invoice;
}
