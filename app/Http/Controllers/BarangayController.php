<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Role;
use App\Models\Actions;
use App\Models\Barangay;
use App\Models\Province;
use App\Models\RefProvince;
use App\Models\RefCityMun;
use App\Models\RefBrgy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class BarangayController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Barangay
            ::select(
                'tblbrgy.*', 
                'tblbrgy.id as brgyid',
                'tblcity.City',
                'tblprovince.Province'
            )
            ->leftJoin('tblcity', 'tblbrgy.cityid', '=', 'tblcity.id')
            ->leftJoin('tblprovince', 'tblcity.provinceid', '=', 'tblprovince.id');
            
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('Barangay', 'like', "%$searchTerm%")
                        ->orWhere('tblcity.City', 'like', "%$searchTerm%")
                        ->orWhere('tblprovince.Province', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.barangay.barangay');
    }

    // search data - get cities by province
    public function getCitiesByProvince(Request $request){

        $provinceId = $request->input('provinceId');
        $provinceName = $request->input('provinceName');

        if (!empty($provinceName)) {

            $provinceIdResult = Province::where('province', $provinceName)->first();

            if ($provinceIdResult) {
                $provinceId = $provinceIdResult->Id;
            }
        }

        $cities = City::where('provinceid', $provinceId)
            ->orderBy("city", "asc")
            ->get();

        return response()->json($cities);
    }

    // search data - get barangay by city
    public function getBarangaysByCity(Request $request){

        $cityName = $request->input('cityName');

        $barangays = Barangay::leftjoin('tblcity', 'tblbrgy.cityid', '=', 'tblcity.id')
        ->where('tblcity.city', $cityName)
        ->orderBy('tblbrgy.barangay', 'asc')
        ->get();

        return response()->json($barangays);
    }

    // barangay form screen
    public function barangayFormScreen(Barangay $barangay){

        $provinces = Province::orderBy("province", "asc")->get();
        $cities = City::orderBy("city", "asc")->get();

        if (!$barangay->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.barangay.barangay-create', [
                    'provinces' => $provinces,
                    'cities' => $cities,
                    'barangays' => $barangay
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
                return view('pages.barangay.barangay-update', [
                    'provinces' => $provinces,
                    'cities' => $cities,
                    'barangays' => $barangay
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createBarangay(Request $request){

        // custom error message
        $messages = [
            'provinceid.required' => 'This field is required.',
            'provinceid.not_in' => 'Please select a valid province.',
            'cityid.required' => 'This field is required.',
            'cityid.not_in' => 'Please select a valid city.',
            'barangayname.required' => 'This field is required.',
            'barangayname.min' => 'Name is too short.',
            'barangayname.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'provinceid' => 'required|not_in:0',
            'cityid' => 'required|not_in:0',
            'barangayname' => 'required|min:3|max:30',
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $provinceid = strip_tags($request->input('provinceid'));
        $cityid = strip_tags($request->input('cityid'));
        $barangayname = strip_tags($validatedData['barangayname']);

        // check if barangay exists
        $barangayExists = Barangay::where('barangay', $barangayname)
                ->where('cityid', $cityid)
                ->first();

        if($barangayExists){
            return redirect()->back()->with('duplicate', 'Barangay already exists!')->withInput();
        }
        // create a new barangay
        else{
            try {
                
                $insertData = [
                    'barangay' => $barangayname,
                    'cityid' => $cityid
                ];
        
                Barangay::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Barangay ' . '[Action] Insert ' . '[Target] ' . $barangayname);

                return redirect('/barangay')->with('success', 'Added new barangay!');
            } catch (\Exception $e) {
                return redirect('/barangay')->with('error', 'An error occurred while creating a new barangay.');
            }
        }
    }

    // update data
    public function updateBarangay(Barangay $barangay, Request $request){

        // custom error message
        $messages = [
            'provinceid.required' => 'This field is required.',
            'provinceid.not_in' => 'Please select a valid province.',
            'cityid.required' => 'This field is required.',
            'cityid.not_in' => 'Please select a valid city.',
            'barangayname.required' => 'This field is required.',
            'barangayname.min' => 'Name is too short.',
            'barangayname.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'provinceid' => 'required|not_in:0',
            'cityid' => 'required|not_in:0',
            'barangayname' => 'required|min:3|max:30',
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $provinceid = strip_tags($request->input('provinceid'));
        $cityid = strip_tags($request->input('cityid'));
        $barangayname = strip_tags($validatedData['barangayname']);

        // check if barangay exists
        $barangayExists = Barangay::where('barangay', $barangayname)
                ->where('cityid', $cityid)
                ->first();

        if($barangayExists){
            return redirect()->back()->with('duplicate', 'Barangay already exists!')->withInput();
        }
        // update selected barangay
        else{
            try {
                
                $updateData = [
                    'barangay' => $barangayname,
                    'cityid' => $cityid
                ];
        
                Barangay::where('id', $barangay->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Barangay ' . '[Action] Update ' . '[Target ID] ' . $barangay->Id);

                return redirect('/barangay')->with('success', 'Selected barangay has been updated!');
            } catch (\Exception $e) {
                return redirect('/barangay')->with('error', 'An error occurred while updating a barangay.');
            }
        }
    }

    // delete barangay
    public function deleteBarangay(Barangay $barangay){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){ 
                Barangay::where('id', $barangay->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Barangay ' . '[Action] Delete ' . '[Target ID] ' . $barangay->Id);

                return redirect('/barangay')->with('warning', 'Selected barangay has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/barangay')->with('error', 'An error occurred while deleting a barangay.');
        }
    }

    /************** 2024 **************/
    /********** REF TABLES ***********/
    /*********************************/

    // Get cities from reference tables by province name
    public function getRefCitiesByProvince(Request $request){
        
        try {
            // Test database connection
            \DB::connection()->getPdo();
            
            $provinceName = $request->input('provinceName');
            \Log::info("getRefCitiesByProvince called with provinceName: " . $provinceName);
            
            // Find the province code from the province name
            $province = RefProvince::where('provDesc', $provinceName)->first();
            
            if (!$province) {
                \Log::warning("Province not found: " . $provinceName);
                return response()->json([]);
            }
            
            \Log::info("Found province: " . $province->provDesc . " with code: " . $province->provCode);
            
            // Get cities by province code
            $cities = RefCityMun::where('provCode', $province->provCode)
                ->orderBy('citymunDesc', 'asc')
                ->get();
            
            \Log::info("Found " . $cities->count() . " cities for province: " . $provinceName);
            
            // Transform to match expected format
            $transformedCities = $cities->map(function($city) {
                return [
                    'City' => $city->citymunDesc,
                    'citymunCode' => $city->citymunCode
                ];
            });
            
            return response()->json($transformedCities, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            \Log::error("Error in getRefCitiesByProvince: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Database connection error', 'message' => $e->getMessage()], 500);
        }
    }

    // Get barangays from reference tables by city name
    public function getRefBarangaysByCity(Request $request){
        
        try {
            // Test database connection
            \DB::connection()->getPdo();
            
            $cityName = $request->input('cityName');
            \Log::info("getRefBarangaysByCity called with cityName: " . $cityName);
            
            // Find the city code from the city name
            $city = RefCityMun::where('citymunDesc', $cityName)->first();
            
            if (!$city) {
                \Log::warning("City not found: " . $cityName);
                return response()->json([]);
            }
            
            \Log::info("Found city: " . $city->citymunDesc . " with code: " . $city->citymunCode);
            
            // Get barangays by city code
            $barangays = RefBrgy::where('citymunCode', $city->citymunCode)
                ->orderBy('brgyDesc', 'asc')
                ->get();
            
            \Log::info("Found " . $barangays->count() . " barangays for city: " . $cityName);
            
            // Transform to match expected format
            $transformedBarangays = $barangays->map(function($barangay) {
                return [
                    'Barangay' => $barangay->brgyDesc
                ];
            });
            
            return response()->json($transformedBarangays, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            \Log::error("Error in getRefBarangaysByCity: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return response()->json(['error' => 'Database connection error', 'message' => $e->getMessage()], 500);
        }
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH BARANGAY ** //
    public function app_searchBarangays(Request $request){

        $query = Barangay::orderBy('barangay', 'asc')->get();
        
        return response()->json($query);
    }
}
