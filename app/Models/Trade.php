<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'type',
        'from_token',
        'to_token',
        'from_amount',
        'to_amount',
        'status',
        'total_fees',
        'uuid',
        'coinbase_id',
    ];

    protected $types = [
        'sell-storj' => 'Sell Storj',
    ];

    public function getType() {
        return $this->types[$this->type];
    }
}
