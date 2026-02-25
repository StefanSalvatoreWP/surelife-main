<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\SlcMail;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/* 2023 SilverDust) S. Maceren */

class UserController extends Controller
{

    public function login(Request $request){

        // get username and password
        $credentials = $request->only('username', 'password', 'accesskey');
        $credentials['username'] = isset($credentials['username']) ? trim($credentials['username']) : '';
        $credentials['password'] = isset($credentials['password']) ? (string) $credentials['password'] : '';
        $hashedPassword = sha1($credentials['password']);

        // Backend logging
        Log::info('=== LOGIN ATTEMPT ===');
        Log::info('Username: ' . $credentials['username']);
        Log::info('Password length: ' . strlen($credentials['password']));
        Log::info('Access Key: ' . ($credentials['accesskey'] ?? '(empty)'));
        Log::info('Hashed Password: ' . $hashedPassword);
        Log::info('Is Admin (has SLC prefix): ' . (strpos($credentials['username'], 'SLC') !== false ? 'YES' : 'NO'));

        // admin view
        if (strpos($credentials['username'], 'SLC') !== false) {

            Log::info('Processing ADMIN login');
            $credentials['username'] = substr($credentials['username'], 3);
            Log::info('Username after removing SLC prefix: ' . $credentials['username']);

            $user = User::
            select('tbluser.id as uid', 'UserName as username', 'Password as password', 'RoleId as roleid', 'tblstaff.EmailAddress as emailaddress')
            ->leftJoin('tblstaff', 'tbluser.id', '=', 'tblstaff.userid')
            ->where('UserName', $credentials['username'])
            ->where('Password', $hashedPassword)
            ->where('RoleId', '!=', 7)
            ->first();

            Log::info('Admin user found: ' . ($user ? 'YES (ID: ' . $user->uid . ')' : 'NO'));
            Log::info('Access key valid: ' . ($credentials['accesskey'] == 'a8821dd1f' ? 'YES' : 'NO'));

            if ($user && $credentials['accesskey'] == 'a8821dd1f') {
                
                $request->session()->regenerate();

                $userId = $user->uid;
                $userName = $user->username;
                $userRoleId = $user->roleid;
                $userEmail = $user->emailaddress;

                $keyString = Str::random(8);
                
                session(['user_id' => $userId]);
                session(['user_name' => $userName]);
                session(['user_roleid' => $userRoleId]);

                Log::info('Admin login SUCCESS - Redirecting to dashboard');
                return redirect('/dashboard');
            } else {
                Log::warning('Admin login FAILED - Invalid credentials or access key');
                return redirect('/')->with('error', 'Invalid username or password');
            }
        }
        // client view
        else{
            
            Log::info('Processing CLIENT login');
            
            // check client exists
            try{
                $user = User::
                select('tbluser.id as uid', 'UserName as username', 'Password as password', 'RoleId as roleid', 'tblclient.EmailAddress as emailaddress')
                ->leftJoin('tblclient', 'tbluser.id', '=', 'tblclient.userid')
                ->where('UserName', $credentials['username'])
                ->where('Password', $hashedPassword)
                ->where('RoleId', '=', 7)
                ->first();

                Log::info('Client user found: ' . ($user ? 'YES (ID: ' . $user->uid . ')' : 'NO'));
                
                if (!$user) {
                    $contractNoSpaces = preg_replace('/\s+/', '', $credentials['username']);

                    $clientByContract = Client::
                        select('id', 'userid', 'contractnumber', 'emailaddress')
                        ->whereRaw('REPLACE(contractnumber, " ", "") = ?', [$contractNoSpaces])
                        ->first();

                    Log::info('Client lookup by contract number: ' . ($clientByContract ? 'YES (ClientID: ' . $clientByContract->id . ')' : 'NO'));

                    if ($clientByContract && $clientByContract->userid) {
                        $user = User::
                            select('tbluser.id as uid', 'UserName as username', 'Password as password', 'RoleId as roleid')
                            ->where('tbluser.id', $clientByContract->userid)
                            ->where('Password', $hashedPassword)
                            ->where('RoleId', '=', 7)
                            ->first();

                        Log::info('User found via contract-linked userid: ' . ($user ? 'YES (UserID: ' . $user->uid . ')' : 'NO'));
                    }

                    if (!$user) {
                        Log::warning('Client login FAILED - User not found in database');
                        return redirect('/')->with('error', 'Invalid username or password');
                    }
                }
                
                $client = Client::
                select('id', 'contractnumber', 'emailaddress')
                ->where('UserId', $user->uid)
                ->first();

                Log::info('Client record found: ' . ($client ? 'YES (ID: ' . $client->id . ', Contract: ' . $client->contractnumber . ')' : 'NO'));
    
                if($client){

                    $request->session()->regenerate();

                    $clientId = $client->id;
                    $clientName = $client->contractnumber;
                    $clientRoleId = $user->roleid;
                    $clientEmail = $client->emailaddress;

                    session(['user_id' => $clientId]);
                    session(['user_name' => $clientName]);
                    session(['user_roleid' => $clientRoleId]);
                    
                    Log::info('Client login SUCCESS - Redirecting to client home');
                    return redirect("/clienthome/$clientId");
                }
                else{
                    Log::warning('Client login FAILED - Client record not found');
                    return redirect('/')->with('error', 'Invalid username or password');
                }
            }
            catch(\Exception $e){
                Log::error('Client login EXCEPTION: ' . $e->getMessage());
                return redirect('/')->with('error', 'Invalid username or password');
            }
        }
    }

    public function logout(){
        auth()->logout();
        return redirect('/');
    }

    /************** 2024 **************/
    /************ SLC APP ************/
    /*********************************/

    // app - login
    public function app_login(Request $request){

        // get username and password
        $username = $request->post('username');
        $password = $request->post('password');
        $hashedPassword = sha1($password);

        // staff
        if (strpos($username, 'SLC') !== false) {

            $username = substr($username, 3);
            $query = User::
                select(
                    'tbluser.id as uid', 
                    'tblstaff.id as staffid',
                    'roleid', 
                    'lastname',
                    'firstname',
                    'middlename',
                    'emailaddress',
                    'role',
                    'level'
                    )
                ->leftJoin('tblstaff', 'tbluser.id', '=', 'tblstaff.userid')
                ->leftJoin('tblrole', 'tbluser.roleid', '=', 'tblrole.id')
                ->where('username', $username)
                ->where('password', $hashedPassword)
                ->where(function($query) {
                    $query->where('roleid', '=', '6')
                          ->orWhere('roleid', '=', '5');
                })
                ->first();

            return response()->json($query);
        }
        // client
        else{

            $user_query = User::
                select('id')
                ->where('username', $username)
                ->where('password', $hashedPassword)
                ->where('roleid', 7)
                ->first();

            if (!$user_query) {
                return response()->json([
                    'user_details' => null,
                    'client_details' => null,
                ]);
            }

            $client_query = Client
            ::select(
                'tblclient.*',
                'tblregion.RegionName', 
                'tblbranch.BranchName', 
                'tblpackage.Package',
                'tblpaymentterm.Id',
                'tblpaymentterm.PackageId',
                'tblpaymentterm.Term',
                'tblpaymentterm.Price',
                'tblclient.Id'
                )
            ->leftJoin('tblregion', 'tblclient.RegionId', '=', 'tblregion.id') 
            ->leftJoin('tblbranch', 'tblclient.BranchId', '=', 'tblbranch.id') 
            ->leftJoin('tblpackage', 'tblclient.PackageId', '=', 'tblpackage.id') 
            ->leftJoin('tblpaymentterm', 'tblclient.PaymentTermId', '=', 'tblpaymentterm.id') 
            ->where('userid', $user_query->id)
            // ->where('contractnumber', $username)
            ->first();

            return response()->json([
                'user_details' => $user_query,
                'client_details' => $client_query,
            ]);
        }

        return response()->json(new User());
    }

     // app - update pass
     public function app_updatePass(Request $request){
        
        $user_id = $request['user_id'];

        $oldpass = $request['oldpass'];
        $old_hashedPassword = sha1($oldpass);

        $newpass = $request['newpass'];
        $new_hashedPassword = sha1($newpass);

        $confirmPass = User::where('password', $old_hashedPassword)
        ->where('id', $user_id)
        ->first();

        if($confirmPass){

            $updateData = [
                'password' => $new_hashedPassword
            ];

            User::where('id', $user_id)->update($updateData);

            return response()->json(['msg' => 'success']);
        }
        else{
            return response()->json(['msg' => 'failed']);
        }
     }
}
