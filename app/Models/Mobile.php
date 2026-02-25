<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Mobile extends Model
{
    protected $table = 'tblmobile';

    use HasFactory;
    protected $fillable = [
        'networkno'
    ];
}
