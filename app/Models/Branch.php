<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Branch extends Model
{

    protected $table = 'tblbranch';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'branchname',
        'regionid'
    ];
}
