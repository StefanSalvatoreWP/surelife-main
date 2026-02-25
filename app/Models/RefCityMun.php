<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class RefCityMun extends Model
{
    protected $table = 'refcitymun';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'psgcCode',
        'citymunDesc',
        'regDesc',
        'provCode',
        'citymunCode'
    ];
}
