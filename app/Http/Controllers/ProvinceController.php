<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Actions;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class ProvinceController extends Controller
{
    // search province - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Province::query();

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('tblprovince.Province', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.province.province');
    }

    // region form screen
    public function provinceFormScreen(Province $province){

        $provinces = Province::all();
        
        if (!$province->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){        
                return view('pages.province.province-create', [
                    'provinces' => $provinces
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
                return view('pages.province.province-update', [
                    'provinces' => $province
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createProvince(Request $request){

        // custom error message
        $messages = [
            'provincename.required' => 'This field is required.',
            'provincename.min' => 'Name is too short',
            'provincename.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'provincename' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $provincename = strip_tags($validatedData['provincename']);
     
        // check if province exists
        $provinceExists = Province::where('province', $provincename)->first();

        if($provinceExists){
            return redirect()->back()->with('duplicate', 'Province already exists!')->withInput();
        }
        // create a new province
        else{
            try {
                
                $insertData = [
                    'province' => $provincename
                ];
        
                Province::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Province ' . '[Action] Insert ' . '[Target] ' . $provincename);

                return redirect('/province')->with('success', 'Added new province!');
            } catch (\Exception $e) {
                return redirect('/province')->with('error', 'An error occurred while creating a new province.');
            }
        }
    }

    // update data
    public function updateProvince(Province $province, Request $request){

        // custom error message
        $messages = [
            'provincename.required' => 'This field is required.',
            'provincename.min' => 'Name is too short',
            'provincename.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'provincename' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $provincename = strip_tags($validatedData['provincename']);
        
        // check if region exists
        $provinceExists = Province::where('province', $provincename)->first();

        if($provinceExists){
            return redirect()->back()->with('duplicate', 'Province already exists!')->withInput();
        }
        // update selected branch
        else{
            try {
                
                $updateData = [
                    'province' => $provincename
                ];
        
                Province::where('id', $province->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Province ' . '[Action] Update ' . '[Target] ' . $province->Id);

                return redirect('/province')->with('success', 'Selected province has been updated!');
            } catch (\Exception $e) {
                return redirect('/province')->with('error', 'An error occurred while updating a province.');
            }
        }
    }

    // delete province
    public function deleteProvince(Province $province){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                Province::where('id', $province->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Province ' . '[Action] Delete ' . '[Target] ' . $province->Id);

                return redirect('/province')->with('warning', 'Selected province has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/province')->with('error', 'An error occurred while deleting a province.');
        }
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH PROVINCE ** //
    public function app_searchProvince(Request $request){

        $query = Province::orderBy('province', 'asc')->get();
        return response()->json($query);
    }
}
