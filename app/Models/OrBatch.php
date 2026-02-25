<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class OrBatch extends Model
{
    protected $table = 'tblorbatch';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'batchcode',
        'seriescode',
        'start',
        'end',
        'regionid',
        'branchid',
        'batchcode',
        'deleted',
        'assignedstaffid',
        'type'
    ];
}
