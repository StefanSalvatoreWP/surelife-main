<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;

/* 2023 SilverDust) S. Maceren */

class EmailController extends Controller
{

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // ** SLC APP - SEARCH EMAIL ** //
    public function app_searchEmail(Request $request){

        $query = Email::query()->get();

        return response()->json($query);
    }
}
