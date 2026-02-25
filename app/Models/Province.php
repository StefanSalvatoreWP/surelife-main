<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Province extends Model
{
    protected $table = 'tblprovince';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'province'
    ];
}
