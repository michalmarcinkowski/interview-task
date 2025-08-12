<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

/**
 * A marker exception for domain-level "not found" exceptions.
 */
abstract class NotFoundException extends \DomainException
{
    protected function __construct(string $message)
    {
        parent::__construct($message);
    }
}
