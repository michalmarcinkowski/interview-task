<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Presentation\Http;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invoices\Domain\Enums\InvoiceStatus;

class CreateInvoiceControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

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
}
