<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Email extends Model
{
    protected $table = 'tblemail';

    use HasFactory;
    protected $fillable = [
        'email'
    ];
}
