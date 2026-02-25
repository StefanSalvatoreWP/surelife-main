<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily {{ $reportType }} Report</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        
        .table-container {
            margin-bottom: 25px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .report-table th {
            background-color: #4CAF50;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #333;
            font-size: 9px;
            white-space: nowrap;
        }
        
        .report-table td {
            padding: 5px 3px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 9px;
            white-space: nowrap;
        }
        
        .report-table td.name {
            text-align: left;
            font-weight: bold;
            background-color: #f9f9f9;
            min-width: 150px;
        }
        
        .report-table td.total {
            font-weight: bold;
            background-color: #e8f5e8;
            min-width: 60px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 14px;
            color: #333;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 10px;
            color: #666;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 3px;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        
        .info-table td.label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 80px;
        }
        
        .report-table {
            width: auto;
            min-width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .report-table th {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            font-weight: bold;
            border: 1px solid #333;
            white-space: nowrap;
        }
        
        .report-table td {
            text-align: center;
            border: 1px solid #ddd;
            white-space: nowrap;
        }
        
        .report-table td.total {
            font-weight: bold;
            background-color: #e8f5e8;
            min-width: 45px;
        }
        
        .date-header {
            background-color: #2196F3 !important;
            color: white !important;
        }
        
        .total-header {
            background-color: #FF9800 !important;
            color: white !important;
        }
        
        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 20px;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #999;
            border-top: 1px solid #ddd;
            padding: 3px 0;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .page-info {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-bottom: 8px;
            font-style: italic;
        }
        
        @media print {
            .footer {
                position: fixed;
                bottom: 0;
            }
            .page-break {
                page-break-before: always;
            }
            .table-container {
                overflow: visible;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily {{ $reportType }} Report</h1>
        <p><strong>Surelife Care & Services</strong></p>
    </div>
    
    <table class="info-table">
        <tr>
            <td class="label">Branch:</td>
            <td>{{ $branch->BranchName ?? 'N/A' }}</td>
            <td class="label">Period:</td>
            <td>{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td class="label">MCPR Period:</td>
            <td>{{ $mcprData->year ?? 'N/A' }}</td>
            <td class="label">Generated:</td>
            <td>{{ now()->format('M d, Y h:i A') }}</td>
        </tr>
    </table>
    
    @if(count($reportData) > 0)
        @php
            $maxDateColumns = 20;
            $totalDates = 0;
            $tempDate = clone $startDate;
            while($tempDate <= $endDate) {
                $totalDates++;
                $tempDate->modify('+1 day');
            }
            
            $totalDatePages = ceil($totalDates / $maxDateColumns);
            $currentDatePage = 1;
        @endphp
        
        @for($datePage = 0; $datePage < $totalDatePages; $datePage++)
            @if($datePage > 0)
                <div class="page-break"></div>
            @endif
            
            <div class="table-container">
                @if($totalDatePages > 1)
                    <div class="page-info">
                        Date Range {{ $datePage + 1 }} of {{ $totalDatePages }}
                    </div>
                @endif
                
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            @php
                                $tempDate = clone $startDate;
                                $dateCount = 0;
                                $startDateOffset = clone $startDate;
                                $startDateOffset->modify('+' . ($datePage * $maxDateColumns) . ' days');
                                $endDateOffset = clone $startDateOffset;
                                $endDateOffset->modify('+' . ($maxDateColumns - 1) . ' days');
                                if($endDateOffset > $endDate) {
                                    $endDateOffset = clone $endDate;
                                }
                            @endphp
                            @while($tempDate <= $endDateOffset)
                                @if($dateCount >= ($datePage * $maxDateColumns))
                                    <th class="date-header">{{ $tempDate->format('m/d') }}</th>
                                @endif
                                @php
                                    $tempDate->modify('+1 day');
                                    $dateCount++;
                                @endphp
                            @endwhile
                            <th class="total-header">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $data)
                            <tr>
                                <td class="name">{{ $data['name'] }}</td>
                                @php
                                    $tempDate = clone $startDate;
                                    $dailyIndex = 0;
                                @endphp
                                @while($tempDate <= $endDateOffset)
                                    @if($dailyIndex >= ($datePage * $maxDateColumns))
                                        <td>
                                            @if(isset($data['dailyData'][$dailyIndex]))
                                                @if($reportType == 'Collections')
                                                    P {{ number_format($data['dailyData'][$dailyIndex], 2) }}
                                                @else
                                                    {{ $data['dailyData'][$dailyIndex] }}
                                                @endif
                                            @else
                                                0
                                            @endif
                                        </td>
                                    @endif
                                    @php
                                        $tempDate->modify('+1 day');
                                        $dailyIndex++;
                                    @endphp
                                @endwhile
                                <td class="total">
                                    @php
                                        $pageTotal = 0;
                                        $tempDate = clone $startDateOffset;
                                        $dailyIndex = ($datePage * $maxDateColumns);
                                        while($tempDate <= $endDateOffset && isset($data['dailyData'][$dailyIndex])) {
                                            $pageTotal += $data['dailyData'][$dailyIndex];
                                            $tempDate->modify('+1 day');
                                            $dailyIndex++;
                                        }
                                    @endphp
                                    @if($reportType == 'Collections')
                                        P {{ number_format($pageTotal, 2) }}
                                    @else
                                        {{ $pageTotal }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endfor
        
        @if($totalDates > 20)
            <div style="margin-top: 20px; font-size: 10px; color: #666;">
                <em>Note: This report spans {{ $totalDates }} days and has been split across {{ $totalDatePages }} pages for better readability.</em>
            </div>
        @endif
    @else
        <div class="no-data">
            No data found for the selected criteria.
        </div>
    @endif
    
    <div class="footer">
        Generated by Surelife Admin Panel on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
