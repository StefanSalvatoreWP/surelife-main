<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class NotificationController extends Controller
{
    public function submitNotif(Request $request){

        // custom error message
        $messages = [
            'notifmsg.required' => 'This field is required.'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'notiftype' => 'required',
            'notiftarget' => 'required',
            'notifmsg' => 'required'
        ], $messages);
        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $notifType = strip_tags($validatedData['notiftype']);
        $notifTarget = strip_tags($validatedData['notiftarget']);
        $notifMsg = strip_tags($validatedData['notifmsg']);

        $sender = "Surelife Care & Services Admin";
        
        if($notifType == 'email'){

            $emailRecipients = [];

            // send email to staffs
            if ($notifTarget == 'staffs') {
                $staffEmails = Staff::whereNotNull('EmailAddress')
                    ->where('EmailAddress', '!=', '') 
                    ->pluck('EmailAddress')
                    ->filter(function ($email) {
                        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
                    })
                    ->toArray();
                
                $emailRecipients = $staffEmails;
            }
            // send email to clients
            else if ($notifTarget == 'clients') {
                $clientEmails = Client::whereNotNull('EmailAddress')
                    ->where('EmailAddress', '!=', '') 
                    ->pluck('EmailAddress')
                    ->filter(function ($email) {
                        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
                    })
                    ->toArray();
                    
                $emailRecipients = $clientEmails;
            }
        
            if (!empty($emailRecipients)) {
                Mail::to($emailRecipients)->send(new NotificationMail($notifMsg, $sender));
            }
        }
        else if($notifType == 'sms'){

            $mobileRecipients = [];
            
            // send sms to staffs
            if($notifTarget == 'staffs'){

                $staffMobileNums = Staff::whereNotNull('MobileNumber')
                    ->where('MobileNumber', '!=', '') 
                    ->pluck('MobileNumber')
                    ->toArray();
                
                $mobileRecipients = $staffMobileNums;
            }
            // send sms to clients
            else if($notifTarget == 'clients'){
                
                $clientMobileNums = Client::whereNotNull('MobileNumber')
                    ->where('MobileNumber', '!=', '') 
                    ->pluck('MobileNumber')
                    ->toArray();
                
                $mobileRecipients = $clientMobileNums;
            }

            // send sms to recipients
            // $sms_message = $notifMsg;

            // $insertSmsData = [
            //     'contactno' => $client->MobileNumber,
            //     'message' => $sms_message,
            //     'sendto' => 'Client',
            //     'status' => 1
            // ];
            // Sms::insert($insertSmsData);
        }
    
        return redirect()->back()->with('success', 'Sent successfully!');
    }
}
