<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Factories;

use PHPUnit\Framework\TestCase;
use Modules\Invoices\Application\Factories\InvoiceFactory;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Application\Commands\CreateInvoiceCommand;

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
        $createData = CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com'
        );

        $invoice = $this->factory->create($createData);

        $this->assertInvoiceBasics($invoice, 'John Doe', 'john@example.com');
        $this->assertInvoiceHasNoProductLines($invoice);
    }

    public function testShouldCreateInvoiceWithProductLines(): void
    {
        $createData = CreateInvoiceCommand::fromValues(
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

        $this->assertInvoiceBasics($invoice, 'Jane Doe', 'jane@example.com');
        $this->assertInvoiceHasProductLines($invoice, 2);

        $productLines = $invoice->getProductLines()->toArray();
        $this->assertProductLine($productLines[0], 'Product 1', 2, 100, 200);
        $this->assertProductLine($productLines[1], 'Product 2', 3, 150, 450);
    }

    public function testShouldCreateInvoiceWithSingleProductLine(): void
    {
        $createData = CreateInvoiceCommand::fromValues(
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

        $this->assertInvoiceBasics($invoice, 'Single Product Customer', 'single@example.com');
        $this->assertInvoiceHasProductLines($invoice, 1);

        $productLines = $invoice->getProductLines()->toArray();
        $this->assertProductLine($productLines[0], 'Single Item', 1, 500, 500);
    }

    public function testShouldCreateInvoiceWithEmptyProductLinesArray(): void
    {
        $createData = CreateInvoiceCommand::fromValues(
            'Empty Products Customer',
            'empty@example.com',
            []
        );

        $invoice = $this->factory->create($createData);

        $this->assertInvoiceBasics($invoice, 'Empty Products Customer', 'empty@example.com');
        $this->assertInvoiceHasNoProductLines($invoice);
    }

    public function testShouldHandleLargeQuantitiesAndPrices(): void
    {
        $createData = CreateInvoiceCommand::fromValues(
            'Large Numbers Customer',
            'large@example.com',
            [
                [
                    'productName' => 'Expensive Item',
                    'quantity' => 999999,
                    'unitPrice' => 99999999,
                ],
            ]
        );

        $invoice = $this->factory->create($createData);

        $this->assertInvoiceBasics($invoice, 'Large Numbers Customer', 'large@example.com');
        $this->assertInvoiceHasProductLines($invoice, 1);

        $productLines = $invoice->getProductLines()->toArray();
        $this->assertProductLine($productLines[0], 'Expensive Item', 999999, 99999999, 999999 * 99999999);
    }

    public function testShouldGenerateUniqueIdsForProductLines(): void
    {
        $createData = CreateInvoiceCommand::fromValues(
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

        $this->assertProductLinesHaveUniqueIds($productLines[0], $productLines[1]);
    }

    /**
     * Assert basic invoice properties
     */
    private function assertInvoiceBasics(Invoice $invoice, string $expectedCustomerName, string $expectedCustomerEmail): void
    {
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($expectedCustomerName, $invoice->getCustomerName());
        $this->assertEquals($expectedCustomerEmail, $invoice->getCustomerEmail()->value());
    }

    /**
     * Assert invoice has no product lines
     */
    private function assertInvoiceHasNoProductLines(Invoice $invoice): void
    {
        $this->assertFalse($invoice->hasProductLines());
        $this->assertEquals(0, $invoice->getProductLines()->count());
    }

    /**
     * Assert invoice has the expected number of product lines
     */
    private function assertInvoiceHasProductLines(Invoice $invoice, int $expectedCount): void
    {
        $this->assertTrue($invoice->hasProductLines());
        $this->assertEquals($expectedCount, $invoice->getProductLines()->count());
    }

    /**
     * Assert product line has the expected properties
     */
    private function assertProductLine(
        $productLine,
        string $expectedProductName,
        int $expectedQuantity,
        int $expectedUnitPrice,
        int $expectedTotalUnitPrice
    ): void {
        $this->assertEquals($expectedProductName, $productLine->getProductName());
        $this->assertEquals($expectedQuantity, $productLine->getQuantity()->value());
        $this->assertEquals($expectedUnitPrice, $productLine->getUnitPrice()->value());
        $this->assertEquals($expectedTotalUnitPrice, $productLine->getTotalUnitPrice());
    }

    /**
     * Assert two product lines have unique IDs
     */
    private function assertProductLinesHaveUniqueIds(InvoiceProductLine $productLine1, InvoiceProductLine $productLine2): void
    {
        $this->assertNotEquals(
            $productLine1->getId()->toString(),
            $productLine2->getId()->toString()
        );
    }
}
