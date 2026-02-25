<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class ClientTransfer extends Model
{

    protected $table = 'tblclienttransfer';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'clientid',
        'transferclientid',
        'datecreated',
        'createdby',
        'datetransferred'
    ];
}
