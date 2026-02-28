<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Region;
use App\Models\Actions;
use App\Models\OrBatch;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\OfficialReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class OrBatchController extends Controller
{
    // search orbatch data - tables
    public function searchOrBatchAll(Request $request){

        if ($request->ajax()) {

            $query = OrBatch::
            select(
                'tblorbatch.*',
                'tblorbatch.id as orbatchid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                Staff::raw('(SELECT CONCAT(LastName, " ", FirstName, " ", MiddleName) FROM `tblstaff` WHERE `Id` = tblorbatch.assignedstaffid) as Assigned'),
                OrBatch::raw('CASE WHEN tblorbatch.Type = 1 THEN "Standard" ELSE "Virtual" END AS Type'),
                OfficialReceipt::raw('(SELECT COUNT(`ORBatchId`) FROM `tblofficialreceipt` WHERE `Status` = 1 AND `ORBatchId` = tblorbatch.id) as countAvailOR')
            )
            ->leftJoin('tblregion', 'tblorbatch.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblorbatch.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblstaff', 'tblorbatch.AssignedStaffId', '=', 'tblstaff.id');

            // Filter by branch if provided
            if (!empty($request->input('branch'))) {
                $branchFilter = $request->input('branch');
                $query->where('tblbranch.BranchName', $branchFilter);
            }

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('SeriesCode', 'like', "%$searchTerm%")
                        ->orWhere('BatchCode', 'like', "%$searchTerm%")
                        ->orWhere('tblregion.RegionName', 'like', "%$searchTerm%")
                        ->orWhere('tblbranch.BranchName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        $branches = Branch::orderBy("branchname", "asc")->get();
        return view('pages.officialreceipt.orbatch', [
            'branches' => $branches
        ]);
    }

    // search staff to assign or batch
    public function getStaffAssignOr(Request $request){

        if($request->ajax()) {

            $sel_orbatchId = $request['orbatchid'];
            $orbatch = OrBatch::query()->where('id', $sel_orbatchId)->first();

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
            ->leftJoin('tblbranch', 'tblstaff.branchid', '=', 'tblbranch.id')
            ->where('tblstaff.regionid', $orbatch->RegionId)
            ->where('tblstaff.branchid', $orbatch->BranchId);

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
    }

    // search orseries data - tables
    public function searchOrSeriesByOrId(OrBatch $orbatch, Request $request){

        return view('pages.officialreceipt.orseries', [
            'orbatch' => $orbatch
        ]);
    }

    // get orseries
    public function getOrSeriesByOrBatchId(OrBatch $orbatch, Request $request){

        if($request->ajax()){

            // Optimized query with left join to get client data in one query
            $orserieses = OfficialReceipt::select(
                    'tblofficialreceipt.Id',
                    'tblofficialreceipt.ornumber as ORNumber',
                    'tblofficialreceipt.status as Status',
                    'tblofficialreceipt.remarks as Remarks',
                    'tblclient.LastName',
                    'tblclient.FirstName',
                    'tblclient.MiddleName'
                )
                ->leftJoin('tblpayment', function($join) {
                    $join->on('tblofficialreceipt.ornumber', '=', 'tblpayment.orno')
                         ->on('tblofficialreceipt.Id', '=', 'tblpayment.orid');
                })
                ->leftJoin('tblclient', 'tblpayment.clientid', '=', 'tblclient.id')
                ->where('tblofficialreceipt.orbatchid', $request->input('orbatchid'))
                ->orderBy("tblofficialreceipt.id", "desc")
                ->get();

            $data = [];
            foreach ($orserieses as $orseries) {
                $client = null;
                if ($orseries->LastName) {
                    $client = [
                        'LastName' => $orseries->LastName,
                        'FirstName' => $orseries->FirstName,
                        'MiddleName' => $orseries->MiddleName,
                    ];
                }
                
                $data[] = [
                    'Id' => $orseries->Id,
                    'ORNumber' => $orseries->ORNumber,
                    'Status' => $orseries->Status,
                    'Remarks' => $orseries->Remarks,
                    'Client' => $client,
                ];
            }

            return DataTables::of($data)->toJson();
        }
        
        return view('pages.officialreceipt.orseries', [
            'orbatch' => $orbatch
        ]);
    }

    // orbatch form screen
    public function orbatchFormScreen(OrBatch $orbatch){

        $regions = Region::orderBy("regionname", "asc")->get();
        $branches = Branch::orderBy("branchname", "asc")->get();

        if (!$orbatch->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.officialreceipt.orbatch-create', [
                    'orbatches' => $orbatch,
                    'regions' => $regions,
                    'branches' => $branches
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
                return view('pages.officialreceipt.orbatch-update', [
                    'orbatches' => $orbatch,
                    'regions' => $regions,
                    'branches' => $branches
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // assign or batch - screen
    public function assignOrBatchScreen(OrBatch $orbatch, Request $request){

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Assign OR Batch')->first();

        if($roleLevel->Level <= $actions->RoleLevel){
            return view('pages.officialreceipt.orbatch-assign', [
                'orbatch' => $orbatch
            ]);
        }
        else{
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // insert new data
    public function createOrBatch(Request $request){

        // custom error message
        $messages = [
            'seriescode.required' => 'This field is required.',
            'seriescode.max' => 'Name is too long',
            'startornum.numeric' => 'This field is required',
            'startornum.min' => 'Invalid number',
            'endornum.numeric' => 'This field is required',
            'endornum.min' => 'Invalid number',
            'regionid.required' => 'This field is required.',
            'branchid.required' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'seriescode' => 'required|max:30',
            'startornum' => 'numeric|min:0',
            'endornum' => 'numeric|min:0',
            'regionid' => 'required',
            'branchid' => 'required',
            'ortype' => 'required'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $seriescode = strip_tags($validatedData['seriescode']);
        $startornum = strip_tags($validatedData['startornum']);
        $endornum = strip_tags($validatedData['endornum']);
        $regionid = strip_tags($validatedData['regionid']);
        $branchid = strip_tags($validatedData['branchid']);
        $type = strip_tags($validatedData['ortype']);

        // check if or batch exists
        $orbatchExists = OrBatch::where('seriescode', $seriescode)
                ->where('start', $startornum)
                ->where('end', $endornum)
                ->where('regionid', $regionid)
                ->where('branchid', $branchid)
                ->where('type', $type)
                ->first();

        if($orbatchExists){
            return redirect()->back()->with('duplicate', 'Batch already exist!')->withInput();
        }
        // create a new or batch
        else{
            try {
                
                $insertData = [
                    'batchcode' => $startornum."-".$endornum,
                    'seriescode' => $seriescode,
                    'start' => $startornum,
                    'end' => $endornum,
                    'regionid' => $regionid,
                    'branchid' => $branchid,
                    'type' => $type
                ];
        
                // create or series on the orseries table
                $unusedStatus = 1;                
                $orbatchId = OrBatch::insertGetId($insertData);

                $records = [];
                for ($i = $startornum; $i <= $endornum; $i++) {
                    $records[] = [
                        'orbatchid' => $orbatchId,
                        'ornumber' => $i,
                        'status' => $unusedStatus,
                        'deleted' => '0',
                        'remarks' => "N/A"
                    ];
                }
                OfficialReceipt::insert($records);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] OR Batch ' . '[Action] Insert ' . '[Target] ' . $startornum."-".$endornum);

                return redirect('/orbatch')->with('success', 'Added new official receipt batch!');
            } catch (\Exception $e) {
                return redirect('/orbatch')->with('error', 'An error occurred while creating a new official receipt batch.');
            }
        }
    }

    // assign or batch
    public function assignOrBatch(Request $request){

        $orbatchId = $request['orbatchid'];
        $staffid = $request['staffid'];

        try{

            $updateData = [
                'assignedstaffid' => $staffid
            ];

            OrBatch::where('id', $orbatchId)
            ->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] OR Batch ' . '[Action] Update ' . '[Target] ' . $orbatchId);

            return redirect('/orbatch')->with('success', 'Selected OR Batch has been successfully assigned!');
        }
        catch(\Exception $e){
            return redirect('/orbatch')->with('error', 'An error occurred with the assign process.');
        }
    }

    // delete orbatch
    public function deleteOrBatch(OrBatch $orbatch){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                //or series
                OfficialReceipt::where('orbatchid', $orbatch->Id)->delete();

                // or batch
                OrBatch::where('id', $orbatch->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] OR Batch ' . '[Action] Update ' . '[Target] ' . $orbatch->Id);

                return redirect('/orbatch')->with('warning', 'Selected official receipt batch has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/orbatch')->with('error', 'An error occurred while deleting selected official receipt batch.');
        }
    }
}
