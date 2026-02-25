<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class City extends Model
{
    protected $table = 'tblcity';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'city',
        'provinceid',
        'zipcode'
    ];
}
