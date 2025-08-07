<?php

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Routing\Controller;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Presentation\Http\Data\CreateInvoiceRequest;
use Modules\Invoices\Presentation\Http\Data\InvoiceData;

class InvoiceController extends Controller
{
    public function create(CreateInvoiceRequest $createInvoiceRequest)
    {
        $newInvoice = Invoice::create($createInvoiceRequest->customerName, $createInvoiceRequest->customerEmail);
        // repo->save($newInvoice)
        
        return InvoiceData::fromDomainModel($newInvoice);
    }
}
