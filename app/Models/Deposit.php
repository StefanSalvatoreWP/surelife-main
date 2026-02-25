<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Deposit extends Model
{
    protected $table = 'tblbankdeposit';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'staffid',
        'branchid',
        'sequenceno',
        'depositedamount',
        'date',
        'bankaccountid',
        'note',
        'depositslip',
        'createdby',
        'datecreated',
        'modifiedby',
        'datemodified'
    ];
}
