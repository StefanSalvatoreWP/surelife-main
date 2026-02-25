<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Actions;
use App\Models\Package;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

/* 2023 SilverDust) S. Maceren */

class PackageController extends Controller
{
    // search packages - tables
    public function searchAll(Request $request){

        if ($request->ajax()) {

            $query = Package::query();

            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('Package', 'like', "%$searchTerm%");
                });
            }

            return DataTables::of($query)->toJson();
        }

        return view('pages.package.package');
    }

    // search selected package - viewing
    public function searchPackage(Package $package, Request $request){

        $paymentTerms = PaymentTerm::where('packageid', $package->Id)
            ->orderBy('price', 'desc')
            ->get();

        $defaultPrices = [0, 0, 0, 0, 0];

        foreach ($paymentTerms as $index => $term) {
            if (!is_null($term->Price) && $index < count($defaultPrices)) {
                $defaultPrices[$index] = $term->Price;
            }
        }

        return view('pages.package.package-view', [
            'packages' => $package,
            'spotcashprice' => $defaultPrices[0],
            'annualprice' => $defaultPrices[1],
            'semiannualprice' => $defaultPrices[2],
            'quarterlyprice' => $defaultPrices[3],
            'monthlyprice' => $defaultPrices[4]
        ]);
    }

    // get package price based on the selected package
    public function getPackagePrice(Request $request){

        $packageId = $request->input('packageId');
        $packagesPrice = Package::where('id', $packageId)->get();

        return response()->json($packagesPrice);
    }

    // package form screen
    public function packageFormScreen(Package $package){

        $packages = Package::all();

        if (!$package->exists) {

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Insert')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                return view('pages.package.package-create', [
                    'packages' => $packages
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
        else{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Update')->first();
            if($roleLevel->Level <= $actions->RoleLevel){ 
                $paymentTerms = PaymentTerm::where('packageid', $package->Id)
                ->orderBy('price', 'desc') 
                ->get();

                return view('pages.package.package-update', [
                    'packages' => $package,
                    'paymentterms' => $paymentTerms
                ]);
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        }
    }

    // insert new data
    public function createPackage(Request $request){

        // custom error message
        $messages = [
            'packagename.required' => 'This field is required.',
            'packagename.min' => 'Name is too short',
            'packagename.max' => 'Name is too long',
            'packageprice.numeric' => 'This field is required',
            'packageprice.min' => 'Invalid amount',
            'spotcashprice.numeric' => 'This field is required',
            'spotcashprice.min' => 'Invalid amount',
            'annualprice.numeric' => 'This field is required',
            'annualprice.min' => 'Invalid amount',
            'semiannualprice.numeric' => 'This field is required',
            'semiannualprice.min' => 'Invalid amount',
            'quarterlyprice.numeric' => 'This field is required',
            'quarterlyprice.min' => 'Invalid amount',
            'monthlyprice.numeric' => 'This field is required',
            'monthlyprice.min' => 'Invalid amount'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'packagename' => 'required|min:3|max:30',
            'packageprice' => 'numeric|min:0',
            'spotcashprice' => 'numeric|min:0',
            'annualprice' => 'numeric|min:0',
            'semiannualprice' => 'numeric|min:0',
            'quarterlyprice' => 'numeric|min:0',
            'monthlyprice' => 'numeric|min:0'
        ], $messages);

        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $packagename = strip_tags($validatedData['packagename']);
        $price = $validatedData['packageprice'];
        $spotcashprice = $validatedData['spotcashprice'];
        $annualprice = $validatedData['annualprice'];
        $semiannualprice = $validatedData['semiannualprice'];
        $quarterlyprice = $validatedData['quarterlyprice'];
        $monthlyprice = $validatedData['monthlyprice'];
        $active = 1;

        // check if package exists
        $packageExists = Package::where('package', $packagename)
                ->where('price', $price)
                ->first();

        if($packageExists){
            return redirect()->back()->with('duplicate', 'Package already exists')->withInput();
        }
        // create a new package
        else{
            try {
                
                $insertData = [
                    'package' => $packagename,
                    'price' => $price,
                    'active' => $active
                ];
        
                $packageId = Package::insertGetId($insertData);
                
                $insertPaymentTerms = [
                    [
                        'packageid' => $packageId,
                        'term' => 'Spotcash',
                        'price' => $spotcashprice,
                    ],
                    [
                        'packageid' => $packageId,
                        'term' => 'Annual',
                        'price' => $annualprice,
                    ],
                    [
                        'packageid' => $packageId,
                        'term' => 'Semi-Annual',
                        'price' => $semiannualprice,
                    ],
                    [
                        'packageid' => $packageId,
                        'term' => 'Quarterly',
                        'price' => $quarterlyprice,
                    ],
                    [
                        'packageid' => $packageId,
                        'term' => 'Monthly',
                        'price' => $monthlyprice,
                    ],
                ];
                
                PaymentTerm::insert($insertPaymentTerms);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Package ' . '[Action] Insert ' . '[Target] ' . $packageId);

                return redirect('/package')->with('success', 'Added new package!');
            } catch (\Exception $e) {
                return redirect('/package')->with('error', 'An error occurred while creating a new package');
            }
        }
    }

    // update data
    public function updatePackage(Package $package, Request $request){

        // custom error message
        $messages = [
            'packagename.required' => 'This field is required.',
            'packagename.min' => 'Name is too short',
            'packagename.max' => 'Name is too long',
            'packageprice.numeric' => 'This field is required',
            'packageprice.min' => 'Invalid amount',
            'spotcashprice.numeric' => 'This field is required',
            'spotcashprice.min' => 'Invalid amount',
            'annualprice.numeric' => 'This field is required',
            'annualprice.min' => 'Invalid amount',
            'semiannualprice.numeric' => 'This field is required',
            'semiannualprice.min' => 'Invalid amount',
            'quarterlyprice.numeric' => 'This field is required',
            'quarterlyprice.min' => 'Invalid amount',
            'monthlyprice.numeric' => 'This field is required',
            'monthlyprice.min' => 'Invalid amount'
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'packagename' => 'required|min:3|max:30',
            'packageprice' => 'numeric|min:0',
            'spotcashprice' => 'numeric|min:0',
            'annualprice' => 'numeric|min:0',
            'semiannualprice' => 'numeric|min:0',
            'quarterlyprice' => 'numeric|min:0',
            'monthlyprice' => 'numeric|min:0'
        ], $messages);

        
        if ($fields->fails()) {
            return redirect()
            ->back()
            ->withErrors($fields)
            ->withInput();
        } 

        // validation has passed
        $validatedData = $fields->validated();

        $packagename = strip_tags($validatedData['packagename']);
        $price = $validatedData['packageprice'];
        $spotcashprice = $validatedData['spotcashprice'];
        $annualprice = $validatedData['annualprice'];
        $semiannualprice = $validatedData['semiannualprice'];
        $quarterlyprice = $validatedData['quarterlyprice'];
        $monthlyprice = $validatedData['monthlyprice'];
        $active = 1;

        // check if package exists and term exists
        $packageExists = Package::where('package', $packagename)->first();
 
        if($packageExists){

            $packageTermsExists = PaymentTerm::where('packageid', $packageExists->Id)
            ->orderBy('price', 'desc')
            ->get();

            if(
                $package->Price == $price &&
                $packageTermsExists[0]->Price == $spotcashprice && 
                $packageTermsExists[1]->Price == $annualprice &&
                $packageTermsExists[2]->Price == $semiannualprice &&
                $packageTermsExists[3]->Price == $quarterlyprice &&
                $packageTermsExists[4]->Price == $monthlyprice
            ){
                return redirect()->back()->with('duplicate', 'Package already exists!')->withInput();
            }
            else{

                // update package
                try {
                    
                    $updateData = [
                        'package' => $packagename,
                        'price' => $price
                    ];
            
                    Package::where('id', $package->Id)->update($updateData);
            
                    $paymentTermData = [
                        [
                            'term' => 'Spotcash',
                            'price' => $spotcashprice,
                        ],
                        [
                            'term' => 'Annual',
                            'price' => $annualprice,
                        ],
                        [
                            'term' => 'Semi-Annual',
                            'price' => $semiannualprice,
                        ],
                        [
                            'term' => 'Quarterly',
                            'price' => $quarterlyprice,
                        ],
                        [
                            'term' => 'Monthly',
                            'price' => $monthlyprice,
                        ],
                    ];
                    
                    foreach ($paymentTermData as $data) {
                        PaymentTerm::where('packageid', $package->Id)
                            ->where('term', $data['term'])
                            ->update(['price' => $data['price']]);
                    }
                    Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Package ' . '[Action] Update ' . '[Target] ' . $package->Id);

                    return redirect('/package')->with('success', 'Selected package has been updated!');
                } catch (\Exception $e) {
                    return redirect('/package')->with('error', $e->getMessage());
                }
            }
        }
        else{
            // update package
            try {
                    
                $updateData = [
                    'package' => $packagename,
                    'price' => $price
                ];
        
                Package::where('id', $package->Id)->update($updateData);
        
                $paymentTermData = [
                    [
                        'term' => 'Spotcash',
                        'price' => $spotcashprice,
                    ],
                    [
                        'term' => 'Annual',
                        'price' => $annualprice,
                    ],
                    [
                        'term' => 'Semi-Annual',
                        'price' => $semiannualprice,
                    ],
                    [
                        'term' => 'Quarterly',
                        'price' => $quarterlyprice,
                    ],
                    [
                        'term' => 'Monthly',
                        'price' => $monthlyprice,
                    ],
                ];
                
                foreach ($paymentTermData as $data) {
                    PaymentTerm::where('packageid', $package->Id)
                        ->where('term', $data['term'])
                        ->update(['price' => $data['price']]);
                }
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Package ' . '[Action] Update ' . '[Target] ' . $package->Id);

                return redirect('/package')->with('success', 'Selected package has been updated!');
            } catch (\Exception $e) {
                return redirect('/package')->with('error', $e->getMessage());
            }
        }
    }

    // delete package
    public function deletePackage(Package $package){

        try{

            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Delete')->first();
            if($roleLevel->Level <= $actions->RoleLevel){ 
                Package::where('id', $package->Id)->delete();
                PaymentTerm::where('packageid', $package->Id)->delete();
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Package ' . '[Action] Delete ' . '[Target] ' . $package->Id);

                return redirect('/package')->with('warning', 'Selected package has been deleted!');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/package')->with('error', 'An error occurred while deleting package.');
        }
    }

    // disable selected package
    public function disablePackage(Package $package){

        try {
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Disable Package')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $updateData = [
                    'active' => '0'
                ];
        
                Package::where('id', $package->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Package ' . '[Action] Disable ' . '[Target] ' . $package->Id);

                return redirect('/package-view/' . $package->Id)->with('warning', 'Selected package has been disabled.');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/package')->with('error', 'An error occurred while disabling the selected package.');
        }
    }

    // enable selected package
    public function enablePackage(Package $package){

        try {
            
            $roleLevel = Role::query()->where('id', session('user_roleid'))->first();
            $actions = Actions::query()->where('action', '=', 'Enable Package')->first();
            if($roleLevel->Level <= $actions->RoleLevel){
                $updateData = [
                    'active' => '1'
                ];
        
                Package::where('id', $package->Id)->update($updateData);
                Log::channel('activity')->info('[StaffID] ' . session('user_id') . ' [Menu] Package ' . '[Action] Enable ' . '[Target] ' . $package->Id);

                return redirect('/package-view/' . $package->Id)->with('success', 'Selected package has been enabled.');
            }
            else{
                return redirect()->back()->with('error', 'You do not have access to this function.');
            }
        } catch (\Exception $e) {
            return redirect('/package')->with('error', 'An error occurred while enabling the selected package.');
        }
    }

    // ** SLC APP - SEARCH PACKAGE ** //
    public function app_getPackages(Request $request){

        $query = Package::orderBy('package', 'asc')
        ->where('active', '=', '1')
        ->get();

        return response()->json($query);
    }
}
