<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service',
    ];

    public function salonCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('salon_category');
    }

    public function salonPrices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('salon_prices');
    }
}
