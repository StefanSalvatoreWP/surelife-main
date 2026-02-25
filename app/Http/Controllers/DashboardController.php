<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Mcpr;
use App\Models\Payment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/* 2024 SilverDust) S. Maceren */

class DashboardController extends Controller
{

    public function getDashboard(Request $request){
        return view('pages.dashboard.view');
    }

    // search collections
    public function searchCollections(Request $request){

        $latest_mcpr = Mcpr::query()
        ->where('year', $request->currentYear)
        ->orderBy('monthid', 'asc')
        ->get();
        
        $monthlyPaymentTotals = [];

        foreach ($latest_mcpr as $mcpr) {
            $startDate = $mcpr->StartingDate;
            $endDate = $mcpr->EndingDate;
            
            $totalAmountPaid = Payment::whereBetween('Date', [$startDate, $endDate])
                ->sum('AmountPaid');

            $monthlyPaymentTotals[$mcpr->MonthId] = $totalAmountPaid;
        }
       
        return response()->json($monthlyPaymentTotals);
    }

    // search sales
    public function searchNewSales(Request $request){
        
        $latest_mcpr = Mcpr::query()
         ->where('year', $request->currentYear)
        ->orderBy('monthid', 'asc')
        ->get();

        $monthlySalesTotals = [];

        foreach ($latest_mcpr as $mcpr) {
            $startDate = $mcpr->StartingDate;
            $endDate = $mcpr->EndingDate;
            
            $totalNewSales = Client::whereBetween('DateCreated', [$startDate, $endDate])
                ->count('DateCreated');

            $monthlySalesTotals[$mcpr->MonthId] = $totalNewSales;
        }
       
        return response()->json($monthlySalesTotals);
    }

    // get sales of the day
    public function getSalesToday(Request $request){
        $today = date('Y-m-d');
        
        // Get all clients created today with full details
        $clientsToday = Client::where('datecreated', $today)
            ->get(['id', 'lastname', 'firstname', 'contractnumber', 'datecreated', 'status']);
        $salesToday = $clientsToday->count();
        
        // Also get recent clients for debugging
        $recentClients = Client::orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'lastname', 'firstname', 'contractnumber', 'datecreated', 'status']);
        
        return response()->json([
            'salesToday' => $salesToday,
            'today' => $today,
            'todayClientsDetails' => $clientsToday,
            'recentClients' => $recentClients
        ]);
    }

    // get collections of the day
    public function getCollectionsToday(Request $request){
        $today = date('Y-m-d');
        
        // Get all payments made today with full details
        $paymentsToday = Payment::where('date', $today)
            ->where('voidstatus', 0)
            ->get(['id', 'orno', 'clientid', 'amountpaid', 'date', 'paymenttype']);
        
        // Calculate total collections for today
        $collectionsToday = Payment::where('date', $today)
            ->where('voidstatus', 0)
            ->sum('amountpaid');
        
        // Get recent payments for debugging
        $recentPayments = Payment::where('voidstatus', 0)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'orno', 'clientid', 'amountpaid', 'date', 'paymenttype']);
        
        return response()->json([
            'collectionsToday' => $collectionsToday,
            'today' => $today,
            'todayPaymentsDetails' => $paymentsToday,
            'recentPayments' => $recentPayments
        ]);
    }
}
