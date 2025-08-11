<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceModel;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void
    {
        $model = new InvoiceModel();
        $model->id = $invoice->getId()->toString();
        $model->status = $invoice->getStatus()->value;
        $model->customer_name = $invoice->getCustomerName();
        $model->customer_email = $invoice->getCustomerEmail()->value();
        $model->save();
    }
}
