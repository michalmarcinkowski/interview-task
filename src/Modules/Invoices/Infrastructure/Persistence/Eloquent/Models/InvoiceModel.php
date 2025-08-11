<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    
    protected $fillable = [
        'id',
        'status',
        'customer_name',
        'customer_email',
    ];
    
    public $incrementing = false;
    protected $keyType = 'string';
}
