<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Staff;
use App\Models\Client;
use App\Models\Actions;
use App\Models\Encashment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EncashmentReq;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2024 SilverDust) S. Maceren */

class EncashmentReqController extends Controller
{

    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = EncashmentReq::
            select(
                'tblencashmentreq.*',
                'tblencashmentreq.Id as eid',
                'tblstaff.LastName',
                'tblstaff.FirstName',
                'tblstaff.MiddleName'
            )
            ->leftJoin('tblstaff', 'tblstaff.id', '=', 'tblencashmentreq.staffid');
            
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('tblstaff.LastName', 'like', "%$searchTerm%")
                    ->orWhere('tblstaff.FirstName', 'like', "%$searchTerm%")
                    ->orWhere('tblstaff.MiddleName', 'like', "%$searchTerm%")
                    ->orWhere('vouchercode', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }
        
        return view('pages.request.encashments.encashment');
    }

    // view encashment request
    public function viewEncashmentReq(EncashmentReq $encashmentReq, Request $request){

        $staff = Staff::
        select(
            'Id',
            'LastName',
            'FirstName',
            'MiddleName'
        )
        ->where('id', $encashmentReq->StaffId)
        ->get();

        $encashmentIds = explode(',', $encashmentReq->EncashmentIds);
        
        $clients = Client::
        leftJoin('tblencashment', 'tblclient.id', '=', 'tblencashment.clientid')
        ->whereIn('tblencashment.id', $encashmentIds)->get();

        return view(
            'pages.request.encashments.encashment-view', [
                'staff' => $staff,
                'clients' => $clients,
                'encashmentData' => $encashmentReq
            ]
        );
    }

    // search clients under the encashment request - tables
    public function searchEncashmentReqClients(Request $request){

        if ($request->ajax()) {

            $encashmentId = $request->input('eid');

            // get the encashment ids from the encashment req list
            $encashmentreq_query = Encashment::
            select(
                'tblencashmentreq.EncashmentIds'
            )
            ->leftJoin('tblencashmentreq', 'tblencashment.staffid', '=', 'tblencashmentreq.staffid')
            ->where('tblencashmentreq.id', $encashmentId)
            ->first();
            
            // get the matched ids from the encashment table
            $encashmentIds = explode(',', $encashmentreq_query->EncashmentIds);

            $encashment_clients_query = Encashment::
            select(
                'tblencashment.Id',
                'tblencashment.ContractNo',
                'tblencashment.AmountPaid',
                'tblencashment.Commission',
                'tblencashment.PaymentDate',
                'tblclient.LastName',
                'tblclient.FirstName',
                'tblclient.MiddleName',
                'tblclient.ContractNumber',
                'tblpaymentterm.Term',
                'tblpackage.Package'
            )
            ->leftJoin('tblclient', 'tblencashment.clientid', '=', 'tblclient.id')
            ->leftJoin('tblpaymentterm', 'tblclient.paymenttermid', '=', 'tblpaymentterm.id')
            ->leftJoin('tblpackage', 'tblclient.packageid', '=', 'tblpackage.id')
            ->whereIn('tblencashment.Id', $encashmentIds);

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $encashment_clients_query->where(function ($query) use ($searchTerm) {
                    $query->where('tblclient.LastName', 'like', "%$searchTerm%")
                    ->orWhere('tblclient.FirstName', 'like', "%$searchTerm%")
                    ->orWhere('tblclient.MiddleName', 'like', "%$searchTerm%")
                    ->orWhere('tblclient.ContractNumber', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($encashment_clients_query)->toJson();
        }
        
        return view('pages.request.encashments.encashment');
    }

    // make adjustments to the encashment request
    public function adjustEncashmentReq(EncashmentReq $encashmentReq, Request $request){

        $access = false;
        if($encashmentReq->Status == 'Pending'){
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Verify')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $access = true;
            }
        }
        else if($encashmentReq->Status == 'Verified'){
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Record')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $access = true;
            }
        }
        else if($encashmentReq->Status == 'Recorded'){
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Approve')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $access = true;
            }
        }

        if($access){
            return view(
                'pages.request.encashments.encashment-view-adjustment',[
                    'encashmentData' => $encashmentReq
                ]
            );
        }
        else{
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // update adjustments to the selected encashment request
    public function updateEncashmentReqAdjustment(EncashmentReq $encashmentReq, Request $request){

        // custom error message
        $messages = [
            'incentives.required' => 'This field is required.',
            'adjustments.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'incentives' => 'required',
            'adjustments' => 'required',
            'incentivesremarks' => 'nullable',
            'adjustmentsremarks' => 'nullable'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $incentives = strip_tags($validatedData['incentives']);
        $adjustments = strip_tags($validatedData['adjustments']);
        $incentivesRemarks = strip_tags($validatedData['incentivesremarks']);
        $adjustmentRemarks = strip_tags($validatedData['adjustmentsremarks']);
        
        if(empty($incentivesRemarks)){
            $incentivesRemarks = 'Not available';
        }
        if(empty($adjustmentRemarks)){
            $adjustmentRemarks = 'Not available';
        }

        if($encashmentReq->Status == 'Pending'){
            $verifiedBy = session('user_id');
            $dateVerified = date('Y-m-d');
            $status = 'Verified';
        }
        else if($encashmentReq->Status == 'Verified'){
            $recordedBy = session('user_id');
            $dateRecorded = date('Y-m-d');
            $status = 'Recorded';
        }
        else if($encashmentReq->Status == 'Recorded'){
            $approvedBy = session('user_id');
            $dateApproved = date('Y-m-d');
            $status = 'Approved';
        }

        try{
            if($encashmentReq->Status == 'Pending'){

                $updateData = [
                    'incentives' => $incentives,
                    'incentivesremarks' => $incentivesRemarks,
                    'adjustments' => $adjustments,
                    'adjustmentsRemarks' => $adjustmentRemarks,
                    'verifiedby' => $verifiedBy,
                    'dateverified' => $dateVerified,
                    'status' => $status
                ];

                EncashmentReq::where('id', $encashmentReq->Id)
                ->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Encashment Request ' . '[Action] Verify ' . '[Target] ' . $encashmentReq->Id);

                return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('success', 'Encashment request has been verified.');
            }
            else if($encashmentReq->Status == 'Verified'){

                $updateData = [
                    'incentives' => $incentives,
                    'incentivesremarks' => $incentivesRemarks,
                    'adjustments' => $adjustments,
                    'adjustmentsRemarks' => $adjustmentRemarks,
                    'recordedby' => $recordedBy,
                    'daterecorded' => $dateRecorded,
                    'status' => $status
                ];

                EncashmentReq::where('id', $encashmentReq->Id)
                ->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Encashment Request ' . '[Action] Record ' . '[Target] ' . $encashmentReq->Id);

                return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('success', 'Encashment request has been recorded.');
            }
            else if($encashmentReq->Status == 'Recorded'){

                // change status of the request coms and add voucher code
                do {
                    $generateVoucherCode = Str::random(9);
                    $voucherCodeExists = EncashmentReq::where('vouchercode', $generateVoucherCode)
                        ->exists();
                
                } while ($voucherCodeExists);

                // update all processing request in coms request
                $updateComsRequestData = [
                    'status' => 'For Releasing',
                    'vouchercode' => $generateVoucherCode
                ];

                Encashment::where('status', 'Processing')->update($updateComsRequestData);

                $updateData = [
                    'incentives' => $incentives,
                    'incentivesremarks' => $incentivesRemarks,
                    'adjustments' => $adjustments,
                    'adjustmentsRemarks' => $adjustmentRemarks,
                    'approvedby' => $approvedBy,
                    'dateapproved' => $dateApproved,
                    'status' => $status,
                    'vouchercode' => $generateVoucherCode
                ];

                EncashmentReq::where('id', $encashmentReq->Id)
                ->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Encashment Request ' . '[Action] Approve ' . '[Target] ' . $encashmentReq->Id);

                return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('success', 'Encashment request has been approved.');
            }
        }
        catch (\Exception $e) {
            return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('error', 'An error occurred while making adjustments to the encashment request.');
        }
    }

    // release encashment request
    public function releaseEncashmentReq(EncashmentReq $encashmentReq, Request $request){

        try{
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Release Encashment Request')->first();
            if($roleLevel->Level > $actions->RoleLevel){
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }

            $input_voucherCode = $request->vc;
            if($input_voucherCode == $encashmentReq->VoucherCode){
                
                $updateEncashmentReqData = [
                    'releasedby' => session('user_id'),
                    'datereleased' => date('Y-m-d'),
                    'status' => 'Released'
                ];

                EncashmentReq::where('id', $encashmentReq->Id)->update($updateEncashmentReqData);

                $encashmentIds = $encashmentReq->EncashmentIds;
                $encashmentIdsArray = explode(',', $encashmentIds);
                $encashmentIdsArray = array_map('intval', $encashmentIdsArray);

                Encashment::whereIn('id', $encashmentIdsArray)->update(['status' => 'Claimed']);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Encashment Request ' . '[Action] Release ' . '[Target] ' . $encashmentReq->Id);

                return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('success', 'Encashment request has been released.');
            }
            else{
                return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('error', 'An error occurred while releasing the encashment request.');
            }
        }
        catch(\Exception $e){
            return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('error', 'An error occurred while releasing the encashment request.');
        }
    }

    // reject encashment request
    public function rejectEncashmentReq(EncashmentReq $encashmentReq, Request $request){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Reject Encashment Request')->first();
            if($roleLevel->Level > $actions->RoleLevel){
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }

            $reject_remarks = $request->input('remarks');
            if(empty($reject_remarks)){
                $reject_remarks = 'Not available';
            }

            $updateEncashmentReqData = [
                'rejectedby' => session('user_id'),
                'daterejected' => date('Y-m-d'),
                'status' => 'Rejected'
            ];

            EncashmentReq::where('id', $encashmentReq->Id)->update($updateEncashmentReqData);

            $encashmentIds = $encashmentReq->EncashmentIds;
            $encashmentIdsArray = explode(',', $encashmentIds);
            $encashmentIdsArray = array_map('intval', $encashmentIdsArray);

            Encashment::whereIn('id', $encashmentIdsArray)->update(['status' => 'Rejected']);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Encashment Request ' . '[Action] Reject ' . '[Target] ' . $encashmentReq->Id);

            return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('error', 'Encashment request has been rejected.');
        }
        catch(\Exception $e){
            return redirect('/view-req-encashment/' . $encashmentReq->Id)->with('error', 'An error occurred while rejecting the encashment request.');
        }
    }

    // insert new encashment request
    public function createEncashmentRequest(Request $request){

        $comsReqData = json_decode($request->query('encashmentReqData'), true);

        $reqComsIds = $comsReqData['comsIds'];
        $amount = $comsReqData['amount'];

        $comsIds = "";
        foreach ($reqComsIds as $comsId) {
            $comsIds .= ',' . $comsId;
        }
        
        $comsIds = ltrim($comsIds, ',');
        $staffId = session('user_id');
        $status = 'Pending';
        
        try{

            // update status of the requested encashments
            Encashment::whereIn('id', $reqComsIds)->update(['status' => 'Processing']);
           
            // insert encashment request
            $insertData = [
                'staffid' => $staffId,
                'encashmentids' => $comsIds,
                'amount' => $amount,
                'incentives' => 0,
                'incentivesremarks' => 'Not available',
                'adjustments' => 0,
                'adjustmentsremarks' => 'Not available',
                'daterequested' => date('Y-m-d'),
                'status' => $status,
                'remarks' => 'Not available',
                'vouchercode' => 'Not available'
            ];

            EncashmentReq::insert($insertData);

            return redirect('/commission')->with('success', 'Request submitted!');
        } 
        catch (\Exception $e) {
            return redirect('/commission')->with('error', 'An error occurred while creating the request.');
        }
    }

    // cancel all requests
    public function cancelEncashmentRequests(Request $request){

        try{
            // revert status
            $updateData = [
                'status' => 'Pending'
            ];

            Encashment::where('staffid', session('user_id'))
            ->where('vouchercode', 'Not available')
            ->update($updateData);

            // delete request in encashment request table
            EncashmentReq::where('staffid', session('user_id'))
            ->where('vouchercode', 'Not available')
            ->delete();

            return redirect('/commission')->with('success', 'All requests have been cancelled.');
        }
        catch (\Exception $e) {
            return redirect('/commission')->with('error', 'An error occurred while creating the request.');
        }
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // app - submit new encashment request
    public function app_newEncashmentRequest(Request $request){

        $comsReqData = $request->get('encashmentReqData');
        $staffId = $request->get('staffId');

        $reqComsIds = [];
        $amount = 0;
        foreach ($comsReqData as $comsData) {
            $reqComsIds[] = $comsData['Id'];
            $amount += $comsData['AmountPaid'];
        }

        $comsIds = "";
        foreach ($reqComsIds as $comsId) {
            $comsIds .= ',' . $comsId;
        }
        
        $comsIds = ltrim($comsIds, ',');
        $status = 'Pending';

        try{

            // update status of the requested encashments
            Encashment::whereIn('id', $reqComsIds)->update(['status' => 'Processing']);
           
            // insert encashment request
            $insertData = [
                'staffid' => $staffId,
                'encashmentids' => $comsIds,
                'amount' => $amount,
                'incentives' => 0,
                'incentivesremarks' => 'Not available',
                'adjustments' => 0,
                'adjustmentsremarks' => 'Not available',
                'daterequested' => date('Y-m-d'),
                'status' => $status,
                'remarks' => 'Not available',
                'vouchercode' => 'Not available'
            ];

            EncashmentReq::insert($insertData);

            return response()->json(['msg' => 'success']);
        } 
        catch (\Exception $e) {
            return response()->json(['msg' => 'An error occurred. Please try again.']);
        }
    }
}
