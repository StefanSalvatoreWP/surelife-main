<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Role;
use App\Models\Actions;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class BankController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){
   
        if ($request->ajax()) {

            $query = Bank::
            select(
                'tblbank.*', 'tblbank.id as bankid',
                'tblbankaccount.AccountNumber'
            )
            ->leftJoin('tblbankaccount', 'tblbank.id', '=', 'tblbankaccount.bankid')
            ->whereNotNull('tblbankaccount.AccountNumber');

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('BankName', 'like', "%$searchTerm%")
                        ->orWhere('tblbankaccount.AccountNumber', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.bank.bank');
    }

    // search data - get bank accounts by bank
    public function getBankAccountsByBank(Request $request){

        $bankId = $request->input('bankId');
        $bankAccounts = BankAccount::where('bankid', $bankId)
            ->orderBy("id", "asc")
            ->get();

        return response()->json($bankAccounts);
    }

    // bank form screen
    public function bankFormScreen(Bank $bank, Request $request){

        if (!$bank->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.bank.bank-create');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
        else{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Update')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $accountno = $request['account_number'];
                $currentBank = Bank::select('tblbank.*', 'tblbank.id as bankid', 'tblbankaccount.AccountNumber', 'tblbankaccount.BankId')
                ->leftJoin('tblbankaccount', 'tblbank.id', '=', 'tblbankaccount.bankid') 
                ->where('tblbank.Id', $bank->Id)
                ->where('tblbankaccount.AccountNumber', $accountno)
                ->first();

                return view('pages.bank.bank-update', [
                    'banks' => $currentBank
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }
    // bank account form screen
    public function bankAccountFormScreen(){

        $banks = Bank::orderBy("bankname", "asc")->get();

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Insert')->first();
        if($roleLevel->Level <= $actions->RoleLevel){
            return view('pages.bank.bankaccount-create', [
                'banks' => $banks                
            ]);
        }
        else{
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }
    // bank delete form screen
    public function bankDeleteFormScreen(Bank $bank, Request $request){

        $banks = Bank::all();

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Delete')->first();
        if($roleLevel->Level <= $actions->RoleLevel){
            return view('pages.bank.bank-delete', [
                'banks' => $banks                
            ]);
        }
        else{
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }
    // insert new data
    public function createBank(Request $request){

        // custom error message
        $messages = [
            'bankname.required' => 'This field is required.',
            'bankname.min' => 'Name is too short',
            'bankname.max' => 'Name is too long',
            'accountno.required' => 'This field is required.',
            'accountno.min' => 'account no. is too short',
            'accountno.max' => 'account no. is too long',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'bankname' => 'required|min:3|max:30',
            'accountno' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $bankname = strip_tags($validatedData['bankname']);
        $accountno = strip_tags($validatedData['accountno']);

        // check if bank exists
        $bankExists = Bank::select('tblbank.*', 'tblbankaccount.AccountNumber', 'tblbankaccount.BankId')
                ->leftJoin('tblbankaccount', 'tblbank.id', '=', 'tblbankaccount.bankid') 
                ->where('bankname', $bankname)
                ->where('accountnumber', $accountno)
                ->first();

        if($bankExists){
            return redirect()->back()->with('duplicate', 'Bank already exists')->withInput();
        }
        // create a new bank
        else{
            try {
                
                $insertBankData = [
                    'bankname' => $bankname
                ];
        
                $newlyInsertedId = Bank::insertGetId($insertBankData);
                $insertBankAccountData = [
                    'accountnumber' => $accountno,
                    'bankid' => $newlyInsertedId
                ];
                BankAccount::insert($insertBankAccountData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Bank ' . '[Action] Insert ' . '[Target] ' . $bankname);

                return redirect('/bank')->with('success', 'Added new bank!');
            } catch (\Exception $e) {
                return redirect('/bank')->with('error', 'An error occurred while creating a new bank.');
            }
        }
    }

    public function createBankAccount(Request $request){

        // custom error message
        $messages = [
            'bankid.required' => 'This field is required.',
            'accountno.required' => 'This field is required.',
            'accountno.min' => 'account no. is too short',
            'accountno.max' => 'account no. is too long',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'bankid' => 'required',
            'accountno' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $bankid = strip_tags($validatedData['bankid']);
        $accountno = strip_tags($validatedData['accountno']);

        // check if bank account exists
        $bankAccountNoExists = BankAccount::where('accountnumber', $accountno)
                ->where('bankid', $bankid)
                ->first();

        if($bankAccountNoExists){
            return redirect()->back()->with('duplicate', 'Bank already exists!')->withInput();
        }
        // create a new bank account
        else{
            try {
               
                $insertBankAccountData = [
                    'accountnumber' => $accountno,
                    'bankid' => $bankid
                ];
                BankAccount::insert($insertBankAccountData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Bank Account ' . '[Action] Insert ' . '[Target] ' . $accountno);

                return redirect('/bank')->with('success', 'Added new account for the selected bank!');
            } catch (\Exception $e) {
                return redirect('/bank')->with('error', 'An error occurred while creating a new bank account.');
            }
        }
    }

    // update data
    public function updateBank(Bank $bank, Request $request){

        // custom error message
        $messages = [
            'bankname.required' => 'This field is required.',
            'bankname.min' => 'Name is too short',
            'bankname.max' => 'Name is too long',
            'accountno.required' => 'This field is required.',
            'accountno.min' => 'account no. is too short',
            'accountno.max' => 'account no. is too long',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'bankname' => 'required|min:3|max:30',
            'accountno' => 'required|min:3|max:30'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $bankname = strip_tags($validatedData['bankname']);
        $accountno = strip_tags($validatedData['accountno']);

        // check if bank exists
        $bankExists = Bank::select('tblbank.*', 'tblbankaccount.AccountNumber', 'tblbankaccount.BankId')
                ->leftJoin('tblbankaccount', 'tblbank.id', '=', 'tblbankaccount.bankid') 
                ->where('bankname', $bankname)
                ->where('accountnumber', $accountno)
                ->first();

        if($bankExists){
            return redirect()->back()->with('duplicate', 'Bank already exists!')->withInput();
        }
        // update bank
        else{
            try {
               
                $prev_accountno = $request['account_number'];
                $updateBankAccountData = [
                    'accountnumber' => $accountno,
                ];

                BankAccount::where('bankid', $bank->Id)
                ->where('accountnumber', $prev_accountno)
                ->update($updateBankAccountData);

                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Bank ' . '[Action] Update ' . '[Target ID] ' . $bank->Id);

                return redirect('/bank')->with('success', 'Selected bank has been updated!');
            } catch (\Exception $e) {
                return redirect('/bank')->with('error', 'An error occurred while updating bank.');
            }
        }
    }

    // delete bank account only
    public function deleteBankAccount(Bank $bank, Request $request){

        try{
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $accountno = $request['account_number'];

                $currentBank = Bank::select('tblbank.*', 'tblbankaccount.AccountNumber', 'tblbankaccount.BankId')
                ->leftJoin('tblbankaccount', 'tblbank.id', '=', 'tblbankaccount.bankid') 
                ->where('tblbank.Id', $bank->Id)
                ->where('accountnumber', $accountno)
                ->first();

                BankAccount::where('accountnumber', $currentBank->AccountNumber)
                ->where('bankid', $currentBank->Id)
                ->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Bank ' . '[Action] Delete ' . '[Target ID] ' . $bank->Id);

                return redirect('/bank')->with('warning', 'Selected bank account has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/bank')->with('error', 'An error occurred while deleting a bank account.');
        }
    }

    // delete bank and all its accounts
    public function deleteBank(Bank $bank){

        try{

            BankAccount::where('bankid', $bank->Id)->delete();
            Bank::where('id', $bank->Id)->delete();

            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Bank ' . '[Action] Delete ' . '[Target ID] ' . $bank->Id);

            return redirect('/bank')->with('warning', 'Selected bank and its accounts has been deleted!');
        } catch (\Exception $e) {
            return redirect('/bank')->with('error', 'An error occurred while deleting a bank.');
        }
    }
}
