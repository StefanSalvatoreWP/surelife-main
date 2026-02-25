<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Actions;
use App\Models\Contract;
use Illuminate\Http\Request;
use App\Models\ContractBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class ContractBatchController extends Controller
{
    // search contractbatch data - tables
    public function searchContractBatchAll(Request $request){

        if ($request->ajax()) {

            $query = ContractBatch
            ::select(
                'tblcontractbatch.*',
                'tblcontractbatch.id as contractbatchid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                Staff::raw('(SELECT CONCAT(LastName, " ", FirstName, " ", MiddleName) FROM `tblstaff` WHERE `Id` = tblcontractbatch.assignedstaffid) as Assigned'),
                Contract::raw('(SELECT COUNT(`ContractBatchId`) FROM `tblcontract` WHERE `Status` = 1 AND `ContractBatchId` = tblcontractbatch.id) as countAvailContract')
            )
            ->leftJoin('tblregion', 'tblcontractbatch.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblcontractbatch.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblstaff', 'tblcontractbatch.AssignedStaffId', '=', 'tblstaff.id');

            if ($request->filled('branch_id')) {
                $query->where('tblcontractbatch.BranchId', $request->input('branch_id'));
            }

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('BatchCode', 'like', "%$searchTerm%")
                        ->orWhere('tblregion.RegionName', 'like', "%$searchTerm%")
                        ->orWhere('tblbranch.BranchName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        $branches = Branch::orderBy('branchname', 'asc')->get();

        return view('pages.contract.contractbatch', [
            'branches' => $branches
        ]);
    }

    // search staff to assign contract batch
    public function getStaffAssignContract(Request $request){

        if($request->ajax()) {

            $sel_contractbatchId = $request['contractbatchid'];
            $contractbatch = ContractBatch::query()->where('id', $sel_contractbatchId)->first();

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
            ->where('tblstaff.regionid', $contractbatch->RegionId)
            ->where('tblstaff.branchid', $contractbatch->BranchId);

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

    // search contract series data - tables
    public function searchContractSeriesByContractId(ContractBatch $contractbatch, Request $request){

        return view('pages.contract.contractseries', [
            'contractbatch' => $contractbatch
        ]);
    }

    // get contract batch series
    public function gerContractSeriesByContractBatchId(ContractBatch $contractbatch, Request $request){

        if($request->ajax()){

            $data = Contract
            ::select('tblcontract.*', 'tblclient.LastName', 'tblclient.FirstName', 'tblclient.MiddleName')
            ->leftJoin('tblclient', 'tblcontract.clientid', '=', 'tblclient.id')
            ->where('contractbatchid', $request->input('contractbatchid'))
            ->orderBy("tblcontract.id", "desc");

            return DataTables::of($data)->toJson();
        }

        return view('pages.contract.contractseries', [
            'contractbatch' => $contractbatch
        ]);
    }

    // contract batch form screen
    public function contractBatchFormScreen(ContractBatch $contractBatch){

        $regions = Region::orderBy("regionname", "asc")->get();
        $branches = Branch::orderBy("branchname", "asc")->get();

        if (!$contractBatch->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){   
                return view('pages.contract.contractbatch-create', [
                    'contractbatches' => $contractBatch,
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
                return view('pages.contract.contractbatch-update', [
                    'contractbatches' => $contractBatch,
                    'regions' => $regions,
                    'branches' => $branches
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // assign contract batch - screen
    public function assignContractBatchScreen(ContractBatch $contractbatch, Request $request){

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Assign Contract Batch')->first();

        if($roleLevel->Level <= $actions->RoleLevel){   
            return view('pages.contract.contractbatch-assign', [
                'contractbatch' => $contractbatch
            ]);
        }
        else{
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // insert new data
    public function createContractBatch(Request $request){

        // custom error message
        $messages = [
            'begcontractnum.numeric' => 'This field is required',
            'begcontractnum.min' => 'Invalid number',
            'endcontractnum.numeric' => 'This field is required',
            'endcontractnum.min' => 'Invalid number',
            'regionid.required' => 'This field is required.',
            'branchid.required' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'begcontractnum' => 'numeric|min:0',
            'endcontractnum' => 'numeric|min:0',
            'regionid' => 'required',
            'branchid' => 'required'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $begcontractnum = strip_tags($validatedData['begcontractnum']);
        $endcontractnum = strip_tags($validatedData['endcontractnum']);
        $regionid = strip_tags($validatedData['regionid']);
        $branchid = strip_tags($validatedData['branchid']);
       
        // check if contract batch exists
        $contractBatchExists = ContractBatch::where('beginning', $begcontractnum)
                ->where('ending', $endcontractnum)
                ->where('regionid', $regionid)
                ->where('branchid', $branchid)
                ->first();

        if($contractBatchExists){
            return redirect()->back()->with('duplicate', 'Batch already exist!')->withInput();
        }
        // create a new contract batch
        else{
            try {
                
                $insertData = [
                    'batchcode' => $begcontractnum."-".$endcontractnum,
                    'beginning' => $begcontractnum,
                    'ending' => $endcontractnum,
                    'regionid' => $regionid,
                    'branchid' => $branchid
                ];

                // create contract series on the contract series table
                $unusedStatus = 1;
                $contractBatchId = ContractBatch::insertGetId($insertData);
        
                $records = [];
                for ($i = $begcontractnum; $i <= $endcontractnum; $i++) {
                    $records[] = [
                        'ContractBatchId' => $contractBatchId,
                        'ContractNumber' => $i,
                        'ClientId' => "0",
                        'Status' => $unusedStatus,
                        'Deleted' => '0',
                        'Remarks' => "N/A"
                    ];
                }
                Contract::insert($records);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Contract Batch ' . '[Action] Insert ' . '[Target] ' . $begcontractnum . '-' . $endcontractnum);

                return redirect('/contractbatch')->with('success', 'Added new contract batch!');
            } catch (\Exception $e) {
                return redirect('/contractbatch')->with('error', 'An error occurred while adding new contract batch.');
            }
        }
    }

    // assign contract batch
    public function assignContractBatch(Request $request){

        $contractbatchId = $request['contractbatchid'];
        $staffid = $request['staffid'];

        try{

            $updateData = [
                'assignedstaffid' => $staffid
            ];

            ContractBatch::where('id', $contractbatchId)
            ->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Contract Batch ' . '[Action] Assign ' . '[Target] ' . $contractbatchId);

            return redirect('/contractbatch')->with('success', 'Selected Contract Batch has been successfully assigned!');
        }
        catch(\Exception $e){
            return redirect('/contractbatch')->with('error', 'An error occurred with the assign process.');
        }
    }

    // delete contract batch
    public function deleteContractBatch(ContractBatch $contractbatch){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                // contract series
                Contract::where('contractbatchid', $contractbatch->Id)->delete();

                // contract batch
                ContractBatch::where('id', $contractbatch->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Contract Batch ' . '[Action] Delete ' . '[Target] ' . $contractbatch->Id);

                return redirect('/contractbatch')->with('warning', 'Selected contract batch has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/contractbatch')->with('error', 'An error occurred while deleting selected contract batch.');
        }
    }
}
