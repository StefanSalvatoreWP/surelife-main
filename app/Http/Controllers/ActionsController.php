<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Actions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2024 SilverDust) S. Maceren */

class ActionsController extends Controller
{
    // search all - table
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Actions
            ::select(
                'id',
                'action',
                'rolelevel'
            );
            
            return DataTables::of($query)->toJson();
        }

        $roles = Role::query()
        ->where('Role', '<>', 'Client')
        ->get();
        
        return view('pages.action.action', [
            'roles' => $roles
        ]);
    }

    // search data - check actions for the logged in role
    public function checkActionPrivilege(Request $request){

        $query = Actions::select(
            'id', 
            'action', 
            'rolelevel'
        );

        $actions = $query->get();
        return $actions;
    }

    // action form screen
    public function actionFormScreen(Actions $actions){

        if (!$actions->exists) {
            return view('pages.action.action-create', [
                'actions' => $actions
            ]);
        }
        else{
            return view('pages.action.action-update', [
                'action' => $actions,
            ]);
        }
    }

    // create new action
    public function insertAction(Request $request){

        // custom error message
        $messages = [
            'actionname.required' => 'This field is required.',
            'actionname.min' => 'Name is too short',
            'actionname.max' => 'Name is too long',
            'actionrolelevel.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'actionname' => 'required|min:3|max:30',
            'actionrolelevel' => 'required|numeric|min:0'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $actionname = strip_tags($validatedData['actionname']);
        $actionrolelevel = strip_tags($validatedData['actionrolelevel']);

        // check if action exists
        $actionExists = Actions::where('action', $actionname)
                ->where('rolelevel', $actionrolelevel)
                ->first();

        if($actionExists){
            return redirect()->back()->with('duplicate', 'Action already exists!')->withInput();
        }

        // create a new action
        else{
            try {
                
                $insertData = [
                    'action' => $actionname,
                    'rolelevel' => $actionrolelevel,
                    'datecreated' => date("Y-m-d"),
                    'createdby' => session('user_id')
                ];
        
                Actions::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Actions ' . '[Action] Insert ' . '[Target] ' . $actionname);

                return redirect('/action')->with('success', 'Added new action!');
            } catch (\Exception $e) {
                return redirect('/action')->with('error', 'An error occurred while creating a new action.');
            }
        }
    }

    // update selected action
    public function updateAction(Actions $action, Request $request){

        // custom error message
        $messages = [
            'actionname.required' => 'This field is required.',
            'actionname.min' => 'Name is too short',
            'actionname.max' => 'Name is too long',
            'actionrolelevel.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'actionname' => 'required|min:3|max:30',
            'actionrolelevel' => 'required|numeric|min:0'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $actionname = strip_tags($validatedData['actionname']);
        $actionrolelevel = strip_tags($validatedData['actionrolelevel']);

        // check if action exists
        $actionExists = Actions::where('action', $actionname)
                ->where('rolelevel', $actionrolelevel)
                ->first();

        if($actionExists){
            return redirect()->back()->with('duplicate', 'Action already exists!')->withInput();
        }

        // update selected action
        else{
            try {
                
                $updateData = [
                    'action' => $actionname,
                    'rolelevel' => $actionrolelevel
                ];
        
                Actions::where('id', $action->Id)
                ->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Actions ' . '[Action] Update ' . '[Target ID] ' . $action->Id);

                return redirect('/action')->with('success', 'Selected action has been updated!');
            } catch (\Exception $e) {
                return redirect('/action')->with('error', 'An error occurred while updating the selected action.');
            }
        }
    }

    // delete selected action
    public function deleteAction(Actions $action, Request $request){
        
        try{

            Actions::where('id', $action->Id)->delete();
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Actions ' . '[Action] Delete ' . '[Target ID] ' . $action->Id);

            return redirect('/action')->with('warning', 'Selected action has been deleted!');
        } catch (\Exception $e) {
            return redirect('/action')->with('error', 'An error occurred while deleting the selected action.');
        }
    }
}
