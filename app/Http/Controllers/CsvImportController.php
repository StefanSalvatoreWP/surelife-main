<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Actions;
use App\Models\OrBatch;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\OfficialReceipt;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/* 2023 SilverDust) S. Maceren */

class CsvImportController extends Controller
{
    public function importClientPayments(Request $request)
    {
       
        try{

            $actions = Actions::query()->where('action', '=', 'Add Payment')->first();
            if($actions->RoleLevel < session('user_roleid')){
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
            
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt',
            ]);

            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            $data = Excel::toArray([], $path, null, \Maatwebsite\Excel\Excel::CSV);

            if (!empty($data)) {

                $rows = array_slice($data[0], 1);
                foreach ($rows as $row) {

                    $contractNo = $row[2];
                    $lastName = $row[3];
                    $firstName = $row[4];
                    
                    $clientData = Client::where('ContractNumber', $contractNo)
                    ->where('LastName', $lastName)
                    ->where('FirstName', $firstName)
                    ->select('Id', 'BranchId', 'RegionId')
                    ->first();
            
                    if ($clientData) {

                        $clientId = $clientData->Id;
                        $clientBranch = $clientData->BranchId;
                        $clientRegion = $clientData->RegionId;

                        $availableOR = '1';
                        if($row[1] == "Standard"){
                            $orType = "1";
                        }
                        else if($row[1] == "Partial"){
                            $orType = "2";
                        }

                        $orSeriesCode = $row[5];
                        $orNo = $row[6];
                        $paymentAmount = $row[7];
                        $paymentDate = $row[0];
                        $installment = $row[8];

                        try{
                            $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
                            ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid') 
                            ->where('ORNumber', $orNo)
                            ->where('RegionId', $clientRegion)
                            ->where('BranchId', $clientBranch)
                            ->where('Status', $availableOR)
                            ->where('Type', $orType)
                            ->where('SeriesCode', $orSeriesCode)
                            ->first();
    
                            if($orExists){
    
                                $searchedOfficialReceiptId = $orExists->id;
                                $paymentType = '1';
                               
                                $insertPaymentData = [
                                    'orno' => $orNo,
                                    'clientid' => $clientId,
                                    'orid' => $searchedOfficialReceiptId,
                                    'amountpaid' => $paymentAmount,
                                    'date' => $paymentDate,
                                    'installment' => $installment,
                                    'paymenttype' => $paymentType,
                                    'createdby' => session('user_id'),
                                    'datecreated' => date("Y-m-d")
                                ];
                    
                                Payment::insert($insertPaymentData);
                                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Payments ' . '[Action] CSV Import ');

                                // update OR status
                                $usedOR = '2';
                                $updateORData = [
                                    'status' => $usedOR
                                ];
                        
                                OfficialReceipt::where('id', $searchedOfficialReceiptId)
                                ->where('ORNumber', $orNo)
                                ->update($updateORData);
                            }
                            else{
                                return redirect()->back()->with('error', 'O.R Number: ' . $orNo . ' is not available.')->withInput();
                            }
                        }
                        catch(\Exception $e){
                            return redirect('/payment')->with('error', 'An error occured while importing data.');
                        }
                    }
                    else{
                        return redirect('/payment')->with('error', 'Client with contract number: ' . $contractNo . ' not found!');
                    }
                }
                // import success
                return redirect('/payment')->with('success', 'Import success!');
            } else {
                return redirect('/payment')->with('error', 'No data found in the CSV file.');
            }
        }
        catch(\Exception $e){
            return redirect('/payment')->with('error', 'File not supported.');
        }
    }
}

// namespace App\Http\Controllers;

// use App\Models\Client;
// use App\Models\Actions;
// use App\Models\OrBatch;
// use App\Models\Payment;
// use Illuminate\Http\Request;
// use App\Models\OfficialReceipt;
// use Illuminate\Support\Facades\Log;
// use Maatwebsite\Excel\Facades\Excel;

// /* 2023 SilverDust) S. Maceren */

// class CsvImportController extends Controller
// {
//     public function importClientPayments(Request $request)
//     {
       
//         try{

//             $actions = Actions::query()->where('action', '=', 'Add Payment')->first();
//             if($actions->RoleLevel < session('user_roleid')){
//                 return redirect()->back()->with('error', 'You do not have access to this function.');
//             }
            
//             $request->validate([
//                 'csv_file' => 'required|file|mimes:csv,txt',
//             ]);

//             $file = $request->file('csv_file');
//             $path = $file->getRealPath();

//             $data = Excel::toArray([], $path, null, \Maatwebsite\Excel\Excel::CSV);

//             if (!empty($data)) {

//                 $rows = array_slice($data[0], 1);
//                 foreach ($rows as $row) {

//                     $contractNo = $row[2];
                    
//                     $clientData = Client::where('ContractNumber', $contractNo)
//                     ->select('Id', 'BranchId', 'RegionId')
//                     ->first();
            
//                     if ($clientData) {

//                         $clientId = $clientData->Id;
//                         $clientBranch = $clientData->BranchId;
//                         $clientRegion = $clientData->RegionId;

//                         $availableOR = '1';
//                         if($row[1] == "Standard"){
//                             $orType = "1";
//                         }
//                         else if($row[1] == "Partial"){
//                             $orType = "2";
//                         }

//                         $orSeriesCode = $row[3];
//                         $orNo = $row[4];
//                         $paymentAmount = $row[5];
//                         $paymentDate = $row[0];
//                         $installment = $row[6];

//                         try{
//                             $orExists = OrBatch::select('tblorbatch.*', 'tblofficialreceipt.id')
//                             ->leftJoin('tblofficialreceipt', 'tblorbatch.id', '=', 'tblofficialreceipt.orbatchid') 
//                             ->where('ORNumber', $orNo)
//                             ->where('RegionId', $clientRegion)
//                             ->where('BranchId', $clientBranch)
//                             ->where('Status', $availableOR)
//                             ->where('Type', $orType)
//                             ->where('SeriesCode', $orSeriesCode)
//                             ->first();
    
//                             if($orExists){
    
//                                 $searchedOfficialReceiptId = $orExists->id;
//                                 $paymentType = '1';
                               
//                                 $insertPaymentData = [
//                                     'orno' => $orNo,
//                                     'clientid' => $clientId,
//                                     'orid' => $searchedOfficialReceiptId,
//                                     'amountpaid' => $paymentAmount,
//                                     'date' => $paymentDate,
//                                     'installment' => $installment,
//                                     'paymenttype' => $paymentType,
//                                     'createdby' => session('user_id'),
//                                     'datecreated' => date("Y-m-d")
//                                 ];
                    
//                                 Payment::insert($insertPaymentData);
//                                 Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Payments ' . '[Action] CSV Import ');

//                                 // update OR status
//                                 $usedOR = '2';
//                                 $updateORData = [
//                                     'status' => $usedOR
//                                 ];
                        
//                                 OfficialReceipt::where('id', $searchedOfficialReceiptId)
//                                 ->where('ORNumber', $orNo)
//                                 ->update($updateORData);
//                             }
//                             else{
//                                 return redirect()->back()->with('error', 'O.R Number: ' . $orNo . ' is not available.')->withInput();
//                             }
//                         }
//                         catch(\Exception $e){
//                             return redirect('/payment')->with('error', 'An error occured while importing data.');
//                         }
//                     }
//                     else{
//                         return redirect('/payment')->with('error', 'Client with contract number: ' . $contractNo . ' not found!');
//                     }
//                 }
//                 // import success
//                 return redirect('/payment')->with('success', 'Import success!');
//             } else {
//                 return redirect('/payment')->with('error', 'No data found in the CSV file.');
//             }
//         }
//         catch(\Exception $e){
//             return redirect('/payment')->with('error', 'File not supported.');
//         }
//     }
// }
