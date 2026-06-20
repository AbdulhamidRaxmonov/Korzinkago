<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'provider', 'transaction_id', 'amount', 'state',
        'payme_state', 'reason', 'perform_time', 'cancel_time', 'create_time',
    ];

    protected $casts = [
        'amount' => 'float',
        'payme_state' => 'integer',
        'reason' => 'integer',
        'perform_time' => 'integer',
        'cancel_time' => 'integer',
        'create_time' => 'integer',
    ];

    // Payme transaction states
    public const STATE_CREATED = 1;
    public const STATE_COMPLETED = 2;
    public const STATE_CANCELLED = -1;
    public const STATE_CANCELLED_AFTER_COMPLETE = -2;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
