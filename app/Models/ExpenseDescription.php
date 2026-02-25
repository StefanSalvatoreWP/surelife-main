<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class ExpenseDescription extends Model
{
    protected $table = 'tblexpensesdescription';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'description'
    ];
}
