<?php

namespace Halogin\NotiStock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockSharePriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_share_id',
        'date',
        'price_close',
        'price_open',
        'price_high',
        'price_low',
    ];
}
