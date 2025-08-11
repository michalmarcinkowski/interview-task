<?php

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

final readonly class InvoiceProductLine
{
    private string $productName;

    private int $quantity;

    private int $unitPrice; // Use Price VO

    public function __construct(string $productName, int $quantity, int $unitPrice)
    {
        Assert::stringNotEmpty($productName, 'Product name must not be empty.');
        Assert::positiveInteger($quantity, 'Quantity must be a positive integer.');
        Assert::positiveInteger($unitPrice, 'Unit price must be a positive integer.');
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getTotalPrice(): int
    {
        return $this->unitPrice * $this->quantity;
    }
}
