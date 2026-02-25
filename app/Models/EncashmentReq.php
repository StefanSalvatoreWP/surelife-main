<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class EncashmentReq extends Model
{
    protected $table = 'tblencashmentreq';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'staffid',
        'encashmentids',
        'amount',
        'incentives',
        'incentivesremarks',
        'adjustments',
        'adjustmentremarks',
        'daterequested',
        'verifiedby',
        'dateverified',
        'recordedby',
        'daterecorded',
        'approvedby',
        'dateapproved',
        'releasedby',
        'datereleased',
        'rejectedby',
        'daterejected',
        'status',
        'remarks',
        'vouchercode'
    ];
}
