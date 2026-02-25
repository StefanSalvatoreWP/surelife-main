<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    protected $table = 'tblsms';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'contactno',
        'message',
        'sendto'
    ];
}
