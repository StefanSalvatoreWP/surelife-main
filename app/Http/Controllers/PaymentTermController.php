<?php

namespace App\Http\Controllers;

use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/* 2023 SilverDust) S. Maceren */

class PaymentTermController extends Controller
{
    // search payment terms - tables
    public function searchAll(Request $request){

        $packages = PaymentTerm::all();
    }

    // get payment term based on the selected package
    public function getPaymentTerm(Request $request){

        $packageId = $request->input('packageId');
        $paymentTerms = PaymentTerm::where('packageid', $packageId)->get();

        return response()->json($paymentTerms);
    }

    // get payment term amount based on the selected term
    public function getPaymentTermAmount(Request $request){

        $paymentTermId = $request->input('paymentTermId');
        $termAmount = PaymentTerm::where('id', $paymentTermId)->get();

        return response()->json($termAmount);
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH PACKAGE ** //
    public function app_getPaymentTerms(Request $request){

        $query = PaymentTerm::orderBy('id', 'asc')->get();
        return response()->json($query);
    }
}
