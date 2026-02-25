<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class RefProvince extends Model
{
    protected $table = 'refprovince';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'psgcCode',
        'provDesc',
        'regCode',
        'provCode'
    ];
}
