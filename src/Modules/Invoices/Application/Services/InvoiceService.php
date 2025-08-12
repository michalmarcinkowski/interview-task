<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Application\Factories\InvoiceFactoryInterface;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Ramsey\Uuid\UuidInterface;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private InvoiceFactoryInterface $factory
    ) {}
    
    public function create(CreateInvoiceCommand $data): Invoice
    {
        $invoice = $this->factory->create($data);
        
        $this->repository->save($invoice);

        return $invoice;
    }
    
    public function findOrFail(UuidInterface $id): Invoice
    {
        return $this->repository->findOrFail($id);
    }
}
