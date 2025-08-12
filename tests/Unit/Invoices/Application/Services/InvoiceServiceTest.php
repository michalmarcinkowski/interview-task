<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Services;

use Tests\TestCase;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Ramsey\Uuid\Uuid;
use Mockery;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;

class InvoiceServiceTest extends TestCase
{
    public function testShouldCreateInvoiceSuccessfully(): void
    {
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('save')
                  ->once()
                  ->with(Mockery::type(Invoice::class));
        
        $service = new InvoiceService($repository);
        $customerName = 'John Doe';
        $customerEmail = Email::fromString('john@example.com');
        
        $invoice = $service->create($customerName, $customerEmail);
        
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($customerName, $invoice->getCustomerName());
        $this->assertEquals($customerEmail, $invoice->getCustomerEmail());
        $this->assertEquals(InvoiceStatus::DRAFT, $invoice->getStatus());
        $this->assertTrue(Uuid::isValid($invoice->getId()->toString()));
    }
    
    public function testShouldFindOrFailInvoiceSuccessfully(): void
    {
        $invoiceId = Uuid::uuid4();
        $status = InvoiceStatus::DRAFT;
        $customerName = 'Jane Doe';
        $customerEmail = Email::fromString('jane@example.com');
        
        $expectedInvoice = Invoice::reconstitute(
            $invoiceId,
            $status,
            $customerName,
            $customerEmail,
        );
        
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
                  ->once()
                  ->with($invoiceId)
                  ->andReturn($expectedInvoice);
        
        $service = new InvoiceService($repository);
        
        $foundInvoice = $service->findOrFail($invoiceId);
        
        $this->assertInstanceOf(Invoice::class, $foundInvoice);
        $this->assertEquals($invoiceId, $foundInvoice->getId());
        $this->assertEquals($customerName, $foundInvoice->getCustomerName());
        $this->assertEquals($customerEmail, $foundInvoice->getCustomerEmail());
        $this->assertEquals($status, $foundInvoice->getStatus());
    }
    
    public function testShouldThrowExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4();
        
        $repository = Mockery::mock(InvoiceRepositoryInterface::class);
        $repository->shouldReceive('findOrFail')
                  ->once()
                  ->with($invoiceId)
                  ->andThrow(InvoiceNotFoundException::withId($invoiceId));
        
        $service = new InvoiceService($repository);
        
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
