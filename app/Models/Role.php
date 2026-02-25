<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Role extends Model
{
    protected $table = 'tblrole';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'role',
        'level'
    ];
}
