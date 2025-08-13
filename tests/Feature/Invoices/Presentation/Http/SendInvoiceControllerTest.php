<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Presentation\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Services\InvoiceServiceInterface;
use Modules\Invoices\Domain\Models\Invoice;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class SendInvoiceControllerTest extends TestCase
{
    use WithFaker;

    private InvoiceServiceInterface $invoiceService;

    protected function setUp(): void
    {
        $this->setUpFaker();
        parent::setUp();

        $this->invoiceService = app(InvoiceServiceInterface::class);
    }

    public function testShouldSendInvoiceWithMultipleProductLines(): void
    {
        // Given I have a draft invoice with multiple product lines
        $invoice = $this->createInvoice(
            'Jane Smith',
            'jane@example.com',
            [
                [
                    'productName' => 'Product A',
                    'quantity' => 3,
                    'unitPrice' => 50,
                ],
                [
                    'productName' => 'Product B',
                    'quantity' => 1,
                    'unitPrice' => 200,
                ],
            ]
        );
        $invoiceId = $invoice->getId()->toString();

        // When I send the invoice
        $response = $this->postJson(route('invoices.send', $invoiceId));

        // Then I should get a success response
        $response->assertStatus(Response::HTTP_ACCEPTED)
            ->assertJson([
                'message' => 'Invoice sending process initiated successfully',
            ]);
    }

    public function testShouldReturnErrorWhenSendingInvoiceWithEmptyProductLines(): void
    {
        // Given I have a draft invoice with no product lines
        $invoice = $this->createInvoice(
            'Empty Products Customer',
            'empty@example.com',
            []
        );
        $invoiceId = $invoice->getId()->toString();

        // When I try to send the invoice
        $response = $this->postJson(route('invoices.send', $invoiceId));

        // Then I should get an error response
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invoice cannot be sent. Make sure it fulfills the business rules.',
            ]);
    }

    public function testShouldReturn404ForNonExistentInvoice(): void
    {
        $nonExistentId = Uuid::uuid4()->toString();

        $response = $this->postJson(route('invoices.send', $nonExistentId));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShouldReturn404ForInvalidUuidFormat(): void
    {
        $invalidId = 'invalid-uuid-format';

        $response = $this->postJson(route('invoices.send', $invalidId));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testShouldSendInvoiceWithSingleProductLine(): void
    {
        // Given I have a draft invoice with single product line
        $invoice = $this->createInvoice(
            'Single Product Customer',
            'single@example.com',
            [
                [
                    'productName' => 'Single Item',
                    'quantity' => 1,
                    'unitPrice' => 500,
                ],
            ]
        );
        $invoiceId = $invoice->getId()->toString();

        // When I send the invoice
        $response = $this->postJson(route('invoices.send', $invoiceId));

        // Then I should get a success response
        $response->assertStatus(Response::HTTP_ACCEPTED)
            ->assertJson([
                'message' => 'Invoice sending process initiated successfully',
            ]);
    }

    /**
     * Create an invoice with optional product lines
     */
    private function createInvoice(string $customerName, string $customerEmail, array $productLines = []): Invoice
    {
        $createInvoiceData = CreateInvoiceCommand::fromValues($customerName, $customerEmail, $productLines);

        return $this->invoiceService->create($createInvoiceData);
    }
}
