<?php

namespace App\Http\Controllers;

use App\Models\Mobile;
use Illuminate\Http\Request;

/* 2023 SilverDust) S. Maceren */

class MobileController extends Controller
{

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH MOBILE NO ** //
    public function app_searchMobileNo(Request $request){

        $query = Mobile::query()->get();
        
        return response()->json($query);
    }
}
