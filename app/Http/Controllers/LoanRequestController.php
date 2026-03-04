<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Actions;
use App\Models\Payment;
use App\Models\LoanRequest;
use App\Models\PaymentTerm;
use App\Models\LoanWaiver;
use App\Services\LoanCalculator;
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
        $clientDetails = Client::where('id', $loanRequest->ClientId)->first();
        $clientBranch = Branch::where('id', $clientDetails->BranchId)->first();
        $clientTerm = PaymentTerm::where('id', $clientDetails->PaymentTermId)->first();

        // Fetch waiver data
        $loanWaiver = LoanWaiver::where('loan_request_id', $loanRequest->Id)->first();

        // Fetch contract for additional details - try contract_id first, then get from client
        $contract = null;
        if ($loanRequest->contract_id) {
            $contract = Contract::where('Id', $loanRequest->contract_id)->first();
        }
        // If no contract record, use client's package/payment data
        if (!$contract) {
            $contract = new \stdClass();
            $contract->packageprice = $clientDetails->PackagePrice ?? $clientDetails->packageprice ?? 0;
            $contract->paymenttermamount = $clientDetails->PaymentTermAmount ?? $clientDetails->paymenttermamount ?? 0;
        }
        
        $totalPremiumsPaid = Payment::where('clientid', $clientDetails->Id)->sum('amountpaid');

        // Use saved loan data from database (note: column names are mixed case)
        $grossLoanableAmount = $loanRequest->Amount ?? 0;
        $processingFee = $loanRequest->processing_fee ?? 0;
        $netLoanableAmount = $loanRequest->net_loan_amount ?? 0;
        $termMonths = $loanRequest->term_months ?? 12;
        $interestRate = $loanRequest->interest_rate ?? 1.25;
        $totalRepayable = $loanRequest->total_repayable ?? 0;
        $monthlyTotalDue = $loanRequest->MonthlyAmount ?? 0;
        $premiumPaidPercent = $loanRequest->premium_paid_percent ?? 0;

        // Calculate derived values for display
        $totalInterest = $totalRepayable - $grossLoanableAmount;
        $monthlyLoanPayment = $termMonths > 0 ? $totalRepayable / $termMonths : 0;
        $monthlyInterest = $termMonths > 0 ? $totalInterest / $termMonths : 0;
        $contractPrice = $contract->packageprice ?? 0;
        $monthlyPremium = $contract->paymenttermamount ?? 0;

        return view('pages.loanrequest.loanrequest-view', [
            'loanRequestDetails' => $loanRequest,
            'clientDetails' => $clientDetails,
            'clientBranch' => $clientBranch,
            'clientTerm' => $clientTerm,
            'loanWaiver' => $loanWaiver,
            // Saved loan values from database
            'grossLoanableAmount' => $grossLoanableAmount,
            'processingFee' => $processingFee,
            'netLoanableAmount' => $netLoanableAmount,
            'termMonths' => $termMonths,
            'interestRate' => $interestRate,
            'totalRepayable' => $totalRepayable,
            'monthlyTotalDue' => $monthlyTotalDue,
            'premiumPaidPercent' => $premiumPaidPercent,
            // Calculated display values
            'totalInterest' => $totalInterest,
            'monthlyLoanPayment' => $monthlyLoanPayment,
            'monthlyInterest' => $monthlyInterest,
            'contractPrice' => $contractPrice,
            'monthlyPremium' => $monthlyPremium,
            'totalPremiumsPaid' => $totalPremiumsPaid,
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

        LoanRequest::where('Id', $loanRequest->Id)->update($updateData);
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

        LoanRequest::where('Id', $loanRequest->Id)->delete();
        Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Loan Request ' . '[Action] Delete ' . '[Target] ' . $loanRequest->Id);

        return redirect('/req-loans')->with('warning', 'Selected loan request has been deleted!');
    }

    // ---- CLIENT VIEW ---- //
    /**
     * Display loan application form for client
     */
    public function showApplyForm(Client $client)
    {
        $contract = \App\Models\Contract::where('clientid', $client->Id)->first();
        $totalPremiumsPaid = Payment::where('clientid', $client->Id)->sum('amountpaid');
        
        return view('pages.loanrequest.apply', compact('client', 'contract', 'totalPremiumsPaid'));
    }

    public function createClientLoanRequest(Request $request, Client $client)
    {
        \Log::info('=== LOAN REQUEST SUBMISSION ===');
        \Log::info('Client ID: ' . $client->Id);
        \Log::info('Request data: ' . json_encode($request->all()));
        
        // Get contract data - use client's own contract info if no separate contract record
        $contract = \App\Models\Contract::where('clientid', $client->Id)->first();
        
        // If no separate contract record, create a virtual contract from client data
        if (!$contract) {
            \Log::info('No separate contract record, using client contract data');
            
            // Get package and payment term data
            $package = \App\Models\Package::find($client->PackageId ?? $client->packageid);
            $paymentTerm = \App\Models\PaymentTerm::find($client->PaymentTermId ?? $client->paymenttermid);
            
            $contract = new \stdClass();
            $contract->Id = null;
            $contract->clientid = $client->Id;
            $contract->contractnumber = $client->ContractNumber ?? $client->contractnumber;
            $contract->packageid = $client->PackageId ?? $client->packageid;
            $contract->packageprice = $client->PackagePrice ?? $client->packageprice ?? $package->Price ?? 0;
            $contract->paymenttermid = $client->PaymentTermId ?? $client->paymenttermid;
            $contract->paymenttermamount = $client->PaymentTermAmount ?? $client->paymenttermamount ?? $paymentTerm->Price ?? 0;
            
            \Log::info('Virtual contract created:', [
                'packageprice' => $contract->packageprice,
                'paymenttermamount' => $contract->paymenttermamount
            ]);
        }
        
        $totalPremiumsPaid = Payment::where('clientid', $client->Id)->sum('amountpaid');
        
        \Log::info('Contract found: ' . ($contract ? 'YES' : 'NO'));
        \Log::info('Total premiums paid: ' . $totalPremiumsPaid);
        
        if (!$contract) {
            \Log::error('No contract found for client');
            return back()->with('error', 'No contract found for this client.');
        }

        // Use defaults if fields missing (backward compatibility with old form)
        $termMonths = $request->input('term_months', 12);
        $waiverSigned = $request->input('waiver_signed', 1);

        // Validate term
        if (!in_array($termMonths, [2, 3, 6, 9, 12])) {
            $termMonths = 12;
        }

        \Log::info('Term months: ' . $termMonths);
        \Log::info('Waiver signed: ' . $waiverSigned);

        // Use LoanCalculator service
        $calculator = new LoanCalculator();
        $details = $calculator->calculateLoanDetails(
            $contract,
            $totalPremiumsPaid,
            intval($termMonths)
        );

        \Log::info('Loan details: ' . json_encode($details));

        if (!$details['eligible']) {
            \Log::warning('Loan not eligible: ' . $details['message']);
            return back()->with('error', $details['message']);
        }

        // Insert loan request with all new fields
        $insertData = [
            'clientid' => $client->Id,
            'contract_id' => $contract->Id,
            'amount' => $details['loanable_amount'],
            'processing_fee' => $details['processing_fee'],
            'net_loan_amount' => $details['net_loan_amount'],
            'interest_rate' => $details['interest_rate'],
            'term_months' => $details['term_months'],
            'total_repayable' => $details['total_repayable'],
            'monthlyamount' => $details['monthly_total_due'],
            'premium_paid_percent' => $details['tier'],
            'waiver_signed' => $waiverSigned ? true : false,
            'waiver_signed_date' => $waiverSigned ? now() : null,
            'daterequested' => date("Y-m-d"),
            'status' => 'Pending',
            'remarks' => 'Not available',
            'code' => date("Ymd") . Str::upper(Str::random(7))
        ];

        \Log::info('Insert data: ' . json_encode($insertData));

        LoanRequest::insert($insertData);

        // Get the new loan request ID
        $loanRequestId = \DB::getPdo()->lastInsertId();

        // Save waiver signature to loan_waivers table
        if ($request->signature_data) {
            \Log::info('Saving waiver signature for loan request ID: ' . $loanRequestId);
            
            LoanWaiver::create([
                'loan_request_id' => $loanRequestId,
                'client_name' => ($client->firstname ?? $client->FirstName ?? '') . ' ' . ($client->lastname ?? $client->LastName ?? ''),
                'contract_number' => $client->contractnumber ?? $client->ContractNumber ?? '',
                'signature_data' => $request->signature_data,
                'signed_date' => now()
            ]);
            
            \Log::info('Waiver signature saved successfully');
        }

        \Log::info('Loan request inserted successfully');

        return back()->with('success', 'Loan request has been forwarded successfully.');
    }

    /**
     * AJAX endpoint to calculate loan details
     */
    public function calculateLoanDetails(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:tblcontract,Id',
            'total_premiums_paid' => 'required|numeric|min:0',
            'term_months' => 'required|integer|in:2,3,6,9,12'
        ]);

        $contract = \App\Models\Contract::find($request->contract_id);
        $calculator = new LoanCalculator();

        $details = $calculator->calculateLoanDetails(
            $contract,
            floatval($request->total_premiums_paid),
            intval($request->term_months)
        );

        return response()->json($details);
    }

    /**
     * Get eligible tiers for a client
     */
    public function getEligibleTiers(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:tblcontract,Id',
            'total_premiums_paid' => 'required|numeric|min:0'
        ]);

        $contract = \App\Models\Contract::find($request->contract_id);
        $contractPrice = $contract->packageprice ?? 0;
        $totalPremiumsPaid = floatval($request->total_premiums_paid);

        $calculator = new LoanCalculator();
        $tiers = $calculator->getEligibleTiers($totalPremiumsPaid, $contractPrice);

        $tierDetails = [];
        foreach ($tiers as $tier) {
            $tierDetails[] = [
                'tier' => $tier,
                'loanable_amount' => $calculator->calculateLoanableAmount($contractPrice, $tier),
                'label' => $tier . '% Premium Paid'
            ];
        }

        return response()->json([
            'eligible_tiers' => $tierDetails,
            'best_tier' => $calculator->getBestTier($totalPremiumsPaid, $contractPrice)
        ]);
    }

    /**
     * Display loan monitoring dashboard
     */
    public function monitoring(Request $request)
    {
        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $branchId = session('user_branchid');

        // Filter by branch for non-admin users
        $branchFilter = ($roleLevel && $roleLevel->Level > 1) ? $branchId : null;

        // Get counts for each category
        $due30Days = LoanRequest::dueInDays(30)
            ->when($branchFilter, function($q) use ($branchFilter) {
                return $q->byBranch($branchFilter);
            })
            ->count();

        $due60Days = LoanRequest::dueInDays(60)
            ->when($branchFilter, function($q) use ($branchFilter) {
                return $q->byBranch($branchFilter);
            })
            ->count();

        $due90Days = LoanRequest::dueInDays(90)
            ->when($branchFilter, function($q) use ($branchFilter) {
                return $q->byBranch($branchFilter);
            })
            ->count();

        $lapsed = LoanRequest::lapsed()
            ->when($branchFilter, function($q) use ($branchFilter) {
                return $q->byBranch($branchFilter);
            })
            ->count();

        $branches = Branch::all();

        return view('pages.loanrequest.monitoring', [
            'due30Days' => $due30Days,
            'due60Days' => $due60Days,
            'due90Days' => $due90Days,
            'lapsed' => $lapsed,
            'branches' => $branches,
            'selectedBranch' => $request->branch_id
        ]);
    }

    /**
     * Get loans data for DataTables (monitoring)
     */
    public function getMonitoringData(Request $request)
    {
        $days = $request->input('days', 30);
        $branchId = $request->input('branch_id');
        $type = $request->input('type', 'due'); // 'due' or 'lapsed'

        $query = LoanRequest::with(['client', 'contract'])
            ->select('tblloanrequest.*');

        if ($type === 'lapsed') {
            $query->lapsed();
        } else {
            $query->dueInDays($days);
        }

        if ($branchId) {
            $query->byBranch($branchId);
        }

        // Role-based filtering
        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        if ($roleLevel && $roleLevel->Level > 1) {
            $userBranchId = session('user_branchid');
            $query->byBranch($userBranchId);
        }

        return DataTables::of($query)
            ->addColumn('client_name', function($loan) {
                return $loan->client ? $loan->client->lastname . ', ' . $loan->client->firstname : 'N/A';
            })
            ->addColumn('contract_number', function($loan) {
                return $loan->contract ? $loan->contract->contractnumber : ($loan->client ? $loan->client->contractnumber : 'N/A');
            })
            ->addColumn('remaining_balance', function($loan) {
                return number_format($loan->remaining_balance, 2);
            })
            ->addColumn('days_until_due', function($loan) {
                $days = $loan->days_until_due;
                if ($days === null) return 'N/A';
                if ($days < 0) return '<span class="text-danger">' . abs($days) . ' days overdue</span>';
                return $days . ' days';
            })
            ->rawColumns(['days_until_due'])
            ->make(true);
    }

    /**
     * Store waiver of rights
     */
    public function storeWaiver(Request $request)
    {
        $request->validate([
            'loan_request_id' => 'required|exists:tblloanrequest,Id',
            'client_name' => 'required|string',
            'contract_number' => 'required|string',
            'signature_data' => 'required|string'
        ]);

        // Save waiver
        $waiver = LoanWaiver::create([
            'loan_request_id' => $request->loan_request_id,
            'client_name' => $request->client_name,
            'contract_number' => $request->contract_number,
            'signature_data' => $request->signature_data,
            'signed_date' => now()
        ]);

        // Update loan request
        LoanRequest::where('Id', $request->loan_request_id)->update([
            'waiver_signed' => true,
            'waiver_signed_date' => now()
        ]);

        return response()->json([
            'success' => true,
            'waiver_id' => $waiver->id
        ]);
    }
}
