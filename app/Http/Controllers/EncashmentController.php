<?php

namespace App\Http\Controllers;

use App\Models\Encashment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/* 2024 SilverDust) S. Maceren */

class EncashmentController extends Controller
{
    // search data - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Encashment::
            select(
                'tblencashment.*',
                'tblclient.LastName',
                'tblclient.FirstName',
                'tblclient.MiddleName',
                'tblclient.ContractNumber'
            )
            ->leftJoin('tblclient', 'tblencashment.clientid', '=', 'tblclient.id')
            ->where('staffid', session('user_id'))
            ->where('tblencashment.status', '!=', 'Claimed')
            ->where('tblencashment.status', '!=', 'Rejected');

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('tblclient.LastName', 'like', "%$searchTerm%")
                    ->orWhere('tblclient.FirstName', 'like', "%$searchTerm%")
                    ->orWhere('tblclient.MiddleName', 'like', "%$searchTerm%")
                    ->orWhere('tblclient.ContractNumber', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.commission.commission');
    }


    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // app - search clients related to encashment request
    public function app_searchComClients(Request $request){

        $recruitedBy = $request->get('staffid');

        $query = Encashment::
            select(
                'tblencashment.*',
                'tblclient.LastName',
                'tblclient.FirstName',
                'tblclient.MiddleName',
                'tblclient.ContractNumber'
            )
            ->leftJoin('tblclient', 'tblencashment.clientid', '=', 'tblclient.id')
            ->where('staffid', $recruitedBy)
            ->where('tblencashment.status', '!=', 'Claimed')
            ->where('tblencashment.status', '!=', 'Rejected')
            ->orderBy('paymentdate', 'desc')
            ->get();

        return response()->json($query);
    }
}
