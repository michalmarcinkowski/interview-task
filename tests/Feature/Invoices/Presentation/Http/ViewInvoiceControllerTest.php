<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Presentation\Http;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\ValueObjects\Email;
use Ramsey\Uuid\Uuid;

use function PHPSTORM_META\map;

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

    public function testShouldViewInvoiceSuccessfully(): void
    {
        // Given the invoice was created
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();
        
        $invoice = $this->invoiceService->create($customerName, Email::fromString($customerEmail));
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

    public function testShouldReturn404ForNonExistentInvoice(): void
    {
        $nonExistentId = Uuid::uuid4()->toString();
        
        $response = $this->getJson(route('invoices.view', $nonExistentId));
        
        $response->assertStatus(404)
                ->assertJson([
                    'error' => 'Not found',
                    'message' => 'Invoice with ID "' . $nonExistentId . '" was not found.',
                ])
                ->assertJsonStructure([
                    'error',
                    'message'
                ]);
    }

    public function testShouldReturn404ForInvalidUuidFormat(): void
    {
        $invalidId = 'invalid-uuid-format';
        
        $response = $this->getJson(route('invoices.view', $invalidId));
        
        $response->assertStatus(404);
    }

    public function testShouldReturn404ForUrlWithoutId(): void
    {
        $response = $this->getJson('/invoices/');
        
        $response->assertStatus(404);
    }

    public function testShouldHandleMultipleInvoiceRetrievals(): void
    {
        // Create multiple invoices using the service
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

    private function createMultipleInvoices(int $count): array
    {
        $invoices = [];
        for ($i = 0; $i < $count; $i++) {
            $invoices[] = $this->invoiceService->create(
                $this->faker->name(),
                Email::fromString($this->faker->safeEmail())
            );
        }
        
        return $invoices;
    }
}
