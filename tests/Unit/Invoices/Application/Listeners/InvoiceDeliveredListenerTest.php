<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Listeners;

use Modules\Invoices\Application\Listeners\InvoiceDeliveredListener;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class InvoiceDeliveredListenerTest extends TestCase
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private LoggerInterface $logger;
    private InvoiceDeliveredListener $listener;

    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new InvoiceDeliveredListener($this->invoiceRepository, $this->logger);
    }

    public function testShouldUpdateInvoiceStatusToSentToClientWhenInSendingStatus(): void
    {
        // Arrange
        $invoice = Invoice::create(
            'John Doe',
            Email::fromString('john@example.com'),
            ProductLines::fromArray([
                InvoiceProductLine::create('Product A', Quantity::fromInteger(2), UnitPrice::fromInteger(100)),
            ])
        );
        
        // Set invoice to sending status
        $invoice->markAsSending();
        
        $event = new ResourceDeliveredEvent($invoice->getId());
        
        $this->invoiceRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with($invoice->getId())
            ->willReturn($invoice);
            
        $this->invoiceRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Invoice $savedInvoice) {
                return $savedInvoice->getStatus() === InvoiceStatus::SENT_TO_CLIENT;
            }));

        // Act
        $this->listener->handle($event);

        // Assert
        $this->assertEquals(InvoiceStatus::SENT_TO_CLIENT, $invoice->getStatus());
    }

    public function testShouldNotUpdateInvoiceStatusWhenNotInSendingStatus(): void
    {
        // Arrange
        $invoice = Invoice::create(
            'John Doe',
            Email::fromString('john@example.com'),
            ProductLines::fromArray([
                InvoiceProductLine::create('Product A', Quantity::fromInteger(2), UnitPrice::fromInteger(100)),
            ])
        );
        // Invoice is in DRAFT status by default
        
        $event = new ResourceDeliveredEvent($invoice->getId());
        
        $this->invoiceRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with($invoice->getId())
            ->willReturn($invoice);
            
        $this->invoiceRepository
            ->expects($this->never())
            ->method('save');

        // Act
        $this->listener->handle($event);

        // Assert
        $this->assertEquals(InvoiceStatus::DRAFT, $invoice->getStatus());
    }

    public function testShouldRethrowGenericExceptionForTransientErrors(): void
    {
        // Arrange
        $invoiceId = Uuid::uuid4();
        $event = new ResourceDeliveredEvent($invoiceId);
        
        $genericException = new \RuntimeException('Temporary network issue');
        
        $this->invoiceRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with($invoiceId)
            ->willThrowException($genericException);

        // Act & Assert - should rethrow for retry
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Temporary network issue');
        
        $this->listener->handle($event);
    }
}
