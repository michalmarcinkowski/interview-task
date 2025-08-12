<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceModel extends Model
{
    protected $table = 'invoices';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'status',
        'customer_name',
        'customer_email',
    ];

    public function productLines(): HasMany
    {
        return $this->hasMany(InvoiceProductLineModel::class, 'invoice_id');
    }
}
