<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use Illuminate\Http\Request;

/* 2024 SilverDust) S. Maceren */

class SmsController extends Controller
{
    
    // send SMS
    public function sendSMS(){

        $data = Sms::where('status', '!=', '0')
        ->limit(30)
        ->get();

        return response()->json($data);
    }

    // delete SMS
    public function deleteSMS(Request $request){

        Sms::where('id', $request['id'])
        ->delete();
    }
}
