<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Presentation\Http;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Invoices\Domain\Enums\InvoiceStatus;

class CreateInvoiceControllerTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        $this->setUpFaker();

        parent::setUp();
    }

    public function testShouldCreateInvoiceWithDraftStatusSuccessfully(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => InvoiceStatus::DRAFT->value,
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
        ]);
    }

    public function testShouldReturnValidationErrorForMissingCustomerName(): void
    {
        $requestData = [
            // 'customerName' is intentionally missing
            'customerEmail' => $this->faker->safeEmail(),
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'customerName' => 'The customer name field is required.'
                 ]);
    }

    public function testShouldReturnValidationErrorForMissingCustomerEmail(): void
    {
        $requestData = [
            'customerName' => $this->faker->name(),
            // 'customerEmail' is intentionally missing
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'customerEmail' => 'The customer email field is required.'
                ]);
    }

    public function testShouldReturnValidationErrorForInvalidEmail(): void
    {
        $requestData = [
            'customerName' => $this->faker->safeEmail(),
            'customerEmail' => 'invalid-email',
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'customerEmail' => 'The customer email field must be a valid email address.'
                ]);
    }

    public function testShouldReturnValidationErrorForEmptyCustomerName(): void
    {
        $requestData = [
            'customerName' => '',
            'customerEmail' => $this->faker->safeEmail(),
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['customerName']);
    }

    public function testShouldReturnValidationErrorForEmptyCustomerEmail(): void
    {
        $requestData = [
            'customerName' => $this->faker->name(),
            'customerEmail' => '',
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['customerEmail']);
    }

    public function testShouldCreateInvoiceWithProductLinesSuccessfully(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'John Doe',
            'customerEmail' => 'john@example.com',
            'productLines' => [
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
            ],
        ]);

        $response->assertStatus(201)
                ->assertJson([
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
        $responseData = $response->json();
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

    public function testShouldCreateInvoiceWithSingleProductLine(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Single Product Customer',
            'customerEmail' => 'single@example.com',
            'productLines' => [
                [
                    'productName' => 'Single Item',
                    'quantity' => 1,
                    'unitPrice' => 500,
                ],
            ],
        ]);

        $response->assertStatus(201)
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

        $responseData = $response->json();
        $this->assertCount(1, $responseData['productLines']);
        $this->assertEquals(500, $responseData['productLines'][0]['totalUnitPrice']);
    }

    public function testShouldCreateInvoiceWithEmptyProductLinesArray(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Empty Products Customer',
            'customerEmail' => 'empty@example.com',
            'productLines' => [],
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'customerName' => 'Empty Products Customer',
                    'customerEmail' => 'empty@example.com',
                ])
                ->assertJsonStructure([
                    'id',
                    'status',
                    'customerName',
                    'customerEmail',
                    'productLines',
                ]);

        $responseData = $response->json();
        $this->assertCount(0, $responseData['productLines']);
    }

    public function testShouldReturnValidationErrorForInvalidProductLineQuantity(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Invalid Quantity Customer',
            'customerEmail' => 'invalid@example.com',
            'productLines' => [
                [
                    'productName' => 'Product with Invalid Quantity',
                    'quantity' => 0, // Invalid: must be positive
                    'unitPrice' => 100,
                ],
            ],
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['productLines.0.quantity']);
    }

    public function testShouldReturnValidationErrorForInvalidProductLineUnitPrice(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Invalid Price Customer',
            'customerEmail' => 'invalid@example.com',
            'productLines' => [
                [
                    'productName' => 'Product with Invalid Price',
                    'quantity' => 1,
                    'unitPrice' => -50, // Invalid: must be positive
                ],
            ],
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['productLines.0.unitPrice']);
    }

    public function testShouldReturnValidationErrorForMissingProductName(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Missing Name Customer',
            'customerEmail' => 'missing@example.com',
            'productLines' => [
                [
                    // 'productName' is missing
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ],
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['productLines.0.productName']);
    }

    public function testShouldReturnValidationErrorForEmptyProductName(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Empty Name Customer',
            'customerEmail' => 'empty@example.com',
            'productLines' => [
                [
                    'productName' => '', // Empty product name
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ],
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['productLines.0.productName']);
    }

    public function testShouldHandleLargeQuantitiesAndPrices(): void
    {
        $response = $this->postJson(route('invoices.create'), [
            'customerName' => 'Large Numbers Customer',
            'customerEmail' => 'large@example.com',
            'productLines' => [
                [
                    'productName' => 'Expensive Item',
                    'quantity' => 999,
                    'unitPrice' => 999999,
                ],
            ],
        ]);

        $response->assertStatus(201);
        
        $responseData = $response->json();
        $this->assertEquals(999 * 999999, $responseData['productLines'][0]['totalUnitPrice']);
    }
}
