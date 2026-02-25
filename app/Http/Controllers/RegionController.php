<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Actions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class RegionController extends Controller
{
    // search regions - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Region::query();

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('tblregion.RegionName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.region.region');
    }

    // search data - get branch by region
    public function getBranchesByRegion(Request $request){

        $regionId = $request->input('regionId');
        $branches = Branch::where('regionid', $regionId)
            ->orderBy("branchname", "asc")
            ->get();

        return response()->json($branches);
    }

    // region form screen
    public function regionFormScreen(Region $region){

        $regions = Region::all();
        
        if (!$region->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){        
                return view('pages.region.region-create', [
                    'regions' => $regions
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
                return view('pages.region.region-update', [
                    'regions' => $region
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createRegion(Request $request){

        // custom error message
        $messages = [
            'regionname.required' => 'This field is required.',
            'regionname.min' => 'Name is too short',
            'regionname.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'regionname' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $regionname = strip_tags($validatedData['regionname']);
     
        // check if region exists
        $regionExists = Region::where('regionname', $regionname)->first();

        if($regionExists){
            return redirect()->back()->with('duplicate', 'Region already exists!')->withInput();
        }
        // create a new region
        else{
            try {
                
                $insertData = [
                    'regionname' => $regionname
                ];
        
                Region::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Region ' . '[Action] Insert ' . '[Target] ' . $regionname);

                return redirect('/region')->with('success', 'Added new region!');
            } catch (\Exception $e) {
                return redirect('/region')->with('error', 'An error occurred while creating a new region.');
            }
        }
    }

    // update data
    public function updateRegion(Region $region, Request $request){

        // custom error message
        $messages = [
            'regionname.required' => 'This field is required.',
            'regionname.min' => 'Name is too short',
            'regionname.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'regionname' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $regionname = strip_tags($validatedData['regionname']);
        
        // check if region exists
        $regionExists = Region::where('regionname', $regionname)->first();

        if($regionExists){
            return redirect()->back()->with('duplicate', 'Region already exists!')->withInput();
        }
        // update selected branch
        else{
            try {
                
                $updateData = [
                    'regionname' => $regionname
                ];
        
                Region::where('id', $region->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Region ' . '[Action] Update ' . '[Target] ' . $region->Id);

                return redirect('/region')->with('success', 'Selected region has been updated!');
            } catch (\Exception $e) {
                return redirect('/region')->with('error', 'An error occurred while updating a region.');
            }
        }
    }

    // delete region
    public function deleteRegion(Region $region){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){        
                Region::where('id', $region->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Region ' . '[Action] Delete ' . '[Target] ' . $region->Id);

                return redirect('/region')->with('warning', 'Selected region has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/region')->with('error', 'An error occurred while deleting a region.');
        }
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH REGIONS ** //
    public function app_getRegions(Request $request){

        $query = Region::orderBy('regionname', 'asc')->get();
        
        return response()->json($query);
    }
}
