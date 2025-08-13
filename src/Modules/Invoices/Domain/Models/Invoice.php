<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Invoice
{
    private function __construct(
        private UuidInterface $id,
        private InvoiceStatus $status,
        private string $customerName,
        private Email $customerEmail,
        private ProductLines $productLines
    ) {}

    public static function create(string $customerName, Email $customerEmail, ProductLines $productLines): self
    {
        return new self(
            Uuid::uuid4(),
            InvoiceStatus::DRAFT,
            $customerName,
            $customerEmail,
            $productLines,
        );
    }

    public static function reconstitute(
        UuidInterface $id,
        InvoiceStatus $status,
        string $customerName,
        Email $customerEmail,
        ProductLines $productLines,
    ): self {
        return new self($id, $status, $customerName, $customerEmail, $productLines);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): Email
    {
        return $this->customerEmail;
    }

    public function getProductLines(): ProductLines
    {
        return $this->productLines;
    }

    public function hasProductLines(): bool
    {
        return $this->productLines->isNotEmpty();
    }
}
