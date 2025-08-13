<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use Mockery;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Modules\Invoices\Application\Commands\SendInvoiceCommand;
use Modules\Invoices\Application\Services\SendInvoiceHandler;
use Modules\Invoices\Application\Services\NotificationServiceInterface;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Exceptions\InvalidInvoiceStatusTransitionException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;

class SendInvoiceHandlerTest extends TestCase
{
    public function testShouldSendInvoiceSuccessfully(): void
    {
        // Given I have a valid draft invoice with product lines
        $invoiceId = Uuid::uuid4();
        $customerName = 'Test Customer';
        $customerEmail = Email::fromString('test@example.com');
        $productLines = ProductLines::fromArray([
            InvoiceProductLine::create('Test Product', Quantity::fromInteger(2), UnitPrice::fromInteger(1000))
        ]);

        $invoice = Invoice::create($customerName, $customerEmail, $productLines);

        // And I have mocked dependencies
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andReturn($invoice);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Invoice::class));

        $notificationService = Mockery::mock(NotificationServiceInterface::class);
        $notificationService->shouldReceive('notify')
            ->once()
            ->with(
                $invoice->getId(),
                $customerEmail->value(),
                'New invoice is now available',
                Mockery::type('string')
            );

        // When I send the invoice
        $handler = new SendInvoiceHandler($repository, $notificationService);
        $command = new SendInvoiceCommand($invoiceId);

        $handler->handle($command);

        // Then the invoice status should be updated to SENDING
        // The handle method returns void, so we verify the side effects
        $this->assertEquals(InvoiceStatus::SENDING, $invoice->getStatus());
    }

    public function testShouldThrowExceptionWhenSendingInvoiceWithEmptyProductLines(): void
    {
        // Given I have a draft invoice with no product lines
        $invoiceId = Uuid::uuid4();
        $customerName = 'Empty Customer';
        $customerEmail = Email::fromString('empty@example.com');
        $productLines = ProductLines::empty();

        $invoice = Invoice::create($customerName, $customerEmail, $productLines);

        // And I have mocked dependencies
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andReturn($invoice);

        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        // When I try to send the invoice
        $handler = new SendInvoiceHandler($repository, $notificationService);
        $command = new SendInvoiceCommand($invoiceId);

        // Then I should get a business rule violation exception
        $this->expectException(InvalidInvoiceStatusTransitionException::class);
        $this->expectExceptionMessage('Cannot mark invoice');

        $handler->handle($command);
    }

    public function testShouldThrowExceptionWhenInvoiceNotFound(): void
    {
        // Given I have a non-existent invoice ID
        $invoiceId = Uuid::uuid4();

        // And I have mocked dependencies that will throw an exception
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andThrow(InvoiceNotFoundException::withId($invoiceId));

        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        // When I try to send the invoice
        $handler = new SendInvoiceHandler($repository, $notificationService);
        $command = new SendInvoiceCommand($invoiceId);

        // Then I should get an exception
        $this->expectException(InvoiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Invoice with ID "%s" was not found.', $invoiceId->toString()));

        $handler->handle($command);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
