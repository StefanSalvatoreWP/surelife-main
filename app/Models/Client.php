<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class Client extends Model
{
    protected $table = 'tblclient';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'contractnumber',
        'userid',
        'lastname',
        'firstname',
        'middlename',
        'birthdate',
        'age',
        'birthplace',
        'address',
        'street',
        'province',
        'city',
        'barangay',
        'religion',
        'zipcode',
        'homeregion',
        'homeprovince',
        'homecity',
        'homebarangay',
        'homezipcode',
        'homestreet',
        'homenumber',
        'mobilenumber',
        'emailaddress',
        'gender',
        'civilstatus',
        'occupation',
        'principalbeneficiaryname',
        'principalbeneficiaryage',
        'secondary1name',
        'secondary1age',
        'secondary2name',
        'secondary2age',
        'secondary3name',
        'secondary3age',
        'secondary4name',
        'secondary4age',
        'packageid',
        'packageprice',
        'paymenttermid',
        'paymenttermamount',
        'besttimetocollect',
        'bestplacetocollect',
        'recruitedby',
        'dateverified',
        'regionid',
        'branchid',
        'photo',
        'sketch',
        'datecreated',
        'fsacomsrem',
        'status',
        'remarks',
        'fsc',
        'cfpno',
        'appliedchangemode',
        'completedmemorial'
    ];

    public function recruiter()
    {
        return $this->belongsTo(Staff::class, 'recruitedby', 'Id');
    }
}
