<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Package extends Model
{
    protected $table = 'tblpackage';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'package',
        'price',
        'spotcash',
        'annual',
        'semiannual',
        'quarterly',
        'monthly',
        'active'
    ];
}
