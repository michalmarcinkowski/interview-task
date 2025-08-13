<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * An Eloquent implementation of the InvoiceRepositoryInterface.
 *
 * This repository handles the persistence of Invoice domain model
 * using Laravel's Eloquent ORM. It encapsulates the logic for
 * translating between the domain models and the database models.
 */
class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * Finds an Invoice by its ID or throws an exception if not found.
     *
     * @param  UuidInterface  $id  The UUID of the invoice to find.
     * @return Invoice The reconstituted Invoice domain model.
     *
     * @throws InvoiceNotFoundException if no invoice is found with the given ID.
     */
    public function findOrFail(UuidInterface $id): Invoice
    {
        try {
            // Use Eloquent's findOrFail to simplify the check.
            // This will automatically throw a ModelNotFoundException if the model isn't found.
            $model = InvoiceModel::with('productLines')->findOrFail($id->toString());

            return $this->mapModelToDomain($model);
        } catch (ModelNotFoundException) {
            // Catch the framework's exception and throw our domain-specific exception.
            // This keeps the rest of our application decoupled from the persistence layer.
            throw InvoiceNotFoundException::withId($id);
        }
    }

    /**
     * Saves an Invoice domain model to the database.
     *
     * This method handles both creating a new invoice and updating an existing one.
     * It uses a database transaction to ensure data integrity.
     *
     * @param  Invoice  $invoice  The Invoice domain model to save.
     */
    public function save(Invoice $invoice): void
    {
        // Wrap the entire save operation in a database transaction.
        // This ensures that if any part of the process fails (e.g., saving product lines),
        // the entire operation is rolled back, maintaining data consistency.
        DB::transaction(function () use ($invoice) {
            $invoiceData = [
                'status' => $invoice->getStatus()->value,
                'customer_name' => $invoice->getCustomerName(),
                'customer_email' => $invoice->getCustomerEmail()->value(),
            ];

            $invoiceModel = InvoiceModel::updateOrCreate(
                ['id' => $invoice->getId()->toString()],
                $invoiceData
            );

            // Handle the saving of product lines.
            $this->saveProductLines($invoice, $invoiceModel);
        });
    }

    /**
     * Maps an Eloquent InvoiceModel to a domain Invoice model.
     *
     * This centralizes the mapping logic, making it reusable and easier to maintain.
     *
     * @param  InvoiceModel  $model  The Eloquent model instance.
     * @return Invoice The domain model instance.
     */
    private function mapModelToDomain(InvoiceModel $model): Invoice
    {
        $productLines = $model->productLines->map(function ($lineModel) {
            return InvoiceProductLine::reconstitute(
                Uuid::fromString($lineModel->id),
                $lineModel->product_name,
                Quantity::fromInteger($lineModel->quantity),
                UnitPrice::fromInteger($lineModel->unit_price)
            );
        })->all(); // Use ->all() to get a plain array

        return Invoice::reconstitute(
            Uuid::fromString($model->id),
            InvoiceStatus::from($model->status),
            $model->customer_name,
            Email::fromString($model->customer_email),
            ProductLines::fromArray($productLines)
        );
    }

    /**
     * Saves the product lines associated with an invoice.
     *
     * This method uses a delete + insert approach for optimal performance:
     * - DELETE all existing product lines for the invoice
     * - INSERT all new product lines in a single bulk operation
     *
     * Performance Analysis:
     * - For typical invoice sizes (5-20 product lines): DELETE + INSERT is faster than change detection
     * - Avoids overhead of fetching existing data and comparing changes
     * - Modern databases optimize bulk operations efficiently
     * - Simpler code = easier maintenance and fewer bugs
     *
     * Alternative approach (change detection) was considered but rejected because:
     * - Added complexity without significant performance gains for most use cases
     * - Required additional SELECT query + comparison logic
     * - Benefits only materialize with very large datasets (50+ lines) and frequent updates
     * - Can be implemented later if needed
     *
     * @param  Invoice  $invoice  The domain model containing the product lines.
     * @param  InvoiceModel  $model  The corresponding Eloquent model.
     */
    private function saveProductLines(Invoice $invoice, InvoiceModel $model): void
    {
        // First, remove all existing product lines for this invoice.
        // This is a simple and effective way to handle updates, additions, and deletions.
        $model->productLines()->delete();

        // Prepare the new product lines for a bulk insert.
        $productLinesData = collect($invoice->getProductLines()->toArray())
            ->map(fn (InvoiceProductLine $line) => [
                'id' => $line->getId()->toString(),
                'invoice_id' => $model->id,
                'product_name' => $line->getProductName(),
                'quantity' => $line->getQuantity()->value(),
                'unit_price' => $line->getUnitPrice()->value(),
            ])
            ->all();

        // Perform a single, efficient bulk insert instead of creating models one by one in a loop.
        // This is significantly more performant for multiple records.
        if (! empty($productLinesData)) {
            $model->productLines()->insert($productLinesData);
        }
    }
}
