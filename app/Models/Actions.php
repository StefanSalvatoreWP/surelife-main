<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class Actions extends Model
{
    protected $table = 'tblactions';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'action',
        'rolelevel',
        'datecreated',
        'createdby'
    ];
}
