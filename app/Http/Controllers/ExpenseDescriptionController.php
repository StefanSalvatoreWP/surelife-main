<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Actions;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\ExpenseDescription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/* 2024 SilverDust) S. Maceren */

class ExpenseDescriptionController extends Controller
{
   // search data - tables
   public function searchAll(Request $request){

      if ($request->ajax()) {

         $query = ExpenseDescription::query();
         
         if (!empty($request->input('search.value'))) {
             $searchTerm = $request->input('search.value');
             $query->where(function ($query) use ($searchTerm) {
                 $query->where('Description', 'like', "%$searchTerm%");
             });
         }

         return DataTables::of($query)->toJson();
     }

     return view('pages.expensedesc.expensedesc');
   }

   public function expenseDescFormScreen(Request $request, ExpenseDescription $expenseDesc){

      if (!$expenseDesc->exists) {
            
         $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
         $actions = Actions::query()->where('action', '=', 'Insert')->first();
         if($roleLevel->Level <= $actions->RoleLevel){        
             return view('pages.expensedesc.expensedesc-create', [
                 'expenseDesc' => $expenseDesc
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
             return view('pages.expensedesc.expensedesc-update', [
                 'expenseDesc' => $expenseDesc
             ]);
         }
         else{
             return redirect()->back()->with('error', 'You do not have access to this function.');
         }
     }
   }

   // insert new data
   public function createExpenseDesc(Request $request){

      // custom error message
      $messages = [
          'expensedescname.required' => 'This field is required.',
          'expensedescname.min' => 'Name is too short',
          'expensedescname.max' => 'Name is too long'
      ];

      // validation fields
      $fields = Validator::make($request->all(), [
          'expensedescname' => 'required|min:3|max:30'
      ], $messages);
      
      if ($fields->fails()) {
          return redirect()
          ->back()
          ->withErrors($fields)
          ->withInput();
      } 

      // validation has passed
      $validatedData = $fields->validated();

      $expensedescname = strip_tags($validatedData['expensedescname']);
    
      // check if expense description exists
      $expenseDescExists = ExpenseDescription::where('description', $expensedescname)->first();

      if($expenseDescExists){
          return redirect()->back()->with('duplicate', 'Description already exists!')->withInput();
      }
      // create a new expense description
      else{
          try {
              
              $insertData = [
                  'description' => $expensedescname
              ];
      
              ExpenseDescription::insert($insertData);
              Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Expense Description ' . '[Action] Insert ' . '[Target] ' . $expensedescname);

              return redirect('/expense-desc')->with('success', 'Added new expense description!');
          } catch (\Exception $e) {
              return redirect('/expense-desc')->with('error', 'An error occurred while creating a new expense description.');
          }
      }
  }

  // updated data
  public function updateExpenseDesc(Request $request, ExpenseDescription $expenseDesc){

      // custom error message
      $messages = [
         'expensedescname.required' => 'This field is required.',
         'expensedescname.min' => 'Name is too short',
         'expensedescname.max' => 'Name is too long'
      ];

      // validation fields
      $fields = Validator::make($request->all(), [
         'expensedescname' => 'required|min:3|max:30'
      ], $messages);
      
      if ($fields->fails()) {
         return redirect()
         ->back()
         ->withErrors($fields)
         ->withInput();
      } 

      // validation has passed
      $validatedData = $fields->validated();

      $expensedescname = strip_tags($validatedData['expensedescname']);
   
      // check if expense description exists
      $expenseDescExists = ExpenseDescription::where('description', $expensedescname)->first();

      if($expenseDescExists){
         return redirect()->back()->with('duplicate', 'Description already exists!')->withInput();
      }
      // update expense description
      else{
         try {
            
            $updateData = [
                  'description' => $expensedescname
            ];
      
            ExpenseDescription::where('id', $expenseDesc->Id)->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Expense Description ' . '[Action] Update ' . '[Target] ' . $expenseDesc->Id);

            return redirect('/expense-desc')->with('success', 'Updated expense description!');
         } catch (\Exception $e) {
            return redirect('/expense-desc')->with('error', 'An error occurred while updating the selected expense description.');
         }
      }
   }

   // delete data
   public function deleteExpenseDesc(Request $request, ExpenseDescription $expenseDesc){

      try{
         $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
         $actions = Actions::query()->where('action', '=', 'Delete')->first();
         if($roleLevel->Level <= $actions->RoleLevel){        

             ExpenseDescription::where('id', $expenseDesc->Id)
             ->delete();
             Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Expense Description ' . '[Action] Delete ' . '[Target] ' . $expenseDesc->Id);

             return redirect('/expense-desc')->with('warning', 'Selected expense description has been deleted!');
         }
         else{
             return redirect()->back()->with('error', 'You do not have access to this function.');
         }
     } catch (\Exception $e) {
         return redirect('/expense-desc')->with('error', 'An error occurred while deleting a branch.');
     }
   }
}
