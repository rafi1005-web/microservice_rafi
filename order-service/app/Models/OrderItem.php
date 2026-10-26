<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $hidden = ['id', 'order_id'];

protected function casts(): array
{
    return [
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];
}
    protected $fillable = [
        'order_id', 'product_id', 'product_name',
        'unit_price', 'quantity', 'subtotal',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

