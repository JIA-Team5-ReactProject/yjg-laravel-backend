<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;
    protected $fillable = [
        'admin_id',
        'title',
        'content',
        'tag',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function noticeImages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NoticeImage::class);
    }
}
