<?php

namespace Supravatm\CloverPayment\Models;

use Illuminate\Database\Eloquent\Model;

class CloverPaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'status',
        'response_payload',
    ];
}
