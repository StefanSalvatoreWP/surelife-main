<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    protected $table = 'tblloanrequest';
    public $timestamps = false;

    use HasFactory;
    protected $fillabe = [
        'clientid',
        'amount',
        'monthlyamount',
        'daterequested',
        'status',
        'remarks',
        'code'
    ];
}
