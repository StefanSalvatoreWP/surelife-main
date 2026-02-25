<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{

    protected $table = 'tblbankaccount';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'accountnumber',
        'bankid'
    ];
}
