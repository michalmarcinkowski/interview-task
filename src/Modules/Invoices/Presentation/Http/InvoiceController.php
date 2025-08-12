<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Routing\Controller;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Presentation\Http\Request\CreateInvoiceRequest;
use Modules\Invoices\Presentation\Http\Data\InvoiceData;
use Ramsey\Uuid\Uuid;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}
    
    public function create(CreateInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->create(
            $request->customerName,
            Email::fromString($request->customerEmail),
        );
        
        return InvoiceData::fromDomainModel($invoice);
    }
    
    public function view(string $id)
    {
        $invoice = $this->invoiceService->findOrFail(Uuid::fromString($id));
        return InvoiceData::fromDomainModel($invoice);
    }
}
