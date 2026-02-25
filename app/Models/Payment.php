<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Payment extends Model
{
    protected $table = 'tblpayment';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'orno',
        'clientid',
        'bankdepositid',
        'orid',
        'amountpaid',
        'netpayment',
        'comsmultiplier',
        'installment',
        'date',
        'ridate',
        'paymenttype',
        'iscleared',
        'checkno',
        'cardno',
        'createdby',
        'status',
        'remarks',
        'deposited',
        'voidstatus'
    ];

    // Relationship with Client
    public function client()
    {
        return $this->belongsTo(Client::class, 'clientid', 'id');
    }

    // Relationship with OfficialReceipt
    public function officialReceipt()
    {
        return $this->belongsTo(OfficialReceipt::class, 'orid', 'id');
    }
}
