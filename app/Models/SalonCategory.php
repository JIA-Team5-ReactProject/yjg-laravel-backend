<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalonCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
    ];

    public function salonServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('salon_services');
    }
}
