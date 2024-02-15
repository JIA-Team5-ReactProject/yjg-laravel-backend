<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonService extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_category_id',
        'service',
    ];


    public function salonCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalonCategory::class);
    }

    public function salonPrices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalonPrice::class);
    }
}
