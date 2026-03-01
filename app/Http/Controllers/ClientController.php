<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sms;
use App\Models\City;
use App\Models\Role;
use App\Models\User;
use App\Models\Email;
use App\Models\Staff;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Mobile;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use App\Models\Actions;
use App\Models\OrBatch;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Barangay;
use App\Models\Contract;
use App\Models\Province;
use App\Mail\NewClientMail;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\PaymentTerm;
use App\Models\LoanPayments;
use Illuminate\Http\Request;
use App\Models\AssignedPlans;
use App\Models\ContractBatch;
use App\Models\ClientTransfer;
use App\Models\OfficialReceipt;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

/* 2023 SilverDust) S. Maceren */

class ClientController extends Controller
{

    // get available contracts
    public function getAvailableContracts(Request $request)
    {
        $regionId = $request->input('region_id');
        $branchId = $request->input('branch_id');

        if (!$regionId) {
            return response()->json([]);
        }

        $query = ContractBatch::select(
            'tblcontract.ContractNumber',
            'tblcontract.id',
            'tblcontract.Status as ContractStatus',
            'tblcontractbatch.batchcode as BatchCode',
            'tblcontractbatch.RegionId',
            'tblcontractbatch.BranchId',
            'tblbranch.BranchName'
        )
            ->join('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
            ->leftJoin('tblbranch', 'tblcontractbatch.BranchId', '=', 'tblbranch.id')
            ->where('tblcontractbatch.RegionId', $regionId)
            ->where('tblcontract.Status', '1') // 1 = Available
            ->whereNotIn('tblcontract.ContractNumber', function ($sub) {
                $sub->select('contractnumber')->from('tblclient')->whereNotNull('contractnumber');
            });

        // Try branch-specific first
        if ($branchId) {
            $branchQuery = clone $query;
            $contracts = $branchQuery->where('tblcontractbatch.BranchId', $branchId)
                ->orderBy('tblcontract.ContractNumber')
                ->get();

            if ($contracts->isNotEmpty()) {
                Log::info('getAvailableContracts: Results found for branch', ['branchId' => $branchId, 'count' => $contracts->count()]);
                return response()->json($contracts);
            }
        }

        // Fallback or No Branch: Get all available in the region
        $contracts = $query->orderBy('tblcontract.ContractNumber')->limit(300)->get();

        // Log for server-side debugging
        Log::info('getAvailableContracts: Region fallback', [
            'regionId' => $regionId,
            'branchId' => $branchId,
            'count' => $contracts->count()
        ]);

        return response()->json($contracts);
    }

    // search data - tables
    public function searchAll(Request $request)
    {

        if ($request->ajax()) {

            $query = Client::
                select(
                    'tblclient.*',
                    'tblclient.id as cid',
                    'tblregion.RegionName',
                    'tblbranch.BranchName',
                    'tblpackage.Package',
                    'tblpaymentterm.Term'
                )
                ->leftJoin('tblregion', 'tblclient.regionid', '=', 'tblregion.id')
                ->leftJoin('tblbranch', 'tblclient.branchid', '=', 'tblbranch.id')
                ->leftJoin('tblpackage', 'tblclient.packageid', '=', 'tblpackage.id')
                ->leftJoin('tblpaymentterm', 'tblclient.paymenttermid', '=', 'tblpaymentterm.id');

            if ($request->input('status') === 'pending') {
                $query->where('Status', '=', '1');
            } else if ($request->input('status') === 'verified') {
                $query->where('Status', '=', '2');
            } else if ($request->input('status') === 'approved') {
                $query->where('Status', '=', '3');
            } else if ($request->input('status') === 'lapse') {
                // Lapse: Approved clients whose last valid payment was more than 3 months ago
                // OR clients with NO valid payments at all (but still have outstanding balance)
                $threeMonthsAgo = Carbon::now()->subMonths(3)->format('Y-m-d');

                // Use efficient JOINs with pre-aggregated payment data
                $query->where('tblclient.Status', '=', '3')
                    ->leftJoin(\DB::raw("(
                        SELECT 
                            clientid,
                            SUM(AmountPaid) as total_paid,
                            MAX(Date) as last_payment_date
                        FROM tblpayment
                        WHERE VoidStatus != '1'
                        AND (Remarks IS NULL OR Remarks IN ('Standard', 'Partial', 'Custom'))
                        GROUP BY clientid
                    ) as payment_stats"), 'tblclient.id', '=', 'payment_stats.clientid')
                    // Lapsed: last payment > 3 months ago OR no payments at all
                    ->where(function ($q) use ($threeMonthsAgo) {
                        $q->where('payment_stats.last_payment_date', '<', $threeMonthsAgo)
                            ->orWhereNull('payment_stats.last_payment_date');
                    })
                    // Not fully paid yet
                    ->whereRaw("COALESCE(payment_stats.total_paid, 0) < (
                        CASE 
                            WHEN tblpaymentterm.Term = 'Spotcash' THEN tblpaymentterm.Price
                            WHEN tblpaymentterm.Term = 'Annual' THEN tblpaymentterm.Price * 5
                            WHEN tblpaymentterm.Term = 'Semi-Annual' THEN tblpaymentterm.Price * 10
                            WHEN tblpaymentterm.Term = 'Quarterly' THEN tblpaymentterm.Price * 20
                            WHEN tblpaymentterm.Term = 'Monthly' THEN tblpaymentterm.Price * 60
                            ELSE tblpaymentterm.Price * 60
                        END
                    )");
            } else if ($request->input('status') === 'active') {
                // Active: Approved clients with outstanding balance and last payment within 3 months
                $threeMonthsAgo = Carbon::now()->subMonths(3)->format('Y-m-d');

                // Use efficient JOINs with pre-aggregated payment data
                $query->where('tblclient.Status', '=', '3')
                    ->leftJoin(\DB::raw("(
                        SELECT 
                            clientid,
                            SUM(AmountPaid) as total_paid,
                            MAX(Date) as last_payment_date
                        FROM tblpayment
                        WHERE VoidStatus != '1'
                        AND (Remarks IS NULL OR Remarks IN ('Standard', 'Partial', 'Custom'))
                        GROUP BY clientid
                    ) as payment_stats"), 'tblclient.id', '=', 'payment_stats.clientid')
                    // Active: last payment within 3 months
                    ->where('payment_stats.last_payment_date', '>=', $threeMonthsAgo)
                    // Not fully paid yet
                    ->whereRaw("COALESCE(payment_stats.total_paid, 0) < (
                        CASE 
                            WHEN tblpaymentterm.Term = 'Spotcash' THEN tblpaymentterm.Price
                            WHEN tblpaymentterm.Term = 'Annual' THEN tblpaymentterm.Price * 5
                            WHEN tblpaymentterm.Term = 'Semi-Annual' THEN tblpaymentterm.Price * 10
                            WHEN tblpaymentterm.Term = 'Quarterly' THEN tblpaymentterm.Price * 20
                            WHEN tblpaymentterm.Term = 'Monthly' THEN tblpaymentterm.Price * 60
                            ELSE tblpaymentterm.Price * 60
                        END
                    )");
            }

            // Filter by branch if provided
            if (!empty($request->input('branch'))) {
                $query->where('tblbranch.BranchName', $request->input('branch'));
            }

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');

                // Only search if term is at least 2 characters
                if (strlen($searchTerm) >= 2) {
                    $query->where(function ($query) use ($searchTerm) {
                        // Prioritize exact matches on contract numbers first
                        if (is_numeric($searchTerm)) {
                            $query->where('ContractNumber', 'like', "%$searchTerm%");
                        } else {
                            // Use OR conditions but limit to most likely matches
                            $query->where('LastName', 'like', "$searchTerm%")  // Starts with
                                ->orWhere('FirstName', 'like', "$searchTerm%")  // Starts with
                                ->orWhere('ContractNumber', 'like', "%$searchTerm%")
                                ->orWhere('tblregion.RegionName', 'like', "$searchTerm%")
                                ->orWhere('tblbranch.BranchName', 'like', "$searchTerm%");
                        }
                    });
                }
            }

            return DataTables::of($query)->toJson();
        }

        // Fetch branches data for the filter dropdown
        $branches = Branch::orderBy("branchname", "asc")->get();

        return view('pages.client.client', ['branches' => $branches]);
    }

    // search data - selected client
    /**
     * Resolve address code to human-readable name
     */
    private function resolveAddressName($code, $type)
    {
        if (empty($code)) {
            return '';
        }

        try {
            $address = DB::table('tbladdress')
                ->where('code', $code)
                ->where('address_type', $type)
                ->value('description');

            return $address ?: $code; // Return code if not found
        } catch (\Exception $e) {
            return $code; // Return original code on error
        }
    }

    public function viewClientInfo(Request $request, Client $client)
    {

        $clients = Client
            ::select(
                'tblclient.*',
                'tblclient.Id as cid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblpackage.Package',
                'tblpaymentterm.Id',
                'tblpaymentterm.PackageId',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price',
                'tblstaff.LastName as FSALastName',
                'tblstaff.FirstName as FSAFirstName',
                'tblstaff.MiddleName as FSAMiddleName'
            )
            ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
            ->leftJoin('tblstaff', 'tblclient.RecruitedBy', '=', 'tblstaff.id')
            ->where('tblclient.id', $client->Id)
            ->first();

        // Resolve ALL 6 address codes in ONE batched query instead of 6 separate queries
        if ($clients) {
            $addressCodes = array_filter([
                $clients->Province,
                $clients->City,
                $clients->Barangay,
                $clients->HomeProvince,
                $clients->HomeCity,
                $clients->HomeBarangay,
            ]);

            $addressMap = [];
            if (!empty($addressCodes)) {
                $addressRows = DB::table('tbladdress')
                    ->whereIn('code', array_unique($addressCodes))
                    ->whereIn('address_type', ['province', 'citymun', 'barangay'])
                    ->select('code', 'address_type', 'description')
                    ->get();

                foreach ($addressRows as $row) {
                    $addressMap[$row->address_type][$row->code] = $row->description;
                }
            }

            $clients->ProvinceDisplay = $addressMap['province'][$clients->Province] ?? ($clients->Province ?: '');
            $clients->CityDisplay = $addressMap['citymun'][$clients->City] ?? ($clients->City ?: '');
            $clients->BarangayDisplay = $addressMap['barangay'][$clients->Barangay] ?? ($clients->Barangay ?: '');
            $clients->HomeProvinceDisplay = $addressMap['province'][$clients->HomeProvince] ?? ($clients->HomeProvince ?: '');
            $clients->HomeCityDisplay = $addressMap['citymun'][$clients->HomeCity] ?? ($clients->HomeCity ?: '');
            $clients->HomeBarangayDisplay = $addressMap['barangay'][$clients->HomeBarangay] ?? ($clients->HomeBarangay ?: '');
        }

        // Fetch payments for this client (uses idx_tblpayment_clientid index)
        $payments = Payment::query()
            ->select('tblpayment.*')
            ->where('tblpayment.clientid', $client->Id)
            ->orderBy('tblpayment.date', 'desc')
            ->orderBy('tblpayment.installment', 'desc')
            ->get();

        // Batch-fetch SeriesCode in one query instead of chained JOINs
        // The old triple JOIN (tblpayment→tblofficialreceipt→tblorbatch) was doing a
        // full table scan on 27,669 rows causing 30+ second load times
        if ($payments->isNotEmpty()) {
            $orIds = $payments->pluck('ORId')->filter()->unique()->values()->toArray();
            if (!empty($orIds)) {
                $seriesMap = DB::table('tblofficialreceipt')
                    ->join('tblorbatch', 'tblofficialreceipt.orbatchid', '=', 'tblorbatch.id')
                    ->whereIn('tblofficialreceipt.id', $orIds)
                    ->select('tblofficialreceipt.id as orid', 'tblorbatch.SeriesCode')
                    ->get()
                    ->keyBy('orid');

                $payments = $payments->map(function ($payment) use ($seriesMap) {
                    $payment->SeriesCode = $seriesMap[$payment->ORId]->SeriesCode ?? null;
                    return $payment;
                });
            }
        }

        // loan payments
        $hasLoanRequest = LoanRequest::query()
            ->where('clientid', $client->Id)
            ->where('status', 'Approved')
            ->where('remarks', '<>', 'Completed')
            ->first();

        $loanBalance = 0;
        $loanPayments = collect();
        if ($hasLoanRequest) {
            $loanPayments = LoanPayment::query()
                ->select(
                    'tblloanpayment.*',
                    'tblorbatch.SeriesCode'
                )
                ->leftJoin('tblofficialreceipt', 'tblloanpayment.orid', '=', 'tblofficialreceipt.id')
                ->leftJoin('tblorbatch', 'tblofficialreceipt.orbatchid', '=', 'tblorbatch.id')
                ->where('tblloanpayment.clientid', $hasLoanRequest->clientid)
                ->where('tblloanpayment.loanrequestid', $hasLoanRequest->id)
                ->get();

            $totalLoanPayments = $loanPayments->sum('amount');
            $loanBalance = $hasLoanRequest->amount - $totalLoanPayments;
        }

        // get details of the assigned member from the client
        $assignedMemberData = AssignedPlans::where('clientid', $client->Id)
            ->first();

        $assignedBy = [];
        if ($assignedMemberData) {
            $assignedBy = Staff::where('userid', $assignedMemberData->AssignedById)
                ->first();
        }

        // cfp approval action - batch Role + Actions into session-cached lookup
        $cfpApprover = 0;
        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'CFP Approval')->first();
        if ($roleLevel && $actions && $roleLevel->Level <= $actions->RoleLevel) {
            $cfpApprover = 1;
        }

        // check if it is available for transfer - for transfer client purpose
        $canTransfer = ClientTransfer::query()->where('clientid', $client->Id)->first();

        return view('pages.client.client-view', [
            'clients' => $clients,
            'payments' => $payments,
            'hasLoanRequest' => $hasLoanRequest,
            'loanPayments' => $loanPayments,
            'loanBalance' => $loanBalance,
            'assignedMemberData' => $assignedMemberData,
            'staff' => $assignedBy,
            'cfpApprover' => $cfpApprover,
            'canTransfer' => $canTransfer
        ]);
    }

    // add payment form screen
    public function addClientPayment(Client $client)
    {

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Add Payment')->first();
        if ($roleLevel->Level <= $actions->RoleLevel) {

            // get details of the assigned member from the client
            $assignedMemberData = AssignedPlans::where('clientid', $client->Id)
                ->first();

            // get client payment term
            $clientTerm = PaymentTerm::query()
                ->where('packageid', $client->PackageID)
                ->where('id', $client->PaymentTermId)
                ->first();

            // get payments data
            $payments = Payment
                ::where('clientid', $client->Id)
                ->orderBy('date', 'desc')
                ->orderBy('installment', 'desc')
                ->get();

            return view('pages.client.client-addpayment', [
                'clients' => $client,
                'client_terms' => $clientTerm,
                'payments' => $payments,
                'assignedMemberData' => $assignedMemberData
            ]);
        } else {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // add loan payment form screen
    public function addClientLoanPayment(Client $client)
    {

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Add Payment')->first();
        if ($roleLevel->Level <= $actions->RoleLevel) {

            // get loan payments data
            $hasLoanRequest = LoanRequest::query()
                ->where('clientid', $client->Id)
                ->where('status', 'Approved')
                ->where('remarks', '<>', 'Completed')
                ->first();

            $loanPayments = LoanPayment::query()
                ->where('clientid', $hasLoanRequest->ClientId)
                ->where('loanrequestid', $hasLoanRequest->Id)
                ->get();

            $totalMonthlyDue = $hasLoanRequest->MonthlyAmount;
            $totalLoanPayments = $loanPayments->sum('Amount');
            $loanBalance = $hasLoanRequest->Amount - $totalLoanPayments;

            // temp - get monthly loan amount
            $term = 12;
            $amounts = [];
            for ($i = $totalMonthlyDue; $i <= $loanBalance; $i += $totalMonthlyDue) {
                $amounts[] = $i;
            }
            $amounts[] = $loanBalance;

            return view('pages.client.client-addloanpayment', [
                'clients' => $client,
                'loanRequestData' => $hasLoanRequest,
                'loanBalance' => $loanBalance,
                'loanMonthlyAmount' => $totalMonthlyDue,
                'amounts' => $amounts
            ]);
        } else {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // client form screen
    public function clientFormScreen(Client $client)
    {

        $staffs = Staff::orderBy("lastname", "asc")->get();
        $regions = Region::orderBy("regionname", "asc")->get();
        $branches = Branch::orderBy("branchname", "asc")->get();
        $provinces = Province::orderBy("province", "asc")->get();
        $cities = City::orderBy("city", "asc")->get();
        $barangays = Barangay::orderBy("barangay", "asc")->get();
        $mobiles = Mobile::all();
        $emails = Email::all();
        $packages = Package::orderBy("package", "asc")
            ->where("active", 1)
            ->get();

        // Get address regions for new cascading address system
        $addressRegions = DB::table('tbladdress')
            ->where('address_type', 'region')
            ->select('code', 'description')
            ->orderBy('description')
            ->get();

        // Resolve home address codes for pre-filling
        $client->HomeProvinceDisplay = $this->resolveAddressName($client->HomeProvince, 'province');
        $client->HomeCityDisplay = $this->resolveAddressName($client->HomeCity, 'citymun');
        $client->HomeBarangayDisplay = $this->resolveAddressName($client->HomeBarangay, 'barangay');

        // Region mapping: Convert old RegionId to new address codes
        $regionMapping = [
            '1' => '07', // Cebu North -> REGION VII (CENTRAL VISAYAS)
            '2' => '07', // Cebu South -> REGION VII (CENTRAL VISAYAS) 
            '3' => '10', // Mindanao -> REGION X (NORTHERN MINDANAO)
            '4' => '07', // Bohol -> REGION VII (CENTRAL VISAYAS)
            '5' => '06', // Negros -> REGION VI (WESTERN VISAYAS)
            '6' => '08', // Leyte -> REGION VIII (EASTERN VISAYAS)
            '7' => '08', // Southern Leyte -> REGION VIII (EASTERN VISAYAS)
            '8' => '07', // Negros Oriental -> REGION VII (CENTRAL VISAYAS)
        ];

        // Check if user can edit contract number
        // Rule: If contract is NOT approved (status != '3'), any user can edit
        // If contract IS approved, only admin/approver can edit
        $userRoleId = session('user_roleid');
        $userId = session('user_id');

        // Use Role model to get the role (consistent with rest of codebase)
        $roleData = Role::query()->where('id', $userRoleId)->first();
        $canEditContractNumber = false;

        // Check if contract is not approved yet - allow any user to edit contract number
        if ($client->exists && $client->Status != '3') {
            $canEditContractNumber = true;
        } else {
            // For approved contracts or new clients, check if user is admin/approver
            // Also check the staff's position to ensure we get the correct role
            // This handles cases where tbluser.RoleId may differ from tblstaff.Position
            $staffRole = null;
            $staffData = \DB::table('tblstaff')->where('userid', $userId)->first();
            if ($staffData) {
                $staffPosition = $staffData->Position ?? $staffData->position ?? null;
                if ($staffPosition) {
                    $staffRole = Role::query()->where('id', $staffPosition)->first();
                }
            }

            // Get role name from roleData first, then staffRole as fallback
            $userRoleName = null;

            if ($roleData) {
                $userRoleName = $roleData->Role;
            }

            // If role name not found from roleData, try staffRole (from tblstaff.Position)
            if ($userRoleName === null && $staffRole) {
                $userRoleName = $staffRole->Role;
            }

            // Allow editing if role is Administrator or Approver (case-insensitive)
            $allowedRoles = ['administrator', 'approver'];
            if ($userRoleName !== null && in_array(strtolower($userRoleName), $allowedRoles)) {
                $canEditContractNumber = true;
            }
        }

        // Debug logging
        \Log::info('Contract Number Edit Permission Check:', [
            'user_id' => $userId,
            'user_roleid' => $userRoleId,
            'client_status' => $client->exists ? $client->Status : 'New',
            'can_edit' => $canEditContractNumber,
            'reason' => ($client->exists && $client->Status != '3') ? 'Contract not approved - all users can edit' : 'Approved or new - role check applied'
        ]);

        if (!$client->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if ($roleLevel->level <= $actions->RoleLevel) {
                return view('pages.client.client-create', [
                    'staffs' => $staffs,
                    'regions' => $regions,
                    'branches' => $branches,
                    'provinces' => $provinces,
                    'cities' => $cities,
                    'barangays' => $barangays,
                    'mobiles' => $mobiles,
                    'emails' => $emails,
                    'packages' => $packages,
                    'addressRegions' => $addressRegions,
                    'regionMapping' => $regionMapping,
                    'canEditContractNumber' => $canEditContractNumber
                ]);
            } else {
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } else {

            if ($client->Status != '3') {

                $paymentsInfo = Payment::where('clientid', $client->Id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($paymentsInfo == null) {
                    $paymentsInfo = new Payment();
                }

                $orInfo = OfficialReceipt::where('id', $paymentsInfo->ORId)
                    ->where('ornumber', $paymentsInfo->ORNo)
                    ->first();
                if ($orInfo == null) {
                    $orbatchInfo = new OrBatch();
                } else {
                    $orbatchInfo = OrBatch::where('id', $orInfo->ORBatchId)->first();
                }

                // If contract is pending (status = '1'), allow any user to update
                // Otherwise, check role permissions
                $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
                $actions = Actions::query()->where('action', '=', 'Update')->first();

                $canUpdate = ($client->Status == '1') || ($roleLevel->Level <= $actions->RoleLevel);

                if ($canUpdate) {
                    return view('pages.client.client-update', [
                        'clients' => $client,
                        'paymentinfo' => $paymentsInfo,
                        'orbatchinfo' => $orbatchInfo,
                        'orinfo' => $orInfo,
                        'staffs' => $staffs,
                        'regions' => $regions,
                        'branches' => $branches,
                        'provinces' => $provinces,
                        'cities' => $cities,
                        'barangays' => $barangays,
                        'mobiles' => $mobiles,
                        'emails' => $emails,
                        'packages' => $packages,
                        'addressRegions' => $addressRegions,
                        'regionMapping' => $regionMapping,
                        'canEditContractNumber' => $canEditContractNumber
                    ]);
                } else {
                    return redirect()->back()->with('error', 'You do not have access to this function.');
                }
            } else {

                $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
                $actions = Actions::query()->where('action', '=', 'Update')->first();
                if ($roleLevel->Level <= $actions->RoleLevel) {
                    return view('pages.client.client-update', [
                        'clients' => $client,
                        'staffs' => $staffs,
                        'regions' => $regions,
                        'branches' => $branches,
                        'provinces' => $provinces,
                        'cities' => $cities,
                        'barangays' => $barangays,
                        'mobiles' => $mobiles,
                        'emails' => $emails,
                        'packages' => $packages,
                        'addressRegions' => $addressRegions,
                        'regionMapping' => $regionMapping,
                        'canEditContractNumber' => $canEditContractNumber
                    ]);
                } else {
                    return redirect()->back()->with('error', 'You do not have access to this function.');
                }
            }
        }
    }
    // client form screen - for transfer creation
    public function transferClientFormScreen(Client $client, Request $request)
    {

        $staffs = Staff::orderBy("lastname", "asc")->get();
        $regions = Region::orderBy("regionname", "asc")->get();
        $branches = Branch::orderBy("branchname", "asc")->get();
        $provinces = Province::orderBy("province", "asc")->get();
        $cities = City::orderBy("city", "asc")->get();
        $barangays = Barangay::orderBy("barangay", "asc")->get();
        $mobiles = Mobile::all();
        $emails = Email::all();
        $packages = Package::orderBy("package", "asc")
            ->where("active", 1)
            ->get();

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'New Client Transfer')->first();
        if ($roleLevel->Level <= $actions->RoleLevel) {
            return view('pages.client.client-transfer-create', [
                'client' => $client,
                'staffs' => $staffs,
                'regions' => $regions,
                'branches' => $branches,
                'provinces' => $provinces,
                'cities' => $cities,
                'barangays' => $barangays,
                'mobiles' => $mobiles,
                'emails' => $emails,
                'packages' => $packages
            ]);
        } else {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // insert new data
    public function createClient(Request $request)
    {

        // Handle mobile number splitting - the form sends mobilenumber but validation expects mobilenetwork and mobileno
        if ($request->has('mobilenumber') && !$request->has('mobilenetwork')) {
            $mobileNumber = preg_replace('/\s+/', '', $request->input('mobilenumber')); // Remove spaces
            Log::info('Processing mobile number in createClient: ' . $mobileNumber);

            // Extract network prefix (first 3 digits) and mobile number (last 7 digits)
            if (strlen($mobileNumber) == 10) {
                $mobileNetwork = substr($mobileNumber, 0, 3);
                $mobileNo = substr($mobileNumber, 3, 7);

                // Add these to the request for validation
                $request->merge([
                    'mobilenetwork' => $mobileNetwork,
                    'mobileno' => $mobileNo
                ]);

                Log::info('Mobile number split - Network: ' . $mobileNetwork . ', Number: ' . $mobileNo);
            }
        }

        // custom error message - FIELD SPECIFIC to help users identify exactly what's missing
        $messages = [
            'contractno.required' => 'Contract Number is required. Please select a contract.',
            'package.required' => 'Package is required. Please select a package.',
            'packageprice.required' => 'Package Price is required.',
            'paymentterm.required' => 'Payment Term is required. Please select a payment term.',
            'termamount.required' => 'Term Amount is required.',
            'region.required' => 'Region is required. Please select a region.',
            'branch.required' => 'Branch is required. Please select a branch.',
            'paymentamount.required' => 'Payment Amount is required. Please select or enter a payment amount.',
            'orseriescode.required' => 'O.R. Series Code is required. Please select an O.R. series.',
            'ornumber.required' => 'O.R. Number is required. Please select an O.R. number.',
            'paymentdate.required' => 'Payment Date is required. Please select a payment date.',
            'lastname.required' => 'Last Name is required. Please enter the client\'s last name.',
            'lastname.min' => 'Last Name is too short (minimum 1 character).',
            'lastname.max' => 'Last Name is too long (maximum 30 characters).',
            'firstname.required' => 'First Name is required. Please enter the client\'s first name.',
            'firstname.min' => 'First Name is too short (minimum 1 character).',
            'firstname.max' => 'First Name is too long (maximum 30 characters).',
            'gender.required' => 'Gender is required. Please select a gender.',
            'birthdate.required' => 'Birth Date is required. Please select a birth date.',
            'age.required' => 'Age is required. Please enter the client\'s age.',
            'age.min' => 'Age must be at least 18 years old.',
            'bestplacetocollect.required' => 'Best Place to Collect is required. Please enter a collection location.',
            'besttimetocollect.required' => 'Best Time to Collect is required. Please enter a collection time.',
            'province.required' => 'Province is required. Please select a province.',
            'city.required' => 'City/Municipality is required. Please select a city.',
            'barangay.required' => 'Barangay is required. Please select a barangay.',
            'mobilenumber.required_without' => 'Please provide either a Mobile Number or Telephone Number.',
            'telephone.required_without' => 'Please provide either a Mobile Number or Telephone Number.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'contractno' => 'required',
            'package' => 'required',
            'packageprice' => 'required',
            'paymentterm' => 'required',
            'termamount' => 'required',
            'region' => 'required',
            'branch' => 'required',
            'recruitedby' => 'nullable',
            'downpaymenttype' => 'nullable',
            'paymentamount' => 'required',
            'orseriescode' => 'required',
            'ornumber' => 'required',
            'paymentdate' => 'required',
            'lastname' => 'required|min:1|max:30',
            'firstname' => 'required|min:1|max:30',
            'middlename' => 'nullable',
            'gender' => 'required',
            'birthdate' => 'required',
            'age' => 'required|numeric|min:18',
            'birthplace' => 'nullable',
            'civilstatus' => 'nullable',
            'religion' => 'nullable',
            'occupation' => 'nullable',
            'bestplacetocollect' => 'required',
            'besttimetocollect' => 'required',
            'province' => 'required',
            'city' => 'required',
            'barangay' => 'required',
            'zipcode' => 'nullable',
            'street' => 'nullable',
            'telephone' => 'nullable|required_without:mobilenumber',
            'mobilenumber' => 'nullable|required_without:telephone',
            'mobilenetwork' => 'nullable',
            'mobileno' => 'nullable',
            'email' => 'nullable',
            'emailaddress' => 'nullable',
            'principalbeneficiary' => 'nullable',
            'principalbeneficiaryage' => 'nullable',
            'principalbeneficiaryrelation' => 'nullable',
            'principalbeneficiaryid' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'beneficiary1' => 'nullable',
            'beneficiary1age' => 'nullable',
            'beneficiary2' => 'nullable',
            'beneficiary2age' => 'nullable',
            'beneficiary3' => 'nullable',
            'beneficiary3age' => 'nullable',
            'beneficiary4' => 'nullable',
            'beneficiary4age' => 'nullable',
            'home_region' => 'nullable',
            'home_province' => 'nullable',
            'home_city' => 'nullable',
            'home_barangay' => 'nullable',
            'home_zipcode' => 'nullable',
            'home_street' => 'nullable',
            'same_as_current_address' => 'nullable'
        ], $messages);

        if ($fields->fails()) {
            Log::error('=== VALIDATION FAILED (createClient) ===');
            Log::error('Validation Errors: ', $fields->errors()->toArray());
            Log::error('Failed Fields: ' . implode(', ', array_keys($fields->errors()->toArray())));
            Log::info('=== END VALIDATION DEBUG (createClient) ===');

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $fields->errors()
                ], 422);
            }
            
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();

        $contractNo = strip_tags($validatedData['contractno']);
        $packageId = strip_tags($validatedData['package']);
        $packagePriceRaw = strip_tags($validatedData['packageprice']);
        $packagePrice = (float) preg_replace('/[^0-9.]/', '', $packagePriceRaw);
        $termId = strip_tags($validatedData['paymentterm']);
        $termAmount = strip_tags($validatedData['termamount']);
        $regionId = strip_tags($validatedData['region']);
        $branchId = strip_tags($validatedData['branch']);
        $recruitedById = !empty($validatedData['recruitedby']) ? (int) strip_tags($validatedData['recruitedby']) : null;
        $downpaymentType = strip_tags($validatedData['downpaymenttype']);
        $paymentAmount = strip_tags($validatedData['paymentamount']);
        $orSeriesCode = strip_tags($validatedData['orseriescode']);
        $orNo = strip_tags($validatedData['ornumber']);
        $paymentMethod = strip_tags($validatedData['paymentmethod'] ?? 'Cash');
        $paymentDate = strip_tags($validatedData['paymentdate']);
        $lastName = strip_tags($validatedData['lastname']);
        $firstName = strip_tags($validatedData['firstname']);
        $middleName = strip_tags($validatedData['middlename'] ?? '');
        $gender = strip_tags($validatedData['gender']);
        $birthDate = strip_tags($validatedData['birthdate']);
        $age = strip_tags($validatedData['age']);
        $birthPlace = strip_tags($validatedData['birthplace'] ?? '');
        $civilStatus = strip_tags($validatedData['civilstatus'] ?? 'Single');
        $religion = strip_tags($validatedData['religion'] ?? '');
        $occupation = strip_tags($validatedData['occupation'] ?? '');
        $bestPlaceToCollect = strip_tags($validatedData['bestplacetocollect']);
        $bestTimeToCollect = strip_tags($validatedData['besttimetocollect']);
        $province = strip_tags($validatedData['province']);
        $city = strip_tags($validatedData['city']);
        $barangay = strip_tags($validatedData['barangay']);
        $zipcode = strip_tags($validatedData['zipcode'] ?? '');
        $street = strip_tags($validatedData['street'] ?? '');
        $telephoneRaw = strip_tags($validatedData['telephone'] ?? '');
        $telephone = preg_replace('/\s+/', '', (string) $telephoneRaw);
        $mobileNetwork = strip_tags($validatedData['mobilenetwork'] ?? '');
        $mobileNo = strip_tags($validatedData['mobileno'] ?? '');
        $mobileNumberInput = preg_replace('/\s+/', '', (string) ($validatedData['mobilenumber'] ?? ''));
        $email = strip_tags($validatedData['email']);
        $emailAddress = strip_tags($validatedData['emailaddress']);
        $principalBeneficiary = strip_tags($validatedData['principalbeneficiary'] ?? '');
        $principalBeneficiaryAge = strip_tags($validatedData['principalbeneficiaryage'] ?? '');
        $principalBeneficiaryRelation = strip_tags($validatedData['principalbeneficiaryrelation'] ?? '');

        $principalBeneficiaryIdPath = null;
        if ($request->hasFile('principalbeneficiaryid')) {
            $principalBeneficiaryIdPath = $request->file('principalbeneficiaryid')->store('beneficiary_ids', 'public');
        }

        $beneficiary1 = strip_tags($validatedData['beneficiary1'] ?? '');
        $beneficiary1Age = strip_tags($validatedData['beneficiary1age'] ?? '');
        $beneficiary2 = strip_tags($validatedData['beneficiary2'] ?? '');
        $beneficiary2Age = strip_tags($validatedData['beneficiary2age'] ?? '');
        $beneficiary3 = strip_tags($validatedData['beneficiary3'] ?? '');
        $beneficiary3Age = strip_tags($validatedData['beneficiary3age'] ?? '');
        $beneficiary4 = strip_tags($validatedData['beneficiary4'] ?? '');
        $beneficiary4Age = strip_tags($validatedData['beneficiary4age'] ?? '');

        // Home address fields
        $sameAsCurrentAddress = $request->has('same_as_current_address');
        if ($sameAsCurrentAddress) {
            $homeRegion = strip_tags($request->input('address_region', ''));
            $homeProvince = $province;
            $homeCity = $city;
            $homeBarangay = $barangay;
            $homeZipcode = $zipcode;
            $homeStreet = $street;
        } else {
            $homeRegion = strip_tags($request->input('home_region', ''));
            $homeProvince = strip_tags($request->input('home_province', ''));
            $homeCity = strip_tags($request->input('home_city', ''));
            $homeBarangay = strip_tags($request->input('home_barangay', ''));
            $homeZipcode = strip_tags($request->input('home_zipcode', ''));
            $homeStreet = strip_tags($request->input('home_street', ''));
        }

        $mobilenumber = null;
        if (!empty($mobileNumberInput)) {
            $mobilenumber = $mobileNumberInput;
            if (strlen($mobilenumber) === 10 && str_starts_with($mobilenumber, '9')) {
                $mobilenumber = '0' . $mobilenumber;
            }
        } else if (!empty($mobileNetwork) && !empty($mobileNo)) {
            $mobilenumber = '0' . $mobileNetwork . $mobileNo;
        }
        
        // Build complete email only if email is provided
        $emailcomplete = null;
        if (!empty($email) && !empty($emailAddress)) {
            $emailcomplete = $email . '@' . $emailAddress;
        } else if (!empty($email)) {
            $emailcomplete = $email;
        }

        $status = '1';
        $remarks = "To be verified";
        $fsaComsRem = '0';

        // check if contract no is available (Relaxed branch check to allow regional cross-usage)
        $availableContract = '1';
        $contractExists = ContractBatch::select('tblcontractbatch.*', 'tblcontract.id as contractid')
            ->leftJoin('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
            ->where('contractnumber', $contractNo)
            ->where('regionid', $regionId)
            // Removed strict BranchId check here to allow Danao contracts to be used for Alegria (same Region)
            ->where('status', $availableContract)
            ->first();

        if ($contractExists) {
            // ... (check if OR exists)

            // check if OR no is available
            $availableOR = '1';
            if ($downpaymentType == "Standard") {
                $remarks = "Standard";
                $orType = "1";
            } else if ($downpaymentType == "Partial") {
                $remarks = "Partial";
                $orType = "2";
            }

            $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('ornumber', $orNo)
                ->where('regionid', $regionId)
                ->where('branchid', $branchId)
                ->where('status', $availableOR)
                // Relaxed Type check to allow cross-usage (e.g. Standard series for Partial payment)
                // ->where('type', $orType)
                ->where('seriescode', $orSeriesCode)
                ->first();

            if ($orExists) {

                $searchedOfficialReceiptId = $orExists->id;

                // create a new client
                try {

                    $insertClientData = [
                        'contractnumber' => $contractNo,
                        'packageid' => $packageId,
                        'paymenttermid' => $termId,
                        'paymenttermamount' => (float) preg_replace('/[^0-9.]/', '', (string) $termAmount),
                        'regionid' => $regionId,
                        'branchid' => $branchId,
                        'recruitedby' => $recruitedById,
                        'lastname' => $lastName,
                        'firstname' => $firstName,
                        'middlename' => $middleName,
                        'gender' => $gender,
                        'birthdate' => $birthDate,
                        'age' => $age,
                        'birthplace' => $birthPlace,
                        'civilstatus' => $civilStatus,
                        'religion' => $religion,
                        'occupation' => $occupation,
                        'bestplacetocollect' => $bestPlaceToCollect,
                        'besttimetocollect' => $bestTimeToCollect,
                        'province' => $province,
                        'city' => $city,
                        'barangay' => $barangay,
                        'zipcode' => $zipcode,
                        'street' => $street,
                        'homenumber' => $telephone,
                        'mobilenumber' => $mobilenumber,
                        'emailaddress' => $emailcomplete,
                        'principalbeneficiaryname' => $principalBeneficiary,
                        'principalbeneficiaryage' => $principalBeneficiaryAge,
                        'secondary1name' => $beneficiary1,
                        'secondary1age' => $beneficiary1Age,
                        'secondary2name' => $beneficiary2,
                        'secondary2age' => $beneficiary2Age,
                        'secondary3name' => $beneficiary3,
                        'secondary3age' => $beneficiary3Age,
                        'secondary4name' => $beneficiary4,
                        'secondary4age' => $beneficiary4Age,
                        'status' => $status,
                        'remarks' => $remarks,
                        'fsacomsrem' => $fsaComsRem
                    ];

                    Log::info('createClient tblclient schema check', [
                        'db_connection' => DB::getDefaultConnection(),
                        'db_database' => DB::connection()->getDatabaseName(),
                        'tblclient_has_packageprice' => Schema::hasColumn('tblclient', 'packageprice') ? 1 : 0,
                    ]);

                    if (Schema::hasColumn('tblclient', 'principalbeneficiaryrelation')) {
                        $insertClientData['principalbeneficiaryrelation'] = $principalBeneficiaryRelation;
                    }

                    if (Schema::hasColumn('tblclient', 'principalbeneficiaryid_path')) {
                        $insertClientData['principalbeneficiaryid_path'] = $principalBeneficiaryIdPath;
                    }

                    if (Schema::hasColumn('tblclient', 'packageprice')) {
                        $insertClientData['packageprice'] = (float) preg_replace('/[^0-9.]/', '', (string) $packagePrice);
                    }

                    if (Schema::hasColumn('tblclient', 'homeregion')) {
                        $insertClientData['homeregion'] = $homeRegion;
                    }

                    if (Schema::hasColumn('tblclient', 'homeprovince')) {
                        $insertClientData['homeprovince'] = $homeProvince;
                    }

                    if (Schema::hasColumn('tblclient', 'homecity')) {
                        $insertClientData['homecity'] = $homeCity;
                    }

                    if (Schema::hasColumn('tblclient', 'homebarangay')) {
                        $insertClientData['homebarangay'] = $homeBarangay;
                    }

                    if (Schema::hasColumn('tblclient', 'homezipcode')) {
                        $insertClientData['homezipcode'] = $homeZipcode;
                    }

                    if (Schema::hasColumn('tblclient', 'homestreet')) {
                        $insertClientData['homestreet'] = $homeStreet;
                    }

                    $clientId = Client::insertGetId($insertClientData);
                    Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Insert ' . '[Target] ' . $contractNo);

                    // add new payment data
                    $comsMultiplier = '9';

                    // standard payment
                    if ($orType == '1' && $downpaymentType == 'Standard') {
                        $current_installment = $paymentAmount / $termAmount;
                    }
                    // partial payment
                    else if ($orType == '2') {
                        $current_installment = 1;
                    }

                    if ($paymentMethod == 'Cash') {
                        $paymentMethod = '1';
                    }

                    $insertPaymentData = [
                        'orno' => $orNo,
                        'clientid' => $clientId,
                        'orid' => $searchedOfficialReceiptId,
                        'amountpaid' => (float) preg_replace('/[^0-9.]/', '', (string) $paymentAmount),
                        'installment' => $current_installment,
                        'comsmultiplier' => $comsMultiplier,
                        'date' => $paymentDate,
                        'paymenttype' => $paymentMethod,
                        'remarks' => $remarks,
                        'createdby' => session('user_id'),
                        'datecreated' => date("Y-m-d")
                    ];
                    Payment::insert($insertPaymentData);
                    Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Insert Payment' . '[Target] ' . $clientId);

                    // Check if request is AJAX
                    if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                        return response()->json([
                            'success' => true,
                            'message' => 'Client created successfully',
                            'client_id' => $clientId,
                            'redirect' => '/client'
                        ]);
                    }

                    return redirect('/client')->with('success', 'Added new client!');
                } catch (\Exception $e) {
                    Log::error('Error creating client: ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                    
                    // Check if request is AJAX
                    if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                        return response()->json([
                            'success' => false,
                            'message' => 'An error occurred while creating the client: ' . $e->getMessage()
                        ], 500);
                    }
                    
                    return redirect('/client')->with('error', 'An error occured while creating a new client: ' . $e->getMessage());
                }
            } else {
                // OR not available - determine WHY and provide specific error
                $errorMessage = 'O.R not available.';
                
                // Check if OR exists at all
                $orAnyStatus = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                    ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                    ->where('ornumber', $orNo)
                    ->where('seriescode', $orSeriesCode)
                    ->first();
                
                if (!$orAnyStatus) {
                    $errorMessage = 'O.R number ' . $orNo . ' not found in series ' . $orSeriesCode . '. Please select a different O.R number.';
                } else {
                    // Check specific issues
                    $issues = [];
                    if ($orAnyStatus->regionid != $regionId) {
                        $issues[] = 'wrong region (expected: ' . $regionId . ', found: ' . $orAnyStatus->regionid . ')';
                    }
                    if ($orAnyStatus->branchid != $branchId) {
                        $issues[] = 'wrong branch (expected: ' . $branchId . ', found: ' . $orAnyStatus->branchid . ')';
                    }
                    if ($orAnyStatus->status != $availableOR) {
                        $statusText = $orAnyStatus->status == '1' ? 'available' : 'already used';
                        $issues[] = 'status is ' . $statusText;
                    }
                    
                    if (count($issues) > 0) {
                        $errorMessage = 'O.R number ' . $orNo . ' cannot be used: ' . implode(', ', $issues) . '. Please select a different O.R number.';
                    }
                }
                
                // Check if request is AJAX
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                
                return redirect()->back()->with('duplicate', $errorMessage)->withInput();
            }
        } else {
            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Contract is not available or does not belong to the selected Region'
                ], 422);
            }
            
            return redirect()->back()->with('duplicate', 'Contract is not available or does not belong to the selected Region.')->withInput();
        }
    }

    // insert new data - for transferred clients
    public function createTransferClient(Client $client, Request $request)
    {

        // custom error message
        $messages = [
            'contractno.required' => 'This field is required.',
            'package.required' => 'This field is required.',
            'packageprice.required' => 'This field is required.',
            'paymentterm.required' => 'This field is required.',
            'termamount.required' => 'This field is required.',
            'region.required' => 'This field is required.',
            'branch.required' => 'This field is required.',
            'paymentamount.required' => 'This field is required.',
            'orseriescode.required' => 'This field is required.',
            'ornumber.required' => 'This field is required.',
            'paymentdate.required' => 'This field is required.',
            'lastname.required' => 'This field is required.',
            'lastname.min' => 'Name is too short.',
            'lastname.max' => 'Name is too long.',
            'firstname.required' => 'This field is required.',
            'firstname.min' => 'Name is too short.',
            'firstname.max' => 'Name is too long.',
            'gender.required' => 'This field is required.',
            'birthdate.required' => 'This field is required.',
            'age.required' => 'This field is required.',
            'age.min' => 'Age must be at least 18 years old.',
            'bestplacetocollect.required' => 'This field is required.',
            'besttimetocollect.required' => 'This field is required.',
            'province.required' => 'This field is required.',
            'city.required' => 'This field is required.',
            'barangay.required' => 'This field is required.',
            'mobilenetwork.required' => 'This field is required.',
            'mobileno.required' => 'This field is required.',
            'mobileno.min' => 'Invalid number.',
            'mobileno.max' => 'Invalid number.',
            'email.required' => 'This field is required.',
            'emailaddress.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'contractno' => 'required',
            'package' => 'required',
            'packageprice' => 'required',
            'paymentterm' => 'required',
            'termamount' => 'required',
            'region' => 'required',
            'branch' => 'required',
            'recruitedby' => 'nullable',
            'downpaymenttype' => 'nullable',
            'paymentamount' => 'required',
            'orseriescode' => 'required',
            'ornumber' => 'required',
            'paymentdate' => 'required',
            'lastname' => 'required|min:1|max:30',
            'firstname' => 'required|min:1|max:30',
            'middlename' => 'nullable',
            'gender' => 'required',
            'birthdate' => 'required',
            'age' => 'required|numeric|min:18',
            'birthplace' => 'nullable',
            'civilstatus' => 'nullable',
            'religion' => 'nullable',
            'occupation' => 'nullable',
            'bestplacetocollect' => 'required',
            'besttimetocollect' => 'required',
            'province' => 'required',
            'city' => 'required',
            'barangay' => 'required',
            'zipcode' => 'nullable',
            'street' => 'nullable',
            'telephone' => 'nullable',
            'mobilenetwork' => 'nullable',
            'mobileno' => 'nullable',
            'email' => 'nullable',
            'emailaddress' => 'nullable',
            'principalbeneficiary' => 'nullable',
            'principalbeneficiaryage' => 'nullable',
            'principalbeneficiaryrelation' => 'nullable',
            'principalbeneficiaryid' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'beneficiary1' => 'nullable',
            'beneficiary1age' => 'nullable',
            'beneficiary2' => 'nullable',
            'beneficiary2age' => 'nullable',
            'beneficiary3' => 'nullable',
            'beneficiary3age' => 'nullable',
            'beneficiary4' => 'nullable',
            'beneficiary4age' => 'nullable'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        } else {
            // validation has passed
            $validatedData = $fields->validated();

            $contractNo = strip_tags($validatedData['contractno']);
            $packageId = strip_tags($validatedData['package']);
            $packagePriceRaw = strip_tags($validatedData['packageprice']);
            $packagePrice = (float) preg_replace('/[^0-9.]/', '', $packagePriceRaw);
            $termId = strip_tags($validatedData['paymentterm']);
            $termAmount = strip_tags($validatedData['termamount']);
            $regionId = strip_tags($validatedData['region']);
            $branchId = strip_tags($validatedData['branch']);
            $recruitedById = !empty($validatedData['recruitedby']) ? (int) strip_tags($validatedData['recruitedby']) : null;
            $downpaymentType = strip_tags($validatedData['downpaymenttype']);
            $paymentAmount = strip_tags($validatedData['paymentamount']);
            $orSeriesCode = strip_tags($validatedData['orseriescode']);
            $orNo = strip_tags($validatedData['ornumber']);
            $paymentMethod = strip_tags($validatedData['paymentmethod'] ?? 'Cash');
            $paymentDate = strip_tags($validatedData['paymentdate']);
            $lastName = strip_tags($validatedData['lastname']);
            $firstName = strip_tags($validatedData['firstname']);
            $middleName = strip_tags($validatedData['middlename'] ?? '');
            $gender = strip_tags($validatedData['gender']);
            $birthDate = strip_tags($validatedData['birthdate']);
            $age = strip_tags($validatedData['age']);
            $birthPlace = strip_tags($validatedData['birthplace'] ?? '');
            $civilStatus = strip_tags($validatedData['civilstatus'] ?? 'Single');
            $religion = strip_tags($validatedData['religion'] ?? '');
            $occupation = strip_tags($validatedData['occupation'] ?? '');
            $bestPlaceToCollect = strip_tags($validatedData['bestplacetocollect']);
            $bestTimeToCollect = strip_tags($validatedData['besttimetocollect']);
            $province = strip_tags($validatedData['province']);
            $city = strip_tags($validatedData['city']);
            $barangay = strip_tags($validatedData['barangay']);
            $zipcode = strip_tags($validatedData['zipcode'] ?? '');
            $street = strip_tags($validatedData['street'] ?? '');
            $telephone = strip_tags($validatedData['telephone'] ?? '');
            $mobileNetwork = strip_tags($validatedData['mobilenetwork']);
            $mobileNo = strip_tags($validatedData['mobileno']);
            $email = strip_tags($validatedData['email']);
            $emailAddress = strip_tags($validatedData['emailaddress']);
            $clientStatus = strip_tags($validatedData['status'] ?? $client->Status);
            $principalBeneficiary = strip_tags($validatedData['principalbeneficiary'] ?? '');
            $principalBeneficiaryAge = strip_tags($validatedData['principalbeneficiaryage'] ?? '');
            $principalBeneficiaryRelation = strip_tags($validatedData['principalbeneficiaryrelation'] ?? '');

            $principalBeneficiaryIdPath = null;
            if ($request->hasFile('principalbeneficiaryid')) {
                $principalBeneficiaryIdPath = $request->file('principalbeneficiaryid')->store('beneficiary_ids', 'public');
            }

            $beneficiary1 = strip_tags($validatedData['beneficiary1'] ?? '');
            $beneficiary1Age = strip_tags($validatedData['beneficiary1age'] ?? '');
            $beneficiary2 = strip_tags($validatedData['beneficiary2'] ?? '');
            $beneficiary2Age = strip_tags($validatedData['beneficiary2age'] ?? '');
            $beneficiary3 = strip_tags($validatedData['beneficiary3'] ?? '');
            $beneficiary3Age = strip_tags($validatedData['beneficiary3age'] ?? '');
            $beneficiary4 = strip_tags($validatedData['beneficiary4'] ?? '');
            $beneficiary4Age = strip_tags($validatedData['beneficiary4age'] ?? '');

            $mobilenumber = '0' . $mobileNetwork . $mobileNo;
            
            // Build complete email only if email is provided
            $emailcomplete = null;
            if (!empty($email) && !empty($emailAddress)) {
                $emailcomplete = $email . '@' . $emailAddress;
            } else if (!empty($email)) {
                $emailcomplete = $email;
            }

            $status = '1';
            $remarks = "To be verified";
            $fsaComsRem = '0';

            // check if contract no is available
            $availableContract = '1';
            $contractExists = ContractBatch::select('tblcontractbatch.*', 'tblcontract.id as contractid')
                ->leftJoin('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
                ->where('ContractNumber', $contractNo)
                ->where('RegionId', $regionId)
                ->where('BranchId', $branchId)
                ->where('Status', $availableContract)
                ->first();

            if ($contractExists) {

                // check if OR no is available
                $availableOR = '1';
                if ($downpaymentType == "Standard") {
                    $remarks = "Standard";
                    $orType = "1";
                } else if ($downpaymentType == "Partial") {
                    $remarks = "Partial";
                    $orType = "2";
                }

                $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                    ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                    ->where('ornumber', $orNo)
                    ->where('regionid', $regionId)
                    ->where('branchid', $branchId)
                    ->where('status', $availableOR)
                    ->where('type', $orType)
                    ->where('seriescode', $orSeriesCode)
                    ->first();

                if ($orExists) {

                    $searchedOfficialReceiptId = $orExists->id;

                    // create a new transfer client
                    try {

                        $insertClientData = [
                            'contractnumber' => $contractNo,
                            'packageid' => $packageId,
                            'paymenttermid' => $termId,
                            'paymenttermamount' => (float) preg_replace('/[^0-9.]/', '', (string) $termAmount),
                            'regionid' => $regionId,
                            'branchid' => $branchId,
                            'recruitedby' => $recruitedById,
                            'lastname' => $lastName,
                            'firstname' => $firstName,
                            'middlename' => $middleName,
                            'gender' => $gender,
                            'birthdate' => $birthDate,
                            'age' => $age,
                            'birthplace' => $birthPlace,
                            'civilstatus' => $civilStatus,
                            'religion' => $religion,
                            'occupation' => $occupation,
                            'bestplacetocollect' => $bestPlaceToCollect,
                            'besttimetocollect' => $bestTimeToCollect,
                            'province' => $province,
                            'city' => $city,
                            'barangay' => $barangay,
                            'zipcode' => $zipcode,
                            'street' => $street,
                            'homenumber' => $telephone,
                            'mobilenumber' => $mobilenumber,
                            'emailaddress' => $emailcomplete,
                            'principalbeneficiaryname' => $principalBeneficiary,
                            'principalbeneficiaryage' => $principalBeneficiaryAge,
                            'secondary1name' => $beneficiary1,
                            'secondary1age' => $beneficiary1Age,
                            'secondary2name' => $beneficiary2,
                            'secondary2age' => $beneficiary2Age,
                            'secondary3name' => $beneficiary3,
                            'secondary3age' => $beneficiary3Age,
                            'secondary4name' => $beneficiary4,
                            'secondary4age' => $beneficiary4Age,
                            'status' => $status,
                            'remarks' => $remarks,
                            'fsacomsrem' => $fsaComsRem,
                            'datecreated' => date("Y-m-d")
                        ];

                        if (Schema::hasColumn('tblclient', 'principalbeneficiaryrelation')) {
                            $insertClientData['principalbeneficiaryrelation'] = $principalBeneficiaryRelation;
                        }

                        if (Schema::hasColumn('tblclient', 'principalbeneficiaryid_path')) {
                            $insertClientData['principalbeneficiaryid_path'] = $principalBeneficiaryIdPath;
                        }

                        if (Schema::hasColumn('tblclient', 'packageprice')) {
                            $insertClientData['packageprice'] = (float) preg_replace('/[^0-9.]/', '', (string) $packagePrice);
                        }

                        $clientId = Client::insertGetId($insertClientData);
                        Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Transfer Client ' . '[Action] Insert ' . '[Target] ' . $contractNo);

                        $updateClientTransferData = [
                            'transferclientid' => $clientId,
                            'datetransferred' => date("Y-m-d")
                        ];

                        // update client transfer data
                        ClientTransfer::where('clientid', $client->Id)
                            ->update($updateClientTransferData);

                        // copy payments from the old plan holder
                        $current_installment = 1;
                        $oldOwnerPayments = Payment::where('clientid', $client->Id)->get();
                        foreach ($oldOwnerPayments as $payment) {
                            $paymentData = [
                                'ORNo' => $payment->ORNo,
                                'ClientId' => $clientId,
                                'BankDepositId' => $payment->BankDepositId,
                                'ORId' => $payment->ORId,
                                'AmountPaid' => $payment->AmountPaid,
                                'NetPayment' => $payment->NetPayment,
                                'ComsMultiplier' => $payment->ComsMultiplier,
                                'Installment' => $payment->Installment,
                                'Date' => $payment->Date,
                                'RIDate' => $payment->RIDate,
                                'PaymentType' => $payment->PaymentType,
                                'IsCleared' => $payment->IsCleared,
                                'CheckNo' => $payment->CheckNo,
                                'CardNo' => $payment->CardNo,
                                'CreatedBy' => $payment->CreatedBy,
                                'DateCreated' => $payment->DateCreated,
                                'ModifiedBy' => $payment->ModifiedBy,
                                'DateModified' => $payment->DateModified,
                                'Deleted' => $payment->Deleted,
                                'isDummy' => $payment->isDummy,
                                'DateDeleted' => $payment->DateDeleted,
                                'Status' => $payment->Status,
                                'Remarks' => $payment->Remarks,
                                'Deposited' => $payment->Deposited,
                                'LastUpdatedByStaff' => $payment->LastUpdatedByStaff,
                                'VoidStatus' => $payment->VoidStatus,
                            ];

                            if ($payment->Installment != null && $payment->Installment != 'Not available') {
                                $current_installment++;
                            }

                            Payment::insert($paymentData);
                        }


                        // add new payment data
                        $comsMultiplier = '9';
                        if ($paymentMethod == 'Cash') {
                            $paymentMethod = '1';
                        }

                        $insertPaymentData = [
                            'orno' => $orNo,
                            'clientid' => $clientId,
                            'orid' => $searchedOfficialReceiptId,
                            'amountpaid' => (float) preg_replace('/[^0-9.]/', '', (string) $paymentAmount),
                            'installment' => $current_installment,
                            'comsmultiplier' => $comsMultiplier,
                            'date' => $paymentDate,
                            'paymenttype' => $paymentMethod,
                            'remarks' => $remarks,
                            'createdby' => session('user_id'),
                            'datecreated' => date("Y-m-d")
                        ];
                        Payment::insert($insertPaymentData);
                        Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Transfer Client ' . '[Action] Insert Payment ' . '[Target] ' . $clientId);

                        return redirect('/client')->with('success', 'Added new client!');
                    } catch (\Exception $e) {
                        return redirect('/client')->with('error', 'An error occured while creating a new client.');
                    }
                } else {
                    return redirect()->back()->with('duplicate', 'O.R not available.')->withInput();
                }
            } else {
                return redirect()->back()->with('duplicate', 'Contract is already assigned to another client.')->withInput();
            }
        }
    }

    // update data
    public function updateClient(Client $client, Request $request)
    {

        // Comprehensive logging for debugging
        Log::info('=== CLIENT UPDATE FORM SUBMISSION DEBUG ===');
        Log::info('Client ID: ' . $client->Id);
        Log::info('Client Status: ' . $client->Status);
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('All Request Data: ', $request->all());

        // Handle mobile number splitting - the form sends mobilenumber but validation expects mobilenetwork and mobileno
        if ($request->has('mobilenumber') && !$request->has('mobilenetwork')) {
            $mobileNumber = $request->input('mobilenumber');
            Log::info('Processing mobile number: ' . $mobileNumber);

            // Extract network prefix (first 3 digits) and mobile number (last 7 digits)
            if (strlen($mobileNumber) == 10) {
                $mobileNetwork = substr($mobileNumber, 0, 3);
                $mobileNo = substr($mobileNumber, 3, 7);

                // Add these to the request for validation
                $request->merge([
                    'mobilenetwork' => $mobileNetwork,
                    'mobileno' => $mobileNo
                ]);

                Log::info('Mobile number split - Network: ' . $mobileNetwork . ', Number: ' . $mobileNo);
            } else {
                Log::error('Invalid mobile number length: ' . strlen($mobileNumber) . ' for number: ' . $mobileNumber);
            }
        }

        // Log specific important fields
        Log::info('--- Key Form Fields ---');
        Log::info('Contract No: ' . $request->input('contractno'));
        Log::info('Package: ' . $request->input('package'));
        Log::info('Payment Term: ' . $request->input('paymentterm'));
        Log::info('Region: ' . $request->input('region'));
        Log::info('Branch: ' . $request->input('branch'));
        Log::info('Recruited By: ' . $request->input('recruitedby'));
        Log::info('Last Name: ' . $request->input('lastname'));
        Log::info('First Name: ' . $request->input('firstname'));
        Log::info('Email: ' . $request->input('email'));
        Log::info('Email Address: ' . $request->input('emailaddress'));
        Log::info('Address Region: ' . $request->input('address_region'));
        Log::info('Address Province: ' . $request->input('address_province'));
        Log::info('Address City: ' . $request->input('address_city'));
        Log::info('Address Barangay: ' . $request->input('address_barangay'));
        Log::info('Mobile Network: ' . $request->input('mobilenetwork'));
        Log::info('Mobile No: ' . $request->input('mobileno'));
        Log::info('--- End Key Fields ---');

        if ($client->Status != '3') {

            // custom error message
            $messages = [
                'contractno.required' => 'This field is required.',
                'package.required' => 'This field is required.',
                'packageprice.required' => 'This field is required.',
                'paymentterm.required' => 'This field is required.',
                'termamount.required' => 'This field is required.',
                'branch.required' => 'This field is required.',
                'paymentamount.required' => 'This field is required.',
                'ornumber.required' => 'This field is required.',
                'paymentdate.required' => 'This field is required.',
                'lastname.required' => 'This field is required.',
                'lastname.min' => 'Name is too short.',
                'lastname.max' => 'Name is too long.',
                'firstname.required' => 'This field is required.',
                'firstname.min' => 'Name is too short.',
                'firstname.max' => 'Name is too long.',
                'gender.required' => 'This field is required.',
                'birthdate.required' => 'This field is required.',
                'age.required' => 'This field is required.',
                'age.min' => 'Age must be at least 18 years old.',
                'bestplacetocollect.required' => 'This field is required.',
                'besttimetocollect.required' => 'This field is required.',
                'address_region.required' => 'This field is required.',
                'address_province.required' => 'This field is required.',
                'address_city.required' => 'This field is required.',
                'address_barangay.required' => 'This field is required.',
                'mobilenetwork.required' => 'This field is required.',
                'mobileno.required' => 'This field is required.',
                'mobileno.min' => 'Invalid number.',
                'mobileno.max' => 'Invalid number.',
                'email.required' => 'This field is required.',
                'emailaddress.required' => 'This field is required.'
            ];

            // validation fields
            $fields = Validator::make($request->all(), [
                'contractno' => 'required',
                'package' => 'required',
                'packageprice' => 'required',
                'paymentterm' => 'required',
                'termamount' => 'required',
                'region' => 'required',
                'branch' => 'required',
                'recruitedby' => 'nullable',
                'downpaymenttype' => 'nullable',
                'paymentamount' => 'required',
                'orseriescode' => 'required',
                'ornumber' => 'required',
                'paymentdate' => 'required',
                'lastname' => 'required|min:1|max:30',
                'firstname' => 'required|min:1|max:30',
                'middlename' => 'nullable',
                'gender' => 'required',
                'birthdate' => 'required',
                'age' => 'required|numeric|min:18',
                'birthplace' => 'nullable',
                'civilstatus' => 'nullable',
                'religion' => 'nullable',
                'occupation' => 'nullable',
                'bestplacetocollect' => 'required',
                'besttimetocollect' => 'required',
                'address_region' => 'required',
                'address_province' => 'required',
                'address_city' => 'required',
                'address_barangay' => 'required',
                'zipcode' => 'nullable',
                'street' => 'nullable',
                'telephone' => 'nullable',
                'mobilenetwork' => 'nullable',
                'mobileno' => 'nullable',
                'email' => 'nullable',
                'emailaddress' => 'nullable',
                'status' => 'nullable',
                'principalbeneficiary' => 'nullable',
                'principalbeneficiaryage' => 'nullable',
                'principalbeneficiaryrelation' => 'nullable',
                'principalbeneficiaryid' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
                'beneficiary1' => 'nullable',
                'beneficiary1age' => 'nullable',
                'beneficiary2' => 'nullable',
                'beneficiary2age' => 'nullable',
                'beneficiary3' => 'nullable',
                'beneficiary3age' => 'nullable',
                'beneficiary4' => 'nullable',
                'beneficiary4age' => 'nullable',
                'home_region' => 'nullable',
                'home_province' => 'nullable',
                'home_city' => 'nullable',
                'home_barangay' => 'nullable',
                'home_zipcode' => 'nullable',
                'home_street' => 'nullable',
                'same_as_current_address' => 'nullable'
            ], $messages);

            if ($fields->fails()) {
                Log::error('=== VALIDATION FAILED ===');
                Log::error('Validation Errors: ', $fields->errors()->toArray());
                Log::error('Failed Fields: ' . implode(', ', array_keys($fields->errors()->toArray())));
                Log::info('=== END VALIDATION DEBUG ===');

                return redirect()
                    ->back()
                    ->withErrors($fields)
                    ->withInput();
            } else {
                Log::info('✅ Validation passed successfully');
            }

            // validation has passed
            Log::info('--- Processing Validated Data ---');
            $validatedData = $fields->validated();

            $contractNo = strip_tags($validatedData['contractno']);
            $packageId = strip_tags($validatedData['package']);
            $packagePriceRaw = strip_tags($validatedData['packageprice']);
            $packagePrice = (float) preg_replace('/[^0-9.]/', '', $packagePriceRaw);
            $termId = strip_tags($validatedData['paymentterm']);
            $termAmount = strip_tags($validatedData['termamount']);
            $regionId = strip_tags($validatedData['region']);
            $branchId = strip_tags($validatedData['branch']);
            $recruitedById = strip_tags($validatedData['recruitedby']);
            $downpaymentType = strip_tags($validatedData['downpaymenttype']);
            $paymentAmount = strip_tags($validatedData['paymentamount']);
            $orSeriesCode = strip_tags($validatedData['orseriescode']);
            $orNo = strip_tags($validatedData['ornumber']);
            $paymentMethod = strip_tags($validatedData['paymentmethod'] ?? 'Cash');
            $paymentDate = strip_tags($validatedData['paymentdate']);
            $lastName = strip_tags($validatedData['lastname']);
            $firstName = strip_tags($validatedData['firstname']);
            $middleName = strip_tags($validatedData['middlename'] ?? '');
            $gender = strip_tags($validatedData['gender']);
            $birthDate = strip_tags($validatedData['birthdate']);
            $age = strip_tags($validatedData['age']);
            $birthPlace = strip_tags($validatedData['birthplace'] ?? '');
            $civilStatus = strip_tags($validatedData['civilstatus'] ?? 'Single');
            $religion = strip_tags($validatedData['religion'] ?? '');
            $occupation = strip_tags($validatedData['occupation'] ?? '');
            $bestPlaceToCollect = strip_tags($validatedData['bestplacetocollect']);
            $bestTimeToCollect = strip_tags($validatedData['besttimetocollect']);
            $province = strip_tags($validatedData['address_province']);
            $city = strip_tags($validatedData['address_city']);
            $barangay = strip_tags($validatedData['address_barangay']);
            $zipcode = strip_tags($validatedData['zipcode'] ?? '');
            $street = strip_tags($validatedData['street'] ?? '');
            $telephone = strip_tags($validatedData['telephone'] ?? '');
            $mobileNetwork = strip_tags($validatedData['mobilenetwork']);
            $mobileNo = strip_tags($validatedData['mobileno']);
            $email = strip_tags($validatedData['email']);
            $emailAddress = strip_tags($validatedData['emailaddress']);
            $clientStatus = strip_tags($validatedData['status'] ?? $client->Status);
            $principalBeneficiary = strip_tags($validatedData['principalbeneficiary'] ?? '');
            $principalBeneficiaryAge = strip_tags($validatedData['principalbeneficiaryage'] ?? '');
            $principalBeneficiaryRelation = strip_tags($validatedData['principalbeneficiaryrelation'] ?? '');

            $principalBeneficiaryIdPath = null;
            if ($request->hasFile('principalbeneficiaryid')) {
                $principalBeneficiaryIdPath = $request->file('principalbeneficiaryid')->store('beneficiary_ids', 'public');
            }

            $beneficiary1 = strip_tags($validatedData['beneficiary1'] ?? '');
            $beneficiary1Age = strip_tags($validatedData['beneficiary1age'] ?? '');
            $beneficiary2 = strip_tags($validatedData['beneficiary2'] ?? '');
            $beneficiary2Age = strip_tags($validatedData['beneficiary2age'] ?? '');
            $beneficiary3 = strip_tags($validatedData['beneficiary3'] ?? '');
            $beneficiary3Age = strip_tags($validatedData['beneficiary3age'] ?? '');
            $beneficiary4 = strip_tags($validatedData['beneficiary4'] ?? '');
            $beneficiary4Age = strip_tags($validatedData['beneficiary4age'] ?? '');

            // Home address fields
            $sameAsCurrentAddress = $request->has('same_as_current_address');
            Log::info('Home Address Logic - SameAsCurrent? ' . ($sameAsCurrentAddress ? 'YES' : 'NO'));
            if ($sameAsCurrentAddress) {
                // Copy current address to home address - use form values which contain the actual selected values
                $homeRegion = strip_tags($request->input('address_region', ''));
                $homeProvince = $province;
                $homeCity = $city;
                $homeBarangay = $barangay;
                $homeZipcode = $zipcode;
                $homeStreet = $street;
            } else {
                $homeRegion = strip_tags($request->input('home_region', ''));
                $homeProvince = strip_tags($request->input('home_province', ''));
                $homeCity = strip_tags($request->input('home_city', ''));
                $homeBarangay = strip_tags($request->input('home_barangay', ''));
                $homeZipcode = strip_tags($request->input('home_zipcode', ''));
                $homeStreet = strip_tags($request->input('home_street', ''));
            }

            $mobilenumber = '0' . $mobileNetwork . $mobileNo;
            $emailcomplete = $email . '@' . $emailAddress;

            Log::info('--- Contract & OR Validation ---');
            Log::info('Mobile Number (complete): ' . $mobilenumber);
            Log::info('Email (complete): ' . $emailcomplete);
            Log::info('Contract No: ' . $contractNo);
            Log::info('Region ID: ' . $regionId);
            Log::info('Branch ID: ' . $branchId);
            Log::info('OR No: ' . $orNo);
            Log::info('OR Series Code: ' . $orSeriesCode);
            Log::info('Downpayment Type: ' . $downpaymentType);

            // check if contract no is available
            Log::info('Checking contract availability...');

            Log::info('DEBUG - Comparing Contract values:');
            Log::info('DB Contract: [' . $client->ContractNumber . '] vs Form: [' . $contractNo . ']');
            Log::info('DB Region: [' . $client->RegionId . '] vs Form: [' . $regionId . ']');
            Log::info('DB Branch: [' . $client->BranchId . '] vs Form: [' . $branchId . ']');

            // Check if the client is keeping their existing contract
            $isSameContract = (
                (string) $client->ContractNumber === (string) $contractNo &&
                (string) $client->RegionId === (string) $regionId &&
                (string) $client->BranchId === (string) $branchId
            );

            Log::info('DEBUG - isSameContract evaluated to: ' . ($isSameContract ? 'TRUE' : 'FALSE'));

            if ($isSameContract) {
                Log::info('DEBUG - Client kept original contract. Bypassing ContractBatch Region/Branch validation to prevent data anomaly errors.');
                // Just find their existing contract ID directly
                $existingContract = \App\Models\Contract::where('contractnumber', $contractNo)
                    ->first();

                if ($existingContract) {
                    $contractExists = true;
                    $searchedContractId = $existingContract->id;
                    Log::info('✅ Existing Contract fetched directly without client ID filter. ID: ' . $searchedContractId);
                } else {
                    // Fallback if somehow it's not even linked to them in tblcontract
                    $contractExists = false;
                    Log::error('❌ FAILED: Client kept contract but ContractNumber ' . $contractNo . ' was not found in tblcontract!');
                }
            } else {
                $contractQuery = ContractBatch::select('tblcontractbatch.*', 'tblcontract.id as contractid')
                    ->leftJoin('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
                    ->where('ContractNumber', $contractNo)
                    ->where('RegionId', $regionId)
                    ->where('BranchId', $branchId);

                // If it's a new contract being assigned, it must be available
                $availableContract = '1';
                $contractQuery->where('Status', $availableContract);
                Log::info('DEBUG - Contract changed. Added Status = 1 condition.');

                \Illuminate\Support\Facades\DB::enableQueryLog();

                $contractExistsResult = $contractQuery->first();

                $queries = \Illuminate\Support\Facades\DB::getQueryLog();
                Log::info('RAW CONTRACT QUERY EXECUTED: ', $queries);

                if ($contractExistsResult) {
                    $contractExists = true;
                    $searchedContractId = $contractExistsResult->contractid;
                    Log::info('✅ New Contract is available. ID: ' . $searchedContractId);
                } else {
                    $contractExists = false;
                    Log::info('CONTRACT EXISTS RESULT: NULL');
                }
            }

            if ($contractExists) {

                // check if OR no is available
                $availableOR = '1';
                if ($downpaymentType == "Standard") {
                    $remarks = "Standard";
                    $orType = "1";
                } else if ($downpaymentType == "Partial") {
                    $remarks = "Partial";
                    $orType = "2";
                }

                Log::info('Checking OR availability...');
                Log::info('OR Type: ' . $orType);

                $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id as orid')
                    ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                    ->where('ORNumber', $orNo)
                    ->where('RegionId', $regionId)
                    ->where('BranchId', $branchId)
                    ->where('Status', $availableOR)
                    ->where('Type', $orType)
                    ->where('SeriesCode', $orSeriesCode)
                    ->first();

                if ($orExists) {
                    Log::info('✅ OR is available');
                    Log::info('OR ID: ' . $orExists->orid);
                    $searchedOfficialReceiptId = $orExists->orid;

                    // update client
                    try {
                        Log::info('--- Starting Database Update ---');

                        $updateClientData = [
                            'contractnumber' => $contractNo,
                            'packageid' => $packageId,
                            'paymenttermid' => $termId,
                            'paymenttermamount' => (float) preg_replace('/[^0-9.]/', '', (string) $termAmount),
                            'regionid' => $regionId,
                            'branchid' => $branchId,
                            'recruitedby' => $recruitedById,
                            'lastname' => $lastName,
                            'firstname' => $firstName,
                            'middlename' => $middleName,
                            'gender' => $gender,
                            'birthdate' => $birthDate,
                            'age' => $age,
                            'birthplace' => $birthPlace,
                            'civilstatus' => $civilStatus,
                            'religion' => $religion,
                            'occupation' => $occupation,
                            'bestplacetocollect' => $bestPlaceToCollect,
                            'besttimetocollect' => $bestTimeToCollect,
                            'province' => $province,
                            'city' => $city,
                            'barangay' => $barangay,
                            'zipcode' => $zipcode,
                            'street' => $street,
                            'homenumber' => $telephone,
                            'mobilenumber' => $mobilenumber,
                            'emailaddress' => $emailcomplete,
                            'principalbeneficiaryname' => $principalBeneficiary,
                            'principalbeneficiaryage' => $principalBeneficiaryAge,
                            'secondary1name' => $beneficiary1,
                            'secondary1age' => $beneficiary1Age,
                            'secondary2name' => $beneficiary2,
                            'secondary2age' => $beneficiary2Age,
                            'secondary3name' => $beneficiary3,
                            'secondary3age' => $beneficiary3Age,
                            'secondary4name' => $beneficiary4,
                            'secondary4age' => $beneficiary4Age,
                            'status' => $clientStatus,
                            'datecreated' => date("Y-m-d")
                        ];

                        if (Schema::hasColumn('tblclient', 'principalbeneficiaryrelation')) {
                            $updateClientData['principalbeneficiaryrelation'] = $principalBeneficiaryRelation;
                        }

                        if ($principalBeneficiaryIdPath && Schema::hasColumn('tblclient', 'principalbeneficiaryid_path')) {
                            $updateClientData['principalbeneficiaryid_path'] = $principalBeneficiaryIdPath;
                        }

                        if (Schema::hasColumn('tblclient', 'packageprice')) {
                            $updateClientData['packageprice'] = (float) preg_replace('/[^0-9.]/', '', (string) $packagePrice);
                        }

                        if (Schema::hasColumn('tblclient', 'homeregion')) {
                            $updateClientData['homeregion'] = $homeRegion;
                        }

                        if (Schema::hasColumn('tblclient', 'homeprovince')) {
                            $updateClientData['homeprovince'] = $homeProvince;
                        }

                        if (Schema::hasColumn('tblclient', 'homecity')) {
                            $updateClientData['homecity'] = $homeCity;
                        }

                        if (Schema::hasColumn('tblclient', 'homebarangay')) {
                            $updateClientData['homebarangay'] = $homeBarangay;
                        }

                        if (Schema::hasColumn('tblclient', 'homezipcode')) {
                            $updateClientData['homezipcode'] = $homeZipcode;
                        }

                        if (Schema::hasColumn('tblclient', 'homestreet')) {
                            $updateClientData['homestreet'] = $homeStreet;
                        }

                        Log::info('Updating client record...');
                        Log::info('Client ID: ' . $client->Id);
                        Log::info('Update Data: ', $updateClientData);

                        Client::where('id', $client->Id)->update($updateClientData);
                        Log::info('✅ Client updated successfully');
                        Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update ' . '[Target] ' . $client->Id);

                        // update payment data
                        if ($paymentMethod == 'Cash') {
                            $paymentMethod = '1';
                        }

                        // if client already has multiple payments, it's a transfer client
                        $checkMultiplePayments = Payment::where('clientid', $client->Id)->get();
                        Log::info('Payment count: ' . count($checkMultiplePayments));

                        if (count($checkMultiplePayments) > 1) {
                            Log::info('Processing transfer client payment update...');

                            $paymentsInfo = Payment::where('clientid', $client->Id)
                                ->orderBy('id', 'desc')
                                ->first();

                            $updatePaymentData = [
                                'orno' => $orNo,
                                'orid' => $searchedOfficialReceiptId,
                                'amountpaid' => $paymentAmount,
                                'installment' => $paymentsInfo->Installment,
                                'date' => $paymentDate,
                                'paymenttype' => $paymentMethod,
                                'remarks' => $remarks,
                                'modifiedby' => session('user_id'),
                                'datecreated' => date("Y-m-d")
                            ];

                            Log::info('Updating payment record (transfer client)...');
                            Log::info('Payment ID: ' . $paymentsInfo->Id);
                            Log::info('Payment Update Data: ', $updatePaymentData);

                            Payment::where('id', $paymentsInfo->Id)->update($updatePaymentData);
                            Log::info('✅ Payment updated successfully (transfer client)');
                            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update Payment ' . '[Target] ' . $paymentsInfo->Id);
                        }
                        // proceed if not a transferred client
                        else {
                            Log::info('Processing regular client payment update...');

                            $updatePaymentData = [
                                'orno' => $orNo,
                                'orid' => $searchedOfficialReceiptId,
                                'amountpaid' => $paymentAmount,
                                'installment' => '1',
                                'date' => $paymentDate,
                                'paymenttype' => $paymentMethod,
                                'remarks' => $remarks,
                                'modifiedby' => session('user_id'),
                                'datecreated' => date("Y-m-d")
                            ];

                            Log::info('Updating payment record (regular client)...');
                            Log::info('Payment Update Data: ', $updatePaymentData);

                            Payment::where('clientid', $client->Id)->update($updatePaymentData);
                            Log::info('✅ Payment updated successfully (regular client)');
                            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update Payment ' . '[Target] ' . $client->Id);
                        }

                        Log::info('=== CLIENT UPDATE COMPLETED SUCCESSFULLY ===');
                        return redirect('/client-view/' . $client->Id)->with('success', 'Successfully updated the selected client!');
                    } catch (\Exception $e) {
                        Log::error('=== CLIENT UPDATE FAILED ===');
                        Log::error('Error Message: ' . $e->getMessage());
                        Log::error('Error Trace: ' . $e->getTraceAsString());
                        Log::info('=== END CLIENT UPDATE DEBUG ===');
                        return redirect('/client-view/' . $client->Id)->with('error', $e->getMessage());
                    }
                } else {
                    Log::error('❌ OR not available');
                    Log::error('OR No: ' . $orNo);
                    Log::error('Region ID: ' . $regionId);
                    Log::error('Branch ID: ' . $branchId);
                    Log::error('OR Type: ' . $orType);
                    Log::error('Series Code: ' . $orSeriesCode);
                    Log::info('=== END CLIENT UPDATE DEBUG ===');
                    return redirect()->back()->with('duplicate', 'O.R not available.')->withInput();
                }
            } else {
                Log::error('❌ Contract is already assigned to another client');
                Log::error('Contract No: ' . $contractNo);
                Log::error('Region ID: ' . $regionId);
                Log::error('Branch ID: ' . $branchId);
                Log::info('=== END CLIENT UPDATE DEBUG ===');
                return redirect()->back()->with('duplicate', 'Contract is already assigned to another client.')->withInput();
            }
        } else {
            Log::info('Client is approved (Status = 3), using simplified update logic');

            // custom error message
            $messages = [
                'contractno.required' => 'This field is required.',
                'package.required' => 'This field is required.',
                'packageprice.required' => 'This field is required.',
                'paymentterm.required' => 'This field is required.',
                'termamount.required' => 'This field is required.',
                'branch.required' => 'This field is required.',
                'lastname.required' => 'This field is required.',
                'lastname.min' => 'Name is too short.',
                'lastname.max' => 'Name is too long.',
                'firstname.required' => 'This field is required.',
                'firstname.min' => 'Name is too short.',
                'firstname.max' => 'Name is too long.',
                'gender.required' => 'This field is required.',
                'birthdate.required' => 'This field is required.',
                'age.required' => 'This field is required.',
                'age.min' => 'Age must be at least 18 years old.',
                'bestplacetocollect.required' => 'This field is required.',
                'besttimetocollect.required' => 'This field is required.',
                'address_region.required' => 'This field is required.',
                'address_province.required' => 'This field is required.',
                'address_city.required' => 'This field is required.',
                'address_barangay.required' => 'This field is required.',
                'mobilenetwork.required' => 'This field is required.',
                'mobileno.required' => 'This field is required.',
                'mobileno.min' => 'Invalid number.',
                'mobileno.max' => 'Invalid number.',
                'email.required' => 'This field is required.',
                'emailaddress.required' => 'This field is required.'
            ];

            // validation fields
            $fields = Validator::make($request->all(), [
                'contractno' => 'required',
                'package' => 'required',
                'packageprice' => 'required',
                'paymentterm' => 'required',
                'termamount' => 'required',
                'region' => 'required',
                'branch' => 'required',
                'recruitedby' => 'nullable',
                'lastname' => 'required|min:1|max:30',
                'firstname' => 'required|min:1|max:30',
                'middlename' => 'nullable',
                'gender' => 'required',
                'birthdate' => 'required',
                'age' => 'required|numeric|min:18',
                'birthplace' => 'nullable',
                'civilstatus' => 'nullable',
                'religion' => 'nullable',
                'occupation' => 'nullable',
                'bestplacetocollect' => 'required',
                'besttimetocollect' => 'required',
                'address_region' => 'required',
                'address_province' => 'required',
                'address_city' => 'required',
                'address_barangay' => 'required',
                'zipcode' => 'nullable',
                'street' => 'nullable',
                'telephone' => 'nullable',
                'mobilenetwork' => 'nullable',
                'mobileno' => 'nullable',
                'email' => 'nullable',
                'emailaddress' => 'nullable',
                'principalbeneficiary' => 'nullable',
                'principalbeneficiaryage' => 'nullable',
                'beneficiary1' => 'nullable',
                'beneficiary1age' => 'nullable',
                'beneficiary2' => 'nullable',
                'beneficiary2age' => 'nullable',
                'beneficiary3' => 'nullable',
                'beneficiary3age' => 'nullable',
                'beneficiary4' => 'nullable',
                'beneficiary4age' => 'nullable',
                'home_region' => 'nullable',
                'home_province' => 'nullable',
                'home_city' => 'nullable',
                'home_barangay' => 'nullable',
                'home_zipcode' => 'nullable',
                'home_street' => 'nullable',
                'same_as_current_address' => 'nullable'
            ], $messages);

            if ($fields->fails()) {
                Log::error('=== VALIDATION FAILED (APPROVED CLIENT) ===');
                Log::error('Validation Errors: ', $fields->errors()->toArray());
                Log::error('Failed Fields: ' . implode(', ', array_keys($fields->errors()->toArray())));
                Log::info('=== END VALIDATION DEBUG ===');

                return redirect()
                    ->back()
                    ->withErrors($fields)
                    ->withInput();
            } else {
                Log::info('✅ Validation passed successfully (approved client)');
            }

            // validation has passed
            Log::info('--- Processing Validated Data (Approved Client) ---');
            $validatedData = $fields->validated();

            $contractNo = strip_tags($validatedData['contractno']);
            $packageId = strip_tags($validatedData['package']);
            $packagePriceRaw = strip_tags($validatedData['packageprice']);
            $packagePrice = (float) preg_replace('/[^0-9.]/', '', $packagePriceRaw);
            $termId = strip_tags($validatedData['paymentterm']);
            $termAmount = strip_tags($validatedData['termamount']);
            $regionId = strip_tags($validatedData['region']);
            $branchId = strip_tags($validatedData['branch']);
            $recruitedById = strip_tags($validatedData['recruitedby']);
            $lastName = strip_tags($validatedData['lastname']);
            $firstName = strip_tags($validatedData['firstname']);
            $middleName = strip_tags($validatedData['middlename'] ?? '');
            $gender = strip_tags($validatedData['gender']);
            $birthDate = strip_tags($validatedData['birthdate']);
            $age = strip_tags($validatedData['age']);
            $birthPlace = strip_tags($validatedData['birthplace'] ?? '');
            $civilStatus = strip_tags($validatedData['civilstatus'] ?? 'Single');
            $religion = strip_tags($validatedData['religion'] ?? '');
            $occupation = strip_tags($validatedData['occupation'] ?? '');
            $bestPlaceToCollect = strip_tags($validatedData['bestplacetocollect']);
            $bestTimeToCollect = strip_tags($validatedData['besttimetocollect']);
            $province = strip_tags($validatedData['address_province']);
            $city = strip_tags($validatedData['address_city']);
            $barangay = strip_tags($validatedData['address_barangay']);
            $zipcode = strip_tags($validatedData['zipcode'] ?? '');
            $street = strip_tags($validatedData['street'] ?? '');
            $telephone = strip_tags($validatedData['telephone'] ?? '');
            $mobileNetwork = strip_tags($validatedData['mobilenetwork']);
            $mobileNo = strip_tags($validatedData['mobileno']);
            $email = strip_tags($validatedData['email']);
            $emailAddress = strip_tags($validatedData['emailaddress']);
            $principalBeneficiary = strip_tags($validatedData['principalbeneficiary'] ?? '');
            $principalBeneficiaryAge = strip_tags($validatedData['principalbeneficiaryage'] ?? '');
            $beneficiary1 = strip_tags($validatedData['beneficiary1'] ?? '');
            $beneficiary1Age = strip_tags($validatedData['beneficiary1age'] ?? '');
            $beneficiary2 = strip_tags($validatedData['beneficiary2'] ?? '');
            $beneficiary2Age = strip_tags($validatedData['beneficiary2age'] ?? '');
            $beneficiary3 = strip_tags($validatedData['beneficiary3'] ?? '');
            $beneficiary3Age = strip_tags($validatedData['beneficiary3age'] ?? '');
            $beneficiary4 = strip_tags($validatedData['beneficiary4'] ?? '');
            $beneficiary4Age = strip_tags($validatedData['beneficiary4age'] ?? '');

            // Home address fields
            $sameAsCurrentAddress = $request->has('same_as_current_address');
            Log::info('Home Address (Approved) - SameAsCurrent? ' . ($sameAsCurrentAddress ? 'YES' : 'NO'));
            if ($sameAsCurrentAddress) {
                // Copy current address to home address - use form values which contain the actual selected values
                $homeRegion = strip_tags($request->input('address_region', ''));
                $homeProvince = $province;
                $homeCity = $city;
                $homeBarangay = $barangay;
                $homeZipcode = $zipcode;
                $homeStreet = $street;
            } else {
                $homeRegion = strip_tags($request->input('home_region', ''));
                $homeProvince = strip_tags($request->input('home_province', ''));
                $homeCity = strip_tags($request->input('home_city', ''));
                $homeBarangay = strip_tags($request->input('home_barangay', ''));
                $homeZipcode = strip_tags($request->input('home_zipcode', ''));
                $homeStreet = strip_tags($request->input('home_street', ''));
            }

            $mobilenumber = '0' . $mobileNetwork . $mobileNo;
            $emailcomplete = $email . '@' . $emailAddress;

            Log::info('--- Checking Contract For Approved Client ---');
            Log::info('DB Contract: [' . $client->ContractNumber . '] vs Form: [' . $contractNo . ']');
            Log::info('DB Region: [' . $client->RegionId . '] vs Form: [' . $regionId . ']');
            Log::info('DB Branch: [' . $client->BranchId . '] vs Form: [' . $branchId . ']');

            $isSameContract = (
                (string) $client->ContractNumber === (string) $contractNo &&
                (string) $client->RegionId === (string) $regionId &&
                (string) $client->BranchId === (string) $branchId
            );

            Log::info('isSameContract evaluated to: ' . ($isSameContract ? 'TRUE' : 'FALSE'));

            if ($isSameContract) {

                // update client
                try {

                    // update change mode status if any
                    $current_packageId = $client->PackageID;
                    $current_termId = $client->PaymentTermId;
                    $appliedChangeMode = '0';

                    if ($current_packageId == $packageId && $current_termId == $termId && $client->AppliedChangeMode == 1) {
                        $appliedChangeMode = '1';
                    }

                    $updateClientData = [
                        'contractnumber' => $contractNo,
                        'packageid' => $packageId,
                        'paymenttermid' => $termId,
                        'paymenttermamount' => $termAmount,
                        'regionid' => $regionId,
                        'branchid' => $branchId,
                        'recruitedby' => $recruitedById,
                        'lastname' => $lastName,
                        'firstname' => $firstName,
                        'middlename' => $middleName,
                        'gender' => $gender,
                        'birthdate' => $birthDate,
                        'age' => $age,
                        'birthplace' => $birthPlace,
                        'civilstatus' => $civilStatus,
                        'religion' => $religion,
                        'occupation' => $occupation,
                        'bestplacetocollect' => $bestPlaceToCollect,
                        'besttimetocollect' => $bestTimeToCollect,
                        'province' => $province,
                        'city' => $city,
                        'barangay' => $barangay,
                        'zipcode' => $zipcode,
                        'street' => $street,
                        'homenumber' => $telephone,
                        'mobilenumber' => $mobilenumber,
                        'emailaddress' => $emailcomplete,
                        'principalbeneficiaryname' => $principalBeneficiary,
                        'principalbeneficiaryage' => $principalBeneficiaryAge,
                        'secondary1name' => $beneficiary1,
                        'secondary1age' => $beneficiary1Age,
                        'secondary2name' => $beneficiary2,
                        'secondary2age' => $beneficiary2Age,
                        'secondary3name' => $beneficiary3,
                        'secondary3age' => $beneficiary3Age,
                        'secondary4name' => $beneficiary4,
                        'secondary4age' => $beneficiary4Age,
                        'appliedchangemode' => $appliedChangeMode
                    ];

                    if (Schema::hasColumn('tblclient', 'principalbeneficiaryrelation')) {
                        $updateClientData['principalbeneficiaryrelation'] = $principalBeneficiaryRelation;
                    }

                    if ($principalBeneficiaryIdPath && Schema::hasColumn('tblclient', 'principalbeneficiaryid_path')) {
                        $updateClientData['principalbeneficiaryid_path'] = $principalBeneficiaryIdPath;
                    }

                    if (Schema::hasColumn('tblclient', 'packageprice')) {
                        $updateClientData['packageprice'] = $packagePrice;
                    }

                    if (Schema::hasColumn('tblclient', 'homeregion')) {
                        $updateClientData['homeregion'] = $homeRegion;
                    }

                    if (Schema::hasColumn('tblclient', 'homeprovince')) {
                        $updateClientData['homeprovince'] = $homeProvince;
                    }

                    if (Schema::hasColumn('tblclient', 'homecity')) {
                        $updateClientData['homecity'] = $homeCity;
                    }

                    if (Schema::hasColumn('tblclient', 'homebarangay')) {
                        $updateClientData['homebarangay'] = $homeBarangay;
                    }

                    if (Schema::hasColumn('tblclient', 'homezipcode')) {
                        $updateClientData['homezipcode'] = $homeZipcode;
                    }

                    if (Schema::hasColumn('tblclient', 'homestreet')) {
                        $updateClientData['homestreet'] = $homeStreet;
                    }

                    Client::where('id', $client->Id)->update($updateClientData);
                    Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update ' . '[Target] ' . $client->Id);

                    return redirect('/client-view/' . $client->Id)->with('success', 'Successfully updated the selected client!');
                } catch (\Exception $e) {
                    return redirect('/client-view/' . $client->Id)->with('error', 'An error occured while updating the selected client.');
                }
            } else {
                Log::warning('❌ Attempted to change contract details for an Approved client');
                return redirect()->back()->with('error', 'Approved clients cannot change their contract number, region, or branch.')->withInput();
            }
        }
    }

    // update client status
    public function updateClientStatus(Request $request, Client $client)
    {

        try {
            $updateClientStatus = $client->Status;
            $statusMessage = "";

            if ($updateClientStatus == '1') {

                $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
                $actions = Actions::query()->where('action', '=', 'Verify')->first();
                if ($roleLevel->Level > $actions->RoleLevel) {
                    return redirect()->back()->with('error', 'You do not have access to this function.');
                }

                $updateClientStatus = '2';
                $statusMessage = "verified!";
                $remarks = 'Verified';

                $updateClientData = [
                    'status' => $updateClientStatus,
                    'remarks' => $remarks
                ];

                Client::where('id', $client->Id)->update($updateClientData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update ' . '[Target] ' . $client->Id);

                return redirect('/client-view/' . $client->Id)->with('success', 'Selected client has been ' . $statusMessage);
            } else if ($updateClientStatus == '2') {

                try {
                    $availableContract = '1';
                    $contractExists = ContractBatch::select('tblcontractbatch.*', 'tblcontract.id as contractid')
                        ->leftJoin('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
                        ->where('ContractNumber', $client->ContractNumber)
                        ->where('RegionId', $client->RegionId)
                        ->where('BranchId', $client->BranchId)
                        ->where('Status', $availableContract)
                        ->first();

                    if ($contractExists) {

                        $searchedContractId = $contractExists->contractid;

                        // check if OR no is available
                        $paymentDetails = Payment::where('clientid', $client->Id)
                            ->orderBy('id', 'desc')
                            ->first();

                        $availableOR = '1';
                        $orExists = ORBatch::select('tblorbatch.*', 'tblofficialreceipt.id as orid')
                            ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                            ->where('ORNumber', $paymentDetails->ORNo)
                            ->where('tblofficialreceipt.id', $paymentDetails->ORId)
                            ->where('Status', $availableOR)
                            ->first();

                        if ($orExists) {

                            $searchedOfficialReceiptId = $orExists->orid;

                            // update contract status
                            $usedContract = '2';
                            $updateContractData = [
                                'status' => $usedContract,
                                'clientid' => $client->Id
                            ];

                            Contract::where('id', $searchedContractId)
                                ->where('contractnumber', $client->ContractNumber)
                                ->update($updateContractData);
                            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update Contract ' . '[Target] ' . $client->Id);

                            // update OR status
                            $usedOR = '2';
                            $updateORData = [
                                'status' => $usedOR
                            ];

                            OfficialReceipt::where('id', $searchedOfficialReceiptId)
                                ->where('ORNumber', $paymentDetails->ORNo)
                                ->update($updateORData);
                            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update OR Status ' . '[Target] ' . $client->Id);

                            // create username
                            $clientName = strtolower($client->LastName . $client->FirstName);
                            $clientUserName = $client->ContractNumber . $clientName;

                            // add to users
                            $clientRole = '7';
                            $insertUserData = [
                                'username' => $clientUserName,
                                'password' => sha1('temp1234'),
                                'defaultpw' => $client->ContractNumber,
                                'roleid' => $clientRole,
                                'datecreated' => date("Y-m-d H:i:s"),
                                'createdby' => session('user_id')
                            ];

                            $clientUserId = User::insertGetId($insertUserData);

                            $updateClientStatus = '3';
                            $statusMessage = "approved!";
                            $remarks = "Approved";

                            $updateClientData = [
                                'status' => $updateClientStatus,
                                'userid' => $clientUserId,
                                'remarks' => $remarks
                            ];

                            Client::where('id', $client->Id)->update($updateClientData);
                            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update ' . '[Target] ' . $client->Id);

                            // send email for successful approved client
                            $clientTerm = PaymentTerm
                                ::select('Term')
                                ->where('Id', $client->PaymentTermId)
                                ->first();

                            $paymentAmount = $paymentDetails->AmountPaid;
                            $paymentDate = Carbon::parse($paymentDetails->Date);
                            $paymenDateFormat = $paymentDate->format('Y-m-d');

                            $paymentMultiplier = $paymentAmount / $client->PaymentTermAmount;
                            $paymentAmount = number_format($paymentAmount, 2);

                            switch ($clientTerm->Term) {
                                case 'Monthly':
                                    $dueDate = $paymentDate->addMonths($paymentMultiplier * 1);
                                    break;
                                case 'Quarterly':
                                    $dueDate = $paymentDate->addMonths($paymentMultiplier * 3);
                                    break;
                                case 'Semi-Annual':
                                    $dueDate = $paymentDate->addMonths($paymentMultiplier * 6);
                                    break;
                                case 'Annual':
                                    $dueDate = $paymentDate->addMonths($paymentMultiplier * 12);
                                    break;
                            }

                            $dueDateFormat = $dueDate->format('Y-m-d');
                            // $sender = "Surelife Care & Services Admin";
                            // Mail::to($client->EmailAddress)->send(new NewClientMail($paymentAmount, $paymenDateFormat, $dueDateFormat, $sender));

                            // send sms to clients
//                             $sms_message = 'Thank you for applying with Surelife Care & Services. 

                            // This is to acknowledge your payment with the amount of P' . $paymentAmount . ' on ' . $paymenDateFormat  . ' has been received by Surelife Care & Services. Your next due is on ' . $dueDateFormat . '. You can pay on the nearest Surelife branch. 

                            // Smile to a worry-free financial future. (This is a system generated message. Do not reply)';

                            //                             $insertSmsData = [
//                                 'contactno' => $client->MobileNumber,
//                                 'message' => $sms_message,
//                                 'sendto' => 'Client',
//                                 'status' => 1
//                             ];
//                             Sms::insert($insertSmsData);

                            return redirect('/client-view/' . $client->Id)->with('success', 'Selected client has been ' . $statusMessage);
                        } else {
                            return redirect()->back()->with('error', 'Could not approve client. O.R number entered is not available.');
                        }
                    } else {
                        return redirect()->back()->with('error', 'Could not approve client. Contract number entered is not available.');
                    }
                } catch (\Exception $e) {
                    return redirect('/client-view/' . $client->Id)->with('error', 'An error occured while updating status of the selected client.');
                }
            }
        } catch (\Exception $e) {
            return redirect('/client-view/' . $client->Id)->with('error', 'An error occured while updating status of the selected client.');
        }
    }
    // delete client
    public function deleteClient(Client $client, Request $request)
    {

        try {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if ($roleLevel->Level <= $actions->RoleLevel) {
                Client::where('id', $client->Id)->delete();
                User::where('id', $client->UserId)->delete();
                Payment::where('clientid', $client->Id)->delete();

                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Delete ' . '[Target] ' . $client->Id);
                
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Client deleted successfully',
                        'redirect' => '/client'
                    ]);
                }
                
                return redirect('/client')->with('warning', 'Selected client has been deleted!');
            } else {
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this function'
                    ], 403);
                }
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') == 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the client'
                ], 500);
            }
            return redirect('/client')->with('error', 'An error occurred while deleting the selected client.');
        }
    }

    // print statement of account
    public function printSOA(Client $client, Request $request)
    {

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Statement of Account')->first();
        if ($roleLevel->Level > $actions->RoleLevel) {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }

        if ($request->has('export')) {

            $clients = Client::query()
                ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
                ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
                ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
                ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
                ->leftJoin('tblprovince', 'tblclient.Province', '=', 'tblprovince.id')
                ->leftJoin('tblcity', 'tblclient.City', '=', 'tblcity.id')
                ->leftJoin('tblbrgy', 'tblclient.Barangay', '=', 'tblbrgy.id')
                ->leftJoin('tbladdress as addr_province', function ($join) {
                    $join->on('tblclient.Province', '=', 'addr_province.code')
                        ->where('addr_province.address_type', '=', 'province');
                })
                ->leftJoin('tbladdress as addr_city', function ($join) {
                    $join->on('tblclient.City', '=', 'addr_city.code')
                        ->where('addr_city.address_type', '=', 'citymun');
                })
                ->leftJoin('tbladdress as addr_brgy', function ($join) {
                    $join->on('tblclient.Barangay', '=', 'addr_brgy.code')
                        ->where('addr_brgy.address_type', '=', 'barangay');
                })
                ->select(
                    'tblclient.*',
                    'tblclient.Id as cid',
                    'tblregion.RegionName',
                    'tblbranch.BranchName',
                    'tblpackage.Package',
                    'tblpaymentterm.Id',
                    'tblpaymentterm.PackageId',
                    'tblpaymentterm.Term',
                    'tblpaymentterm.Price',
                    \DB::raw('COALESCE(addr_province.description, tblprovince.Province, tblclient.Province) as ProvinceName'),
                    \DB::raw('COALESCE(addr_city.description, tblcity.City, tblclient.City) as CityName'),
                    \DB::raw('COALESCE(addr_brgy.description, tblbrgy.Barangay, tblclient.Barangay) as BarangayName')
                )
                ->where('tblclient.id', $client->Id)
                ->first();

            $payments = Payment::with('officialReceipt.orBatch')
                ->where('clientid', $client->Id)
                ->orderBy('date', 'desc')
                ->orderBy('installment', 'desc')
                ->get();

            $name = $clients->LastName . ', ' . $clients->FirstName . ' ' . $clients->MiddleName;
            $contract_num = $clients->ContractNumber;
            $address1 = $clients->Street . ', ' . ($clients->BarangayName ?? $clients->Barangay);
            $address2 = ($clients->CityName ?? $clients->City) . ', ' . ($clients->ProvinceName ?? $clients->Province);
            $due_date = $clients->DateAccomplished;

            $total_payment = 0;

            $base_price = $clients->Price;
            $total_price = 0;

            switch ($clients->Term) {
                case "Spotcash":
                    $total_price = $base_price;
                    break;
                case "Annual":
                    $total_price = $base_price * 5;
                    break;
                case "Semi-Annual":
                    $total_price = ($base_price * 2) * 5;
                    break;
                case "Quarterly":
                    $total_price = ($base_price * 4) * 5;
                    break;
                case "Monthly":
                    $total_price = $base_price * 60;
                    break;
                default:
                    $total_price = $base_price;
            }

            // Output CSV
            ob_start();
            $output = fopen("php://output", "w");

            $main_header = ["STATEMENT OF ACCOUNT"];
            fputcsv($output, $main_header);
            fputcsv($output, [""]);

            $sub_header1 = ["Client Name", "", "Contract Package"];
            fputcsv($output, $sub_header1);
            fputcsv($output, [$name, "", $clients->Package . " - P" . number_format($total_price, 2)]);

            fputcsv($output, [""]);

            $sub_header2 = ["Address", "", "Contract Number"];

            fputcsv($output, $sub_header2);
            fputcsv($output, [$address1, "", $contract_num]);
            fputcsv($output, [$address2]);

            fputcsv($output, [""]);

            $sub_header3 = ["Mode of Payment", "", "Amount"];

            fputcsv($output, $sub_header3);
            fputcsv($output, [$clients->Term, "", "P " . number_format($clients->Price, 2)]);

            fputcsv($output, [""]);
            $sub_header4 = ["Status", "", "Due Date"];
            fputcsv($output, $sub_header4);

            $day = date('d', strtotime($due_date));
            fputcsv($output, ["Active", "", $day . " of the month"]);

            fputcsv($output, [""]);

            $payment_header = ["OR Date", "Series Code", "OR Number", "Amount", "Installment", "Payment Type"];
            fputcsv($output, $payment_header);

            $otherPayments = [];
            foreach ($payments as $payment) {

                $seriesCode = "Not available";
                if ($payment->officialReceipt && $payment->officialReceipt->orBatch) {
                    $seriesCode = $payment->officialReceipt->orBatch->SeriesCode;
                }

                $installment = $payment->Installment ?? 'Not available';

                if ($payment->VoidStatus == '0') {
                    $remarks = $payment->Remarks ?? 'Standard';

                    // Main Payments include Standard, Partial, and Custom Fee
                    if ($remarks == 'Standard' || $remarks == 'Partial' || $remarks == '' || $remarks == 'Custom') {
                        $paymentTypeLabel = $remarks;
                        if ($paymentTypeLabel == 'Standard' || $paymentTypeLabel == 'Partial' || $paymentTypeLabel == '') {
                            switch ($payment->PaymentType) {
                                case 1:
                                    $type = "Cash";
                                    break;
                                case 2:
                                    $type = "Credit Card";
                                    break;
                                case 3:
                                    $type = "Cheque";
                                    break;
                                default:
                                    $type = "Cash";
                            }
                            $paymentTypeLabel = $type;
                        } else if ($paymentTypeLabel == 'Custom') {
                            $paymentTypeLabel = 'Custom Fee';
                        }

                        $payment_result = [
                            $payment->Date,
                            $seriesCode,
                            $payment->ORNo,
                            "P " . number_format($payment->AmountPaid, 2),
                            $installment,
                            $paymentTypeLabel
                        ];

                        fputcsv($output, $payment_result);
                    } else {
                        // Other administrative fees
                        $otherPayments[] = [
                            $payment->Date,
                            $seriesCode,
                            $payment->ORNo,
                            "P " . number_format($payment->AmountPaid, 2),
                            $installment,
                            $remarks . ' Fee'
                        ];
                    }
                    $total_payment += $payment->AmountPaid;
                }
            }

            fputcsv($output, [""]);

            $total_payment_row = ["Total Payment", "", "", "P " . number_format($total_payment, 2)];
            fputcsv($output, $total_payment_row);

            $balance = $total_price - $total_payment;
            if ($balance <= 0) {
                $balance = 0;
            }

            $balance_row = ["Balance", "", "", "P " . number_format($balance, 2)];
            fputcsv($output, $balance_row);

            if (count($otherPayments) > 0) {
                fputcsv($output, [""]);
                fputcsv($output, ["** Other Payments **"]);
                foreach ($otherPayments as $misc) {
                    fputcsv($output, $misc);
                }
            }


            fputcsv($output, [""]);
            fputcsv($output, [""]);

            // cashier info
            $cashier = Staff
                ::select(
                    [
                        'LastName',
                        'FirstName',
                        'MiddleName',
                        'BranchName'
                    ]
                )
                ->leftJoin('tblbranch', 'tblstaff.BranchId', '=', 'tblbranch.Id')
                ->where('tblstaff.UserId', '=', session('user_id'))
                ->first();

            $footer_main = ["Prepared by", "", "Approved by"];
            fputcsv($output, $footer_main);

            $cashierName = $cashier->LastName . ', ' . $cashier->FirstName;
            $footer_main_name = [$cashierName, "", "ALDIN M. DIAZ"];
            fputcsv($output, $footer_main_name);

            $footer = [$cashier->BranchName, "", "CEO"];
            fputcsv($output, $footer);

            $csvContent = ob_get_clean();
            $file = base_path('/uploads/soa/soa.csv');
            file_put_contents($file, $csvContent);

            return $this->generateCsvResponse($file);
        }

        return response()->json(['error' => 'Export parameter not provided.'], 400);
    }

    // print statement of account - PDF version
    public function printSOAPDF(Client $client, Request $request)
    {

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Statement of Account')->first();
        if ($roleLevel->Level > $actions->RoleLevel) {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }

        // Get client data (same logic as CSV version)
        $clients = Client::query()
            ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
            ->leftJoin('tblprovince', 'tblclient.Province', '=', 'tblprovince.Id')
            ->leftJoin('tblcity', 'tblclient.City', '=', 'tblcity.Id')
            ->leftJoin('tblbrgy', 'tblclient.Barangay', '=', 'tblbrgy.Id')
            ->leftJoin('tbladdress as addr_province', function ($join) {
                $join->on('tblclient.Province', '=', 'addr_province.code')
                    ->where('addr_province.address_type', '=', 'province');
            })
            ->leftJoin('tbladdress as addr_city', function ($join) {
                $join->on('tblclient.City', '=', 'addr_city.code')
                    ->where('addr_city.address_type', '=', 'citymun');
            })
            ->leftJoin('tbladdress as addr_brgy', function ($join) {
                $join->on('tblclient.Barangay', '=', 'addr_brgy.code')
                    ->where('addr_brgy.address_type', '=', 'barangay');
            })
            ->select(
                'tblclient.*',
                'tblclient.Id as cid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblpackage.Package',
                'tblpaymentterm.Id',
                'tblpaymentterm.PackageId',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price',
                \DB::raw('COALESCE(addr_province.description, tblprovince.Province, tblclient.Province) as ProvinceName'),
                \DB::raw('COALESCE(addr_city.description, tblcity.City, tblclient.City) as CityName'),
                \DB::raw('COALESCE(addr_brgy.description, tblbrgy.Barangay, tblclient.Barangay) as BarangayName')
            )
            ->where('tblclient.id', $client->Id)
            ->first();

        $payments = Payment::with('officialReceipt.orBatch')
            ->where('clientid', $client->Id)
            ->orderBy('date', 'desc')
            ->orderBy('installment', 'desc')
            ->get();

        $name = $clients->LastName . ', ' . $clients->FirstName . ' ' . $clients->MiddleName;
        $contract_num = $clients->ContractNumber;
        $address1 = $clients->Street . ', ' . $clients->BarangayName;
        $address2 = $clients->CityName . ', ' . $clients->ProvinceName;
        $due_date = $clients->DateAccomplished;

        $total_payment = 0;

        $base_price = $clients->Price;
        $total_price = 0;

        switch ($clients->Term) {
            case "Spotcash":
                $total_price = $base_price;
                break;
            case "Annual":
                $total_price = $base_price * 5;
                break;
            case "Semi-Annual":
                $total_price = ($base_price * 2) * 5;
                break;
            case "Quarterly":
                $total_price = ($base_price * 4) * 5;
                break;
            case "Monthly":
                $total_price = $base_price * 60;
                break;
            default:
                $total_price = $base_price * 60;
        }

        $total_payment = 0;
        $otherPayments = [];
        foreach ($payments as $payment) {
            if ($payment->VoidStatus == '0') {
                $remarks = $payment->Remarks ?? 'Standard';
                if ($remarks != 'Standard' && $remarks != 'Partial' && $remarks != '' && $remarks != 'Custom') {
                    $otherPayments[] = [
                        'date' => $payment->Date,
                        'or_number' => $payment->ORNo,
                        'amount' => $payment->AmountPaid,
                        'type' => $remarks . ' Fee'
                    ];
                }
                $total_payment += $payment->AmountPaid;
            }
        }

        $balance = $total_price - $total_payment;
        if ($balance <= 0) {
            $balance = 0;
        }

        // Get cashier info
        $cashier = Staff
            ::select(
                [
                    'LastName',
                    'FirstName',
                    'MiddleName',
                    'BranchName'
                ]
            )
            ->leftJoin('tblbranch', 'tblstaff.BranchId', '=', 'tblbranch.Id')
            ->where('tblstaff.UserId', '=', session('user_id'))
            ->first();

        $cashierName = $cashier->LastName . ', ' . $cashier->FirstName;

        // Prepare data for PDF view
        $data = [
            'client' => $clients,
            'name' => $name,
            'contract_num' => $contract_num,
            'address1' => $address1,
            'address2' => $address2,
            'due_date' => $due_date,
            'total_price' => $total_price,
            'total_payment' => $total_payment,
            'balance' => $balance,
            'payments' => $payments,
            'otherPayments' => $otherPayments,
            'cashierName' => $cashierName,
            'cashierBranch' => $cashier->BranchName
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.client.client-soa-pdf', $data);

        // Set optimized options for better performance
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,  // Disable remote loading for speed
            'defaultFont' => 'Arial',
            'isFontSubsettingEnabled' => false,  // Disable for speed
            'dpi' => 150,  // Lower DPI for faster generation
        ]);

        // Download PDF
        return $pdf->download('SOA_' . $contract_num . '.pdf');

    }

    protected function generateCsvResponse($file)
    {
        return response()->download($file, 'soa.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=soa.csv',
        ])->deleteFileAfterSend(true);
    }

    // print certificate of full payment
    public function printCOFP(Client $client, Request $request)
    {

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Certificate of Full Payment')->first();
        if ($roleLevel->Level > $actions->RoleLevel) {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }

        $cfp_no = $client->CFPNO;
        if ($client->CFPNO == null) {

            $cfp_no = "NA";
            $updateCFPNO = [
                'CFPNO' => $cfp_no
            ];

            Client::where('id', $client->Id)
                ->update($updateCFPNO);

            return redirect()->back()->with('approve-cfp-success', 'Certificate of full payment has now been approved.');
        } else if ($client->CFPNO == "NA") {
            $cfp_no = $request->query('cfpNoInput');

            $updateCFPNO = [
                'CFPNO' => $cfp_no
            ];

            Client::where('id', $client->Id)
                ->update($updateCFPNO);

            return redirect()->back()->with('success', 'Certificate number has been updated.');
        }

        // get the contract price
        $clients = Client
            ::select(
                'tblclient.PackageID',
                'PackagePrice',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price'
            )
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
            ->where('tblclient.id', $client->Id)
            ->first();

        $packageId = $clients->PackageID;
        $package_price = Package::where('id', $packageId)->first();

        $total_price = $package_price->Price;

        /** DEBUG  **/
        // $img = Image::make(base_path('public/images/cert.jpeg'));

        // $text = $client->LastName . ', ' . $client->FirstName . ' ' . $client->MiddleName;
        // $img->text($text, 550, 800, function ($font) {
        //     $font->file(base_path('public/fonts/Gobold Regular.otf'));
        //     $font->size(72);
        //     $font->color('#525252');
        // });

        // $img->text('PHP ' . number_format($total_price, 2), 1200, 910, function ($font) {
        //     $font->file(base_path('public/fonts/Gobold Regular.otf'));
        //     $font->size(40);
        //     $font->color('#525252');
        // });

        // $img->text($client->ContractNumber, 1150, 990, function ($font) {
        //     $font->file(base_path('public/fonts/Gobold Regular.otf'));
        //     $font->size(40);
        //     $font->color('#525252');
        // });

        // $dateText = date("d") . "                                       " . date("M") . "   " . date("Y");
        // $img->text($dateText, 950, 1130, function ($font) {
        //     $font->file(base_path('public/fonts/Gobold Regular.otf'));
        //     $font->size(32);
        //     $font->color('#525252');
        // });

        // $img->text($cfp_no, 430, 1470, function ($font) {
        //     $font->file(base_path('public/fonts/Gobold Regular.otf'));
        //     $font->size(32);
        //     $font->color('#525252');
        // });

        // $img->text('Not valid without seal', 250, 1400, function ($font) {
        //     $font->file(base_path('public/fonts/Gobold Regular.otf'));
        //     $font->size(32);
        //     $font->color('#525252');
        // });

        /** PROD **/
        $img = Image::make(base_path('/images/cert.jpeg'));

        $text = $client->LastName . ', ' . $client->FirstName . ' ' . $client->MiddleName;
        $img->text($text, 550, 800, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(72);
            $font->color('#525252');
        });

        // temp
        $img->text('PHP ' . number_format($total_price, 2), 1200, 910, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(40);
            $font->color('#525252');
        });

        $img->text($client->ContractNumber, 1150, 990, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(40);
            $font->color('#525252');
        });

        $dateText = date("d") . "                                       " . date("M") . "   " . date("Y");
        $img->text($dateText, 950, 1130, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(32);
            $font->color('#525252');
        });

        $img->text($cfp_no, 430, 1470, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(32);
            $font->color('#525252');
        });

        $img->text('Not valid without seal', 250, 1400, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(32);
            $font->color('#525252');
        });

        $headers = [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename=cert.jpeg',
        ];

        return $img->response('jpg')->withHeaders($headers);
    }

    // assign plan to another member
    public function assignPlan(Client $client, Request $request)
    {

        $regions = Region::orderBy("regionname", "asc")->get();
        $branches = Branch::orderBy("branchname", "asc")->get();
        $provinces = Province::orderBy("province", "asc")->get();
        $cities = City::orderBy("city", "asc")->get();
        $barangays = Barangay::orderBy("barangay", "asc")->get();

        $payments = Payment
            ::where('clientid', $client->Id)
            ->orderBy('date', 'desc')
            ->orderBy('installment', 'desc')
            ->get();

        $clientTerm = PaymentTerm::query()
            ->where('packageid', $client->PackageID)
            ->where('id', $client->PaymentTermId)
            ->first();

        $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
        $actions = Actions::query()->where('action', '=', 'Assign')->first();
        if ($roleLevel->Level <= $actions->RoleLevel) {
            return view('pages.client.client-assign', [
                'client' => $client,
                'payments' => $payments,
                'client_terms' => $clientTerm,
                'regions' => $regions,
                'branches' => $branches,
                'provinces' => $provinces,
                'cities' => $cities,
                'barangays' => $barangays
            ]);
        } else {
            return redirect()->back()->with('error', 'You do not have access to this function.');
        }
    }

    // submit assign plan
    public function submitClientAssign(Client $client, Request $request)
    {

        // custom error message
        $messages = [
            'orseriescode.required' => 'This field is required.',
            'orno.required' => 'This field is required.',
            'paymentdate.required' => 'This field is required.',
            'lastname.required' => 'This field is required.',
            'lastname.min' => 'Name is too short.',
            'lastname.max' => 'Name is too long.',
            'firstname.required' => 'This field is required.',
            'firstname.min' => 'Name is too short.',
            'firstname.max' => 'Name is too long.',
            'gender.required' => 'This field is required.',
            'birthdate.required' => 'This field is required.',
            'age.required' => 'This field is required.',
            'age.min' => 'Age must be at least 18 years old.',
            'region.required' => 'This field is required.',
            'branch.required' => 'This field is required.',
            'province.required' => 'This field is required.',
            'city.required' => 'This field is required.',
            'city.not_in' => 'This field is required.',
            'barangay.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'paymenttype' => 'required',
            'paymentamount' => 'required',
            'orseriescode' => 'required',
            'orno' => 'required',
            'paymentdate' => 'required',
            'lastname' => 'required|min:1|max:30',
            'firstname' => 'required|min:1|max:30',
            'middlename' => 'nullable',
            'gender' => 'required',
            'birthdate' => 'required',
            'age' => 'required|numeric|min:18',
            'region' => 'required',
            'branch' => 'required',
            'province' => 'required',
            'city' => 'required|not_in:0',
            'barangay' => 'required',
            'zipcode' => 'nullable'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();

        $paymentType = strip_tags($validatedData['paymenttype']);
        $paymentAmount = strip_tags($validatedData['paymentamount']);
        $orSeriesCode = strip_tags($validatedData['orseriescode']);
        $orNo = strip_tags($validatedData['orno']);
        $paymentMethod = strip_tags($validatedData['paymentmethod'] ?? 'Cash');
        $paymentDate = strip_tags($validatedData['paymentdate']);

        $lastName = strip_tags($validatedData['lastname']);
        $firstName = strip_tags($validatedData['firstname']);
        $middleName = strip_tags($validatedData['middlename'] ?? '');
        $gender = strip_tags($validatedData['gender']);
        $birthDate = strip_tags($validatedData['birthdate']);
        $age = strip_tags($validatedData['age']);
        $region = strip_tags($validatedData['region']);
        $branch = strip_tags($validatedData['branch']);
        $province = strip_tags($validatedData['province']);
        $city = strip_tags($validatedData['city']);
        $barangay = strip_tags($validatedData['barangay']);
        $zipcode = strip_tags($validatedData['zipcode'] ?? '');

        $int_paymentAmount = preg_replace('/[^\d.]/', '', $paymentAmount);

        $total_paymentAmount = $int_paymentAmount;

        // check OR number
        $availableOR = '1';
        if ($paymentType == "Standard") {
            $orType = "1";
        }

        try {
            $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('ORNumber', $orNo)
                ->where('RegionId', $region)
                ->where('BranchId', $branch)
                ->where('Status', $availableOR)
                // Relaxed Type check to allow cross-usage (e.g. Standard series for Partial payment)
                // ->where('Type', $orType)
                ->where('SeriesCode', $orSeriesCode)
                ->first();

            if ($orExists) {

                $searchedOfficialReceiptId = $orExists->id;

                if ($paymentMethod == 'Cash') {
                    $paymentType = '1';
                }

                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $client->Id,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $total_paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentType,
                    'remarks' => 'Assigned',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                $paymentId = Payment::insertGetId($insertPaymentData);

                // update OR status
                $usedOR = '2';
                $updateORData = [
                    'status' => $usedOR
                ];

                OfficialReceipt::where('id', $searchedOfficialReceiptId)
                    ->where('ORNumber', $orNo)
                    ->update($updateORData);

                // insert assigned plan data
                $insert_assignedPlanData = [
                    'clientid' => $client->Id,
                    'lastname' => $lastName,
                    'firstname' => $firstName,
                    'middlename' => $middleName,
                    'gender' => $gender,
                    'birthdate' => $birthDate,
                    'age' => $age,
                    'province' => $province,
                    'city' => $city,
                    'barangay' => $barangay,
                    'zipcode' => $zipcode,
                    'paymentid' => $paymentId,
                    'assignedbyid' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                AssignedPlans::insert($insert_assignedPlanData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Assign ' . '[Target] ' . $client->Id);

                return redirect('/client-view/' . $client->Id)->with('success', 'Successfully assigned.');
            } else {
                return redirect()->back()->with('error', 'O.R not available.')->withInput();
            }
        } catch (\Exception $e) {
            return redirect('/client-assignplan/' . $client->Id)->with('error', 'An error occurred during the process.')->withInput();
        }
    }

    // add attachment to assign plans
    public function uploadAssignAttachment(Request $request, AssignedPlans $assignedPlans)
    {

        // custom error message
        $messages = [
            'assignattachment.image' => 'The uploaded file is not an image.',
            'assignattachment.mimes' => 'Only JPEG, PNG, and JPG files are allowed.',
        ];

        $fields = Validator::make($request->all(), [
            'assignattachment' => 'image|mimes:jpeg,png,jpg',
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();
        $imageName = 'Not available';

        try {
            if ($request->hasFile('assignattachment')) {

                $image = $request->file('assignattachment');
                $imageName = 'assignplan_' . $assignedPlans->Id . '.' . $image->getClientOriginalExtension();
                $image->move(base_path('uploads/assignedplans'), $imageName);
            }

            $updateData = [
                'attachment' => $imageName
            ];

            AssignedPlans::where('id', $assignedPlans->Id)->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Assign Attachment ' . '[Target] ' . $assignedPlans->Id);

            return redirect('/client-view/' . $assignedPlans->ClientId)->with('success', 'Uploaded new attachment!');
        } catch (\Exception $e) {
            return redirect('/client-view/' . $assignedPlans->ClientId)->with('error', 'An error occurred while updating attachment.');
        }
    }

    public function updateCompletedMemorial(Request $request, Client $client)
    {

        try {
            $completedMemorialStatus = 1;
            $updateData = [
                'completedmemorial' => $completedMemorialStatus
            ];

            Client::where('id', $client->Id)->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Client ' . '[Action] Update Used Contract ' . '[Target] ' . $client->Id);

            return redirect('/client-view/' . $client->Id)->with('success', 'Selected client memorial service has been completed.');
        } catch (\Exception $e) {
            return redirect('/client-view/' . $client->Id)->with('error', 'An error occurred while updating contract status.');
        }
    }

    // ** CLIENT VIEW ** // 
    // search client - payment history and information - client view
    public function viewClientHomeInfo(Request $request)
    {

        $clients = Client::query()
            ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
            ->leftJoin('tblprovince', 'tblclient.Province', '=', 'tblprovince.Id')
            ->leftJoin('tblcity', 'tblclient.City', '=', 'tblcity.Id')
            ->leftJoin('tblbrgy', 'tblclient.Barangay', '=', 'tblbrgy.Id')
            ->select(
                'tblclient.*',
                'tblclient.Id as cid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblpackage.Package',
                'tblpaymentterm.Id',
                'tblpaymentterm.PackageId',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price',
                'tblprovince.Province as ProvinceName',
                'tblcity.City as CityName',
                'tblbrgy.Barangay as BarangayName'
            )
            ->where('tblclient.id', $request['clientId'])
            ->first();

        $payments = Payment::with('officialReceipt.orBatch')->where('clientid', $clients->cid)->get();

        // Calculate balance for the client
        $total_payment = 0;
        $base_price = $clients->Price;
        $total_price = 0;

        switch ($clients->Term) {
            case "Spotcash":
                $total_price = $base_price;
                break;
            case "Annual":
                $total_price = $base_price * 5;
                break;
            case "Semi-Annual":
                $total_price = ($base_price * 2) * 5;
                break;
            case "Quarterly":
                $total_price = ($base_price * 4) * 5;
                break;
            case "Monthly":
                $total_price = $base_price * 60;
                break;
            default:
                $total_price = $base_price * 60;
        }

        foreach ($payments as $payment) {
            if ($payment->VoidStatus != '1') {
                $total_payment += $payment->AmountPaid;
            }
        }

        $balance = $total_price - $total_payment;
        if ($balance <= 0) {
            $balance = 0;
        }

        return view('pages.client-home.client-home', [
            'clients' => $clients,
            'payments' => $payments,
            'balance' => $balance
        ]);
    }

    // client view - loan request
    public function clientHomeLoanRequest(Request $request)
    {

        $clientDetails = Client::where('id', session('user_id'))->first();
        $hasLoanRequest = LoanRequest::where('clientid', $clientDetails->Id)->first();

        $loanBalance = 0;
        $loanStatus = $hasLoanRequest->Status ?? 'Ready';

        if ($hasLoanRequest) {
            $loanPayments = LoanPayment::query()
                ->where('clientid', $clientDetails)
                ->where('loanrequestid', $hasLoanRequest->Id)
                ->get();

            $totalLoanPayments = $loanPayments->sum('Amount');
            $loanBalance = $hasLoanRequest->Amount - $totalLoanPayments;
        }

        $term = 12;

        $clientTerm = PaymentTerm::where('id', $clientDetails->PaymentTermId)->first();
        $clientInstallments = Payment::where('clientid', $clientDetails->Id)->count();

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

        return view('pages.client-home.client-loanrequest', [
            'loanStatus' => $loanStatus,
            'loanRequest' => $hasLoanRequest,
            'loanBalance' => $loanBalance,
            'netLoanAmount' => $netLoanableAmount,
            'monthlyLoanAmount' => $totalMonthlyDue,
            'totalNumYearsPaid' => $totalNumYearsPaid
        ]);
    }

    // print statement of account - client view
    public function clientHomePrintSOA(Request $request)
    {

        if ($request->has('export')) {

            $client_id = $request['clientId'];

            $clients = Client::query()
                ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
                ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
                ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
                ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
                ->leftJoin('tblprovince', 'tblclient.Province', '=', 'tblprovince.id')
                ->leftJoin('tblcity', 'tblclient.City', '=', 'tblcity.id')
                ->leftJoin('tblbrgy', 'tblclient.Barangay', '=', 'tblbrgy.id')
                ->leftJoin('tbladdress as addr_province', function ($join) {
                    $join->on('tblclient.Province', '=', 'addr_province.code')
                        ->where('addr_province.address_type', '=', 'province');
                })
                ->leftJoin('tbladdress as addr_city', function ($join) {
                    $join->on('tblclient.City', '=', 'addr_city.code')
                        ->where('addr_city.address_type', '=', 'citymun');
                })
                ->leftJoin('tbladdress as addr_brgy', function ($join) {
                    $join->on('tblclient.Barangay', '=', 'addr_brgy.code')
                        ->where('addr_brgy.address_type', '=', 'barangay');
                })
                ->select(
                    'tblclient.*',
                    'tblclient.Id as cid',
                    'tblregion.RegionName',
                    'tblbranch.BranchName',
                    'tblpackage.Package',
                    'tblpaymentterm.Id',
                    'tblpaymentterm.PackageId',
                    'tblpaymentterm.Term',
                    'tblpaymentterm.Price',
                    \DB::raw('COALESCE(addr_province.description, tblprovince.Province, tblclient.Province) as ProvinceName'),
                    \DB::raw('COALESCE(addr_city.description, tblcity.City, tblclient.City) as CityName'),
                    \DB::raw('COALESCE(addr_brgy.description, tblbrgy.Barangay, tblclient.Barangay) as BarangayName')
                )
                ->where('tblclient.id', $client_id)
                ->first();

            $payments = Payment::with('officialReceipt.orBatch')
                ->where('clientid', $client_id)
                ->where('voidstatus', 0)
                ->get();

            $name = $clients->LastName . ', ' . $clients->FirstName . ' ' . $clients->MiddleName;
            $contract_num = $clients->ContractNumber;
            $address1 = $clients->Street . ', ' . ($clients->BarangayName ?? $clients->Barangay);
            $address2 = ($clients->CityName ?? $clients->City) . ', ' . ($clients->ProvinceName ?? $clients->Province);
            $due_date = $clients->DateAccomplished;

            $total_payment = 0;

            $base_price = $clients->Price;
            $total_price = 0;

            switch ($clients->Term) {
                case "Spotcash":
                    $total_price = $base_price;
                    break;
                case "Annual":
                    $total_price = $base_price * 5;
                    break;
                case "Semi-Annual":
                    $total_price = ($base_price * 2) * 5;
                    break;
                case "Quarterly":
                    $total_price = ($base_price * 4) * 5;
                    break;
                case "Monthly":
                    $total_price = $base_price * 60;
                    break;
                default:
                    $total_price = $base_price * 60;
            }

            // Output CSV
            ob_start();
            $output = fopen("php://output", "w");

            $main_header = ["STATEMENT OF ACCOUNT"];
            fputcsv($output, $main_header);
            fputcsv($output, [""]);

            $sub_header1 = ["Client Name", "", "Contract Package"];
            fputcsv($output, $sub_header1);
            fputcsv($output, [$name, "", $clients->Package . " - P" . number_format($total_price, 2)]);

            fputcsv($output, [""]);

            $sub_header2 = ["Address", "", "Contract Number"];

            fputcsv($output, $sub_header2);
            fputcsv($output, [$address1, "", $contract_num]);
            fputcsv($output, [$address2]);

            fputcsv($output, [""]);

            $sub_header3 = ["Mode of Payment", "", "Amount"];

            fputcsv($output, $sub_header3);
            fputcsv($output, [$clients->Term, "", "P " . number_format($clients->Price, 2)]);

            fputcsv($output, [""]);
            $sub_header4 = ["Status", "", "Due Date"];
            fputcsv($output, $sub_header4);

            $day = date('d', strtotime($due_date));
            fputcsv($output, ["Active", "", $day . " of the month"]);

            fputcsv($output, [""]);

            $payment_header = ["OR Date", "Series Code", "OR Number", "Amount", "Installment", "Payment Type"];
            fputcsv($output, $payment_header);

            $otherPayments = [];
            foreach ($payments as $payment) {

                $seriesCode = "Not available";
                if ($payment->officialReceipt && $payment->officialReceipt->orBatch) {
                    $seriesCode = $payment->officialReceipt->orBatch->SeriesCode;
                }

                if ($payment->VoidStatus == '0') {
                    $remarks = $payment->Remarks ?? 'Standard';

                    // Main Payments include Standard, Partial, and Custom Fee
                    if ($remarks == 'Standard' || $remarks == 'Partial' || $remarks == '' || $remarks == 'Custom') {
                        $paymentTypeLabel = $remarks;
                        if ($paymentTypeLabel == 'Standard' || $paymentTypeLabel == 'Partial' || $paymentTypeLabel == '') {
                            switch ($payment->PaymentType) {
                                case 1:
                                    $type = "Cash";
                                    break;
                                case 2:
                                    $type = "Credit Card";
                                    break;
                                case 3:
                                    $type = "Cheque";
                                    break;
                                default:
                                    $type = "Cash";
                            }
                            $paymentTypeLabel = $type;
                        } else if ($paymentTypeLabel == 'Custom') {
                            $paymentTypeLabel = 'Custom Fee';
                        }

                        $payment_result = [
                            $payment->Date,
                            $seriesCode,
                            $payment->ORNo,
                            "P " . number_format($payment->AmountPaid, 2),
                            $payment->Installment,
                            $paymentTypeLabel
                        ];

                        fputcsv($output, $payment_result);
                    } else {
                        // Other administrative fees
                        $otherPayments[] = [
                            $payment->Date,
                            $seriesCode,
                            $payment->ORNo,
                            "P " . number_format($payment->AmountPaid, 2),
                            $payment->Installment,
                            $remarks . ' Fee'
                        ];
                    }
                    $total_payment += $payment->AmountPaid;
                }
            }

            fputcsv($output, [""]);

            $total_payment_row = ["Total Payment", "", "", "P " . number_format($total_payment, 2)];
            fputcsv($output, $total_payment_row);

            $balance = $total_price - $total_payment;
            if ($balance <= 0) {
                $balance = 0;
            }

            $balance_row = ["Balance", "", "", "P " . number_format($balance, 2)];
            fputcsv($output, $balance_row);

            fputcsv($output, [""]);

            $csvContent = ob_get_clean();
            $fileDir = base_path('/uploads/soa');
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            $file = $fileDir . '/soa.csv';
            file_put_contents($file, $csvContent);

            return $this->generateCsvResponse($file);
        }

        return response()->json(['error' => 'Export parameter not provided.'], 400);
    }

    // print statement of account - PDF version (client view)
    public function clientHomePrintSOAPDF(Request $request)
    {

        $client_id = $request['clientId'];

        $clients = Client::query()
            ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
            ->leftJoin('tblprovince', 'tblclient.Province', '=', 'tblprovince.Id')
            ->leftJoin('tblcity', 'tblclient.City', '=', 'tblcity.Id')
            ->leftJoin('tblbrgy', 'tblclient.Barangay', '=', 'tblbrgy.Id')
            ->leftJoin('tbladdress as addr_province', function ($join) {
                $join->on('tblclient.Province', '=', 'addr_province.code')
                    ->where('addr_province.address_type', '=', 'province');
            })
            ->leftJoin('tbladdress as addr_city', function ($join) {
                $join->on('tblclient.City', '=', 'addr_city.code')
                    ->where('addr_city.address_type', '=', 'citymun');
            })
            ->leftJoin('tbladdress as addr_brgy', function ($join) {
                $join->on('tblclient.Barangay', '=', 'addr_brgy.code')
                    ->where('addr_brgy.address_type', '=', 'barangay');
            })
            ->select(
                'tblclient.*',
                'tblclient.Id as cid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblpackage.Package',
                'tblpaymentterm.Id',
                'tblpaymentterm.PackageId',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price',
                \DB::raw('COALESCE(addr_province.description, tblprovince.Province, tblclient.Province) as ProvinceName'),
                \DB::raw('COALESCE(addr_city.description, tblcity.City, tblclient.City) as CityName'),
                \DB::raw('COALESCE(addr_brgy.description, tblbrgy.Barangay, tblclient.Barangay) as BarangayName')
            )
            ->where('tblclient.id', $client_id)
            ->first();

        $payments = Payment::with('officialReceipt.orBatch')
            ->where('clientid', $client_id)
            ->where('voidstatus', 0)
            ->get();

        $name = $clients->LastName . ', ' . $clients->FirstName . ' ' . $clients->MiddleName;
        $contract_num = $clients->ContractNumber;
        $address1 = $clients->Street . ', ' . ($clients->BarangayName ?? $clients->Barangay);
        $address2 = ($clients->CityName ?? $clients->City) . ', ' . ($clients->ProvinceName ?? $clients->Province);
        $due_date = $clients->DateAccomplished;

        $total_payment = 0;

        $base_price = $clients->Price;
        $total_price = 0;

        switch ($clients->Term) {
            case "Spotcash":
                $total_price = $base_price;
                break;
            case "Annual":
                $total_price = $base_price * 5;
                break;
            case "Semi-Annual":
                $total_price = ($base_price * 2) * 5;
                break;
            case "Quarterly":
                $total_price = ($base_price * 4) * 5;
                break;
            case "Monthly":
                $total_price = $base_price * 60;
                break;
            default:
                $total_price = $base_price * 60;
        }

        $total_payment = 0;
        $otherPayments = [];
        foreach ($payments as $payment) {
            if ($payment->VoidStatus == '0') {
                $remarks = $payment->Remarks ?? 'Standard';
                if ($remarks != 'Standard' && $remarks != 'Partial' && $remarks != '' && $remarks != 'Custom') {
                    $otherPayments[] = [
                        'date' => $payment->Date,
                        'or_number' => $payment->ORNo,
                        'amount' => $payment->AmountPaid,
                        'type' => $remarks . ' Fee'
                    ];
                }
                $total_payment += $payment->AmountPaid;
            }
        }

        $balance = $total_price - $total_payment;
        if ($balance <= 0) {
            $balance = 0;
        }


        // Prepare data for PDF view
        $data = [
            'client' => $clients,
            'name' => $name,
            'contract_num' => $contract_num,
            'address1' => $address1,
            'address2' => $address2,
            'due_date' => $due_date,
            'total_price' => $total_price,
            'total_payment' => $total_payment,
            'balance' => $balance,
            'payments' => $payments,
            'otherPayments' => $otherPayments,
            'cashierName' => 'System Generated',
            'cashierBranch' => $clients->BranchName
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.client.client-soa-pdf', $data);

        // Set optimized options for better performance
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,  // Disable remote loading for speed
            'defaultFont' => 'Arial',
            'isFontSubsettingEnabled' => false,  // Disable for speed
            'dpi' => 150,  // Lower DPI for faster generation
        ]);

        // Download PDF
        return $pdf->download('SOA_' . $contract_num . '.pdf');

    }

    // print certificate of full payment - client view
    public function clientHomePrintCOFP(Request $request)
    {

        $client = Client
            ::select('tblclient.*')
            ->where('id', $request['clientId'])
            ->first();

        if ($client->CFPNO == null) {
            return redirect()->back()->with('error', 'Certificate number not yet applied. Please contact the administrator for details.');
        }

        $img = Image::make(base_path('/images/cert.jpeg'));

        $text = $client->LastName . ', ' . $client->FirstName . ' ' . $client->MiddleName;
        $img->text($text, 550, 800, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(72);
            $font->color('#525252');
        });

        $img->text('PHP ' . number_format($client->PackagePrice, 2), 1200, 910, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(40);
            $font->color('#525252');
        });

        $img->text($client->ContractNumber, 1150, 990, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(40);
            $font->color('#525252');
        });

        $dateText = date("d") . "                                       " . date("M") . "   " . date("Y");
        $img->text($dateText, 950, 1130, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(32);
            $font->color('#525252');
        });

        $img->text($client->CFPNO, 430, 1470, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(32);
            $font->color('#525252');
        });

        $img->text('Not valid without seal', 250, 1400, function ($font) {
            $font->file(base_path('/fonts/Gobold Regular.otf'));
            $font->size(32);
            $font->color('#525252');
        });

        $headers = [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename=cert.jpeg',
        ];

        return $img->response('jpg')->withHeaders($headers);
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // app - search clients
    public function app_searchClients(Request $request)
    {

        $staff_userid = $request->get('staffid');
        $recruitedBy = Staff::query()->where('userid', $staff_userid)->first();

        $contractNo = $request->get('contractno');
        $lastName = $request->get('lastname');
        $firstName = $request->get('firstname');

        $query_client = Client::
            select(
                'tblclient.*',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblpackage.Package',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price'
            )
            ->leftJoin('tblregion', 'tblclient.regionid', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblclient.branchid', '=', 'tblbranch.id')
            ->leftJoin('tblpackage', 'tblclient.packageid', '=', 'tblpackage.id')
            ->leftJoin('tblpaymentterm', 'tblclient.paymenttermid', '=', 'tblpaymentterm.id')
            ->where('recruitedby', '=', $recruitedBy->Id)
            ->where('contractnumber', 'like', '%' . $contractNo . '%')
            ->when($lastName, function ($query, $lastName) {
                return $query->where('lastname', 'like', '%' . $lastName . '%');
            })
            ->when($firstName, function ($query, $firstName) {
                return $query->where('firstname', 'like', '%' . $firstName . '%');
            });

        if ($request->input('status') === 'pending') {
            $query_client->where('Status', '=', '1');
        } else if ($request->input('status') === 'verified') {
            $query_client->where('Status', '=', '2');
        } else if ($request->input('status') === 'approved') {
            $query_client->where('Status', '=', '3');
        }

        $results = $query_client->get();
        return response()->json($results);
    }

    // app - search selected client ** //
    public function app_getClient(Request $request)
    {

        $client_id = $request['client_id'];

        $clients = Client
            ::select(
                'tblclient.*',
                'tblclient.Id as cid',
                'tblregion.RegionName',
                'tblbranch.BranchName',
                'tblpackage.Package',
                'tblpaymentterm.Id',
                'tblpaymentterm.PackageId',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price'
            )
            ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id')
            ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id')
            ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id')
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id')
            ->where('tblclient.id', $client_id)
            ->first();

        $payments = Payment::where('clientid', $client_id)->get();
        $orbatchInfo = [];

        foreach ($payments as $payment) {

            $ornumber = $payment->ORNo;
            $orId = $payment->ORId;

            $orbatch = OrBatch::select('tblorbatch.*')
                ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('tblofficialreceipt.ornumber', $ornumber)
                ->where('tblofficialreceipt.id', $orId)
                ->get();

            $orbatchInfo[] = $orbatch;
        }

        return response()->json([
            'client_details' => $clients,
            'payment_details' => $payments,
            'or_details' => $orbatchInfo
        ]);
    }

    // app - insert new client ** //
    public function app_newClient(Request $request)
    {

        $contractNo = $request['contract_no'];
        $packageId = $request['package_id'];
        $packagePrice = (float) str_replace(['P', ',', ' '], '', $request['package_price']);
        $termId = $request['payment_term'];
        $termAmount = (float) str_replace(['P', ',', ' '], '', $request['payment_term_amount']);
        $regionId = $request['region_id'];
        $branchId = $request['branch_id'];
        $recruitedById = $request['recruited_by'];
        $downpaymentType = $request['downpayment_type'];
        $paymentAmount = (float) str_replace(['P', ',', ' '], '', $request['payment_amount']);
        $orSeriesCode = $request['or_seriesCode'];
        $orNo = $request['or_no'];
        $paymentMethod = $request['payment_method'];
        $paymentDate = $request['payment_date'];
        $lastName = $request['last_name'];
        $firstName = $request['first_name'];
        $middleName = $request['middle_name'];
        $gender = $request['gender'];
        $birthDate = $request['birth_date'];
        $age = $request['age'];
        $birthPlace = $request['birth_place'];
        $civilStatus = $request['civil_status'];
        $religion = $request['religion'];
        $occupation = $request['occupation'];
        $bestPlaceToCollect = $request['best_place_collect'];
        $bestTimeToCollect = $request['best_time_collect'];
        $province = $request['province'];
        $city = $request['city'];
        $barangay = $request['barangay'];
        $zipcode = $request['zipcode'];
        $street = $request['street'];
        $telephone = $request['telephone'];
        $mobileNetwork = $request['mobile_network'];
        $mobileNo = $request['mobile_no'];
        $email = $request['email'];
        $emailAddress = $request['email_address'];
        $principalBeneficiary = $request['principal_benef'];
        $principalBeneficiaryAge = $request['principal_benef_age'];
        $beneficiary1 = $request['sec_benef_name'];
        $beneficiary1Age = $request['sec_benef_age'];
        $beneficiary2 = $request['sec_benef2_name'];
        $beneficiary2Age = $request['sec_benef2_age'];
        $beneficiary3 = $request['sec_benef3_name'];
        $beneficiary3Age = $request['sec_benef3_age'];
        $beneficiary4 = $request['sec_benef4_name'];
        $beneficiary4Age = $request['sec_benef4_age'];

        $mobilenumber = '0' . $mobileNetwork . $mobileNo;
        $emailcomplete = $email . '@' . $emailAddress;

        $status = '1';
        $remarks = "To be verified";
        $fsaComsRem = '0';

        // check if contract no is available
        $availableContract = '1';
        $contractExists = ContractBatch::select('tblcontractbatch.*', 'tblcontract.id as contractid')
            ->leftJoin('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
            ->where('ContractNumber', $contractNo)
            ->where('RegionId', $regionId)
            ->where('BranchId', $branchId)
            ->where('Status', $availableContract)
            ->first();

        if ($contractExists) {

            // check if OR no is available
            $availableOR = '1';
            if ($downpaymentType == "Standard") {
                $remarks = "Standard";
                $orType = "1";
            } else if ($downpaymentType == "Partial") {
                $remarks = "Partial";
                $orType = "2";
            }

            $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('ORNumber', $orNo)
                ->where('RegionId', $regionId)
                ->where('BranchId', $branchId)
                ->where('Status', $availableOR)
                // Relaxed Type check to allow cross-usage (e.g. Standard series for Partial payment)
                // ->where('Type', $orType)
                ->where('SeriesCode', $orSeriesCode)
                ->first();

            if ($orExists) {

                $searchedOfficialReceiptId = $orExists->id;

                // create a new client
                try {

                    $insertClientData = [
                        'contractnumber' => $contractNo,
                        'packageid' => $packageId,
                        'paymenttermid' => $termId,
                        'paymenttermamount' => $termAmount,
                        'regionid' => $regionId,
                        'branchid' => $branchId,
                        'recruitedby' => $recruitedById,
                        'lastname' => $lastName,
                        'firstname' => $firstName,
                        'middlename' => $middleName,
                        'gender' => $gender,
                        'birthdate' => $birthDate,
                        'age' => $age,
                        'birthplace' => $birthPlace,
                        'civilstatus' => $civilStatus,
                        'religion' => $religion,
                        'occupation' => $occupation,
                        'bestplacetocollect' => $bestPlaceToCollect,
                        'besttimetocollect' => $bestTimeToCollect,
                        'province' => $province,
                        'city' => $city,
                        'barangay' => $barangay,
                        'zipcode' => $zipcode,
                        'street' => $street,
                        'homenumber' => $telephone,
                        'mobilenumber' => $mobilenumber,
                        'emailaddress' => $emailcomplete,
                        'principalbeneficiaryname' => $principalBeneficiary,
                        'principalbeneficiaryage' => $principalBeneficiaryAge,
                        'secondary1name' => $beneficiary1,
                        'secondary1age' => $beneficiary1Age,
                        'secondary2name' => $beneficiary2,
                        'secondary2age' => $beneficiary2Age,
                        'secondary3name' => $beneficiary3,
                        'secondary3age' => $beneficiary3Age,
                        'secondary4name' => $beneficiary4,
                        'secondary4age' => $beneficiary4Age,
                        'status' => $status,
                        'remarks' => $remarks,
                        'fsacomsrem' => $fsaComsRem,
                        'datecreated' => date("Y-m-d")
                    ];

                    if (Schema::hasColumn('tblclient', 'packageprice')) {
                        $insertClientData['packageprice'] = $packagePrice;
                    }

                    $clientId = Client::insertGetId($insertClientData);

                    // standard payment
                    if ($orType == '1' && $downpaymentType == 'Standard') {
                        $current_installment = $paymentAmount / $termAmount;
                    }
                    // partial payment
                    else if ($orType == '2') {
                        $current_installment = 1;
                    }

                    // add new payment data
                    $comsMultiplier = '9';
                    if ($paymentMethod == 'Cash') {
                        $paymentMethod = '1';
                    }

                    $insertPaymentData = [
                        'orno' => $orNo,
                        'clientid' => $clientId,
                        'orid' => $searchedOfficialReceiptId,
                        'amountpaid' => $paymentAmount,
                        'installment' => $current_installment,
                        'comsmultiplier' => $comsMultiplier,
                        'date' => $paymentDate,
                        'paymenttype' => $paymentMethod,
                        'remarks' => $remarks,
                        'createdby' => session('user_id'),
                        'datecreated' => date("Y-m-d")
                    ];
                    Payment::insert($insertPaymentData);

                    return response()->json(['msg' => 'success']);
                } catch (\Exception $e) {
                    return response()->json(['msg' => 'An error occurred. Please try again.']);
                }
            } else {
                return response()->json(['msg' => 'O.R not available.']);
            }
        } else {
            return response()->json(['msg' => 'Contract is already assigned to another client.']);
        }
    }

    // app - update selected client ** //
    public function app_updateClient(Request $request)
    {

        $client_id = $request['client_id'];
        $contractNo = $request['contract_no'];
        $packageId = $request['package_id'];
        $packagePrice = (float) str_replace(['P', ',', ' '], '', $request['package_price']);
        $termId = $request['payment_term'];
        $termAmount = (float) str_replace(['P', ',', ' '], '', $request['payment_term_amount']);
        $regionId = $request['region_id'];
        $branchId = $request['branch_id'];
        $recruitedById = $request['recruited_by'];
        $downpaymentType = $request['downpayment_type'];
        $paymentAmount = (float) str_replace(['P', ',', ' '], '', $request['payment_amount']);
        $orSeriesCode = $request['or_seriesCode'];
        $orNo = $request['or_no'];
        $paymentMethod = $request['payment_method'];
        $paymentDate = $request['payment_date'];
        $lastName = $request['last_name'];
        $firstName = $request['first_name'];
        $middleName = $request['middle_name'];
        $gender = $request['gender'];
        $birthDate = $request['birth_date'];
        $age = $request['age'];
        $birthPlace = $request['birth_place'];
        $civilStatus = $request['civil_status'];
        $religion = $request['religion'];
        $occupation = $request['occupation'];
        $bestPlaceToCollect = $request['best_place_collect'];
        $bestTimeToCollect = $request['best_time_collect'];
        $province = $request['province'];
        $city = $request['city'];
        $barangay = $request['barangay'];
        $zipcode = $request['zipcode'];
        $street = $request['street'];
        $telephone = $request['telephone'];
        $mobileNetwork = $request['mobile_network'];
        $mobileNo = $request['mobile_no'];
        $email = $request['email'];
        $emailAddress = $request['email_address'];
        $principalBeneficiary = $request['principal_benef'];
        $principalBeneficiaryAge = $request['principal_benef_age'];
        $beneficiary1 = $request['sec_benef_name'];
        $beneficiary1Age = $request['sec_benef_age'];
        $beneficiary2 = $request['sec_benef2_name'];
        $beneficiary2Age = $request['sec_benef2_age'];
        $beneficiary3 = $request['sec_benef3_name'];
        $beneficiary3Age = $request['sec_benef3_age'];
        $beneficiary4 = $request['sec_benef4_name'];
        $beneficiary4Age = $request['sec_benef4_age'];

        $mobilenumber = '0' . $mobileNetwork . $mobileNo;
        $emailcomplete = $email . '@' . $emailAddress;

        $status = '1';
        $remarks = "To be verified";
        $fsaComsRem = '0';

        // check if contract no is available
        $availableContract = '1';
        $contractExists = ContractBatch::select('tblcontractbatch.*', 'tblcontract.id as contractid')
            ->leftJoin('tblcontract', 'tblcontractbatch.id', '=', 'tblcontract.contractbatchid')
            ->where('ContractNumber', $contractNo)
            ->where('RegionId', $regionId)
            ->where('BranchId', $branchId)
            ->where('Status', $availableContract)
            ->first();

        if ($contractExists) {

            $searchedContractId = $contractExists->contractid;

            // check if OR no is available
            $availableOR = '1';
            if ($downpaymentType == "Standard") {
                $remarks = "Standard";
                $orType = "1";
            } else if ($downpaymentType == "Partial") {
                $remarks = "Partial";
                $orType = "2";
            }

            $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id as orid')
                ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('ORNumber', $orNo)
                ->where('RegionId', $regionId)
                ->where('BranchId', $branchId)
                ->where('Status', $availableOR)
                // Relaxed Type check to allow cross-usage (e.g. Standard series for Partial payment)
                // ->where('Type', $orType)
                ->where('SeriesCode', $orSeriesCode)
                ->first();

            if ($orExists) {

                $searchedOfficialReceiptId = $orExists->orid;

                // update client
                try {

                    $updateClientData = [
                        'contractnumber' => $contractNo,
                        'packageid' => $packageId,
                        'paymenttermid' => $termId,
                        'paymenttermamount' => $termAmount,
                        'regionid' => $regionId,
                        'branchid' => $branchId,
                        'recruitedby' => $recruitedById,
                        'lastname' => $lastName,
                        'firstname' => $firstName,
                        'middlename' => $middleName,
                        'gender' => $gender,
                        'birthdate' => $birthDate,
                        'age' => $age,
                        'birthplace' => $birthPlace,
                        'civilstatus' => $civilStatus,
                        'religion' => $religion,
                        'occupation' => $occupation,
                        'bestplacetocollect' => $bestPlaceToCollect,
                        'besttimetocollect' => $bestTimeToCollect,
                        'province' => $province,
                        'city' => $city,
                        'barangay' => $barangay,
                        'zipcode' => $zipcode,
                        'street' => $street,
                        'homenumber' => $telephone,
                        'mobilenumber' => $mobilenumber,
                        'emailaddress' => $emailcomplete,
                        'principalbeneficiaryname' => $principalBeneficiary,
                        'principalbeneficiaryage' => $principalBeneficiaryAge,
                        'secondary1name' => $beneficiary1,
                        'secondary1age' => $beneficiary1Age,
                        'secondary2name' => $beneficiary2,
                        'secondary2age' => $beneficiary2Age,
                        'secondary3name' => $beneficiary3,
                        'secondary3age' => $beneficiary3Age,
                        'secondary4name' => $beneficiary4,
                        'secondary4age' => $beneficiary4Age
                    ];

                    if (Schema::hasColumn('tblclient', 'packageprice')) {
                        $updateClientData['packageprice'] = $packagePrice;
                    }

                    Client::where('id', $client_id)->update($updateClientData);

                    // standard payment
                    if ($orType == '1' && $downpaymentType == 'Standard') {

                        $current_installment = $paymentAmount / $termAmount;
                    }
                    // partial payment
                    else if ($orType == '2') {
                        $current_installment = 1;
                    }

                    // update payment data
                    if ($paymentMethod == 'Cash') {
                        $paymentMethod = '1';
                    }

                    $updatePaymentData = [
                        'orno' => $orNo,
                        'orid' => $searchedOfficialReceiptId,
                        'amountpaid' => $paymentAmount,
                        'installment' => $current_installment,
                        'date' => $paymentDate,
                        'paymenttype' => $paymentMethod,
                        'remarks' => $remarks,
                        'modifiedby' => session('user_id'),
                        'datecreated' => date("Y-m-d")
                    ];

                    Payment::where('clientid', $client_id)->update($updatePaymentData);

                    return response()->json(['msg' => 'success']);
                } catch (\Exception $e) {
                    return response()->json(['msg' => 'An error occurred. Please try again.']);
                }
            } else {
                return response()->json(['msg' => 'O.R not available.']);
            }
        } else {
            return response()->json(['msg' => 'Contract is already assigned to another client.']);
        }
    }
}
