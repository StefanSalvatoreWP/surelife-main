<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Actions;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\OfficialReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/* 2023 SilverDust) S. Maceren */

class OfficialReceiptController extends Controller
{
    // void selected or number
    public function voidOrNumber(OfficialReceipt $orseries, Request $request){
        
        try {
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Void')->first();
            if($roleLevel->Level <= $actions->RoleLevel){

                $status = '1';
                $remarks = "Void by " . session('user_name');
                $updateData = [
                    'status' => $status,
                    'remarks' => $remarks
                ];
        
                OfficialReceipt::where('id', $orseries->Id)->update($updateData);

                $paymentStatus = '1';
                $paymentRemarks = 'Void by' . session('user_name');
                
                $updatePaymentData = [
                    'voidstatus' => $paymentStatus,
                    'remarks' => $paymentRemarks
                ];

                Payment
                ::where('orno', $orseries->ORNumber)
                ->where('orid', $orseries->Id)
                ->update($updatePaymentData);
                
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Official Receipt ' . '[Action] Void ' . '[Target] ' . $orseries->Id);

                return back()->with('success', 'Selected official receipt is now available!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating official receipt status.');
        }
    }
}
