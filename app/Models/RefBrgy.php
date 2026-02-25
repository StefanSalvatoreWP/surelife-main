<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class RefBrgy extends Model
{
    protected $table = 'refbrgy';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'brgyCode',
        'brgyDesc',
        'regCode',
        'provCode',
        'citymunCode'
    ];
}
