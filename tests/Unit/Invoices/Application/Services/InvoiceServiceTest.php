<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use Mockery;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Factories\InvoiceFactoryInterface;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Application\Services\NotificationServiceInterface;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Modules\Invoices\Application\Commands\SendInvoiceCommand;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;

class InvoiceServiceTest extends TestCase
{
    public function testShouldCreateInvoiceSuccessfully(): void
    {
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Invoice::class));

        $factory = Mockery::mock(InvoiceFactoryInterface::class);
        $expectedInvoice = Invoice::create('John Doe', Email::fromString('john@example.com'), ProductLines::empty());
        $factory->shouldReceive('create')
            ->once()
            ->andReturn($expectedInvoice);

        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $service = new InvoiceService($repository, $factory, $notificationService);
        $customerName = 'John Doe';
        $customerEmail = 'john@example.com';
        $createData = CreateInvoiceCommand::fromValues($customerName, $customerEmail);

        $invoice = $service->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($expectedInvoice, $invoice);
    }

    public function testShouldFindOrFailInvoiceSuccessfully(): void
    {
        $customerName = 'Jane Doe';
        $customerEmail = Email::fromString('jane@example.com');

        $expectedInvoice = Invoice::create($customerName, $customerEmail, ProductLines::empty());
        $invoiceId = $expectedInvoice->getId();

        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andReturn($expectedInvoice);

        $factory = Mockery::mock(InvoiceFactoryInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $service = new InvoiceService($repository, $factory, $notificationService);

        $foundInvoice = $service->findOrFail($invoiceId);

        $this->assertInstanceOf(Invoice::class, $foundInvoice);
        $this->assertEquals($expectedInvoice->getId(), $foundInvoice->getId());
        $this->assertEquals($customerName, $foundInvoice->getCustomerName());
        $this->assertEquals($customerEmail, $foundInvoice->getCustomerEmail());
        $this->assertEquals(InvoiceStatus::DRAFT, $foundInvoice->getStatus());
    }

    public function testShouldThrowExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4();

        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andThrow(InvoiceNotFoundException::withId($invoiceId));

        $factory = Mockery::mock(InvoiceFactoryInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $service = new InvoiceService($repository, $factory, $notificationService);

        $this->expectException(InvoiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Invoice with ID "%s" was not found.', $invoiceId->toString()));

        $service->findOrFail($invoiceId);
    }

    public function testShouldSendInvoiceSuccessfully(): void
    {
        $invoiceId = Uuid::uuid4();
        $customerName = 'Test Customer';
        $customerEmail = Email::fromString('test@example.com');
        $productLines = ProductLines::fromArray([
            InvoiceProductLine::create('Test Product', Quantity::fromInteger(2), UnitPrice::fromInteger(1000))
        ]);

        $invoice = Invoice::create($customerName, $customerEmail, $productLines);

        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andReturn($invoice);
        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Invoice::class));

        $factory = Mockery::mock(InvoiceFactoryInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);
        $notificationService->shouldReceive('notify')
            ->once()
            ->with(
                $invoice->getId(),
                $customerEmail->value(),
                'New invoice is now available',
                Mockery::type('string')
            );

        $service = new InvoiceService($repository, $factory, $notificationService);
        $command = new SendInvoiceCommand($invoiceId);

        $service->send($command);

        // Verify that the invoice status was updated to SENDING
        // The send method returns void, so we verify the side effects
        $this->assertEquals(InvoiceStatus::SENDING, $invoice->getStatus());
    }

    public function testShouldThrowExceptionWhenSendingInvoiceWithEmptyProductLines(): void
    {
        $invoiceId = Uuid::uuid4();
        $customerName = 'Empty Customer';
        $customerEmail = Email::fromString('empty@example.com');
        $productLines = ProductLines::empty();

        $invoice = Invoice::create($customerName, $customerEmail, $productLines);

        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
            ->once()
            ->with($invoiceId)
            ->andReturn($invoice);

        $factory = Mockery::mock(InvoiceFactoryInterface::class);
        $notificationService = Mockery::mock(NotificationServiceInterface::class);

        $service = new InvoiceService($repository, $factory, $notificationService);
        $command = new SendInvoiceCommand($invoiceId);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice cannot be sent. Make sure it fulfills the business rules.');

        $service->send($command);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
