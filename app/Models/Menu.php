<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Menu extends Model
{

    protected $table = 'tblmenu';
    public $timestamps = false;
    
    use HasFactory;

    protected $fillable = [
        'menuitem',
        'rolelevel'
    ];
}
