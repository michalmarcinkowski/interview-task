<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Presentation\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Models\Invoice;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ViewInvoiceControllerTest extends TestCase
{
    use WithFaker;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        $this->setUpFaker();
        parent::setUp();

        $this->invoiceService = app(InvoiceService::class);
    }

    public function test_should_view_invoice_successfully(): void
    {
        // Given I have an invoice
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();

        $invoice = $this->createInvoice($customerName, $customerEmail);
        $invoiceId = $invoice->getId()->toString();

        // When I fetch the created invoice
        $viewResponse = $this->getJson(route('invoices.view', $invoiceId));

        // Then I should see the invoice details
        $viewResponse->assertStatus(200);
        $viewResponse->assertJson([
            'id' => $invoiceId,
            'status' => InvoiceStatus::DRAFT->value,
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
        ]);
    }

    public function test_should_return404_for_non_existent_invoice(): void
    {
        $nonExistentId = Uuid::uuid4()->toString();

        $response = $this->getJson(route('invoices.view', $nonExistentId));

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Not found',
                'message' => 'Invoice with ID "'.$nonExistentId.'" was not found.',
            ])
            ->assertJsonStructure([
                'error',
                'message',
            ]);
    }

    public function test_should_return404_for_invalid_uuid_format(): void
    {
        $invalidId = 'invalid-uuid-format';

        $response = $this->getJson(route('invoices.view', $invalidId));

        $response->assertStatus(404);
    }

    public function test_should_return404_for_url_without_id(): void
    {
        $response = $this->getJson('/invoices/');

        $response->assertStatus(404);
    }

    public function test_should_handle_multiple_invoice_retrievals(): void
    {
        // Given I have multiple invoices
        $invoices = $this->createMultipleInvoices(3);

        // Verify each invoice can be retrieved
        foreach ($invoices as $invoice) {
            $viewResponse = $this->getJson(route('invoices.view', $invoice->getId()->toString()));

            $viewResponse->assertStatus(200);
            $viewResponse->assertJson([
                'id' => $invoice->getId()->toString(),
                'customerName' => $invoice->getCustomerName(),
                'customerEmail' => $invoice->getCustomerEmail()->value(),
            ]);
        }
    }

    public function test_should_view_invoice_with_product_lines_successfully(): void
    {
        // Given I have an invoice with product lines
        $invoice = $this->createInvoice(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 2,
                    'unitPrice' => 100,
                ],
                [
                    'productName' => 'Product 2',
                    'quantity' => 3,
                    'unitPrice' => 150,
                ],
            ]
        );
        $invoiceId = $invoice->getId()->toString();

        // When I fetch this invoice
        $viewResponse = $this->getJson(route('invoices.view', $invoiceId));

        // Then I should see the invoice details with product lines
        $viewResponse->assertStatus(200)
            ->assertJson([
                'id' => $invoiceId,
                'customerName' => 'John Doe',
                'customerEmail' => 'john@example.com',
            ])
            ->assertJsonStructure([
                'id',
                'status',
                'customerName',
                'customerEmail',
                'productLines' => [
                    '*' => [
                        'id',
                        'productName',
                        'quantity',
                        'unitPrice',
                        'totalUnitPrice',
                    ],
                ],
            ]);

        // Verify product lines data
        $responseData = $viewResponse->json();
        $this->assertCount(2, $responseData['productLines']);

        $firstProductLine = $responseData['productLines'][0];
        $this->assertEquals('Product 1', $firstProductLine['productName']);
        $this->assertEquals(2, $firstProductLine['quantity']);
        $this->assertEquals(100, $firstProductLine['unitPrice']);
        $this->assertEquals(200, $firstProductLine['totalUnitPrice']); // 2 * 100

        $secondProductLine = $responseData['productLines'][1];
        $this->assertEquals('Product 2', $secondProductLine['productName']);
        $this->assertEquals(3, $secondProductLine['quantity']);
        $this->assertEquals(150, $secondProductLine['unitPrice']);
        $this->assertEquals(450, $secondProductLine['totalUnitPrice']); // 3 * 150
    }

    public function test_should_view_invoice_with_single_product_line(): void
    {
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

        $viewResponse = $this->getJson(route('invoices.view', $invoiceId));

        $viewResponse->assertStatus(200);

        $responseData = $viewResponse->json();
        $this->assertCount(1, $responseData['productLines']);
        $this->assertEquals('Single Item', $responseData['productLines'][0]['productName']);
        $this->assertEquals(500, $responseData['productLines'][0]['totalUnitPrice']);
    }

    public function test_should_view_invoice_with_empty_product_lines(): void
    {
        $invoice = $this->createInvoice(
            'Empty Products Customer',
            'empty@example.com',
            []
        );
        $invoiceId = $invoice->getId()->toString();

        $viewResponse = $this->getJson(route('invoices.view', $invoiceId));

        $viewResponse->assertStatus(200);

        $responseData = $viewResponse->json();
        $this->assertCount(0, $responseData['productLines']);
    }

    public function test_should_view_invoice_with_large_quantities_and_prices(): void
    {
        $invoice = $this->createInvoice(
            'Large Numbers Customer',
            'large@example.com',
            [
                [
                    'productName' => 'Expensive Item',
                    'quantity' => 999,
                    'unitPrice' => 999999,
                ],
            ]
        );
        $invoiceId = $invoice->getId()->toString();

        $viewResponse = $this->getJson(route('invoices.view', $invoiceId));

        $viewResponse->assertStatus(200);
        $responseData = $viewResponse->json();
        $this->assertEquals(999 * 999999, $responseData['productLines'][0]['totalUnitPrice']);
    }

    private function createMultipleInvoices(int $count): array
    {
        $invoices = [];
        for ($i = 0; $i < $count; $i++) {
            $invoices[] = $this->createInvoice(
                $this->faker->name(),
                $this->faker->safeEmail(),
            );
        }

        return $invoices;
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
