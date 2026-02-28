<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Models\Client;
use App\Models\OrBatch;
use App\Models\Payment;
use App\Models\Encashment;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use App\Models\ClientTransfer;
use Illuminate\Support\Carbon;
use App\Models\OfficialReceipt;
use Yajra\DataTables\DataTables;
use App\Mail\SucceedingPaymentMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class PaymentController extends Controller
{
    // search data - tables
    public function searchAll(Request $request)
    {

        if ($request->ajax()) {

            $query = Payment::query()
                ->select(
                    'tblpayment.id',
                    'tblpayment.ORNo',
                    'tblpayment.AmountPaid',
                    'tblpayment.Installment',
                    'tblpayment.DateCreated',
                    'tblclient.LastName',
                    'tblclient.FirstName',
                    'tblclient.MiddleName',
                    'tblclient.ContractNumber'
                )
                ->leftJoin('tblclient', 'tblpayment.clientid', '=', 'tblclient.id');

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $searchTerm = $request->input('search.value');

                        // Optimize search - use exact match for numbers, LIKE for text
                        if (is_numeric($searchTerm)) {
                            // For numeric search, check OR number or contract number
                            $query->where(function ($q) use ($searchTerm) {
                                $q->where('tblpayment.ORNo', '=', $searchTerm)
                                    ->orWhere('tblclient.ContractNumber', '=', $searchTerm);
                            });
                        } else {
                            // For text search, only search lastname with optimized LIKE
                            $query->where('tblclient.LastName', 'like', "$searchTerm%");
                        }
                    }
                })
                ->toJson();
        }

        return view('pages.payment.payment');
    }

    // search payment history - selected client
    public function getPaymentHistory(Request $request)
    {
        $clientId = $request->input('cid');
        $filterPayment = $request->input('filter');

        if ($request->ajax()) {

            $query = Payment::query()
                ->select(
                    'tblpayment.Id',
                    'tblpayment.*',
                    'tblorbatch.SeriesCode'
                )
                ->leftJoin('tblofficialreceipt', 'tblpayment.orid', '=', 'tblofficialreceipt.id')
                ->leftJoin('tblorbatch', 'tblofficialreceipt.orbatchid', '=', 'tblorbatch.id')
                ->where('tblpayment.clientid', $clientId);

            if ($filterPayment == 'Others') {
                $query->whereNotNull('tblpayment.remarks')
                    ->whereNotIn('tblpayment.remarks', ['Standard', 'Partial'])
                    ->where('tblpayment.voidstatus', '<>', 1);
            } else if ($filterPayment == 'Void') {
                $query->where('tblpayment.voidstatus', 1);
            } else if ($filterPayment == 'Plan') {
                $query->where('tblpayment.voidstatus', 0)
                    ->where(function ($q) {
                        $q->whereNull('tblpayment.remarks')
                            ->orWhereIn('tblpayment.remarks', ['Standard', 'Partial']);
                    });
            } else {
                $query->where('tblpayment.voidstatus', '<>', 1);
            }

            return DataTables::of($query)->toJson();
        }

        return redirect('/client-view/' . $clientId);
    }

    // create new payment
    public function submitClientPayment(Request $request, Client $client)
    {

        // custom error message
        $messages = [
            'paymenttype.required' => 'This field is required.',
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
            'paymenttype' => 'required',
            'paymentamount' => 'required',
            'orseriescode' => 'required',
            'orno' => 'required',
            'paymentmethod' => 'required',
            'paymentdate' => 'required|date|before:2050-12-31',
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
        $paymentType = strip_tags($validatedData['paymenttype']);
        $paymentAmount = strip_tags($validatedData['paymentamount']);
        $orSeriesCode = strip_tags($validatedData['orseriescode']);
        $orNo = strip_tags($validatedData['orno']);
        $paymentMethod = strip_tags($validatedData['paymentmethod']);
        $paymentDate = strip_tags($validatedData['paymentdate']);

        // check if OR no is available
        $availableOR = '1';
        if (
            $paymentType == 'Standard' ||
            $paymentType == 'Transfer' || $paymentType == 'Reinstatement' || $paymentType == 'Change Mode' || $paymentType == 'Custom'
        ) {

            $orType = '1';
        } else if ($paymentType == 'Partial') {
            $orType = '2';
        }

        $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
            ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
            ->where('ornumber', $orNo)
            ->where('regionid', $clientRegion)
            ->where('branchid', $clientBranch)
            ->where('status', $availableOR)
            ->where('type', $orType)
            ->where('seriescode', $orSeriesCode)
            ->first();

        if ($orExists) {

            // update installment count
            $installment = Payment::where('clientid', $client->Id)
                ->where('Installment', '<>', 'not available')
                ->orderBy('id', 'desc')
                ->first();

            if ($installment) {
                $current_installment = $installment->Installment;
            } else {
                $current_installment = 1;
            }

            if ($current_installment != null) {

                // standard payment
                if ($orType == '1' && $paymentType == 'Standard') {

                    $remarks = 'Standard';

                    $updated_installment = $paymentAmount / $client->PaymentTermAmount;
                    $current_installment = $updated_installment + $current_installment;

                    // add to fsa coms
                    // $insert_encashmentData = [
                    //     'staffid' => $client->RecruitedBy,
                    //     'clientid' => $client->Id,
                    //     'contractno' => $client->ContractNumber,
                    //     'amountpaid' => $paymentAmount,
                    //     'commission' => '150',
                    //     'paymentdate' => $paymentDate,
                    //     'status' => 'Pending',
                    //     'vouchercode' => 'Not available'
                    // ];

                    // Encashment::insert($insert_encashmentData);
                }
                // partial payment
                else if ($orType == '2') {

                    $remarks = "Partial";
                    $current_installment++;
                }
            }

            $searchedOfficialReceiptId = $orExists->id;

            if ($paymentMethod == 'Cash') {
                $paymentMethod = '1';
            }

            if ($paymentType == 'Standard' || $paymentType == 'Partial') {

                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $client->Id,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'installment' => $current_installment,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => $remarks,
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];
            } else if ($paymentType == 'Transfer') {

                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $client->Id,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Transfer',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                // insert this client for transfer ownership availability
                $insertClientForTransfer = [
                    'clientid' => $client->Id,
                    'datecreated' => date("Y-m-d"),
                    'createdby' => session('user_id')
                ];

                ClientTransfer::insert($insertClientForTransfer);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Payment ' . '[Action] Insert ' . '[Target] ' . $client->Id);
            } else if ($paymentType == 'Reinstatement') {

                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $client->Id,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Reinstatement',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];
            } else if ($paymentType == 'Change Mode') {

                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $client->Id,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Change Mode',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                // update change mode
                Client::where('id', $client->Id)->update(['appliedchangemode' => 1]);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Payment ' . '[Action] Change Mode ' . '[Target] ' . $client->Id);
            } else if ($paymentType == 'Custom') {

                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $client->Id,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Custom',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Payment ' . '[Action] Custom Payment ' . '[Target] ' . $client->Id);
            }

            Payment::insert($insertPaymentData);

            // update OR status
            $usedOR = '2';
            $updateORData = [
                'status' => $usedOR
            ];

            OfficialReceipt::where('id', $searchedOfficialReceiptId)
                ->where('ornumber', $orNo)
                ->update($updateORData);

            // get client payment term
            $clientTerm = PaymentTerm
                ::select('Term')
                ->where('Id', $client->PaymentTermId)
                ->first();

            // get the remaining balance
            $base_price = $clientTerm->Price;
            $total_payments = 0;

            $payments = Payment
                ::where('clientid', $client->Id)
                ->orderBy('date', 'desc')
                ->orderBy('installment', 'desc')
                ->get();

            switch ($clientTerm->Term) {
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

            $balance = $total_price;
            foreach ($payments as $paymentKey => $paymentIndex)

                if (
                    $paymentIndex->VoidStatus != '1' &&
                    $paymentIndex->Remarks == null ||
                    ($paymentIndex->Remarks == 'Standard' || $paymentIndex->Remarks == 'Partial' || $paymentIndex->Remarks == 'Custom')
                ) {
                    $total_payments += $paymentIndex->AmountPaid;
                }

            $balance = ($total_price - $total_payments) - $paymentAmount;

            // send email and text for successful payment
            if ($balance > 0) {

                $paymentDate = Carbon::parse($paymentDate);
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

                if ($paymentType == 'Standard') {
                    // $sender = "Surelife Care & Services Admin";
                    // Mail::to($client->EmailAddress)->send(new SucceedingPaymentMail($paymentAmount, $paymenDateFormat, $sender));

                    // send sms to clients
//                     $sms_message = 'This is to acknowledge your payment with the amount of P' . $paymentAmount . ' on ' . $paymenDateFormat . ' has been received by Surelife Care & Services. Your next due is on ' . $dueDateFormat . '. You can pay on the nearest Surelife branch. Thank you for your constant support! 

                    // Smile to a worry-free financial future. (This is a system generated message. Do not reply)';

                    //                     $insertSmsData = [
//                         'contactno' => $client->MobileNumber,
//                         'message' => $sms_message,
//                         'sendto' => 'Client',
//                         'status' => 1
//                     ];
//                     Sms::insert($insertSmsData);
                }
            }

            return redirect('/client-view/' . $client->Id)->with('success', 'Added new payment!');
        } else {
            return redirect()->back()->with('duplicate', 'O.R not available.')->withInput();
        }
    }

    // void selected client payment
    public function voidClientPayment(Payment $payment)
    {

        try {

            $voidStatus = '1';
            $voidRemarks = 'Void';

            $updateData = [
                'voidstatus' => $voidStatus,
                'remarks' => $voidRemarks
            ];

            Payment::where('id', $payment->Id)
                ->update($updateData);
            Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Payment ' . '[Action] Void ' . '[Target] ' . $payment->Id);

            return redirect()->back()->with('success', 'Void payment successful!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occured while performing the action.');
        }
    }

    // Get OR series codes filtered by branch for dropdown
    public function getOrSeriesCodesByBranch(Request $request)
    {
        $branchId = $request->input('branchId');
        $regionId = $request->input('regionId');
        $paymentType = $request->input('paymentType');

        // Primary OR type based on payment type (1 = Standard/Virtual, 2 = Partial)
        // But we'll relax this to allow all if none or few are found
        $orType = ($paymentType === 'Partial') ? '2' : '1';

        Log::info('getOrSeriesCodesByBranch (relaxed search)', [
            'branchId' => $branchId,
            'regionId' => $regionId,
            'paymentType' => $paymentType,
            'requestedOrType' => $orType
        ]);

        // 1. Get ALL series codes for the specific branch and region, prioritizing the requested type
        $query = OrBatch::select(
            'tblorbatch.seriescode as SeriesCode',
            'tblorbatch.type as Type',
            \DB::raw('COUNT(CASE WHEN tblofficialreceipt.status = 1 THEN 1 END) as available_count'),
            \DB::raw('COUNT(tblofficialreceipt.id) as total_count')
        )
            ->join('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
            ->where('tblorbatch.branchid', $branchId)
            ->where('tblorbatch.regionid', $regionId)
            ->groupBy('tblorbatch.seriescode', 'tblorbatch.type')
            ->orderByRaw("CASE WHEN tblorbatch.type = ? THEN 0 ELSE 1 END", [$orType])
            ->orderBy('tblorbatch.seriescode', 'asc');

        $seriesCodes = $query->get();

        // 2. Fallback: If no series for branch, try region level (all types)
        if ($seriesCodes->isEmpty() && $regionId) {
            $seriesCodes = OrBatch::select(
                'tblorbatch.seriescode as SeriesCode',
                'tblorbatch.type as Type',
                \DB::raw('COUNT(CASE WHEN tblofficialreceipt.status = 1 THEN 1 END) as available_count'),
                \DB::raw('COUNT(tblofficialreceipt.id) as total_count')
            )
                ->join('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
                ->where('tblorbatch.regionid', $regionId)
                ->groupBy('tblorbatch.seriescode', 'tblorbatch.type')
                ->orderByRaw("CASE WHEN tblorbatch.type = ? THEN 0 ELSE 1 END", [$orType])
                ->orderBy('tblorbatch.seriescode', 'asc')
                ->get();

            Log::info('Fallback to region-all-types', ['regionId' => $regionId, 'count' => $seriesCodes->count()]);
        }

        Log::info('getOrSeriesCodesByBranch results', [
            'count' => $seriesCodes->count(),
            'seriesCodes' => $seriesCodes->map(function ($s) {
                return $s->SeriesCode . " (Type:" . $s->Type . ")";
            })->toArray()
        ]);

        return response()->json($seriesCodes);
    }

    // Get OR numbers filtered by series code for dropdown
    public function getOrNumbersBySeriesCode(Request $request)
    {
        $seriesCode = $request->input('seriesCode');
        $branchId = $request->input('branchId');
        $regionId = $request->input('regionId');
        $paymentType = $request->input('paymentType');

        // Determine OR type based on payment type (1 = Standard/Virtual, 2 = Partial)
        $orType = ($paymentType === 'Partial') ? '2' : '1';

        // Log the request parameters for debugging
        Log::info('getOrNumbersBySeriesCode called', [
            'seriesCode' => $seriesCode,
            'branchId' => $branchId,
            'regionId' => $regionId,
            'paymentType' => $paymentType,
            'orType' => $orType
        ]);

        // Get available OR numbers for the given series code
        // Relaxed type constraint to allow cross-usage (Standard series for Partial payment)
        $orNumbers = OfficialReceipt::select('tblofficialreceipt.ornumber as ORNumber', 'tblofficialreceipt.id')
            ->join('tblorbatch', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
            ->where('tblofficialreceipt.status', '1') // Available ORs only
            ->where('tblorbatch.seriescode', $seriesCode);

        // Add branch filter if provided
        if ($branchId) {
            $orNumbers->where('tblorbatch.branchid', $branchId);
        }

        // Add region filter if provided
        if ($regionId) {
            $orNumbers->where('tblorbatch.regionid', $regionId);
        }

        $results = $orNumbers->orderBy('tblofficialreceipt.ornumber', 'asc')
            ->limit(100) // Limit to prevent overloading
            ->get();

        // Log the results for debugging
        Log::info('getOrNumbersBySeriesCode results', [
            'count' => $results->count(),
            'firstFew' => $results->take(5)->pluck('ORNumber')->toArray()
        ]);

        return response()->json($results);
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // app - payment history
    public function app_searchPaymentHistory(Request $request)
    {

        $payments = Payment::where('clientid', $request->get('cid'))
            ->orderBy('id', 'desc')
            ->get();
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

        $responseData = [
            'payments' => $payments,
            'orbatchinfo' => $orbatchInfo
        ];

        return response()->json($responseData);
    }

    // app - insert new payment
    public function app_newPayment(Request $request)
    {

        $clientId = $request['client_id'];
        $clientRegion = $request['client_regionid'];
        $clientBranch = $request['client_branchid'];
        $clientEmail = $request['client_email'];
        $clientMobileNo = $request['client_mobile'];

        $paymentType = $request['payment_type'];
        $paymentTermId = $request['payment_termid'];
        $paymentTermAmount = $request['payment_termamount'];
        $paymentAmount = (float) str_replace(['P', ',', ' '], '', $request['payment_amount']);
        $orSeriesCode = $request['or_seriescode'];
        $orNo = $request['or_no'];
        $paymentMethod = $request['payment_method'];
        $paymentDate = $request['payment_date'];

        // check if OR no is available
        $availableOR = '1';
        if ($paymentType == "Standard") {
            $orType = "1";
            $remarks = 'Standard';
        } else if ($paymentType == "Partial") {
            $orType = "2";
            $remarks = 'Partial';
        } else if ($paymentType == "Transfer") {
            $orType = "1";
            $remarks = 'Transfer';
        } else if ($paymentType == "Reinstatement") {
            $orType = "1";
            $remarks = 'Reinstatement';
        } else if ($paymentType == "Change Mode") {
            $orType = "1";
            $remarks = 'Change Mode';
        }

        $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
            ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid')
            ->where('ornumber', $orNo)
            ->where('regionid', $clientRegion)
            ->where('branchid', $clientBranch)
            ->where('status', $availableOR)
            ->where('type', $orType)
            ->where('seriescode', $orSeriesCode)
            ->first();

        if ($orExists) {

            $searchedOfficialReceiptId = $orExists->id;

            // update installment count
            $installment = Payment::where('clientid', $clientId)
                ->where('Installment', '<>', 'not available')
                ->orderBy('id', 'desc')
                ->first();

            if ($installment) {
                $current_installment = $installment->Installment;
            } else {
                $current_installment = 1;
            }

            if ($current_installment != null) {

                // standard payment
                if ($orType == '1' && $paymentType == 'Standard') {

                    $updated_installment = $paymentAmount / $paymentTermAmount;
                    $current_installment = $updated_installment + $current_installment;

                    // add to fsa coms
                    // $insert_encashmentData = [
                    //     'staffid' => $client->RecruitedBy,
                    //     'clientid' => $client->Id,
                    //     'contractno' => $client->ContractNumber,
                    //     'amountpaid' => $paymentAmount,
                    //     'commission' => '150',
                    //     'paymentdate' => $paymentDate,
                    //     'status' => 'Pending',
                    //     'vouchercode' => 'Not available'
                    // ];

                    // Encashment::insert($insert_encashmentData);
                }
                // partial payment
                else if ($orType == '2') {
                    $current_installment++;
                }
            }

            if ($paymentMethod == 'Cash') {
                $paymentMethod = '1';
            }

            // Build payment data based on payment type
            if ($paymentType == 'Standard' || $paymentType == 'Partial') {
                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $clientId,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'installment' => $current_installment,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => $remarks,
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];
            } else if ($paymentType == 'Transfer') {
                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $clientId,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Transfer',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                // insert this client for transfer ownership availability
                $insertClientForTransfer = [
                    'clientid' => $clientId,
                    'datecreated' => date("Y-m-d"),
                    'createdby' => session('user_id')
                ];

                ClientTransfer::insert($insertClientForTransfer);
                Log::channel('activity')->info('[Mobile App] [ClientID] ' . $clientId . ' [Menu] Payment [Action] Insert Transfer');
            } else if ($paymentType == 'Reinstatement') {
                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $clientId,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Reinstatement',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];
            } else if ($paymentType == 'Change Mode') {
                $insertPaymentData = [
                    'orno' => $orNo,
                    'clientid' => $clientId,
                    'orid' => $searchedOfficialReceiptId,
                    'amountpaid' => $paymentAmount,
                    'date' => $paymentDate,
                    'paymenttype' => $paymentMethod,
                    'remarks' => 'Change Mode',
                    'createdby' => session('user_id'),
                    'datecreated' => date("Y-m-d")
                ];

                // update change mode
                Client::where('id', $clientId)->update(['appliedchangemode' => 1]);
                Log::channel('activity')->info('[Mobile App] [ClientID] ' . $clientId . ' [Menu] Payment [Action] Change Mode');
            }

            Payment::insert($insertPaymentData);

            // update OR status
            $usedOR = '2';
            $updateORData = [
                'status' => $usedOR
            ];

            OfficialReceipt::where('id', $searchedOfficialReceiptId)
                ->where('ornumber', $orNo)
                ->update($updateORData);

            // send email for successful payment
            $clientTerm = PaymentTerm
                ::select('Term')
                ->where('Id', $paymentTermId)
                ->first();

            $paymentDate = Carbon::parse($paymentDate);
            $paymenDateFormat = $paymentDate->format('Y-m-d');

            $paymentMultiplier = $paymentAmount / $paymentTermAmount;
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

            if ($paymentType == "Standard") {
                // $sender = "Surelife Care & Services Admin";
                // Mail::to($client->EmailAddress)->send(new SucceedingPaymentMail($paymentAmount, $paymenDateFormat, $dueDateFormat, $sender));

                // send sms to clients
//                 $sms_message = 'This is to acknowledge your payment with the amount of P' . $paymentAmount . ' on ' . $paymenDateFormat . ' has been received by Surelife Care & Services. Your next due is on ' . $dueDateFormat . '. You can pay on the nearest Surelife branch. Thank you for your constant support! 

                // Smile to a worry-free financial future. (This is a system generated message. Do not reply)';

                //                 $insertSmsData = [
//                     'contactno' => $clientMobileNo,
//                     'message' => $sms_message,
//                     'sendto' => 'Client',
//                     'status' => 1
//                 ];
//                 Sms::insert($insertSmsData);
            }

            return response()->json(['msg' => 'success']);
        } else {
            return response()->json(['msg' => 'O.R not available']);
        }

        return response()->json(['msg' => 'Something went wrong']);
    }
}
