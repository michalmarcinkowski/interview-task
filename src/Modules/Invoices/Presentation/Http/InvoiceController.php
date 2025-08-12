<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Routing\Controller;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Presentation\Http\Request\CreateInvoiceRequest;
use Modules\Invoices\Presentation\Http\Data\CreateInvoiceData;
use Modules\Invoices\Presentation\Http\Data\InvoiceData;
use Ramsey\Uuid\Uuid;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}
    
    public function create(CreateInvoiceRequest $request)
    {
        $createInvoiceData = CreateInvoiceData::fromRequest($request);
        $invoice = $this->invoiceService->create($createInvoiceData);
        
        return InvoiceData::fromDomainModel($invoice);
    }
    
    public function view(string $id)
    {
        $invoice = $this->invoiceService->findOrFail(Uuid::fromString($id));

        return InvoiceData::fromDomainModel($invoice);
    }
}
