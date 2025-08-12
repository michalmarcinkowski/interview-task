<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Factories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Presentation\Http\Data\CreateInvoiceData;

interface InvoiceFactoryInterface
{
    public function create(CreateInvoiceData $data): Invoice;
}
