<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Actions;
use App\Models\Deposit;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class DepositController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Deposit
            ::select(
                'tblbankdeposit.*', 
                'tblbankdeposit.id as depoid', 
                'tblbranch.branchname as BranchName',
                'tblbankaccount.accountnumber as BankAccountNo',
                'tblbank.bankname as BankName',
                'tblstaff.LastName',
                'tblstaff.FirstName',
                'tblstaff.MiddleName'
            )
            ->leftJoin('tblbranch', 'tblbankdeposit.branchid', '=', 'tblbranch.id')
            ->leftJoin('tblbankaccount', 'tblbankdeposit.bankaccountid', '=', 'tblbankaccount.id')
            ->leftJoin('tblbank', 'tblbankaccount.bankid', '=', 'tblbank.id')
            ->leftJoin('tblstaff', 'tblbankdeposit.staffid', '=', 'tblstaff.id');

            if ($request->filled('bank_id')) {
                $query->where('tblbankaccount.bankid', $request->input('bank_id'));
            }

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('SequenceNo', 'like', "%$searchTerm%")
                        ->orWhere('tblbankaccount.accountnumber', 'like', "%$searchTerm%")
                        ->orWhere('BranchName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        $banks = Bank::orderBy('bankname', 'asc')->get();

        return view('pages.deposit.deposit', [
            'banks' => $banks
        ]);
    }

    // deposit form screen
    public function depositFormScreen(Deposit $deposit){

        $regions = Region::orderBy("regionname", "asc")->get();
        $banks = Bank::orderBy("bankname", "asc")->get();
        
        if (!$deposit->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.deposit.deposit-create', [
                    'deposits' => $deposit,
                    'regions' => $regions,
                    'banks' => $banks
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
        else{

            $regionId = Branch::where('id', $deposit->BranchId)->value('regionid');
            $bankId = BankAccount::where('id', $deposit->BankAccountId)->value('bankid');

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Update')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.deposit.deposit-update', [
                    'deposits' => $deposit,
                    'regions' => $regions,
                    'regionid' => $regionId,
                    'bankid' => $bankId,
                    'banks' => $banks
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createDeposit(Request $request){

        // custom error message
        $messages = [
            'regionid.required' => 'This field is required.',
            'branchid.required' => 'This field is required.',
            'bankid.required' => 'This field is required.',
            'bankaccountid.required' => 'This field is required.',
            'depositamount.required' => 'This field is required',
            'sequenceno.required' => 'This field is required.',
            'sequenceno.min' => 'Sequence No. is too short.',
            'sequenceno.max' => 'Sequence No. is too long.',
            'depositdate.required' => 'This field is required.',
            'depositedbystaffid.required' => 'This field is required'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'branchid' => 'required',
            'regionid' => 'required',
            'bankid' => 'required',
            'bankaccountid' => 'required',
            'depositamount' => 'required',
            'sequenceno' => 'required|min:3|max:100',
            'depositdate' => 'required',
            'depositedbystaffid' => 'required',
            'depositslip' => 'image|mimes:jpeg,png,jpg,gif',
            'depositnote' => 'nullable'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $branchid = strip_tags($validatedData['branchid']);
        $bankaccountid = strip_tags($validatedData['bankaccountid']);
        $depositamount = strip_tags($validatedData['depositamount']);
        $sequenceno = strip_tags($validatedData['sequenceno']);
        $staffid = strip_tags($validatedData['depositedbystaffid']);
        $depositdate = strip_tags($validatedData['depositdate']);
        $note = strip_tags($validatedData['depositnote'] ?? '');
        
        // check if deposit exists
        $depositExists = Deposit::where('sequenceno', $sequenceno)
                ->where('branchid', $branchid)
                ->where('bankaccountid', $bankaccountid)
                ->first();

        if($depositExists){
            return redirect()->back()->with('duplicate', 'Deposit information already exists!')->withInput();
        }

        // create a new deposit
        else{
            try {
                
                $imageName = 'Not available';
                if ($request->hasFile('depositslip')) {
                    
                    $image = $request->file('depositslip');
                    $imageName = $sequenceno . '_' . time() . '.' . $image->getClientOriginalExtension();
                    $image->move(base_path('uploads/deposits'), $imageName); 
                }

                $insertData = [
                    'staffid' => $staffid,
                    'branchid' => $branchid,
                    'sequenceno' => $sequenceno,
                    'depositedamount' => $depositamount,
                    'date' => $depositdate,
                    'bankaccountid' => $bankaccountid,
                    'note' => $note,
                    'depositslip' => $imageName,
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d H:i:s")
                ];
        
                Deposit::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Deposit ' . '[Action] Insert ' . '[Target] ' . $sequenceno);

                return redirect('/deposit')->with('success', 'Added new deposit!');
            } catch (\Exception $e) {
                return redirect('/deposit')->with('error', 'An error occurred while creating a new deposit.');
            }
        }
    }

    // update data
    public function updateDeposit(Deposit $deposit, Request $request){

        // custom error message
        $messages = [
            'regionid.required' => 'This field is required.',
            'branchid.required' => 'This field is required.',
            'bankid.required' => 'This field is required.',
            'bankaccountid.required' => 'This field is required.',
            'depositamount.required' => 'This field is required',
            'sequenceno.required' => 'This field is required.',
            'sequenceno.min' => 'Sequence No. is too short.',
            'sequenceno.max' => 'Sequence No. is too long.',
            'depositdate.required' => 'This field is required.',
            'depositedbystaffid.required' => 'This field is required'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'branchid' => 'required',
            'regionid' => 'required',
            'bankid' => 'required',
            'bankaccountid' => 'required',
            'depositamount' => 'required',
            'sequenceno' => 'required|min:3|max:100',
            'depositdate' => 'required',
            'depositedbystaffid' => 'required',
            'depositslip' => 'image|mimes:jpeg,png,jpg,gif',
            'depositnote' => 'nullable'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $branchid = strip_tags($validatedData['branchid']);
        $bankaccountid = strip_tags($validatedData['bankaccountid']);
        $depositamount = strip_tags($validatedData['depositamount']);
        $sequenceno = strip_tags($validatedData['sequenceno']);
        $staffid = strip_tags($validatedData['depositedbystaffid']);
        $depositdate = strip_tags($validatedData['depositdate']);
        $note = strip_tags($validatedData['depositnote'] ?? '');
        
        // update deposit data
        try {
            
            $imageName = $deposit->DepositSlip;
            if ($request->hasFile('depositslip')) {

                $oldImagePath = base_path('uploads/deposits/' . $deposit->DepositSlip);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); 
                }

                $image = $request->file('depositslip');
                $imageName = $sequenceno . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(base_path('uploads/deposits'), $imageName); 
            }

            $updateData = [
                'staffid' => $staffid,
                'branchid' => $branchid,
                'sequenceno' => $sequenceno,
                'depositedamount' => $depositamount,
                'date' => $depositdate,
                'bankaccountid' => $bankaccountid,
                'note' => $note,
                'depositslip' => $imageName,
                'modifiedby' => session('user_id'),
                'datemodified' => date("Y-m-d h:i:s")
            ];
    
            Deposit::where('id', $deposit->Id)
            ->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Deposit ' . '[Action] Update ' . '[Target] ' . $deposit->Id);

            return redirect('/deposit')->with('success', 'Selected deposit information has been updated!');
        } catch (\Exception $e) {
            return redirect('/deposit')->with('error', 'An error occured while updating the selected deposit data.');
        }
    }

    // delete deposit
    public function deleteDeposit(Deposit $deposit){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                Deposit::where('id', $deposit->Id)
                ->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Deposit ' . '[Action] Delete ' . '[Target] ' . $deposit->Id);

                return redirect('/deposit')->with('warning', 'Selected deposit data has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/deposit')->with('error', 'An error occurred while deleting the selected deposit data.');
        }
    }
}
