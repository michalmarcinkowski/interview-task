<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Ramsey\Uuid\UuidInterface;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $repository
    ) {}
    
    public function create(string $customerName, Email $customerEmail): Invoice
    {
        $invoice = Invoice::create($customerName, $customerEmail);
        $this->repository->save($invoice);

        return $invoice;
    }
    
    public function findOrFail(UuidInterface $id): Invoice
    {
        return $this->repository->findOrFail($id);
    }
}
