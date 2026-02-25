<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

use function PHPSTORM_META\map;

class Staff extends Model
{
    protected $table = 'tblstaff';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    
    use HasFactory;
    protected $fillable = [
        'IdNumber',
        'UserId',
        'FirstName',
        'LastName',
        'MiddleName',
        'BirthDate',
        'Age',
        'BirthPlace',
        'Street',
        'Subdivision',
        'District',
        'Barangay',
        'Municipality',
        'ZipCode',
        'HomeNumber',
        'MobileNumber',
        'EmailAddress',
        'TIN',
        'SSS',
        'GSIS',
        'Nationality',
        'Gender',
        'CivilStatus',
        'Spouse',
        'NoOfDependents',
        'Occupation',
        'TelephoneNumber',
        'LastSchoolAttended',
        'EducationalAttainment',
        'RecruitedBy',
        'DateAccomplished',
        'CompanyName',
        'StartDateC',
        'EndDateC',
        'WorkNature',
        'Position',
        'RegionId',
        'AssignedRegionsId',
        'BranchId',
        'Photo',
        'Scheme',
        'AmountCA',
        'Balance',
        'NewSales',
        'Quota',
        'CollectionAmount',
        'CashOnHand',
        'ActiveStatus',
        'LastActivity'
    ];

    // Ensure Laravel can access Pascal case attributes
    protected $attributes = [];
    
    // Force Laravel to not snake_case the attributes
    public static $snakeAttributes = false;
}
