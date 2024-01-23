<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
    ];

    public function notices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Notice::class);
    }
}
