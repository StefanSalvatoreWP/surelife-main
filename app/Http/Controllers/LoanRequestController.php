<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Actions;
use App\Models\Payment;
use App\Models\LoanRequest;
use App\Models\PaymentTerm;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LoanRequestController extends Controller
{
    public function searchAll(Request $request)
    {

        if ($request->ajax()) {

            $query = LoanRequest::
                select(
                    'tblloanrequest.*',
                    'tblclient.id as ClientId',
                    'tblclient.contractnumber as ContractNumber',
                    'tblclient.lastname as LastName',
                    'tblclient.firstname as FirstName',
                    'tblclient.middlename as MiddleName'
                )
                ->leftJoin('tblclient', 'tblloanrequest.clientid', '=', 'tblclient.id');

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('tblclient.ContractNumber', 'like', "%$searchTerm%")
                        ->orWhere('tblclient.LastName', 'like', "%$searchTerm%")
                        ->orWhere('tblclient.FirstName', 'like', "%$searchTerm%")
                        ->orWhere('tblclient.MiddleName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.loanrequest.loanrequest');
    }

    public function viewLoanRequest(LoanRequest $loanRequest, Request $request)
    {

        $term = 12;

        $clientDetails = Client::where('id', $loanRequest->ClientId)->first();
        $clientBranch = Branch::where('id', $clientDetails->BranchId)->first();
        $clientTerm = PaymentTerm::where('id', $clientDetails->PaymentTermId)->first();
        $clientInstallments = Payment::where('clientid', $clientDetails->Id)->count();

        $annualPaymentAmount = floor($clientTerm->Price * $term) - (floor($clientTerm->Price * $term) * 0.10);
        $noOfYearsPaid = floor($clientInstallments / $term);

        $totalAnnualPayment = $annualPaymentAmount * $noOfYearsPaid;
        $totalNumYearsPaid = $noOfYearsPaid * 10;
        $grossLoanableAmount = $totalAnnualPayment * ($totalNumYearsPaid / 100);
        $handlingFee = $grossLoanableAmount * 0.10;
        $netLoanableAmount = $grossLoanableAmount - $handlingFee;

        $noOfMonthPayments = 12;
        $loanMonthlyDue = $grossLoanableAmount / $noOfMonthPayments;
        $percentageInterest = 1.25 / 100;
        $interest = $loanMonthlyDue * $percentageInterest;
        $monthlyInterest = $interest * $term;
        $totalMonthlyDue = $loanMonthlyDue + $monthlyInterest;

        return view('pages.loanrequest.loanrequest-view', [
            'term' => $term,
            'loanRequestDetails' => $loanRequest,
            'clientDetails' => $clientDetails,
            'clientBranch' => $clientBranch,
            'clientTerm' => $clientTerm,
            'annualPaymentAmount' => $annualPaymentAmount,
            'noOfYearsPaid' => $noOfYearsPaid,
            'totalAnnualPayment' => $totalAnnualPayment,
            'totalNumYearsPaid' => $totalNumYearsPaid,
            'grossLoanableAmount' => $grossLoanableAmount,
            'handlingFee' => $handlingFee,
            'netLoanableAmount' => $netLoanableAmount,
            'noOfMonthPayments' => $noOfMonthPayments,
            'loanMonthlyDue' => $loanMonthlyDue,
            'percentageInterest' => $percentageInterest,
            'interest' => $interest,
            'monthlyInterest' => $monthlyInterest,
            'totalMonthlyDue' => $totalMonthlyDue
        ]);
    }

    public function updateLoanRequest(LoanRequest $loanRequest)
    {

        $currentDate = date("Ymd");

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();

        $actions = '';
        $updateStatus = 'Pending';
        $loanRequestCode = 'Pending';

        if ($loanRequest->Status == 'Pending') {
            $updateStatus = 'Verified';
            $actions = Actions::query()->where('action', '=', 'Verify')->first();
        } else if ($loanRequest->Status == 'Verified') {
            $updateStatus = 'Approved';
            $loanRequestCode = $currentDate . Str::random(7);

            $actions = Actions::query()->where('action', '=', 'Approve')->first();
        }

        if ($roleLevel->Level > $actions->RoleLevel) {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }

        $updateData = [
            'status' => $updateStatus,
            'code' => $loanRequestCode
        ];

        LoanRequest::where('id', $loanRequest->Id)->update($updateData);
        Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Loan Request ' . '[Action] Update ' . '[Target] ' . $loanRequest->Id);

        return redirect('/req-loans')->with('success', 'Selected loan request has been updated!');
    }

    public function deleteLoanRequet(LoanRequest $loanRequest)
    {

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Delete')->first();

        if ($roleLevel->Level > $actions->RoleLevel) {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }

        LoanRequest::where('id', $loanRequest->Id)->delete();
        Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Loan Request ' . '[Action] Delete ' . '[Target] ' . $loanRequest->Id);

        return redirect('/req-loans')->with('warning', 'Selected loan request has been deleted!');
    }

    // ---- CLIENT VIEW ---- //
    public function createClientLoanRequest(Client $client)
    {

        $term = 12;

        $clientTerm = PaymentTerm::where('id', $client->PaymentTermId)->first();
        $clientInstallments = Payment::where('clientid', $client->Id)->count();

        $annualPaymentAmount = floor($clientTerm->Price * $term) - (floor($clientTerm->Price * $term) * 0.10);
        $noOfYearsPaid = floor($term / $clientInstallments);

        $totalAnnualPayment = $annualPaymentAmount * $noOfYearsPaid;

        $totalNumYearsPaid = $noOfYearsPaid * 10;
        $grossLoanableAmount = $totalAnnualPayment * ($totalNumYearsPaid / 100);
        $handlingFee = $grossLoanableAmount * 0.10;
        $netLoanableAmount = $grossLoanableAmount - $handlingFee;

        $noOfMonthPayments = 12;
        $loanMonthlyDue = $grossLoanableAmount / $noOfMonthPayments;
        $percentageInterest = 1.25 / 100;
        $interest = $loanMonthlyDue * $percentageInterest;
        $monthlyInterest = $interest * $term;
        $totalMonthlyDue = $loanMonthlyDue + $monthlyInterest;

        $insertData = [
            'clientid' => $client->Id,
            'amount' => $netLoanableAmount,
            'monthlyamount' => $totalMonthlyDue,
            'daterequested' => date("Y-m-d"),
            'status' => 'Pending',
            'remarks' => 'Not available',
            'code' => date("Ymd") . Str::upper(Str::random(7))
        ];

        LoanRequest::insert($insertData);

        return back()->with('success', 'Loan request has been forwarded successfully.');
    }
}
