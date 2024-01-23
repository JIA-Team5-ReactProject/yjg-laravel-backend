<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfterServiceImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
    ];

    public function afterService(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AfterService::class);
    }
}
