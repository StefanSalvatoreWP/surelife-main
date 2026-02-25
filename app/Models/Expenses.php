<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class Expenses extends Model
{
    protected $table = 'tblexpenses';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'expensesdescid',
        'amount',
        'branchid',
        'note',
        'image'
    ];
}
