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

class BranchController extends Controller
{

    // search all - table
    public function searchAll(Request $request){
        
        if ($request->ajax()) {

            $query = Branch::
            select(
                'tblbranch.*', 'tblbranch.Id as bid',
                'tblregion.RegionName as RegionName'
            )
            ->leftJoin('tblregion', 'tblbranch.regionid', '=', 'tblregion.id');

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('BranchName', 'like', "%$searchTerm%")
                    ->orWhere('tblregion.RegionName', 'like', "%$searchTerm%");
                });
            }
           
            return DataTables::of($query)->toJson();
        }

        return view('pages.branch.branch');
    }

    // branch form screen
    public function branchFormScreen(Branch $branch){

        $regions = Region::orderBy("regionname", "asc")->get();
        
        if (!$branch->exists) {
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){        
                return view('pages.branch.branch-create', [
                    'branches' => $branch,
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
                return view('pages.branch.branch-update', [
                    'branches' => $branch,
                    'regions' => $regions
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createBranch(Request $request){

        // custom error message
        $messages = [
            'branchname.required' => 'This field is required.',
            'branchname.min' => 'Name is too short',
            'branchname.max' => 'Name is too long'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'branchname' => 'required|min:3|max:30',
            'regionid' => 'required'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $branchname = strip_tags($validatedData['branchname']);
        $regionid = strip_tags($validatedData['regionid']);

        // check if branch exists
        $branchExists = Branch::where('branchname', $branchname)
                ->where('regionid', $regionid)
                ->first();

        if($branchExists){
            return redirect()->back()->with('duplicate', 'Branch already exists!')->withInput();
        }
        // create a new branch
        else{
            try {
                
                $insertData = [
                    'branchname' => $branchname,
                    'regionid' => $regionid
                ];
        
                Branch::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Branch ' . '[Action] Insert ' . '[Target] ' . $branchname);

                return redirect('/branch')->with('success', 'Added new branch!');
            } catch (\Exception $e) {
                return redirect('/branch')->with('error', 'An error occurred while creating a new branch.');
            }
        }
    }

    // update data
    public function updateBranch(Branch $branch, Request $request){

        // custom error message
        $messages = [
            'branchname.required' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'branchname' => 'required',
            'regionid' => 'required'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $branchname = strip_tags($validatedData['branchname']);
        $regionid = strip_tags($validatedData['regionid']);

        // check if branch exists
        $branchExists = Branch::where('branchname', $branchname)
                ->where('regionid', $regionid)
                ->first();

        if($branchExists){
            return redirect()->back()->with('duplicate', 'Branch already exists!')->withInput();
        }
        // update selected branch
        else{
            try {
                
                $updateData = [
                    'branchname' => $branchname,
                    'regionid' => $regionid
                ];
        
                Branch::where('id', $branch->Id)
                ->update($updateData);
        
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Branch ' . '[Action] Update ' . '[Target ID] ' . $branch->Id);

                return redirect('/branch')->with('success', 'Selected branch has been updated!');
            } catch (\Exception $e) {
                return redirect('/branch')->with('error', 'An error occured while updating the selected branch.');
            }
        }
    }

    // delete branch
    public function deleteBranch(Branch $branch){

        try{
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){        

                Branch::where('id', $branch->Id)
                ->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Branch ' . '[Action] Delete ' . '[Target ID] ' . $branch->Id);

                return redirect('/branch')->with('warning', 'Selected branch has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/branch')->with('error', 'An error occurred while deleting a branch.');
        }
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH BRANCHES ** //
    public function app_getBranches(Request $request){

        $query = Branch::orderBy('branchname', 'asc')->get();
        
        return response()->json($query);
    }
}
