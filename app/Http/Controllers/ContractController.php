<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Actions;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/* 2023 SilverDust) S. Maceren */

class ContractController extends Controller
{
    // void selected contract number
    public function voidContractNumber(Contract $contractseries, Request $request){

        try {
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Void')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $status = "1";
                $remarks = "Void by " . session('user_name');
                $updateData = [
                    'status' => $status,
                    'remarks' => $remarks
                ];
        
                Contract::where('id', $contractseries->Id)->update($updateData);
        
                return back()->with('success', 'Selected contract is now available!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating contract status.');
        }
    }
}
