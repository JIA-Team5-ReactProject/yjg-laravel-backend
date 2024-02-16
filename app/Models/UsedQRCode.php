<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsedQRCode extends Model
{
    use HasFactory;
    protected $fillable = ['used_code'];
}
