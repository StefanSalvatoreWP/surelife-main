<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Contract extends Model
{
    protected $table = 'tblcontract';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'contractbatchid',
        'contractnumber',
        'clientid',
        'status',
        'remarks'
    ];
}
