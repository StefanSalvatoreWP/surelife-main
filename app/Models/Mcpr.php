<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Mcpr extends Model
{
    protected $table = 'tblmcprcalendar';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'year',
        'monthid',
        'startingdate',
        'endingdate'
    ];
}
