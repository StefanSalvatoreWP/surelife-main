<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class PaymentTerm extends Model
{
    protected $table = 'tblpaymentterm';
    public $timestamps = false;
    
    use HasFactory;
}
