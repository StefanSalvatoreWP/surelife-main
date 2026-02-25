<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Role;
use App\Models\User;
use App\Models\Email;
use App\Models\Staff;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Mobile;
use App\Models\Region;
use App\Models\Actions;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class StaffController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Staff
            ::select(
                'tblstaff.*', 
                'tblstaff.Id as staffid', 
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblrole.Role'
            )
            ->leftJoin('tblrole', 'tblstaff.position', '=', 'tblrole.id')
            ->leftJoin('tblregion', 'tblstaff.regionid', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblstaff.branchid', '=', 'tblbranch.id');
            
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('LastName', 'like', "%$searchTerm%")
                    ->orWhere('tblrole.Role', 'like', "%$searchTerm%")
                    ->orWhere('tblregion.RegionName', 'like', "%$searchTerm%")
                    ->orWhere('tblbranch.BranchName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }
       
        return view('pages.staff.staff');
    }

    // search data - selected staff
    public function viewStaffInfo(Request $request, Staff $staff){

        $staffs = Staff
            ::select(
                'tblstaff.*',
                'tblstaff.Id as sid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblrole.Role'
            )
            ->leftJoin('tblrole', 'tblstaff.position', '=', 'tblrole.id')
            ->leftJoin('tblregion', 'tblstaff.regionid', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblstaff.branchid', '=', 'tblbranch.id')
            ->where('tblstaff.Id', $staff->Id)
            ->first();

        if ($staffs) {
            $staffs->MunicipalityDisplay = $this->resolveAddressName($staffs->Municipality, 'citymun');
            $staffs->BarangayDisplay = $this->resolveAddressName($staffs->Barangay, 'barangay');
        }

        $clients = Client
        ::select(
            'tblclient.*', 
            'tblclient.Id as cid', 
            'tblpackage.Package',
            'tblpaymentterm.Id',
            'tblpaymentterm.Term',
            'tblpaymentterm.Price'
            )
        ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id') 
        ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id') 
        ->where('tblclient.recruitedby', $staff->Id)
        ->orderBy('cid', 'desc')
        ->get();

        return view('pages.staff.staff-view', [
            'staff' => $staffs,
            'clients' => $clients
        ]);
    }

    // search data - get staff by branch
    public function getStaffByBranch(Request $request){

        $branchId = $request->input('branchId');
        
        Log::info('StaffController::getStaffByBranch - Request received', [
            'branchId' => $branchId,
            'request_data' => $request->all()
        ]);
        
        // Use raw query to ensure proper column access
        $staffs = \DB::select("SELECT Id, FirstName, LastName FROM tblstaff WHERE BranchId = ? AND ActiveStatus != 'Inactive' ORDER BY LastName ASC", [$branchId]);

        Log::info('StaffController::getStaffByBranch - Query executed', [
            'branchId' => $branchId,
            'staff_count' => count($staffs),
            'staffs_data' => $staffs
        ]);

        return response()->json($staffs);
    }

    // staff form screen
    public function staffFormScreen(Request $request, Staff $staff){

        $roles = Role::all();
        if (session('user_roleid') != 1) {
            $roles = Role::where('id', '!=', 1)->get();
        }

        $regions = Region::all();
        $branches = Branch::all();
        $cities = City::all();
        $barangays = Barangay::all();
        $staffs = Staff::all();
        $mobiles = Mobile::all();
        $emails = Email::all();
        
        // Using new tbladdress system for cascading address selection
        $addressRegions = \DB::table('tbladdress')
            ->where('address_type', 'region')
            ->select('code', 'description')
            ->orderBy('description')
            ->get();
            
        // Region mapping: Convert old RegionId to new address codes
        $regionMapping = [
            '1' => '07', // Cebu North -> REGION VII (CENTRAL VISAYAS)
            '2' => '07', // Cebu South -> REGION VII (CENTRAL VISAYAS) 
            '3' => '10', // Mindanao -> REGION X (NORTHERN MINDANAO)
            '4' => '07', // Bohol -> REGION VII (CENTRAL VISAYAS)
            '5' => '06', // Negros -> REGION VI (WESTERN VISAYAS)
            '6' => '08', // Leyte -> REGION VIII (EASTERN VISAYAS)
            '7' => '08', // Southern Leyte -> REGION VIII (EASTERN VISAYAS)
            '8' => '07', // Negros Oriental -> REGION VII (CENTRAL VISAYAS)
        ];
        
        if (!$staff->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.staff.staff-create', [
                    'staffs' => $staffs,
                    'roles' => $roles,
                    'regions' => $regions,
                    'branches' => $branches,
                    'cities' => $cities,
                    'barangays' => $barangays,
                    'addressRegions' => $addressRegions,
                    'regionMapping' => $regionMapping,
                    'mobiles' => $mobiles,
                    'emails' => $emails
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
        else{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Update')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.staff.staff-update', [
                    'selstaff' => $staff,
                    'staffs' => $staffs,
                    'roles' => $roles,
                    'regions' => $regions,
                    'branches' => $branches,
                    'cities' => $cities,
                    'barangays' => $barangays,
                    'addressRegions' => $addressRegions,
                    'regionMapping' => $regionMapping,
                    'mobiles' => $mobiles,
                    'emails' => $emails
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createStaff(Request $request){
        
        // custom error message
        $messages = [
            'staffusername.required' => 'This field is required.',
            'staffusername.min' => 'Username is too short',
            'staffusername.max' => 'Username is too long',
            'staffposition.required' => 'This field is required.',
            'staffregion.required' => 'This field is required.',
            'staffbranch.required' => 'This field is required.',
            'staffscheme.required' => 'This field is required.',
            'staffdateaccomplished.required' => 'This field is required.',
            'stafffirstname.required' => 'This field is required.',
            'stafffirstname.min' => 'Name is too short',
            'stafffirstname.max' => 'Name is too long',
            'stafflastname.required' => 'This field is required.',
            'stafflastname.min' => 'Name is too short',
            'stafflastname.max' => 'Name is too long',
            'staffgender.required' => 'This field is required.',
            'staffbirthdate.required' => 'This field is required.',
            'staffage.required' => 'This field is required.',
            'staffage.min' => 'Age must be at least 18 years old.',
            'staffmobileno.required' => 'This field is required.',
            'staffmobileno.digits' => 'Mobile number must be 10 digits.',
            'staffemail.required' => 'This field is required.',
            'staffemailaddress.required' => 'This field is required.',
            'staffcustomemaildomain.required_if' => 'Please provide a custom email domain.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'staffusername' => 'required|min:3|max:30',
            'staffposition' => 'required',
            'staffregion' => 'required',
            'staffbranch' => 'required',
            'staffscheme' => 'required',
            'staffrecruitedby' => 'nullable',
            'staffdateaccomplished' => 'required',
            'stafffirstname' => 'required|min:3|max:30',
            'stafflastname' => 'required|min:3|max:30',
            'staffmiddlename' => 'nullable',
            'staffgender' => 'required',
            'staffbirthdate' => 'required',
            'staffage' => 'required|numeric|min:18',
            'staffbirthplace' => 'nullable',
            'staffnationality' => 'nullable',
            'staffcivilstatus' => 'nullable',
            'staffsss' => 'nullable',
            'stafftin' => 'nullable',
            'staffgsis' => 'nullable',
            'staffspouse' => 'nullable',
            'staffspouseoccu' => 'nullable',
            'staffnoofdependencies' => 'nullable',
            'staff_address_region' => 'nullable',
            'staff_address_province' => 'nullable',
            'staff_address_city' => 'nullable',
            'staff_address_barangay' => 'nullable',
            'staff_zipcode' => 'nullable',
            'staff_street' => 'nullable',
            'stafftelephone' => 'nullable',
            'staffmobileno' => 'required|digits:10',
            'staffemail' => 'required',
            'staffemailaddress' => 'required',
            'staffcustomemaildomain' => 'required_if:staffemailaddress,others|max:50',
            'stafflastschool' => 'nullable',
            'staffleducationalattainment' => 'nullable',
            'staffcompany' => 'nullable',
            'staffworknature' => 'nullable',
            'staffworkstartdate' => 'nullable',
            'staffworkenddate' => 'nullable'
        ], $messages);
        
        if ($fields->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $fields->errors()->toArray()
                ], 422);
            }
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $staffusername = strip_tags($validatedData['staffusername']);
        $staffposition = strip_tags($validatedData['staffposition']);
        $staffregion = strip_tags($validatedData['staffregion']);
        $staffbranch = strip_tags($validatedData['staffbranch']);
        $staffscheme = strip_tags($validatedData['staffscheme']);
        $staffrecruitedby = !empty($validatedData['staffrecruitedby']) ? strip_tags($validatedData['staffrecruitedby']) : null;
        $staffdateaccomplished = strip_tags($validatedData['staffdateaccomplished']);
        $stafffirstname = strip_tags($validatedData['stafffirstname']);
        $stafflastname = strip_tags($validatedData['stafflastname']);
        $staffmiddlename = strip_tags($validatedData['staffmiddlename'] ?? '');
        $staffgender = strip_tags($validatedData['staffgender']);
        $staffbirthdate = strip_tags($validatedData['staffbirthdate']);
        $staffage = strip_tags($validatedData['staffage']);
        $staffbirthplace = strip_tags($validatedData['staffbirthplace'] ?? '');
        $staffnationality = strip_tags($validatedData['staffnationality'] ?? '');
        $staffcivilstatus = strip_tags($validatedData['staffcivilstatus'] ?? 'Single');
        $staffsss = strip_tags($validatedData['staffsss'] ?? '');
        $stafftin = strip_tags($validatedData['stafftin'] ?? '');
        $staffgsis = strip_tags($validatedData['staffgsis'] ?? '');
        $staffspouse = strip_tags($validatedData['staffspouse'] ?? '');
        $staffspouseoccu = strip_tags($validatedData['staffspouseoccu'] ?? '');
        $staffnoofdependencies = strip_tags($validatedData['staffnoofdependencies'] ?? '0');
        $staffaddressregion = strip_tags($validatedData['staff_address_region'] ?? '');
        $staffaddressprovince = strip_tags($validatedData['staff_address_province'] ?? '');
        $staffaddresscity = strip_tags($validatedData['staff_address_city'] ?? '');
        $staffaddressbarangay = strip_tags($validatedData['staff_address_barangay'] ?? '');
        $staffzipcode = strip_tags($validatedData['staff_zipcode'] ?? '');
        $staffstreet = strip_tags($validatedData['staff_street'] ?? '');
        $stafftelephone = strip_tags($validatedData['stafftelephone'] ?? '');
        $staffmobileno = preg_replace('/\D/', '', $validatedData['staffmobileno']);
        if (strlen($staffmobileno) === 11 && substr($staffmobileno, 0, 1) === '0') {
            $staffmobileno = substr($staffmobileno, 1);
        }
        $staffemail = strip_tags($validatedData['staffemail']);
        $staffemailaddress = strip_tags($validatedData['staffemailaddress']);
        $staffcustomemaildomain = strip_tags($validatedData['staffcustomemaildomain'] ?? '');
        $stafflastschool = strip_tags($validatedData['stafflastschool'] ?? '');
        $staffleducationalattainment = strip_tags($validatedData['staffleducationalattainment'] ?? '');
        $staffcompany = strip_tags($validatedData['staffcompany'] ?? '');
        $staffworknature = strip_tags($validatedData['staffworknature'] ?? '');
        $staffworkstartdate = strip_tags($validatedData['staffworkstartdate'] ?? '');
        $staffworkenddate = strip_tags($validatedData['staffworkenddate'] ?? '');

        $staffmobilenumber = '0' . $staffmobileno;
        if ($staffemailaddress === 'others' && !empty($staffcustomemaildomain)) {
            $staffemailcomplete = $staffemail . '@' . $staffcustomemaildomain;
        } else {
            $staffemailcomplete = $staffemail . '@' . $staffemailaddress;
        }

        // check if username of staff exists
        $staffExists = Staff::where('IdNumber', $staffusername)->first();

        if($staffExists){
            return redirect()->back()->with('duplicate', 'Username already exists.')->withInput();
        }
        // create a new staff
        else{
            try {
                
                $insertData = [
                    'IdNumber' => $staffusername,
                    'Position' => $staffposition,
                    'RegionId' => $staffregion,
                    'BranchId' => $staffbranch,
                    'Scheme' => $staffscheme,
                    'RecruitedBy' => $staffrecruitedby,
                    'DateAccomplished' => $staffdateaccomplished,
                    'FirstName' => $stafffirstname,
                    'LastName' => $stafflastname,
                    'MiddleName' => $staffmiddlename,
                    'Gender' => $staffgender,
                    'BirthDate' => $staffbirthdate,
                    'Age' => $staffage,
                    'BirthPlace' => $staffbirthplace,
                    'Nationality' => $staffnationality,
                    'CivilStatus' => $staffcivilstatus,
                    'SSS' => $staffsss,
                    'TIN' => $stafftin,
                    'GSIS' => $staffgsis,
                    'Spouse' => $staffspouse,
                    'Occupation' => $staffspouseoccu,
                    'NoOfDependents' => $staffnoofdependencies,
                    'Municipality' => $staffaddresscity,
                    'ZipCode' => $staffzipcode,
                    'Barangay' => $staffaddressbarangay,
                    'Street' => $staffstreet,
                    'TelephoneNumber' => $stafftelephone,
                    'MobileNumber' => $staffmobilenumber,
                    'EmailAddress' => $staffemailcomplete,
                    'LastSchoolAttended' => $stafflastschool,
                    'EducationalAttainment' => $staffleducationalattainment,
                    'CompanyName' => $staffcompany,
                    'WorkNature' => $staffworknature,
                    'StartDateC' => $staffworkstartdate,
                    'EndDateC' => $staffworkenddate
                ];
        
                $staffId = Staff::insertGetId($insertData);
        
                // add to users
                $insertUserData = [
                    'username' => $staffusername,
                    'password' => sha1('temp1234'),
                    'defaultpw' => 'SLC' . substr($staffusername, 0, 4),
                    'roleid' => $staffposition,
                    'datecreated' => date("Y-m-d H:i:s"),
                    'createdby' => session('user_id')
                ];
                $userId = User::insertGetId($insertUserData);

                $updateStaffUserId = [
                    'userid' => $userId
                ];
                Staff::where('id', $staffId)->update($updateStaffUserId);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Staff ' . '[Action] Insert ' . '[Target] ' . $staffId);

                return redirect('/staff')->with('success', 'Added a new staff!');
            } catch (\Exception $e) {
                return redirect('/staff')->with('error', $e->getMessage());
            }
        }
    }

    // update data
    public function updateStaff(Staff $staff, Request $request){

        // custom error message
        $messages = [
            'staffposition.required' => 'This field is required.',
            'staffregion.required' => 'This field is required.',
            'staffbranch.required' => 'This field is required.',
            'staffscheme.required' => 'This field is required.',
            'staffdateaccomplished.required' => 'This field is required.',
            'stafffirstname.required' => 'This field is required.',
            'stafffirstname.min' => 'Name is too short',
            'stafffirstname.max' => 'Name is too long',
            'stafflastname.required' => 'This field is required.',
            'stafflastname.min' => 'Name is too short',
            'stafflastname.max' => 'Name is too long',
            'staffgender.required' => 'This field is required.',
            'staffbirthdate.required' => 'This field is required.',
            'staffage.required' => 'This field is required.',
            'staffmobileno.required' => 'This field is required.',
            'staffmobileno.digits' => 'Mobile number must be 10 digits.',
            'staffemail.required' => 'This field is required.',
            'staffemailaddress.required' => 'This field is required.',
            'staffcustomemaildomain.required_if' => 'Please provide a custom email domain.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'staffposition' => 'required',
            'staffregion' => 'required',
            'staffbranch' => 'required',
            'staffscheme' => 'required',
            'staffrecruitedby' => 'nullable',
            'staffdateaccomplished' => 'required',
            'stafffirstname' => 'required|min:3|max:30',
            'stafflastname' => 'required|min:3|max:30',
            'staffmiddlename' => 'nullable',
            'staffgender' => 'required',
            'staffbirthdate' => 'required',
            'staffage' => 'required',
            'staffbirthplace' => 'nullable',
            'staffnationality' => 'nullable',
            'staffcivilstatus' => 'nullable',
            'staffsss' => 'nullable',
            'stafftin' => 'nullable',
            'staffgsis' => 'nullable',
            'staffspouse' => 'nullable',
            'staffspouseoccu' => 'nullable',
            'staffnoofdependencies' => 'nullable',
            'staff_address_region' => 'nullable',
            'staff_address_province' => 'nullable',
            'staff_address_city' => 'nullable',
            'staff_address_barangay' => 'nullable',
            'staff_zipcode' => 'nullable',
            'staff_street' => 'nullable',
            'stafftelephone' => 'nullable',
            'staffmobileno' => 'required|digits:10',
            'staffemail' => 'required',
            'staffemailaddress' => 'required',
            'staffcustomemaildomain' => 'required_if:staffemailaddress,others|max:50',
            'stafflastschool' => 'nullable',
            'staffleducationalattainment' => 'nullable',
            'staffcompany' => 'nullable',
            'staffworknature' => 'nullable',
            'staffworkstartdate' => 'nullable',
            'staffworkenddate' => 'nullable'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $staffposition = strip_tags($validatedData['staffposition']);
        $staffregion = strip_tags($validatedData['staffregion']);
        $staffbranch = strip_tags($validatedData['staffbranch']);
        $staffscheme = strip_tags($validatedData['staffscheme']);
        $staffrecruitedby = !empty($validatedData['staffrecruitedby']) ? strip_tags($validatedData['staffrecruitedby']) : null;
        $staffdateaccomplished = strip_tags($validatedData['staffdateaccomplished']);
        $stafffirstname = strip_tags($validatedData['stafffirstname']);
        $stafflastname = strip_tags($validatedData['stafflastname']);
        $staffmiddlename = strip_tags($validatedData['staffmiddlename'] ?? '');
        $staffgender = strip_tags($validatedData['staffgender']);
        $staffbirthdate = strip_tags($validatedData['staffbirthdate']);
        $staffage = strip_tags($validatedData['staffage']);
        $staffbirthplace = strip_tags($validatedData['staffbirthplace'] ?? '');
        $staffnationality = strip_tags($validatedData['staffnationality'] ?? '');
        $staffcivilstatus = strip_tags($validatedData['staffcivilstatus'] ?? 'Single');
        $staffsss = strip_tags($validatedData['staffsss'] ?? '');
        $stafftin = strip_tags($validatedData['stafftin'] ?? '');
        $staffgsis = strip_tags($validatedData['staffgsis'] ?? '');
        $staffspouse = strip_tags($validatedData['staffspouse'] ?? '');
        $staffspouseoccu = strip_tags($validatedData['staffspouseoccu'] ?? '');
        $staffnoofdependencies = strip_tags($validatedData['staffnoofdependencies'] ?? '0');
        $staffaddressregion = strip_tags($validatedData['staff_address_region'] ?? '');
        $staffaddressprovince = strip_tags($validatedData['staff_address_province'] ?? '');
        $staffaddresscity = strip_tags($validatedData['staff_address_city'] ?? '');
        $staffaddressbarangay = strip_tags($validatedData['staff_address_barangay'] ?? '');
        $staffzipcode = strip_tags($validatedData['staff_zipcode'] ?? '');
        $staffstreet = strip_tags($validatedData['staff_street'] ?? '');
        $stafftelephone = strip_tags($validatedData['stafftelephone'] ?? '');
        $staffmobileno = preg_replace('/\D/', '', $validatedData['staffmobileno']);
        if (strlen($staffmobileno) === 11 && substr($staffmobileno, 0, 1) === '0') {
            $staffmobileno = substr($staffmobileno, 1);
        }
        $staffemail = strip_tags($validatedData['staffemail']);
        $staffemailaddress = strip_tags($validatedData['staffemailaddress']);
        $staffcustomemaildomain = strip_tags($validatedData['staffcustomemaildomain'] ?? '');
        $stafflastschool = strip_tags($validatedData['stafflastschool'] ?? '');
        $staffleducationalattainment = strip_tags($validatedData['staffleducationalattainment'] ?? '');
        $staffcompany = strip_tags($validatedData['staffcompany'] ?? '');
        $staffworknature = strip_tags($validatedData['staffworknature'] ?? '');
        $staffworkstartdate = strip_tags($validatedData['staffworkstartdate'] ?? '');
        $staffworkenddate = strip_tags($validatedData['staffworkenddate'] ?? '');

        $staffmobilenumber = '0' . $staffmobileno;
        if ($staffemailaddress === 'others' && !empty($staffcustomemaildomain)) {
            $staffemailcomplete = $staffemail . '@' . $staffcustomemaildomain;
        } else {
            $staffemailcomplete = $staffemail . '@' . $staffemailaddress;
        }

        // update staff
        try {
            
            $updateData = [
                'Position' => $staffposition,
                'RegionId' => $staffregion,
                'BranchId' => $staffbranch,
                'Scheme' => $staffscheme,
                'RecruitedBy' => $staffrecruitedby,
                'DateAccomplished' => $staffdateaccomplished,
                'FirstName' => $stafffirstname,
                'LastName' => $stafflastname,
                'MiddleName' => $staffmiddlename,
                'Gender' => $staffgender,
                'BirthDate' => $staffbirthdate,
                'Age' => $staffage,
                'BirthPlace' => $staffbirthplace,
                'Nationality' => $staffnationality,
                'CivilStatus' => $staffcivilstatus,
                'SSS' => $staffsss,
                'TIN' => $stafftin,
                'GSIS' => $staffgsis,
                'Spouse' => $staffspouse,
                'Occupation' => $staffspouseoccu,
                'NoOfDependents' => $staffnoofdependencies,
                'Municipality' => $staffaddresscity,
                'ZipCode' => $staffzipcode,
                'Barangay' => $staffaddressbarangay,
                'Street' => $staffstreet,
                'TelephoneNumber' => $stafftelephone,
                'MobileNumber' => $staffmobilenumber,
                'EmailAddress' => $staffemailcomplete,
                'LastSchoolAttended' => $stafflastschool,
                'EducationalAttainment' => $staffleducationalattainment,
                'CompanyName' => $staffcompany,
                'WorkNature' => $staffworknature,
                'StartDateC' => $staffworkstartdate,
                'EndDateC' => $staffworkenddate
            ];
    
            Staff::where('Id', $staff->Id)->update($updateData);
            
            // update user
            $updateUserData = [
                'roleid' => $staffposition
            ];

            User::where('id', $staff->UserId)->update($updateUserData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Staff ' . '[Action] Update ' . '[Target] ' . $staff->Id);

            return redirect('/staff-view/' . $staff->Id)->with('success', 'Selected staff information has been updated!');
        } catch (\Exception $e) {
            return redirect('/staff-view/' . $staff->Id)->with('error', 'An error occurred while updating the selected staff.');
        }
    }

    // delete staff
    public function deleteStaff(Staff $staff){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                Staff::where('Id', $staff->Id)->delete();
                
                // delete also from tbluser
                User::where('id', $staff->UserId)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Staff ' . '[Action] Delete ' . '[Target] ' . $staff->Id);

                return redirect('/staff')->with('warning', 'Selected staff has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/staff')->with('error', 'An error occurred while deleting selected staff.');
        }
    }

    private function resolveAddressName($rawValue, string $addressType)
    {
        if (empty($rawValue)) {
            return '';
        }

        if (preg_match('/[a-zA-Z]/', $rawValue)) {
            return $rawValue;
        }

        $addressQuery = DB::table('tbladdress')
            ->where('address_type', $addressType)
            ->where(function ($query) use ($rawValue) {
                $query->where('code', $rawValue)
                    ->orWhere('psgc_code', $rawValue);
            })
            ->value('description');

        return $addressQuery ?: $rawValue;
    }
}
