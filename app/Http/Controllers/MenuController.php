<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class MenuController extends Controller
{
   // search data - tables
   public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Menu
            ::select(
                'id',
                'menuitem',
                'rolelevel' 
            );
            
            return DataTables::of($query)->toJson();
        }

        $roles = Role::query()
        ->where('Role', '<>', 'Client')
        ->get();
        
        return view('pages.menu.menu', [
            'roles' => $roles
        ]);
    }

    // search data - check menu item for the logged in role
    public function checkMenuPrivilege(Request $request){

        $query = Menu::select(
            'id', 
            'menuitem', 
            'rolelevel'
        );

        $menu = $query->get();
        return $menu;
    }

    // menu form screen
    public function menuFormScreen(Menu $menu){

        if (!$menu->exists) {
            return view('pages.menu.menu-create', [
                'menus' => $menu
            ]);
        }
        else{
            return view('pages.menu.menu-update', [
                'menu' => $menu,
            ]);
        }
    }

    // insert new data
    public function createMenu(Request $request){

        // custom error message
        $messages = [
            'menuname.required' => 'This field is required.',
            'menuname.min' => 'Name is too short',
            'menuname.max' => 'Name is too long',
            'menurolelevel.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'menuname' => 'required|min:3|max:30',
            'menurolelevel' => 'required|numeric|min:0'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $menuname = strip_tags($validatedData['menuname']);
        $menurolelevel = strip_tags($validatedData['menurolelevel']);

        // check if menu exists
        $menuExists = Menu::where('menuitem', $menuname)
                ->where('rolelevel', $menurolelevel)
                ->first();

        if($menuExists){
            return redirect()->back()->with('duplicate', 'Menu already exists!')->withInput();
        }
        // create a new menu
        else{
            try {
                
                $insertData = [
                    'menuitem' => $menuname,
                    'rolelevel' => $menurolelevel
                ];
        
                Menu::insert($insertData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Menu ' . '[Action] Insert ' . '[Target] ' . $menuname);

                return redirect('/menu')->with('success', 'Added new menu!');
            } catch (\Exception $e) {
                return redirect('/menu')->with('error', 'An error occurred while creating a new menu.');
            }
        }
    }

    // update data
    public function updateMenu(Menu $menu, Request $request){

        // custom error message
        $messages = [
            'menuname.required' => 'This field is required.',
            'menuname.min' => 'Name is too short',
            'menuname.max' => 'Name is too long',
            'menurolelevel.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'menuname' => 'required|min:3|max:30',
            'menurolelevel' => 'required|numeric|min:0'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $menuname = strip_tags($validatedData['menuname']);
        $menurolelevel = strip_tags($validatedData['menurolelevel']);

        // check if menu exists
        $menuExists = Menu::where('menuitem', $menuname)
                ->where('rolelevel', $menurolelevel)
                ->first();

        if($menuExists){
            return redirect()->back()->with('duplicate', 'Menu already exists!')->withInput();
        }
        // update selected menu
        else{
            try {
                
                $updateData = [
                    'menuitem' => $menuname,
                    'rolelevel' => $menurolelevel
                ];
        
                Menu::where('id', $menu->Id)
                ->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Menu ' . '[Action] Update ' . '[Target] ' . $menu->Id);

                return redirect('/menu')->with('success', 'Selected menu has been updated!');
            } catch (\Exception $e) {
                return redirect('/menu')->with('error', 'An error occured while updating the selected menu.');
            }
        }
    }

    // delete menu
    public function deleteMenu(Menu $menu){

        try{

            Menu::where('id', $menu->Id)->delete();
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Menu ' . '[Action] Delete ' . '[Target] ' . $menu->Id);

            return redirect('/menu')->with('warning', 'Selected menu has been deleted!');
        } catch (\Exception $e) {
            return redirect('/menu')->with('error', 'An error occurred while deleting the selected menu.');
        }
    }
}
