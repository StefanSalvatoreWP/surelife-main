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
                    'tblorbatch.seriescode as SeriesCode'
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
            'paymentmethod.required' => 'This field is required.',
            'paymentdate.required' => 'This field is required.'
        ];

        // validation fields - OR fields are now nullable
        $fields = Validator::make($request->all(), [
            'clientregion' => 'nullable',
            'clientbranch' => 'nullable',
            'paymentamount' => 'required',
            'orseriescode' => 'nullable',
            'orno' => 'nullable',
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
        $orSeriesCode = isset($validatedData['orseriescode']) ? strip_tags($validatedData['orseriescode']) : null;
        $orNo = isset($validatedData['orno']) ? strip_tags($validatedData['orno']) : null;
        $paymentMethod = strip_tags($validatedData['paymentmethod']);
        $paymentDate = strip_tags($validatedData['paymentdate']);

        // Get loan data first (needed regardless of OR)
        $loanRequestData = LoanRequest::where('clientid', $client->Id)
            ->where('remarks', '<>', 'Completed')
            ->first();

        if (!$loanRequestData) {
            return redirect()->back()->with('error', 'No active loan found for this client.')->withInput();
        }

        // Calculate current total paid (excluding voided payments)
        $currentTotalPaid = LoanPayment::where('clientid', $client->Id)
            ->where('loanrequestid', $loanRequestData->Id)
            ->where('status', '<>', 'Void')
            ->sum('Amount');

        // Get total repayable amount (principal + interest)
        $totalRepayable = $loanRequestData->total_repayable ?? $loanRequestData->TotalRepayable ?? $loanRequestData->Amount;

        // Calculate remaining balance before this payment
        $remainingBalance = $totalRepayable - $currentTotalPaid;

        // VALIDATION: Prevent overpayment - payment cannot exceed remaining balance
        if ($paymentAmount > $remainingBalance) {
            return redirect()
                ->back()
                ->withErrors(['paymentamount' => "Payment amount (₱" . number_format($paymentAmount, 2) . ") exceeds remaining balance (₱" . number_format($remainingBalance, 2) . "). Maximum allowed: ₱" . number_format($remainingBalance, 2)])
                ->withInput();
        }

        // Check OR only if provided
        $searchedOfficialReceiptId = null;

        if (!empty($orNo) && !empty($orSeriesCode)) {
            // check if OR no is available
            $availableOR = '1';
            $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('ornumber', $orNo)
                ->where('regionid', $clientRegion)
                ->where('branchid', $clientBranch)
                ->where('status', $availableOR)
                ->whereIn('type', ['1', '2'])
                ->where('seriescode', $orSeriesCode)
                ->first();

            if (!$orExists) {
                return redirect()->back()->with('duplicate', 'O.R not available.')->withInput();
            }

            $searchedOfficialReceiptId = $orExists->id;
        }

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

        // Update OR status only if OR was provided
        if ($searchedOfficialReceiptId && $orNo) {
            $usedOR = '2';
            OfficialReceipt::where('id', $searchedOfficialReceiptId)
                ->where('ornumber', $orNo)
                ->update(['status' => $usedOR]);
        }

        // Calculate remaining balance correctly
        // Sum all non-void payments for this loan (including the one we just inserted)
        $totalPaid = LoanPayment::where('clientid', $client->Id)
            ->where('loanrequestid', $loanRequestData->Id)
            ->where('status', '<>', 'Void')
            ->sum('Amount');

        $totalRepayableAfterInsert = $loanRequestData->total_repayable ?? $loanRequestData->TotalRepayable ?? $loanRequestData->Amount;

        // Calculate remaining balance from TotalRepayable (principal + interest)
        $remainingBalance = $totalRepayableAfterInsert - $totalPaid;

        // Send loan payment confirmation SMS
        if ($client->MobileNumber) {
            \App\Services\SmsService::sendLoanPaymentConfirmation(
                $client,
                (object)['amount' => $paymentAmount, 'id' => null],
                max(0, $remainingBalance)
            );
        }

        // update loan status and remarks if fully paid
        if ($remainingBalance <= 0) {
            $updateLoanRequestData = [
                'remarks' => 'Completed',
                'Status' => 'Completed'
            ];

            LoanRequest::where('id', $loanRequestData->Id)->update($updateLoanRequestData);
        }

        return redirect('/client-view/' . $client->Id . '#loan')->with('success', 'Added new loan payment!');
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