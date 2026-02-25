<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Bank extends Model
{
    protected $table = 'tblbank';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'bankname'
    ];
}
