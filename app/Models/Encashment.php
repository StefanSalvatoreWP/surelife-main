<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class Encashment extends Model
{
    protected $table = 'tblencashment';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'staffid',
        'clientid',
        'contractno',
        'amountpaid',
        'commission',
        'paymentdate',
        'status',
        'vouchercode'
    ];
}
