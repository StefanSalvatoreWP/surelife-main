<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Month extends Model
{
    protected $table = 'tblmonth';

    use HasFactory;
    protected $fillable = [
        'month'
    ];
}
