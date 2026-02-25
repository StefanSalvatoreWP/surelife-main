<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    use HasFactory;

    protected $table = 'tblloanpayment';
    public $timestamps = false;

    protected $fillable = [
        'clientid',
        'orno',
        'orid',
        'amount',
        'installment',
        'loanrequestid',
        'paymentmethod',
        'paymentdate',
        'datecreated',
        'createdby'
    ];
}
