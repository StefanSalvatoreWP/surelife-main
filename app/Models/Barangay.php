<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Barangay extends Model
{
    protected $table = 'tblbrgy';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'barangay',
        'cityid'
    ];
}
