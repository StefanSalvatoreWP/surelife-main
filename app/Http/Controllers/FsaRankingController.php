<?php

namespace App\Http\Controllers;

use App\Models\Mcpr;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

/* 2024 SilverDust) S. Maceren */

class FsaRankingController extends Controller
{
    // search all by collections - by default
    public function getCollectionsRanking(Request $request){

        $latest_mcpr = Mcpr::query()
        ->orderBy('year', 'desc')
        ->orderBy('id', 'desc')
        ->first();
        
        if($request->ajax()){

            $sel_mcpr = Mcpr::query()->where('id', $request->get('mcprid'))->first();
            if($sel_mcpr){
                $startDate = $sel_mcpr->StartingDate;
                $endDate = $sel_mcpr->EndingDate;
            }
            else if($latest_mcpr){
                $startDate = $latest_mcpr->StartingDate;
                $endDate = $latest_mcpr->EndingDate;
            }
            else{
                // No MCPR data available, return empty result
                return DataTables::of([])->toJson();
            }

            $collectionsQuery = Payment::
            select(
                'tblpayment.CreatedBy',
                'tblstaff.LastName',
                'tblstaff.FirstName',
                'tblstaff.MiddleName',
                DB::raw('SUM(tblpayment.AmountPaid) as TotalAmountPaid')
            )
            ->leftJoin('tblstaff', 'tblpayment.CreatedBy', '=', 'tblstaff.Id')
            ->whereBetween('Date', [$startDate, $endDate])
            ->groupBy('tblpayment.CreatedBy', 'tblstaff.LastName', 'tblstaff.FirstName', 'tblstaff.MiddleName')
            ->orderBy('TotalAmountPaid', 'desc');

            $collectionsQuery->whereNotNull('tblstaff.LastName')
                        ->whereNotNull('tblstaff.FirstName');
                        
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $collectionsQuery->where(function ($query) use ($searchTerm) {
                    $query->where('tblstaff.LastName', 'like', "%$searchTerm%")
                    ->orWhere('tblstaff.FirstName', 'like', "%$searchTerm%")
                    ->orWhere('tblstaff.MiddleName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($collectionsQuery)->toJson();
        }

        $mcprs = Mcpr::query()
        ->orderBy('year', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        return view('pages.fsaranking.rank-collections', [
            'latest_mcpr' => $latest_mcpr,
            'mcpr_list' => $mcprs
        ]);
    }

    // search all by sales - by default
    public function getSalesRanking(Request $request){

        $latest_mcpr = Mcpr::query()
        ->orderBy('year', 'desc')
        ->orderBy('id', 'desc')
        ->first();
        
        if($request->ajax()){
           
            $sel_mcpr = Mcpr::query()->where('id', $request->get('mcprid'))->first();
            if($sel_mcpr){
                $startDate = $sel_mcpr->StartingDate;
                $endDate = $sel_mcpr->EndingDate;
            }
            else if($latest_mcpr){
                $startDate = $latest_mcpr->StartingDate;
                $endDate = $latest_mcpr->EndingDate;
            }
            else{
                // No MCPR data available, return empty result
                return DataTables::of([])->toJson();
            }

            $salesQuery = Client::
            select(
                'tblclient.RecruitedBy',
                'tblstaff.LastName',
                'tblstaff.FirstName',
                'tblstaff.MiddleName',
                DB::raw('COUNT(tblclient.RecruitedBy) as Sales')
            )
            ->leftJoin('tblstaff', 'tblclient.RecruitedBy', '=', 'tblstaff.Id')
            ->whereBetween('tblclient.DateCreated', [$startDate, $endDate])
            ->groupBy('tblclient.RecruitedBy', 'tblstaff.LastName', 'tblstaff.FirstName', 'tblstaff.MiddleName')
            ->orderBy('Sales', 'desc');

            $salesQuery->whereNotNull('tblstaff.LastName')
            ->whereNotNull('tblstaff.FirstName');
            
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $salesQuery->where(function ($query) use ($searchTerm) {
                    $query->where('tblstaff.LastName', 'like', "%$searchTerm%")
                    ->orWhere('tblstaff.FirstName', 'like', "%$searchTerm%")
                    ->orWhere('tblstaff.MiddleName', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($salesQuery)->toJson();
        }

        $mcprs = Mcpr::query()
        ->orderBy('year', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        return view('pages.fsaranking.rank-sales', [
            'latest_mcpr' => $latest_mcpr,
            'mcpr_list' => $mcprs
        ]);
    }
}
