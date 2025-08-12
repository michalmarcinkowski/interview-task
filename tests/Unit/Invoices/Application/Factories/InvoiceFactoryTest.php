<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Factories;

use PHPUnit\Framework\TestCase;
use Modules\Invoices\Application\Factories\InvoiceFactory;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Presentation\Http\Data\CreateInvoiceData;

class InvoiceFactoryTest extends TestCase
{
    private InvoiceFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new InvoiceFactory();
    }

    public function testShouldCreateInvoiceWithoutProductLines(): void
    {
        $createData = new CreateInvoiceData(
            'John Doe',
            'john@example.com'
        );

        $invoice = $this->factory->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('John Doe', $invoice->getCustomerName());
        $this->assertEquals('john@example.com', $invoice->getCustomerEmail()->value());
        $this->assertFalse($invoice->hasProductLines());
        $this->assertEquals(0, $invoice->getProductLines()->count());
    }

    public function testShouldCreateInvoiceWithProductLines(): void
    {
        $createData = new CreateInvoiceData(
            'Jane Doe',
            'jane@example.com',
            [
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
            ]
        );

        $invoice = $this->factory->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('Jane Doe', $invoice->getCustomerName());
        $this->assertEquals('jane@example.com', $invoice->getCustomerEmail()->value());
        $this->assertTrue($invoice->hasProductLines());
        $this->assertEquals(2, $invoice->getProductLines()->count());

        $productLines = $invoice->getProductLines()->toArray();
        $this->assertEquals('Product 1', $productLines[0]->getProductName());
        $this->assertEquals(2, $productLines[0]->getQuantity()->value());
        $this->assertEquals(100, $productLines[0]->getUnitPrice()->value());
        $this->assertEquals(200, $productLines[0]->getTotalUnitPrice());

        $this->assertEquals('Product 2', $productLines[1]->getProductName());
        $this->assertEquals(3, $productLines[1]->getQuantity()->value());
        $this->assertEquals(150, $productLines[1]->getUnitPrice()->value());
        $this->assertEquals(450, $productLines[1]->getTotalUnitPrice());
    }

    public function testShouldCreateInvoiceWithSingleProductLine(): void
    {
        $createData = new CreateInvoiceData(
            'Single Product Customer',
            'single@example.com',
            [
                [
                    'productName' => 'Single Item',
                    'quantity' => 1,
                    'unitPrice' => 500,
                ],
            ]
        );

        $invoice = $this->factory->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertTrue($invoice->hasProductLines());
        $this->assertEquals(1, $invoice->getProductLines()->count());

        $productLines = $invoice->getProductLines()->toArray();
        $this->assertEquals('Single Item', $productLines[0]->getProductName());
        $this->assertEquals(1, $productLines[0]->getQuantity()->value());
        $this->assertEquals(500, $productLines[0]->getUnitPrice()->value());
        $this->assertEquals(500, $productLines[0]->getTotalUnitPrice());
    }

    public function testShouldCreateInvoiceWithEmptyProductLinesArray(): void
    {
        $createData = new CreateInvoiceData(
            'Empty Products Customer',
            'empty@example.com',
            []
        );

        $invoice = $this->factory->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertFalse($invoice->hasProductLines());
        $this->assertEquals(0, $invoice->getProductLines()->count());
    }

    public function testShouldHandleSpecialCharactersInProductNames(): void
    {
        $createData = new CreateInvoiceData(
            'Special Chars Customer',
            'special@example.com',
            [
                [
                    'productName' => 'Product with Special Chars: !@#$%^&*()',
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ]
        );

        $invoice = $this->factory->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $productLines = $invoice->getProductLines()->toArray();
        $this->assertEquals('Product with Special Chars: !@#$%^&*()', $productLines[0]->getProductName());
    }

    public function testShouldHandleLargeQuantitiesAndPrices(): void
    {
        $createData = new CreateInvoiceData(
            'Large Numbers Customer',
            'large@example.com',
            [
                [
                    'productName' => 'Expensive Item',
                    'quantity' => 999,
                    'unitPrice' => 999999,
                ],
            ]
        );

        $invoice = $this->factory->create($createData);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $productLines = $invoice->getProductLines()->toArray();
        $this->assertEquals(999, $productLines[0]->getQuantity()->value());
        $this->assertEquals(999999, $productLines[0]->getUnitPrice()->value());
        $this->assertEquals(999 * 999999, $productLines[0]->getTotalUnitPrice());
    }

    public function testShouldGenerateUniqueIdsForProductLines(): void
    {
        $createData = new CreateInvoiceData(
            'Unique IDs Customer',
            'unique@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
                [
                    'productName' => 'Product 2',
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ]
        );

        $invoice = $this->factory->create($createData);
        $productLines = $invoice->getProductLines()->toArray();

        $this->assertNotEquals(
            $productLines[0]->getId()->toString(),
            $productLines[1]->getId()->toString()
        );
    }
}
