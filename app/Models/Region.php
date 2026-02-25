<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Region extends Model
{

    protected $table = 'tblregion';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'regionname'
    ];
}
