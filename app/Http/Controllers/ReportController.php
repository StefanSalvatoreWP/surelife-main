<?php

namespace App\Http\Controllers;

use App\Models\Mcpr;
use App\Models\Staff;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Expenses;
use Nette\Utils\DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ReportController extends Controller
{
    public function searchReportData()
    {

        $branch = Branch::query()->orderBy('branchname', 'asc')->get();
        $mcpr = Mcpr::query()->orderby('year', 'desc')->get();

        $currentYear = date("Y");
        $year_list = range(2011, $currentYear);

        $reportType = ['New Sales', 'Collections', 'Expenses', 'FSA List'];

        return view('pages.reports.report', [
            'year_list' => $year_list,
            'report_types' => $reportType,
            'branch_list' => $branch,
            'mcpr_list' => $mcpr
        ]);
    }

    public function searchDailyReports(Request $request)
    {

        // custom error message
        $messages = [
            'dailyreporttype.not_in' => 'This field is required.',
            'dailybranch.not_in' => 'This field is required.',
            'dailymcpr.not_in' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'dailyreporttype' => 'not_in:0',
            'dailybranch' => 'not_in:0',
            'dailymcpr' => 'not_in:0'
        ], $messages);

        if ($fields->fails()) {
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        // validation has passed
        $validatedData = $fields->validated();

        $dailyReportType = strip_tags($validatedData['dailyreporttype']);
        $dailyBranch = strip_tags($validatedData['dailybranch']);
        $dailyMcpr = strip_tags($validatedData['dailymcpr']);

        $mcprData = Mcpr::query()->where('id', $dailyMcpr)->first();
        $startDate = new DateTime($mcprData->StartingDate);
        $endDate = new DateTime($mcprData->EndingDate);

        // Output CSV
        ob_start();
        $output = fopen("php://output", "w");

        $main_header = ["Daily " . $dailyReportType];
        fputcsv($output, $main_header);

        $date_header = clone $startDate;

        $date_header_range = [];
        array_push($date_header_range, 'Name');

        while ($date_header <= $endDate) {

            array_push($date_header_range, $date_header->format('Y-m-d'));
            $date_header->modify("+1 day");
        }

        $date_header_range[] = "TOTAL";
        fputcsv($output, $date_header_range);

        // fetch results from selected report type
        switch ($dailyReportType) {

            // Daily New Sales
            case "New Sales": {
                // Optimized single query with eager loading
                $ns_clients = Client::with(['recruiter'])
                    ->where('branchid', $dailyBranch)
                    ->whereBetween('dateaccomplished', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orderBy('dateaccomplished', 'asc')
                    ->get();

                // Group by staff to avoid N+1 queries
                $groupedByStaff = $ns_clients->whereNotNull('RecruitedBy')->groupBy('RecruitedBy');
                $previousStaff = collect();

                foreach ($groupedByStaff as $staffId => $staffClients) {
                    $staff = $staffClients->first()->recruiter;

                    if ($staff && !$previousStaff->contains('Id', $staffId)) {
                        $ns_count_data = [];
                        $ns_count_data[] = $staff->LastName . ', ' . $staff->FirstName . ' ' . $staff->MiddleName;

                        $totalSales = 0;
                        $temp_date = clone $startDate;
                        while ($temp_date <= $endDate) {
                            $currentDate = $temp_date->format('Y-m-d');
                            $ns_count = $staffClients
                                ->where('dateaccomplished', $currentDate)
                                ->count();

                            $ns_count_data[] = $ns_count;
                            $temp_date->modify("+1 day");

                            $totalSales += $ns_count;
                        }

                        $previousStaff->push($staff);
                        $ns_count_data[] = $totalSales;
                        fputcsv($output, $ns_count_data);
                    }
                }

                break;
            }
            // Daily Collections
            case "Collections": {
                // Optimized single query with proper indexing
                $coll_data = Payment::select(
                    'tblpayment.amountpaid',
                    'tblpayment.date',
                    'tblstaff.Id as staffid',
                    'tblstaff.LastName',
                    'tblstaff.FirstName',
                    'tblstaff.MiddleName'
                )
                    ->join('tblclient', 'tblpayment.clientid', '=', 'tblclient.id')
                    ->join('tblstaff', 'tblclient.recruitedby', '=', 'tblstaff.id')
                    ->where('tblclient.branchid', $dailyBranch)
                    ->whereBetween('tblpayment.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orderBy('tblstaff.LastName', 'asc')
                    ->orderBy('tblpayment.date', 'asc')
                    ->get();

                // Group by staff to avoid N+1 queries
                $groupedByStaff = $coll_data->groupBy('staffid');
                $previousStaff = collect();

                foreach ($groupedByStaff as $staffId => $staffPayments) {
                    $staff = $staffPayments->first();

                    if ($staff && !$previousStaff->contains('Id', $staffId)) {
                        $previousStaff->push((object) ['Id' => $staffId]);
                        $collections_data = [];
                        $collections_data[] = $staff->LastName . ', ' . $staff->FirstName . ' ' . $staff->MiddleName;

                        $totalCollections = 0;
                        $temp_date = clone $startDate;

                        // Use collection filtering instead of database queries in loop
                        while ($temp_date <= $endDate) {
                            $currentDate = $temp_date->format('Y-m-d');
                            $coll_count = $staffPayments
                                ->where('date', $currentDate)
                                ->sum('amountpaid');

                            $collections_data[] = "P " . number_format($coll_count, 2);
                            $temp_date->modify("+1 day");

                            $totalCollections += $coll_count;
                        }

                        $collections_data[] = "P " . number_format($totalCollections, 2);
                        fputcsv($output, $collections_data);
                    }
                }

                break;
            }
            // Daily Expenses
            case "Expenses": {

                $daily_expenses = Expenses::query()
                    ->leftJoin('tblbranch', 'tblbranch.id', '=', 'tblexpenses.branchid')
                    ->where('branchid', $dailyBranch)
                    ->whereBetween(DB::raw('DATE(datecreated)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orderBy('datecreated', 'asc')
                    ->get();

                $expenses_data = [];
                $totalExpenses = 0;

                if ($daily_expenses->count() > 1) {
                    $expenses_data[] = $daily_expenses->first()->BranchName;

                    $temp_date = clone $startDate;
                    while ($temp_date <= $endDate) {

                        $expenses_count = $daily_expenses->filter(function ($expense) use ($temp_date) {
                            $datecreated = Carbon::parse($expense['DateCreated']);
                            return $datecreated->format('Y-m-d') == $temp_date->format('Y-m-d');
                        })->sum('Amount');

                        $expenses_data[] = "P " . number_format($expenses_count, 2);
                        $temp_date->modify("+1 day");

                        $totalExpenses += $expenses_count;
                    }

                    $expenses_data[] = "P " . number_format($totalExpenses, 2);
                    fputcsv($output, $expenses_data);
                } else {
                    return redirect('/reports')->with('error', 'No data found!');
                }

                break;
            }
            // FSA List
            case "FSA List": {
                $fsaData = $this->getFsaListData($dailyBranch);

                if (empty($fsaData)) {
                    return redirect('/reports')->with('error', 'No active staff found for this branch.');
                }

                // CSV Header for FSA List (Override previous header logic if needed or just append)
                // Since the previous header logic is outside the switch and uses date range, 
                // we might want to completely replace the CSV output logic for FSA List or adapt it.
                // The current code writes $main_header and $date_header_range BEFORE the switch.
                // For FSA List, the date header calculation is irrelevant/wrong.

                // Hack: We need to clear the buffer and restart for FSA List because the structure is different
                ob_clean();
                $output = fopen("php://output", "w");

                fputcsv($output, ["FSA List - " . $fsaData[0]['branchName']]);
                fputcsv($output, ["Name", "Position", "Date Accomplished", "Mobile Number"]);

                foreach ($fsaData as $staff) {
                    fputcsv($output, [
                        $staff['name'],
                        $staff['position'],
                        $staff['dateAccomplished'],
                        $staff['mobileNumber']
                    ]);
                }

                break;
            }
        }

        $csvContent = ob_get_clean();

        $fileName = 'daily_reports_' . strtolower($dailyReportType);
        $file = base_path('/uploads/reports/' . $fileName . '.csv');

        file_put_contents($file, $csvContent);

        return $this->generateCsvResponse($file, $fileName);
    }

    public function searchDailyReportsPDF(Request $request)
    {
        Log::info('=== PDF Export Request Started ===');
        Log::info('Request data:', $request->all());

        // custom error message
        $messages = [
            'dailyreporttype.not_in' => 'This field is required.',
            'dailybranch.not_in' => 'This field is required.',
            'dailymcpr.not_in' => 'This field is required.',
        ];

        // validation fields
        $fields = Validator::make($request->all(), [
            'dailyreporttype' => 'not_in:0',
            'dailybranch' => 'not_in:0',
            'dailymcpr' => 'not_in:0'
        ], $messages);

        Log::info('Validation created, checking for failures...');

        if ($fields->fails()) {
            Log::error('Validation failed:', $fields->errors()->toArray());
            return redirect()
                ->back()
                ->withErrors($fields)
                ->withInput();
        }

        Log::info('Validation passed, processing data...');

        // validation has passed
        $validatedData = $fields->validated();
        Log::info('Validated data:', $validatedData);

        $dailyReportType = strip_tags($validatedData['dailyreporttype']);
        $dailyBranch = strip_tags($validatedData['dailybranch']);
        $dailyMcpr = strip_tags($validatedData['dailymcpr']);

        Log::info('Extracted parameters:', [
            'reportType' => $dailyReportType,
            'branchId' => $dailyBranch,
            'mcprId' => $dailyMcpr
        ]);

        $mcprData = Mcpr::query()->where('id', $dailyMcpr)->first();

        if (!$mcprData) {
            Log::error('MCPR data not found for ID:', ['id' => $dailyMcpr]);
            return redirect('/reports')->with('error', 'MCPR period not found. Please select a valid period.');
        }

        Log::info('MCPR data found:', [
            'id' => $mcprData->id,
            'year' => $mcprData->year,
            'startDate' => $mcprData->StartingDate,
            'endDate' => $mcprData->EndingDate
        ]);

        $startDate = new DateTime($mcprData->StartingDate);
        $endDate = new DateTime($mcprData->EndingDate);

        $branch = Branch::query()->where('Id', $dailyBranch)->first();

        if (!$branch) {
            Log::error('Branch not found for ID:', ['id' => $dailyBranch]);
            return redirect('/reports')->with('error', 'Branch not found. Please select a valid branch.');
        }

        Log::info('Branch found:', ['id' => $branch->Id, 'name' => $branch->BranchName]);

        // Prepare data for PDF
        Log::info('PDF Export - Input parameters:', [
            'reportType' => $dailyReportType,
            'branchId' => $dailyBranch,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);

        $reportData = $this->getReportData($dailyReportType, $dailyBranch, $startDate, $endDate);

        Log::info('PDF Export - Retrieved report data:', [
            'reportData' => $reportData,
            'count' => count($reportData)
        ]);

        // If no data found, check if there's any data for this branch at all
        if (empty($reportData)) {
            Log::warning('No report data found, checking branch data availability...');
            $hasAnyData = $this->checkBranchHasAnyData($dailyReportType, $dailyBranch);

            Log::info('Branch data check result:', ['hasAnyData' => $hasAnyData]);

            if (!$hasAnyData) {
                Log::error('Branch has no data for this report type');
                return redirect('/reports')->with('error', 'No data found for the selected branch and report type. Please try a different branch or report type.');
            } else {
                Log::error('Branch has data but not in selected date range');
                return redirect('/reports')->with('error', 'No data found for the selected date period. The branch has data but outside this date range. Please try a different MCPR period.');
            }
        }

        Log::info('Data found, preparing PDF generation...');

        $pdfData = [
            'reportType' => $dailyReportType,
            'branch' => $branch,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'mcprData' => $mcprData,
            'reportData' => $reportData
        ];

        Log::info('PDF data prepared:', [
            'reportType' => $pdfData['reportType'],
            'branchName' => $pdfData['branch']->BranchName,
            'dataCount' => count($pdfData['reportData'])
        ]);

        // Generate PDF
        try {
            Log::info('Starting PDF generation...');
            if ($dailyReportType == 'FSA List') {
                $pdf = Pdf::loadView('pages.reports.fsa-list-pdf', $pdfData);
            } else {
                $pdf = Pdf::loadView('pages.reports.daily-report-pdf', $pdfData);
            }

            $fileName = 'daily_reports_' . strtolower(str_replace(' ', '_', $dailyReportType)) . '_' . date('Y-m-d') . '.pdf';

            Log::info('PDF generated successfully, returning download...');
            Log::info('=== PDF Export Request Completed ===');

            return $pdf->download($fileName);
        } catch (Exception $e) {
            Log::error('PDF generation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect('/reports')->with('error', 'PDF generation failed: ' . $e->getMessage());
        }
    }

    private function getReportData($reportType, $branchId, $startDate, $endDate)
    {
        switch ($reportType) {
            case "New Sales":
                return $this->getNewSalesData($branchId, $startDate, $endDate);
            case "Collections":
                return $this->getCollectionsData($branchId, $startDate, $endDate);
            case "Expenses":
                return $this->getExpensesData($branchId, $startDate, $endDate);
            case "FSA List":
                return $this->getFsaListData($branchId);
            default:
                return [];
        }
    }

    private function getNewSalesData($branchId, $startDate, $endDate)
    {
        Log::info('getNewSalesData called:', [
            'branchId' => $branchId,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);

        $ns_clients = Client::with(['recruiter'])
            ->where('branchid', $branchId)
            ->whereBetween('dateaccomplished', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('dateaccomplished', 'asc')
            ->get();

        Log::info('New Sales clients found:', ['count' => $ns_clients->count()]);

        // Debug: Check first few clients
        if ($ns_clients->count() > 0) {
            $sampleClients = $ns_clients->take(3);
            foreach ($sampleClients as $client) {
                Log::info('Sample client:', [
                    'id' => $client->id,
                    'name' => $client->lastname . ', ' . $client->firstname,
                    'recruitedby' => $client->recruitedby,
                    'hasRecruiter' => $client->recruiter ? 'YES' : 'NO',
                    'dateaccomplished' => $client->dateaccomplished
                ]);
            }
        }

        // Separate clients with and without recruiters
        $clientsWithRecruiters = $ns_clients->whereNotNull('RecruitedBy')->where('RecruitedBy', '>', 0);
        $clientsWithoutRecruiters = $ns_clients->where(function ($client) {
            return $client->RecruitedBy === null || $client->RecruitedBy == 0;
        });

        Log::info('Client breakdown:', [
            'withRecruiters' => $clientsWithRecruiters->count(),
            'withoutRecruiters' => $clientsWithoutRecruiters->count()
        ]);

        $reportData = [];

        // Process clients with recruiters
        $groupedByStaff = $clientsWithRecruiters->groupBy('RecruitedBy');

        foreach ($groupedByStaff as $staffId => $staffClients) {
            $staff = $staffClients->first()->recruiter;

            // Handle missing staff records
            $staffName = 'Unknown Staff';
            if ($staff) {
                $staffName = $staff->LastName . ', ' . $staff->FirstName . ' ' . ($staff->MiddleName ?? '');
            } else {
                Log::warning('Staff not found for staffId:', ['staffId' => $staffId]);
            }

            $staffData = [
                'name' => $staffName,
                'dailyData' => [],
                'total' => 0
            ];

            $totalSales = 0;
            $temp_date = clone $startDate;
            while ($temp_date <= $endDate) {
                $currentDate = $temp_date->format('Y-m-d');
                $ns_count = $staffClients
                    ->where('dateaccomplished', $currentDate)
                    ->count();

                $staffData['dailyData'][] = $ns_count;
                $temp_date->modify("+1 day");
                $totalSales += $ns_count;
            }

            $staffData['total'] = $totalSales;
            $reportData[] = $staffData;

            Log::info('Staff data added:', [
                'staffName' => $staffData['name'],
                'totalSales' => $totalSales
            ]);
        }

        // Add clients without recruiters as "Unassigned"
        if ($clientsWithoutRecruiters->count() > 0) {
            $unassignedData = [
                'name' => 'Unassigned',
                'dailyData' => [],
                'total' => 0
            ];

            $totalSales = 0;
            $temp_date = clone $startDate;
            while ($temp_date <= $endDate) {
                $currentDate = $temp_date->format('Y-m-d');
                $ns_count = $clientsWithoutRecruiters
                    ->where('dateaccomplished', $currentDate)
                    ->count();

                $unassignedData['dailyData'][] = $ns_count;
                $temp_date->modify("+1 day");
                $totalSales += $ns_count;
            }

            $unassignedData['total'] = $totalSales;
            $reportData[] = $unassignedData;

            Log::info('Unassigned data added:', ['totalSales' => $totalSales]);
        }

        Log::info('getNewSalesData returning:', ['reportDataCount' => count($reportData)]);
        return $reportData;
    }

    private function getCollectionsData($branchId, $startDate, $endDate)
    {
        $coll_data = Payment::select(
            'tblpayment.amountpaid',
            'tblpayment.date',
            'tblstaff.Id as staffid',
            'tblstaff.LastName',
            'tblstaff.FirstName',
            'tblstaff.MiddleName'
        )
            ->join('tblclient', 'tblpayment.clientid', '=', 'tblclient.id')
            ->join('tblstaff', 'tblclient.recruitedby', '=', 'tblstaff.id')
            ->where('tblclient.branchid', $branchId)
            ->whereBetween('tblpayment.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('tblstaff.LastName', 'asc')
            ->orderBy('tblpayment.date', 'asc')
            ->get();

        $groupedByStaff = $coll_data->groupBy('staffid');
        $reportData = [];

        foreach ($groupedByStaff as $staffId => $staffPayments) {
            $staff = $staffPayments->first();

            if ($staff) {
                $staffData = [
                    'name' => $staff->LastName . ', ' . $staff->FirstName . ' ' . $staff->MiddleName,
                    'dailyData' => [],
                    'total' => 0
                ];

                $totalCollections = 0;
                $temp_date = clone $startDate;

                while ($temp_date <= $endDate) {
                    $currentDate = $temp_date->format('Y-m-d');
                    $coll_count = $staffPayments
                        ->where('date', $currentDate)
                        ->sum('amountpaid');

                    $staffData['dailyData'][] = $coll_count;
                    $temp_date->modify("+1 day");
                    $totalCollections += $coll_count;
                }

                $staffData['total'] = $totalCollections;
                $reportData[] = $staffData;
            }
        }

        return $reportData;
    }

    private function getExpensesData($branchId, $startDate, $endDate)
    {
        $daily_expenses = Expenses::query()
            ->leftJoin('tblbranch', 'tblbranch.id', '=', 'tblexpenses.branchid')
            ->where('branchid', $branchId)
            ->whereBetween(DB::raw('DATE(datecreated)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('datecreated', 'asc')
            ->get();

        if ($daily_expenses->count() == 0) {
            return [];
        }

        $branch = $daily_expenses->first()->BranchName;
        $dailyData = [];
        $totalExpenses = 0;

        $temp_date = clone $startDate;
        while ($temp_date <= $endDate) {
            $expenses_count = $daily_expenses->filter(function ($expense) use ($temp_date) {
                $datecreated = Carbon::parse($expense['DateCreated']);
                return $datecreated->format('Y-m-d') == $temp_date->format('Y-m-d');
            })->sum('Amount');

            $dailyData[] = $expenses_count;
            $temp_date->modify("+1 day");
            $totalExpenses += $expenses_count;
        }

        return [
            [
                'name' => $branch,
                'dailyData' => $dailyData,
                'total' => $totalExpenses
            ]
        ];
    }

    private function getFsaListData($branchId)
    {
        $staffMembers = Staff::select(
            'tblstaff.*',
            'tblbranch.BranchName',
            'tblrole.Role as PositionName'
        )
            ->leftJoin('tblbranch', 'tblstaff.BranchId', '=', 'tblbranch.Id')
            ->leftJoin('tblrole', 'tblstaff.Position', '=', 'tblrole.Id')
            ->where('tblstaff.BranchId', $branchId)
            ->where('tblstaff.ActiveStatus', '!=', 'Inactive') // Assuming 'Inactive' is the status for inactive staff
            ->orderBy('tblstaff.LastName', 'asc')
            ->get();

        $data = [];
        foreach ($staffMembers as $staff) {
            $data[] = [
                'name' => $staff->LastName . ', ' . $staff->FirstName . ' ' . $staff->MiddleName,
                'position' => $staff->PositionName,
                'dateAccomplished' => $staff->DateAccomplished ? Carbon::parse($staff->DateAccomplished)->format('M d, Y') : 'N/A',
                'mobileNumber' => $staff->MobileNumber,
                'branchName' => $staff->BranchName,
                'fullData' => $staff // Include full object just in case
            ];
        }

        return $data;
    }

    private function checkBranchHasAnyData($reportType, $branchId)
    {
        switch ($reportType) {
            case "New Sales":
                return Client::where('branchid', $branchId)
                    ->whereNotNull('recruitedby')
                    ->exists();
            case "Collections":
                return Payment::join('tblclient', 'tblpayment.clientid', '=', 'tblclient.id')
                    ->where('tblclient.branchid', $branchId)
                    ->exists();
            case "Expenses":
                return Expenses::where('branchid', $branchId)->exists();
            case "FSA List":
                return Staff::where('BranchId', $branchId)->where('ActiveStatus', '!=', 'Inactive')->exists();
            default:
                return false;
        }
    }

    protected function generateCsvResponse($file, $fileName)
    {
        return response()->download($file, $fileName . '.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=' . $fileName . '.csv',
        ])->deleteFileAfterSend(true);
    }

    public function searchMonthlyReports(Request $request)
    {
        // For now, redirect to daily reports with a message
        return redirect('/reports')->with('info', 'Monthly reports feature is coming soon. Please use daily reports for now.');
    }

    public function searchAnnualReports(Request $request)
    {
        // For now, redirect to daily reports with a message
        return redirect('/reports')->with('info', 'Annual reports feature is coming soon. Please use daily reports for now.');
    }
}
