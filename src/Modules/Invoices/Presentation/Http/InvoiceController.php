<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Commands\SendInvoiceCommand;
use Modules\Invoices\Application\Services\InvoiceServiceInterface;
use Modules\Invoices\Application\Services\SendInvoiceHandlerInterface;
use Modules\Invoices\Presentation\Http\Data\InvoiceData;
use Modules\Invoices\Presentation\Http\Request\CreateInvoiceRequest;
use Ramsey\Uuid\Uuid;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceServiceInterface $invoiceService,
        private SendInvoiceHandlerInterface $sendInvoiceHandler
    ) {}

    public function create(CreateInvoiceRequest $request)
    {
        $createInvoiceCommand = CreateInvoiceCommand::fromValues(
            $request->customerName,
            $request->customerEmail,
            $request->productLines ?? []
        );
        $invoice = $this->invoiceService->create($createInvoiceCommand);

        return InvoiceData::fromDomainModel($invoice);
    }

    public function view(string $id)
    {
        $invoice = $this->invoiceService->findOrFail(Uuid::fromString($id));

        return InvoiceData::fromDomainModel($invoice);
    }

    public function send(string $id)
    {
        $sendInvoiceCommand = new SendInvoiceCommand(Uuid::fromString($id));
        $this->sendInvoiceHandler->handle($sendInvoiceCommand);

        return response()->json(['message' => 'Invoice sending process initiated successfully'], Response::HTTP_ACCEPTED);
    }
}
