<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Webmozart\Assert\Assert;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;

/**
 *
 * In our domain model, `InvoiceProductLine` is explicitly modeled as an **Entity**,
 * not a Value Object. This is a critical design decision for the following reasons:
 *
 * Even though not states explicitly in the requirements, I made the following assumptions:
 * 
 * 1.  Unique Identity is Required: An invoice can contain multiple lines that are
 * otherwise identical (e.g., two separate entries for the same product).
 * To manage these lines—for instance, to delete or update a *specific* one—each
 * line must have a stable identity that is independent of its attributes.
 * Equality is therefore based on its unique ID, not the values of its properties.
 *
 * 2.  Has a Distinct Life Cycle: A product line has its own life cycle within the
 * `Invoice` aggregate. It is created, can be modified (e.g., the quantity can be
 * updated while the invoice is a draft), and can be deleted. This concept of
 * persistence and change over time is characteristic of an Entity.
 *
 * 3.  Mutability: Value Objects are immutable by definition. Since the attributes of
 * an invoice line may need to be updated during the drafting phase of an invoice,
 * it requires mutability, which aligns with the nature of an Entity.
 *
 * By modeling this as an Entity, we ensure the integrity of the `Invoice` aggregate
 * and accurately reflect the business requirement of managing distinct, individual
 * line items.
 */
final class InvoiceProductLine
{
    private function __construct(
        private UuidInterface $id,
        private string $productName,
        private Quantity $quantity,
        private UnitPrice $unitPrice
    ) {}

    public static function create(string $productName, Quantity $quantity, UnitPrice $unitPrice): self
    {
        Assert::notEmpty(trim($productName), 'Product name must not be empty or only whitespace');
        
        return new self(
            Uuid::uuid4(),
            $productName,
            $quantity,
            $unitPrice,
        );
    }

    public static function reconstitute(
        UuidInterface $id,
        string $productName,
        Quantity $quantity,
        UnitPrice $unitPrice
    ): self {
        return new self(
            $id,
            $productName,
            $quantity,
            $unitPrice,
        );
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getQuantity(): Quantity
    {
        return $this->quantity;
    }

    public function getUnitPrice(): UnitPrice
    {
        return $this->unitPrice;
    }

    public function getTotalUnitPrice(): int
    {
        return $this->quantity->value() * $this->unitPrice->value();
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}
