<?php

namespace Ducnm\NotiStock\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockShare extends Model
{
    use HasFactory;

    public function histories()
    {
        return $this->hasMany(StockSharePriceHistory::class, 'stock_share_id', 'id');
    }
}
