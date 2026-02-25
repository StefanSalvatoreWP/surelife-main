<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Client;
use App\Models\OrBatch;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use App\Models\OfficialReceipt;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class LoanPaymentController extends Controller
{

    public function searchAll(Request $request)
    {

        if ($request->ajax()) {

            $query = LoanPayment::
                select(
                    'tblloanpayment.*',
                    'tblloanrequest.Code',
                    'tblclient.LastName',
                    'tblclient.FirstName',
                    'tblclient.MiddleName',
                    'tblclient.ContractNumber'
                )
                ->leftJoin('tblloanrequest', 'tblloanpayment.loanrequestid', '=', 'tblloanrequest.id')
                ->leftJoin('tblclient', 'tblloanpayment.clientid', '=', 'tblclient.id')
                ->orderBy('paymentdate', 'desc')
                ->orderBy('installment', 'desc');

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('tblclient.LastName', 'like', "%$searchTerm%")
                        ->orWhere('tblclient.ContractNumber', 'like', "%$searchTerm%")
                        ->orWhere('ORNo', 'like', "%$searchTerm%")
                        ->orWhere('tblloanpayment.PaymentDate', 'like', "%$searchTerm%")
                        ->orWhere('tblloanrequest.Code', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.loanpayment.loanpayment');
    }

    public function getLoanPayments(Request $request)
    {
        $clientId = $request->input('cid');

        if ($request->ajax()) {
            $query = LoanPayment::query()
                ->select(
                    'tblloanpayment.*',
                    'tblorbatch.SeriesCode'
                )
                ->leftJoin('tblofficialreceipt', 'tblloanpayment.orid', '=', 'tblofficialreceipt.id')
                ->leftJoin('tblorbatch', 'tblofficialreceipt.orbatchid', '=', 'tblorbatch.id')
                ->where('tblloanpayment.clientid', $clientId)
                ->where('tblloanpayment.status', '<>', 'void');

            return DataTables::of($query)->toJson();
        }

        return redirect('/client-view/' . $clientId);
    }

    public function submitClientLoanPayment(Request $request, Client $client)
    {

        // custom error message
        $messages = [
            'paymentamount.required' => 'This field is required.',
            'orseriescode.required' => 'This field is required.',
            'orno.required' => 'This field is required.',
            'paymentmethod.required' => 'This field is required.',
            'paymentdate.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'clientregion' => 'nullable',
            'clientbranch' => 'nullable',
            'paymentamount' => 'required',
            'orseriescode' => 'required',
            'orno' => 'required',
            'paymentmethod' => 'required',
            'paymentdate' => 'required',
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();

        $clientRegion = strip_tags($validatedData['clientregion']);
        $clientBranch = strip_tags($validatedData['clientbranch']);
        $paymentAmount = strip_tags($validatedData['paymentamount']);
        $orSeriesCode = strip_tags($validatedData['orseriescode']);
        $orNo = strip_tags($validatedData['orno']);
        $paymentMethod = strip_tags($validatedData['paymentmethod']);
        $paymentDate = strip_tags($validatedData['paymentdate']);

        // check if OR no is available
        $availableOR = '1';
        $orType = '1';

        $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
            ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
            ->where('ORNumber', $orNo)
            ->where('RegionId', $clientRegion)
            ->where('BranchId', $clientBranch)
            ->where('Status', $availableOR)
            ->where('Type', $orType)
            ->where('SeriesCode', $orSeriesCode)
            ->first();

        if ($orExists) {

            // get recent loan data
            $loanRequestData = LoanRequest::where('clientid', $client->Id)
                ->where('remarks', '<>', 'Completed')
                ->first();

            // update installment count
            $loanPaymentData = LoanPayment::where('clientid', $client->Id)
                ->where('loanrequestid', $loanRequestData->Id)
                ->orderBy('id', 'desc')
                ->orderBy('installment', 'desc')
                ->first();

            try {
                $updated_installment = $loanPaymentData->Installment + ceil($paymentAmount / $loanRequestData->MonthlyAmount);
            } catch (Exception $e) {
                $updated_installment = ceil($paymentAmount / $loanRequestData->MonthlyAmount);
            }

            $searchedOfficialReceiptId = $orExists->id;
            $status = 'Paid';

            $insertLoanPaymentData = [
                'clientid' => $client->Id,
                'orno' => $orNo,
                'orid' => $searchedOfficialReceiptId,
                'amount' => $paymentAmount,
                'installment' => $updated_installment,
                'loanrequestid' => $loanRequestData->Id,
                'status' => $status,
                'paymentmethod' => $paymentMethod,
                'paymentdate' => $paymentDate,
                'datecreated' => date("Y-m-d"),
                'createdby' => session('user_id')
            ];

            LoanPayment::insert($insertLoanPaymentData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Loan Payment ' . '[Action] Insert ' . '[Target] ' . $client->Id);

            // update OR status
            $usedOR = '2';
            $updateORData = [
                'status' => $usedOR
            ];

            OfficialReceipt::where('id', $searchedOfficialReceiptId)
                ->where('ORNumber', $orNo)
                ->update($updateORData);

            // update loan remarks
            $updated_totalAmount = $loanRequestData->Amount - $paymentAmount;
            if ($updated_totalAmount <= 0) {
                $remarks = 'Completed';
                $updateLoanRequestData = [
                    'remarks' => $remarks
                ];

                LoanRequest::where('id', $loanRequestData->Id)->update($updateLoanRequestData);
            }

            return redirect('/client-view/' . $client->Id)->with('success', 'Added new loan payment!');
        } else {
            return redirect()->back()->with('duplicate', 'O.R not available.')->withInput();
        }
    }

    // void selected client loan payment
    public function voidClientLoanPayment(LoanPayment $loanPayment)
    {

        try {

            $voidStatus = 'Void';
            $updateData = ['status' => $voidStatus];

            LoanPayment::where('id', $loanPayment->Id)->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Loan Payment ' . '[Action] Void ' . '[Target] ' . $loanPayment->Id);

            return redirect()->back()->with('success', 'Selected loan payment has been voided successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occured while performing the action.');
        }
    }

}