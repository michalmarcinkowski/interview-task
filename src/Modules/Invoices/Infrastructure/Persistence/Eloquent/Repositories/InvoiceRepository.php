<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceModel;
use Modules\Invoices\Infrastructure\Persistence\Eloquent\Models\InvoiceProductLineModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function findOrFail(UuidInterface $id): Invoice
    {
        $model = InvoiceModel::with('productLines')->find($id->toString());

        if ($model === null) {
            throw InvoiceNotFoundException::withId($id);
        }

        $productLines = $model->productLines->map(function (InvoiceProductLineModel $lineModel) {
            return InvoiceProductLine::reconstitute(
                Uuid::fromString($lineModel->id),
                $lineModel->product_name,
                Quantity::fromInteger($lineModel->quantity),
                UnitPrice::fromInteger($lineModel->unit_price)
            );
        })->toArray();

        return Invoice::reconstitute(
            $id,
            InvoiceStatus::from($model->status),
            $model->customer_name,
            Email::fromString($model->customer_email),
            ProductLines::fromArray($productLines)
        );
    }

    public function save(Invoice $invoice): void
    {
        $model = InvoiceModel::find($invoice->getId()->toString());

        if ($model === null) {
            $model = new InvoiceModel;
            $model->id = $invoice->getId()->toString();
        }

        $model->status = $invoice->getStatus()->value;
        $model->customer_name = $invoice->getCustomerName();
        $model->customer_email = $invoice->getCustomerEmail()->value();
        $model->save();

        // Handle product lines
        $this->saveProductLines($invoice, $model);
    }

    private function saveProductLines(Invoice $invoice, InvoiceModel $model): void
    {
        // Delete existing product lines
        $model->productLines()->delete();

        // Create new product lines
        foreach ($invoice->getProductLines()->toArray() as $productLine) {
            InvoiceProductLineModel::create([
                'id' => $productLine->getId()->toString(),
                'invoice_id' => $model->id,
                'product_name' => $productLine->getProductName(),
                'quantity' => $productLine->getQuantity()->value(),
                'unit_price' => $productLine->getUnitPrice()->value(),
            ]);
        }
    }
}
