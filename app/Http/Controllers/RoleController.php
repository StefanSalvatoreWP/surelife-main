<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class RoleController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Role
            ::select(
                'id',
                'role',
                'level' 
            );
            
            return DataTables::of($query)->toJson();
        }

        return view('pages.role.role');
    }

    // search data - check role level for the logged in user
    public function checkRolePrivilege(Request $request){

        $query = Role::select(
            'id', 
            'role', 
            'level'
        )->where('id', '=', session('user_roleid'));

        $roles = $query->get();
        return $roles;
    }

    // role form screen
    public function roleFormScreen(Role $role){

        if (!$role->exists) {
            return view('pages.role.role-create', [
                'roles' => $role
            ]);
        }
        else{
            return view('pages.role.role-update', [
                'role' => $role,
            ]);
        }
    }

    // insert new data
    public function createRole(Request $request){

        // custom error message
        $messages = [
            'role.required' => 'This field is required.',
            'role.min' => 'Name is too short',
            'role.max' => 'Name is too long',
            'rolelevel.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'role' => 'required|min:3|max:30',
            'rolelevel' => 'required|numeric|min:0'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $rolename = strip_tags($validatedData['role']);
        $rolelevel = strip_tags($validatedData['rolelevel']);

        // check if role exists
        $roleExists = Role::where('role', $rolename)
                ->where('level', $rolelevel)
                ->first();

        if($roleExists){
            return redirect()->back()->with('duplicate', 'Role already exists!')->withInput();
        }
        // create a new role
        else{
            try {
                
                $insertData = [
                    'role' => $rolename,
                    'level' => $rolelevel
                ];
        
                Role::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Role ' . '[Action] Insert ' . '[Target] ' . $rolename);

                return redirect('/role')->with('success', 'Added new role!');
            } catch (\Exception $e) {
                return redirect('/role')->with('error', 'An error occurred while creating a new role.');
            }
        }
    }

    // update data
    public function updateRole(Role $role, Request $request){

        // custom error message
        $messages = [
            'role.required' => 'This field is required.',
            'role.min' => 'Name is too short',
            'role.max' => 'Name is too long',
            'rolelevel.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'role' => 'required|min:3|max:30',
            'rolelevel' => 'required|numeric|min:0'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $rolename = strip_tags($validatedData['role']);
        $rolelevel = strip_tags($validatedData['rolelevel']);

        // check if role exists
        $roleExists = Role::where('role', $rolename)
                ->where('level', $rolelevel)
                ->first();

        if($roleExists){
            return redirect()->back()->with('duplicate', 'Role already exists!')->withInput();
        }
        // update role
        else{
            try {
                
                $updateData = [
                    'role' => $rolename,
                    'level' => $rolelevel
                ];
        
                Role::where('id', $role->Id)
                ->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Role ' . '[Action] Update ' . '[Target] ' . $role->Id);

                return redirect('/role')->with('success', 'Selected role has been updated!');
            } catch (\Exception $e) {
                return redirect('/role')->with('error', 'An error occurred while creating a new role.');
            }
        }
    }

    // delete role
    public function deleteRole(Role $role){

        try{

            Role::where('id', $role->Id)->delete();
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Role ' . '[Action] Delete ' . '[Target] ' . $role->Id);

            return redirect('/role')->with('warning', 'Selected role has been deleted!');
        } catch (\Exception $e) {
            return redirect('/role')->with('error', 'An error occurred while deleting the selected role.');
        }
    }
}
