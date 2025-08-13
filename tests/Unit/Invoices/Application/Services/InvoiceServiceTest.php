<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use Mockery;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\Factories\InvoiceFactoryInterface;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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

        $service = new InvoiceService($repository, $factory);
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

        $service = new InvoiceService($repository, $factory);

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

        $service = new InvoiceService($repository, $factory);

        $this->expectException(InvoiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Invoice with ID "%s" was not found.', $invoiceId->toString()));

        $service->findOrFail($invoiceId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
