<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AfterService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'status',
        'visit_place',
        'visit_date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function afterServiceComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AfterServiceComment::class);
    }

    public function afterServiceImages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AfterServiceImage::class);
    }

}
