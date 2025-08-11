<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $repository
    ) {}
    
    public function create(string $customerName, string $customerEmail): Invoice
    {
        $invoice = Invoice::create($customerName, Email::fromString($customerEmail));
        $this->repository->save($invoice);

        return $invoice;
    }
}
