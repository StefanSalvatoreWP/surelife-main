<?php

namespace App\Http\Controllers;

use App\Models\Mcpr;
use App\Models\Role;
use App\Models\Month;
use App\Models\Actions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class McprController extends Controller
{
    
    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Mcpr::
            select(
                'tblmcprcalendar.*',
                'tblmcprcalendar.Id as mcprid',
                'tblmonth.Month'
            )
            ->leftJoin('tblmonth', 'tblmcprcalendar.monthid', '=', 'tblmonth.id');


            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('Year', 'like', "%$searchTerm%")
                        ->orWhere('tblmonth.Month', 'like', "%$searchTerm%")
                        ->orWhere('StartingDate', 'like', "%$searchTerm%")
                        ->orWhere('EndingDate', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.mcpr.mcpr');
    }

    // mcpr form screen
    public function McprFormScreen(Mcpr $mcpr){

        $mcprs = Mcpr::all();   
        
        if (!$mcpr->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.mcpr.mcpr-create', [
                    'mcprs' => $mcprs
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
                return view('pages.mcpr.mcpr-update', [
                    'mcprs' => $mcpr
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createMcpr(Request $request){

        // custom error message
        $messages = [
            'year.required' => 'This field is required.',
            'monthid.required' => 'This field is required.',
            'startdate.required' => 'This field is required.',
            'enddate.required' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'year' => 'required',
            'monthid' => 'required',
            'startdate' => 'required',
            'enddate' => 'required'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $year = strip_tags($validatedData['year']);
        $monthid = strip_tags($validatedData['monthid']);
        $startdate = strip_tags($validatedData['startdate']);
        $enddate = strip_tags($validatedData['enddate']);

        // check if mcpr exists
        $mcprExists = Mcpr::where('year', $year)
                ->where('monthid', $monthid)
                ->where('startingdate', $startdate)
                ->where('endingdate', $enddate)
                ->first();

        if($mcprExists){
            return redirect()->back()->with('duplicate', 'Mcpr already exists!')->withInput();
        }
        // insert new mcpr data
        else{
            try {
                
                $insertData = [
                    'year' => $year,
                    'monthid' => $monthid,
                    'startingdate' => $startdate,
                    'endingdate' => $enddate
                ];
        
                Mcpr::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] MCPR ' . '[Action] Insert ' . '[Target] ' . $startdate . '-' . $enddate);

                return redirect('/mcpr')->with('success', 'Added new mcpr!');
            } catch (\Exception $e) {
                return redirect('/mcpr')->with('error', 'An error occurred while creating a new mcpr.');
            }
        }
    }

    public function updateMcpr(Mcpr $mcpr, Request $request){

        // custom error message
        $messages = [
            'year.required' => 'This field is required.',
            'monthid.required' => 'This field is required.',
            'startdate.required' => 'This field is required.',
            'enddate.required' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'year' => 'required',
            'monthid' => 'required',
            'startdate' => 'required',
            'enddate' => 'required'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $year = strip_tags($validatedData['year']);
        $monthid = strip_tags($validatedData['monthid']);
        $startdate = strip_tags($validatedData['startdate']);
        $enddate = strip_tags($validatedData['enddate']);

        // check if mcpr exists
        $mcprExists = Mcpr::where('year', $year)
                ->where('monthid', $monthid)
                ->where('startingdate', $startdate)
                ->where('endingdate', $enddate)
                ->first();

        if($mcprExists){
            return redirect()->back()->with('duplicate', 'Mcpr already exists!')->withInput();
        }
        // update mcpr
        else{
            try {
                
                $updateData = [
                    'year' => $year,
                    'monthid' => $monthid,
                    'startingdate' => $startdate,
                    'endingdate' => $enddate
                ];
        
                Mcpr::where('id', $mcpr->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] MCPR ' . '[Action] Update ' . '[Target] ' . $mcpr->Id);

                return redirect('/mcpr')->with('success', 'Selected MCPR has been updated!');
            } catch (\Exception $e) {
                return redirect('/mcpr')->with('error', 'An error occurred while updating mcpr.');
            }
        }
    }

    // delete mcpr
    public function deleteMcpr(Mcpr $mcpr){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                Mcpr::where('id', $mcpr->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] MCPR ' . '[Action] Delete ' . '[Target] ' . $mcpr->Id);

                return redirect('/mcpr')->with('warning', 'Selected mcpr has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/mcpr')->with('error', 'An error occurred while deleting mcpr.');
        }
    }
}
