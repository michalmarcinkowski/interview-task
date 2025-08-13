<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Presentation\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Tests\TestCase;

class CreateInvoiceControllerTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        $this->setUpFaker();
        parent::setUp();
    }

    public function test_should_create_empty_invoice_with_draft_status_successfully(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => InvoiceStatus::DRAFT->value,
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
            ]);
    }

    public function test_should_return_validation_error_for_missing_customer_name(): void
    {
        $requestData = [
            // 'customerName' is intentionally missing
            'customerEmail' => $this->faker->safeEmail(),
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customerName' => 'The customer name field is required.',
            ]);
    }

    public function test_should_return_validation_error_for_missing_customer_email(): void
    {
        $requestData = [
            'customerName' => $this->faker->name(),
            // 'customerEmail' is intentionally missing
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customerEmail' => 'The customer email field is required.',
            ]);
    }

    public function test_should_return_validation_error_for_invalid_email(): void
    {
        $requestData = [
            'customerName' => $this->faker->name(),
            'customerEmail' => 'invalid-email',
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'customerEmail' => 'The customer email field must be a valid email address.',
            ]);
    }

    public function test_should_return_validation_error_for_empty_customer_name(): void
    {
        $requestData = [
            'customerName' => '',
            'customerEmail' => $this->faker->safeEmail(),
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customerName']);
    }

    public function test_should_return_validation_error_for_empty_customer_email(): void
    {
        $requestData = [
            'customerName' => $this->faker->name(),
            'customerEmail' => '',
        ];

        $response = $this->postJson(route('invoices.create'), $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customerEmail']);
    }

    public function test_should_create_invoice_with_product_lines_successfully(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();
        $product1Name = $this->faker->words(2, true);
        $product2Name = $this->faker->words(2, true);
        $quantity1 = $this->faker->numberBetween(1, 10);
        $quantity2 = $this->faker->numberBetween(1, 10);
        $unitPrice1 = $this->faker->numberBetween(100, 1000);
        $unitPrice2 = $this->faker->numberBetween(100, 1000);

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [
                [
                    'productName' => $product1Name,
                    'quantity' => $quantity1,
                    'unitPrice' => $unitPrice1,
                ],
                [
                    'productName' => $product2Name,
                    'quantity' => $quantity2,
                    'unitPrice' => $unitPrice2,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
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
        $this->assertEquals($product1Name, $firstProductLine['productName']);
        $this->assertEquals($quantity1, $firstProductLine['quantity']);
        $this->assertEquals($unitPrice1, $firstProductLine['unitPrice']);
        $this->assertEquals($quantity1 * $unitPrice1, $firstProductLine['totalUnitPrice']);

        $secondProductLine = $responseData['productLines'][1];
        $this->assertEquals($product2Name, $secondProductLine['productName']);
        $this->assertEquals($quantity2, $secondProductLine['quantity']);
        $this->assertEquals($unitPrice2, $secondProductLine['unitPrice']);
        $this->assertEquals($quantity2 * $unitPrice2, $secondProductLine['totalUnitPrice']);
    }

    public function test_should_create_invoice_with_single_product_line(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();
        $productName = $this->faker->words(2, true);
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->numberBetween(100, 500);

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [
                [
                    'productName' => $productName,
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
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
        $this->assertEquals($quantity * $unitPrice, $responseData['productLines'][0]['totalUnitPrice']);
    }

    public function test_should_create_invoice_with_empty_product_lines_array(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
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

    public function test_should_return_validation_error_for_invalid_product_line_quantity(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();
        $productName = $this->faker->words(2, true);

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [
                [
                    'productName' => $productName,
                    'quantity' => 0, // Invalid: must be positive
                    'unitPrice' => $this->faker->numberBetween(100, 100000),
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['productLines.0.quantity']);
    }

    public function test_should_return_validation_error_for_invalid_product_line_unit_price(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();
        $productName = $this->faker->words(2, true);

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [
                [
                    'productName' => $productName,
                    'quantity' => $this->faker->numberBetween(1, 50),
                    'unitPrice' => 0, // Invalid: must be positive
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['productLines.0.unitPrice']);
    }

    public function test_should_return_validation_error_for_missing_product_name(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [
                [
                    // 'productName' is missing
                    'quantity' => $this->faker->numberBetween(1, 50),
                    'unitPrice' => $this->faker->numberBetween(100, 100000),
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['productLines.0.productName']);
    }

    public function test_should_return_validation_error_for_empty_product_name(): void
    {
        $customerName = $this->faker->name();
        $customerEmail = $this->faker->safeEmail();

        $response = $this->postJson(route('invoices.create'), [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'productLines' => [
                [
                    'productName' => '', // Empty product name
                    'quantity' => $this->faker->numberBetween(1, 50),
                    'unitPrice' => $this->faker->numberBetween(100, 100000),
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['productLines.0.productName']);
    }
}
