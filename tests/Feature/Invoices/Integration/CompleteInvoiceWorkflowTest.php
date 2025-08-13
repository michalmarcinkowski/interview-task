<?php

declare(strict_types=1);

namespace Tests\Feature\Invoices\Integration;

use Illuminate\Http\Response;
use Tests\TestCase;

final class CompleteInvoiceWorkflowTest extends TestCase
{
    public function testShouldCompleteFullInvoiceWorkflow(): void
    {
        // Step 1: Create an invoice
        $invoiceId = $this->createInvoiceWithProductLines();
        
        // Step 2: Verify initial state
        $this->assertInvoiceStatus($invoiceId, 'draft');

        // Step 3: Send the invoice
        $this->sendInvoice($invoiceId);

        // Step 4: Verify final state (webhook simulation should complete immediately)
        $this->assertInvoiceStatus($invoiceId, 'sent-to-client');
    }

    public function testShouldHandleInvoiceWithEmptyProductLines(): void
    {
        // Step 1: Create invoice with empty product lines
        $invoiceId = $this->createEmptyInvoice();
        
        // Step 2: Verify initial state
        $this->assertInvoiceStatus($invoiceId, 'draft');

        // Step 3: Attempt to send (should fail)
        $this->attemptToSendInvoiceWithEmptyProductLines($invoiceId);

        // Step 4: Verify status remains unchanged
        $this->assertInvoiceStatus($invoiceId, 'draft');
    }

    public function testShouldHandleInvoiceWithSingleProductLine(): void
    {
        // Step 1: Create invoice with single product
        $invoiceId = $this->createInvoiceWithSingleProduct();
        
        // Step 2: Verify initial state
        $this->assertInvoiceStatus($invoiceId, 'draft');

        // Step 3: Send the invoice
        $this->sendInvoice($invoiceId);

        // Step 4: Verify final state
        $this->assertInvoiceStatus($invoiceId, 'sent-to-client');
    }

    private function createInvoiceWithProductLines(): string
    {
        $response = $this->postJson('/api/invoices', [
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'productLines' => [
                [
                    'productName' => 'Product A',
                    'quantity' => 2,
                    'unitPrice' => 100,
                ],
                [
                    'productName' => 'Product B',
                    'quantity' => 1,
                    'unitPrice' => 50,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        return $response->json('id');
    }

    private function createEmptyInvoice(): string
    {
        $response = $this->postJson('/api/invoices', [
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'productLines' => [],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        return $response->json('id');
    }

    private function createInvoiceWithSingleProduct(): string
    {
        $response = $this->postJson('/api/invoices', [
            'customerName' => 'Bob Smith',
            'customerEmail' => 'bob@example.com',
            'productLines' => [
                [
                    'productName' => 'Single Product',
                    'quantity' => 1,
                    'unitPrice' => 150,
                ],
            ],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        return $response->json('id');
    }

    private function sendInvoice(string $invoiceId): void
    {
        $response = $this->postJson("/api/invoices/{$invoiceId}/send");
        $response->assertStatus(Response::HTTP_ACCEPTED);
    }

    private function attemptToSendInvoiceWithEmptyProductLines(string $invoiceId): void
    {
        $response = $this->postJson("/api/invoices/{$invoiceId}/send");
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => 'Invoice cannot be sent. Make sure it fulfills the business rules.',
        ]);
    }

    private function assertInvoiceStatus(string $invoiceId, string $expectedStatus): void
    {
        $response = $this->getJson("/api/invoices/{$invoiceId}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['status' => $expectedStatus]);
    }
}
