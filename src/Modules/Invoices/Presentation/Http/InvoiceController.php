<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Routing\Controller;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Presentation\Http\Request\CreateInvoiceRequest;
use Modules\Invoices\Presentation\Http\Data\InvoiceData;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {}
    
    public function create(CreateInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->create(
            $request->customerName,
            $request->customerEmail
        );
        
        return InvoiceData::fromDomainModel($invoice);
    }
}
