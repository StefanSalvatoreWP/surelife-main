<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Role;
use App\Models\Actions;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class CityController extends Controller
{
    // search data - tables
    public function searchAll(Request $request)
    {

        if ($request->ajax()) {

            $query = City
                ::select(
                    'tblcity.*',
                    'tblcity.id as cityid',
                    'tblprovince.Province'
                )
                ->leftJoin('tblprovince', 'tblcity.provinceid', '=', 'tblprovince.id');

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('City', 'like', "%$searchTerm%")
                        ->orWhere('Zipcode', 'like', "%$searchTerm%")
                        ->orWhere('Province', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.city.city');
    }

    // search data - city zipcode
    // search data - city zipcode - NOW PROVINCE-AWARE
    public function getCityZipcode(Request $request)
    {
        $cityName = $request->input('cityName');
        $provinceCode = $request->input('provinceCode'); // NEW: Accept province code

        // 1. Try tbladdress with PROVINCE-AWARE lookup (if province provided)
        if (!empty($provinceCode)) {
            $zipInfo = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('province_code', $provinceCode)
                ->where(function ($query) use ($cityName) {
                    $query->where('description', 'LIKE', $cityName)
                          ->orWhere('description', 'LIKE', '%' . $cityName . '%');
                })
                ->select('zipcode', 'description')
                ->first();

            if ($zipInfo && !empty($zipInfo->zipcode)) {
                return response()->json([$zipInfo->zipcode]);
            }
        }

        // 2. Try tbladdress (fallback: match by city name only)
        $zipInfo = DB::table('tbladdress')
            ->where('address_type', 'citymun')
            ->where('description', 'LIKE', $cityName)
            ->select('zipcode')
            ->first();

        if ($zipInfo && !empty($zipInfo->zipcode)) {
            return response()->json([$zipInfo->zipcode]);
        }

        // 3. Fallback to old tblcity
        $zipcodeCollection = City::where('city', $cityName)->get();
        $zipcodes = $zipcodeCollection->pluck('Zipcode')->toArray();

        return response()->json($zipcodes);
    }

    // city form screen
    public function cityFormScreen(City $city)
    {

        $provinces = Province::all();

        if (!$city->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if ($roleLevel->Level <= $actions->RoleLevel) {
                return view('pages.city.city-create', [
                    'cities' => $city,
                    'provinces' => $provinces
                ]);
            } else {
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } else {
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Update')->first();
            if ($roleLevel->Level <= $actions->RoleLevel) {
                return view('pages.city.city-update', [
                    'cities' => $city,
                    'provinces' => $provinces
                ]);
            } else {
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createCity(Request $request)
    {

        // custom error message
        $messages = [
            'cityname.required' => 'This field is required.',
            'cityname.min' => 'Name is too short',
            'cityname.max' => 'Name is too long',
            'cityzip.required' => 'This field is required.',
            'cityzip.max' => 'Zipcode is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'cityname' => 'required|min:3|max:30',
            'cityzip' => 'required|max:10'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();

        $cityname = strip_tags($validatedData['cityname']);
        $provinceid = strip_tags($request->input('provinceid'));
        $cityzip = strip_tags($validatedData['cityzip']);

        // check if city exists
        $cityExists = City::where('City', $cityname)
            ->where('ProvinceId', $provinceid)
            ->where('Zipcode', $cityzip)
            ->first();

        if ($cityExists) {
            return redirect()->back()->with('duplicate', 'City already exists!')->withInput();
        }
        // create a new city
        else {
            try {

                $insertData = [
                    'city' => $cityname,
                    'provinceid' => $provinceid,
                    'zipcode' => $cityzip
                ];

                City::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] City ' . '[Action] Insert ' . '[Target] ' . $cityname);

                return redirect('/city')->with('success', 'Added new city!');
            } catch (\Exception $e) {
                return redirect('/city')->with('error', 'An error occurred while creating a new city.');
            }
        }
    }

    // update data
    public function updateCity(City $city, Request $request)
    {

        // custom error message
        $messages = [
            'cityname.required' => 'This field is required.',
            'cityname.min' => 'Name is too short',
            'cityname.max' => 'Name is too long',
            'cityzip.required' => 'This field is required.',
            'cityzip.max' => 'Zipcode is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'cityname' => 'required|min:3|max:30',
            'cityzip' => 'required|max:10'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();

        $cityname = strip_tags($validatedData['cityname']);
        $provinceid = strip_tags($request->input('provinceid'));
        $cityzip = strip_tags($validatedData['cityzip']);

        // check if city exists
        $cityExists = City::where('city', $cityname)
            ->where('provinceid', $provinceid)
            ->where('zipcode', $cityzip)
            ->first();

        if ($cityExists) {
            return redirect()->back()->with('duplicate', 'City already exists!')->withInput();
        }
        // update selected city
        else {
            try {

                $updateData = [
                    'city' => $cityname,
                    'provinceid' => $provinceid,
                    'zipcode' => $cityzip
                ];

                City::where('id', $city->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] City ' . '[Action] Update ' . '[Target ID] ' . $city->Id);

                return redirect('/city')->with('success', 'Selected city has been updated!');
            } catch (\Exception $e) {
                return redirect('/city')->with('error', 'An error occurred while updating a city.');
            }
        }
    }

    // delete city
    public function deleteCity(City $city)
    {

        try {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if ($roleLevel->Level <= $actions->RoleLevel) {
                City::where('id', $city->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] City ' . '[Action] Delete ' . '[Target ID] ' . $city->Id);

                return redirect('/city')->with('warning', 'Selected city has been deleted!');
            } else {
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/city')->with('error', 'An error occurred while deleting a city.');
        }
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH CITY ** //
    public function app_searchCities(Request $request)
    {

        $query = City::orderBy('city', 'asc')->get();
        return response()->json($query);
    }
}
