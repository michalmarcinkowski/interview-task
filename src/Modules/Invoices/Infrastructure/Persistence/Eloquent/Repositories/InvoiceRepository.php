<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceModel;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Ramsey\Uuid\UuidInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function findOrFail(UuidInterface $id): Invoice
    {
        $model = InvoiceModel::find($id->toString());
        
        if (null === $model) {
            throw InvoiceNotFoundException::withId($id);
        }
        
        return Invoice::reconstitute(
            $id,
            InvoiceStatus::from($model->status),
            $model->customer_name,
            Email::fromString($model->customer_email),
            ProductLines::empty(),
        );
    }

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
