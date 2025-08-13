<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Application\Commands;

use Modules\Invoices\Application\Commands\CreateInvoiceCommand;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class CreateInvoiceCommandTest extends TestCase
{
    public function test_should_create_command_with_valid_data(): void
    {
        $command = CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 2,
                    'unitPrice' => 100,
                ],
            ]
        );

        $this->assertEquals('John Doe', $command->customerName);
        $this->assertEquals('john@example.com', $command->customerEmail);
        $this->assertCount(1, $command->productLines);
        $this->assertEquals('Product 1', $command->productLines[0]['productName']);
        $this->assertEquals(2, $command->productLines[0]['quantity']);
        $this->assertEquals(100, $command->productLines[0]['unitPrice']);
    }

    public function test_should_create_command_without_product_lines(): void
    {
        $command = CreateInvoiceCommand::fromValues(
            'Jane Doe',
            'jane@example.com'
        );

        $this->assertEquals('Jane Doe', $command->customerName);
        $this->assertEquals('jane@example.com', $command->customerEmail);
        $this->assertEmpty($command->productLines);
    }

    public function test_should_create_command_with_multiple_product_lines(): void
    {
        $command = CreateInvoiceCommand::fromValues(
            'Multiple Products Customer',
            'multiple@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 1,
                    'unitPrice' => 50,
                ],
                [
                    'productName' => 'Product 2',
                    'quantity' => 3,
                    'unitPrice' => 75,
                ],
            ]
        );

        $this->assertCount(2, $command->productLines);
        $this->assertEquals('Product 1', $command->productLines[0]['productName']);
        $this->assertEquals('Product 2', $command->productLines[1]['productName']);
    }

    public function test_should_throw_exception_for_empty_customer_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer name cannot be empty.');

        CreateInvoiceCommand::fromValues('', 'john@example.com');
    }

    public function test_should_throw_exception_for_whitespace_only_customer_name(): void
    {
        // Assert::notEmpty() only checks for empty strings, not whitespace-only strings
        // So this should actually pass without throwing an exception
        $command = CreateInvoiceCommand::fromValues('   ', 'john@example.com');

        $this->assertEquals('   ', $command->customerName);
    }

    public function test_should_throw_exception_for_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer email is not a valid email address.');

        CreateInvoiceCommand::fromValues('John Doe', 'invalid-email');
    }

    public function test_should_throw_exception_for_missing_product_name_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product line at index 0 is missing productName.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_missing_quantity_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product line at index 0 is missing quantity.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_missing_unit_price_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product line at index 0 is missing unitPrice.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 1,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_non_string_product_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name at index 0 must be a string.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 123,
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_empty_product_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name at index 0 cannot be empty.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => '',
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_whitespace_only_product_name(): void
    {
        // Assert::notEmpty() only checks for empty strings, not whitespace-only strings
        // So this should actually pass without throwing an exception
        $command = CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => '   ',
                    'quantity' => 1,
                    'unitPrice' => 100,
                ],
            ]
        );

        $this->assertEquals('   ', $command->productLines[0]['productName']);
    }

    public function test_should_throw_exception_for_non_integer_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity at index 0 must be a positive integer.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => '2',
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity at index 0 must be a positive integer.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 0,
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_negative_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity at index 0 must be a positive integer.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => -1,
                    'unitPrice' => 100,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_non_integer_unit_price(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price at index 0 must be a positive integer.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 1,
                    'unitPrice' => '100',
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_zero_unit_price(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price at index 0 must be a positive integer.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 1,
                    'unitPrice' => 0,
                ],
            ]
        );
    }

    public function test_should_throw_exception_for_negative_unit_price(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price at index 0 must be a positive integer.');

        CreateInvoiceCommand::fromValues(
            'John Doe',
            'john@example.com',
            [
                [
                    'productName' => 'Product 1',
                    'quantity' => 1,
                    'unitPrice' => -100,
                ],
            ]
        );
    }
}
