<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class ContractBatch extends Model
{
    protected $table = 'tblcontractbatch';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'batchcode',
        'beginning',
        'ending',
        'regionid',
        'branchid',
        'deleted',
        'assignedstaffid'
    ];
}
