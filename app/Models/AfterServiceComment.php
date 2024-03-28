<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AfterServiceComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'after_service_id',
        'comment',
    ];

    public function afterService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AfterService::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
