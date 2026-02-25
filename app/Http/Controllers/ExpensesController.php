<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Actions;
use App\Models\Expenses;
use Illuminate\Http\Request;
use App\Models\ExpenseDescription;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2024 SilverDust) S. Maceren */

class ExpensesController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){

        if($request->ajax()){

            $query = Expenses::select(
                'tblexpenses.id as exid',
                'tblbranch.id',
                'tblbranch.BranchName as branchname',
                'tblexpensesdescription.description',
                'tblexpenses.amount',
                'tblexpenses.note',
                'tblexpenses.image'
            )
            ->leftJoin('tblbranch', 'tblbranch.id', '=', 'tblexpenses.branchid')
            ->leftJoin('tblexpensesdescription', 'tblexpensesdescription.id', '=', 'tblexpenses.expensesdescid');

            // Filter by branch if provided
            if (!empty($request->input('branch'))) {
                $branchFilter = $request->input('branch');
                $query->where('tblbranch.BranchName', $branchFilter);
            }

            $query->orderBy('exid', 'desc');

            return DataTables::of($query)->toJson();
        }

        // Get all branches for the filter dropdown
        $branches = Branch::orderBy('BranchName', 'asc')->get();

        return view('pages.expense.expense', compact('branches'));
    }

    // expense form screen
    public function expenseFormScreen(Expenses $expense){

        $regions = Region::orderBy("regionname", "asc")->get();
        $expense_desc = ExpenseDescription::orderBy('description', 'asc')->get();

        if (!$expense->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.expense.expense-create', [
                    'expensedesc' => $expense_desc,
                    'regions' => $regions
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
        else{

            $region = Branch::where('id', $expense->BranchId)->first();
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Update')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.expense.expense-update', [
                    'expense' => $expense,
                    'expensedesc' => $expense_desc,
                    'regions' => $regions,
                    'region' => $region
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createExpense(Request $request){

        // custom error message
        $messages = [
            'regionid.required' => 'This field is required.',
            'branchid.required' => 'This field is required.',
            'expenseamount.required' => 'This field is required',
            'expensedesc.required' => 'This field is required',
            'expensedate.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'regionid' => 'required',
            'branchid' => 'required',
            'expenseamount' => 'required',
            'expensedesc' => 'required',
            'expensedate' => 'required',
            'expenseimage' => 'image|mimes:jpeg,png,jpg,gif',
            'expensenote' => 'nullable'
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
        $expenseamount = strip_tags($validatedData['expenseamount']);
        $expensedesc = strip_tags($validatedData['expensedesc']);
        $expensedate = strip_tags($validatedData['expensedate']);
        $note = strip_tags($validatedData['expensenote'] ?? '');
        
        // create a new expense
        try {
            
            $imageName = 'Not available';
            if ($request->hasFile('expenseimage')) {
                
                $image = $request->file('expenseimage');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(base_path('uploads/expenses'), $imageName); 
            }

            $insertData = [
                'branchid' => $branchid,
                'expensesdescid' => $expensedesc,
                'amount' => $expenseamount,
                'datecreated' => $expensedate,
                'note' => $note,
                'image' => $imageName,
                'createdby' => session('user_id')
            ];
    
            Expenses::insert($insertData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Expense ' . '[Action] Insert ' . '[Target] ' . $expenseamount);

            return redirect('/expenses')->with('success', 'Added new expense!');
        } catch (\Exception $e) {
            return redirect('/expenses')->with('error', 'An error occurred while creating a new expense.');
        }
    }

    // update data
    public function updateExpense(Expenses $expense, Request $request){

        // custom error message
        $messages = [
            'regionid.required' => 'This field is required.',
            'branchid.required' => 'This field is required.',
            'expenseamount.required' => 'This field is required',
            'expensedesc.required' => 'This field is required',
            'expensedate.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'regionid' => 'required',
            'branchid' => 'required',
            'expenseamount' => 'required',
            'expensedesc' => 'required',
            'expensedate' => 'required',
            'expenseimage' => 'image|mimes:jpeg,png,jpg,gif',
            'expensenote' => 'nullable'
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
        $expenseamount = strip_tags($validatedData['expenseamount']);
        $expensedesc = strip_tags($validatedData['expensedesc']);
        $expensedate = strip_tags($validatedData['expensedate']);
        $note = strip_tags($validatedData['expensenote'] ?? '');
        
        // update deposit data
        try {
            
            $imageName = $expense->Image;
            if ($request->hasFile('expenseimage')) {

                $oldImagePath = base_path('uploads/expenses/' . $expense->Image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath); 
                }

                $image = $request->file('expenseimage');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(base_path('uploads/expenses'), $imageName); 
            }

            $updateData = [
                'branchid' => $branchid,
                'expensesdescid' => $expensedesc,
                'amount' => $expenseamount,
                'datecreated' => $expensedate,
                'note' => $note,
                'image' => $imageName,
                'modifiedby' => session('user_id'),
                'datemodified' => date("Y-m-d h:i:s")
            ];
    
            Expenses::where('id', $expense->Id)
            ->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Expense ' . '[Action] Update ' . '[Target] ' . $expense->Id);

            return redirect('/expenses')->with('success', 'Selected expense document has been updated!');
        } catch (\Exception $e) {
            return redirect('/expenses')->with('error', 'An error occured while updating the selected expense document.');
        }
    }

    // delete expense
    public function deleteExpense(Expenses $expense){

        try{
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){        

                Expenses::where('id', $expense->Id)
                ->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Expense ' . '[Action] Delete ' . '[Target] ' . $expense->Id);

                return redirect('/expenses')->with('warning', 'Selected expense document has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/expenses')->with('error', 'An error occurred while deleting the selected expense document.');
        }
    }
}
