<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2024 SilverDust) S. Maceren */

class AssignedPlans extends Model
{
    protected $table = 'tblassignedplans';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'clientid',
        'lastname',
        'firstname',
        'middlename',
        'birthdate',
        'age',
        'gender',
        'province',
        'city',
        'barangay',
        'zipcode',
        'paymentid',
        'assignedbyid',
        'datecreated',
        'attachment'
    ];
}
