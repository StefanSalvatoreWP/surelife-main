<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AddressController - TBLADDRESS Integration
 * Handles cascading address selection for the SureLife system
 * Uses the newly integrated Philippine address reference database
 */

class AddressController extends Controller
{
    /**
     * Get all regions for dropdown
     */
    public function getRegions(Request $request)
    {
        try {
            $regions = DB::table('tbladdress')
                ->where('address_type', 'region')
                ->select('code', 'description as name')
                ->orderBy('description')
                ->get();

            return response()->json($regions, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error fetching regions: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch regions'], 500);
        }
    }

    /**
     * Get provinces by region code
     */
    public function getProvincesByRegion(Request $request)
    {
        try {
            $regionCode = $request->input('regionCode');

            if (empty($regionCode)) {
                return response()->json([]);
            }

            $provinces = DB::table('tbladdress')
                ->where('address_type', 'province')
                ->where('region_code', $regionCode)
                ->select('code', 'description as name', 'psgc_code')
                ->orderBy('description')
                ->get();

            return response()->json($provinces, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error fetching provinces: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch provinces'], 500);
        }
    }

    /**
     * Get cities/municipalities by province code
     */
    public function getCitiesByProvince(Request $request)
    {
        try {
            $provinceCode = $request->input('provinceCode');

            if (empty($provinceCode)) {
                return response()->json([]);
            }

            $cities = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('province_code', $provinceCode)
                ->select('code', 'description as name', 'psgc_code')
                ->orderBy('description')
                ->get();

            return response()->json($cities, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error fetching cities: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch cities'], 500);
        }
    }

    /**
     * Get barangays by city/municipality code
     */
    public function getBarangaysByCity(Request $request)
    {
        try {
            $cityCode = $request->input('cityCode');

            if (empty($cityCode)) {
                return response()->json([]);
            }

            $barangays = DB::table('tbladdress')
                ->where('address_type', 'barangay')
                ->where('citymun_code', $cityCode)
                ->select('code', 'description as name', 'psgc_code')
                ->orderBy('description')
                ->get();

            return response()->json($barangays, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error fetching barangays: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch barangays'], 500);
        }
    }

    /**
     * Get complete address hierarchy by barangay code
     */
    public function getCompleteAddress(Request $request)
    {
        try {
            $barangayCode = $request->input('barangayCode');

            if (empty($barangayCode)) {
                return response()->json(['error' => 'Barangay code required'], 400);
            }

            $address = DB::table('tbladdress as b')
                ->leftJoin('tbladdress as c', function ($join) {
                    $join->on('c.code', '=', 'b.citymun_code')
                        ->where('c.address_type', '=', 'citymun');
                })
                ->leftJoin('tbladdress as p', function ($join) {
                    $join->on('p.code', '=', 'b.province_code')
                        ->where('p.address_type', '=', 'province');
                })
                ->leftJoin('tbladdress as r', function ($join) {
                    $join->on('r.code', '=', 'b.region_code')
                        ->where('r.address_type', '=', 'region');
                })
                ->where('b.address_type', 'barangay')
                ->where('b.code', $barangayCode)
                ->select(
                    'r.description as region_name',
                    'r.code as region_code',
                    'p.description as province_name',
                    'p.code as province_code',
                    'c.description as city_name',
                    'c.code as city_code',
                    'b.description as barangay_name',
                    'b.code as barangay_code'
                )
                ->first();

            return response()->json($address, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error fetching complete address: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch address'], 500);
        }
    }

    /**
     * Search addresses by text (for autocomplete)
     */
    public function searchAddresses(Request $request)
    {
        try {
            $searchTerm = $request->input('search');
            $addressType = $request->input('type', 'all'); // region, province, citymun, barangay, or all

            if (empty($searchTerm) || strlen($searchTerm) < 2) {
                return response()->json([]);
            }

            $query = DB::table('tbladdress')
                ->select('address_type', 'code', 'description', 'psgc_code')
                ->where('description', 'like', "%$searchTerm%");

            if ($addressType !== 'all') {
                $query->where('address_type', $addressType);
            }

            $results = $query->orderBy('address_type')
                ->orderBy('description')
                ->limit(50)
                ->get();

            return response()->json($results, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error searching addresses: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    /**
     * Get zipcode by city name (integrating with old tblcity system)
     */
    public function getZipcodeByCity(Request $request)
    {
        try {
            $code = $request->input('cityName'); // Input is actually the City/Mun Code

            if (empty($code)) {
                return response()->json(['zipcode' => '']);
            }

            // Direct fetch from tbladdress using unique Code
            $cityInfo = DB::table('tbladdress')
                ->where('address_type', 'citymun')
                ->where('code', $code)
                ->select('zipcode', 'description')
                ->first();

            // IF we found a zip in tbladdress, return it (This is the PRIMARY source now)
            if ($cityInfo && !empty($cityInfo->zipcode)) {
                return response()->json([
                    'zipcode' => $cityInfo->zipcode,
                    'city_name' => $cityInfo->description
                ]);
            }

            // Fallback: If no zip in tbladdress, try legacy tblcity match (using name)
            $searchCityName = $cityInfo ? $cityInfo->description : $code;

            $legacyZip = DB::table('tblcity')
                ->where('city', 'LIKE', "%$searchCityName%")
                ->value('Zipcode');

            return response()->json([
                'zipcode' => $legacyZip ?? '',
                'city_name' => $searchCityName
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching zipcode: ' . $e->getMessage());
            return response()->json(['zipcode' => '', 'error' => 'Failed to fetch zipcode'], 500);
        }
    }

    /**
     * Get address statistics (for dashboard/reports)
     */
    public function getAddressStats()
    {
        try {
            $stats = DB::table('tbladdress')
                ->select('address_type', DB::raw('COUNT(*) as count'))
                ->groupBy('address_type')
                ->get()
                ->pluck('count', 'address_type');

            return response()->json([
                'regions' => $stats['region'] ?? 0,
                'provinces' => $stats['province'] ?? 0,
                'cities' => $stats['citymun'] ?? 0,
                'barangays' => $stats['barangay'] ?? 0,
                'total' => array_sum($stats->toArray())
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching address stats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch stats'], 500);
        }
    }

    /**
     * Get province code from city code
     * Used for staff address pre-population where province needs to be derived from city
     */
    public function getProvinceFromCity(Request $request)
    {
        try {
            $cityCode = $request->get('cityCode');

            if (empty($cityCode)) {
                return response()->json(['error' => 'City code is required'], 400);
            }

            // Look up the city to get its parent (province)
            $city = DB::table('tbladdress')
                ->where('code', $cityCode)
                ->where('address_type', 'citymun')
                ->first();

            if (!$city) {
                return response()->json(['error' => 'City not found'], 404);
            }

            // Return the province code (parent_code of the city)
            return response()->json([
                'provinceCode' => $city->parent_code,
                'cityCode' => $city->code,
                'cityName' => $city->description
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting province from city: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get province'], 500);
        }
    }
}
