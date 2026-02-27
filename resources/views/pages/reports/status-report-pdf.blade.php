<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $reportType }} Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #111;
        }

        .wrap {
            padding: 14px;
        }

        .title {
            font-size: 13pt;
            font-weight: bold;
            color: #166534;
            margin-bottom: 2px;
        }

        .sub {
            font-size: 8.5pt;
            color: #444;
            margin-bottom: 1px;
        }

        .divider {
            border-top: 1.5pt solid #166534;
            margin: 6px 0 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #166534;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            padding: 4px 5px;
            border: 0.5pt solid #14532d;
        }

        td {
            font-size: 8pt;
            padding: 3px 5px;
            border: 0.5pt solid #d1fae5;
            vertical-align: top;
        }

        .even {
            background-color: #f0fdf4;
        }

        .odd {
            background-color: #ffffff;
        }

        .footer {
            margin-top: 10px;
            font-size: 7.5pt;
            color: #777;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="wrap">

        <div class="title">SureLife Network Global &mdash; {{ $reportType }} Report</div>
        <div class="sub">Branch: <b>{{ $branch ? $branch->BranchName : 'All Branches' }}</b></div>
        @if($dateFrom || $dateTo)
            <div class="sub">
                Period:
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M d, Y') : 'Start' }}
                &ndash;
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M d, Y') : 'Present' }}
            </div>
        @endif
        <div class="sub">Generated: {{ \Carbon\Carbon::now()->format('M d, Y h:i A') }}</div>
        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $index => $row)
                    <tr class="{{ $index % 2 === 0 ? 'odd' : 'even' }}">
                        <td>{{ $index + 1 }}</td>
                        @foreach(array_values($row) as $cell)
                            <td>{{ $cell ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" style="text-align:center;padding:12px;color:#888;">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">Total Records: {{ count($reportData) }}</div>

    </div>
</body>

</html>