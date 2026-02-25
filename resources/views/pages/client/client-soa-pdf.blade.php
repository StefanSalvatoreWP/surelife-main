<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Statement of Account</title>
    <style>
        @page {
            margin: 15px;
            size: A4;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }

        .logo-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-right: 15px;
        }

        .company-info {
            text-align: center;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }

        .tagline {
            font-size: 11px;
            color: #64748b;
            font-style: italic;
            margin-top: 3px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #2c3e50;
        }

        .header-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
        }

        .client-info {
            margin-bottom: 25px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            margin-bottom: 6px;
        }

        .info-label {
            font-weight: bold;
            width: 140px;
            color: #374151;
        }

        .info-value {
            flex: 1;
            color: #1f2937;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 25px 0 15px 0;
            color: #2c3e50;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .payment-table th,
        .payment-table td {
            border: 1px solid #e5e7eb;
            padding: 10px 8px;
            text-align: left;
        }

        .payment-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-table td {
            font-size: 11px;
        }

        .payment-table .amount {
            text-align: right;
            font-weight: bold;
            color: #16a34a;
            /* Green color */
        }

        .payment-table .date {
            text-align: center;
            min-width: 80px;
        }

        .payment-table .or-number {
            text-align: center;
            font-family: 'Courier New', monospace;
        }

        .payment-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .payment-table tr:hover {
            background-color: #f3f4f6;
        }

        .summary {
            margin-top: 25px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #0ea5e9;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .summary-label {
            font-weight: bold;
            color: #1e293b;
        }

        .summary-value {
            font-weight: bold;
            color: #16a34a;
            /* Green color */
            font-size: 14px;
        }

        .other-payments {
            margin-top: 20px;
        }

        .other-payments h3 {
            font-size: 13px;
            margin-bottom: 10px;
            color: #6b7280;
        }

        .other-payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            background: white;
            border-radius: 6px;
            overflow: hidden;
        }

        .other-payment-table th,
        .other-payment-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }

        .other-payment-table th {
            background: #fef3c7;
            font-weight: bold;
            color: #92400e;
        }

        .other-payment-table .amount {
            text-align: right;
            font-weight: bold;
            color: #16a34a;
            /* Green color */
        }

        .footer {
            margin-top: 60px;
            border-top: 2px solid #2c3e50;
            padding-top: 30px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #374151;
            margin-bottom: 5px;
            height: 40px;
        }

        .signature-name {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .signature-title {
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div class="watermark">SURELIFE</div>

    <div class="logo-section">
        <div class="company-info">
            <div class="company-name">SURELIFE CARE & SERVICES</div>
            <div class="tagline">Funeral Service & Insurance Management</div>
        </div>
    </div>

    <div class="header">
        <h1>STATEMENT OF ACCOUNT</h1>
        <div class="header-date">Generated on {{ date('F d, Y') }}</div>
    </div>

    <div class="client-info">
        <div class="info-row">
            <div class="info-label">Client Name:</div>
            <div class="info-value">{{ $name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Contract Package:</div>
            <div class="info-value">{{ $client->Package }} - ₱ {{ number_format($total_price, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Address:</div>
            <div class="info-value">{{ $address1 }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">&nbsp;</div>
            <div class="info-value">{{ $address2 }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Contract Number:</div>
            <div class="info-value">{{ $contract_num }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Mode of Payment:</div>
            <div class="info-value">{{ $client->Term }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Amount:</div>
            <div class="info-value">₱ {{ number_format($client->Price, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">Active</div>
        </div>
        <div class="info-row">
            <div class="info-label">Due Date:</div>
            <div class="info-value">{{ date('d', strtotime($due_date)) }} of the month</div>
        </div>
    </div>

    <div class="section-title">Payment History</div>
    <table class="payment-table">
        <thead>
            <tr>

            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                @php
                    $remarks = $payment->Remarks ?? 'Standard';
                @endphp
                @if($payment->VoidStatus == '0' && ($remarks == 'Standard' || $remarks == 'Partial' || $remarks == '' || $remarks == 'Custom'))
                    <tr>
                        <td class="date">{{ date('m/d/Y', strtotime($payment->Date)) }}</td>
                        <td>{{ $payment->officialReceipt->orBatch->SeriesCode ?? 'Not available' }}</td>
                        <td class="or-number">{{ $payment->ORNo }}</td>
                        <td class="amount">₱ {{ number_format($payment->AmountPaid, 2) }}</td>
                        <td>{{ $payment->Installment ?? 'Not available' }}</td>
                        <td>
                            @php
                                if ($remarks == 'Standard' || $remarks == 'Partial' || $remarks == '') {
                                    switch ($payment->PaymentType) {
                                        case 1:
                                            $type = "Cash";
                                            break;
                                        case 2:
                                            $type = "Credit Card";
                                            break;
                                        case 3:
                                            $type = "Cheque";
                                            break;
                                        default:
                                            $type = "Cash";
                                    }
                                    $typeLabel = $type;
                                } else if ($remarks == 'Custom') {
                                    $typeLabel = 'Custom Fee';
                                } else {
                                    $typeLabel = $remarks . ' Fee';
                                }
                            @endphp
                            {{ $typeLabel }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    @if(isset($otherPayments) && count($otherPayments) > 0)
        <div class="other-payments" style="margin-top: 20px;">
            <h3>** Other Payments **</h3>
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">OR Number</th>
                        <th style="border: 1px solid #ddd; padding: 8px;" class="amount">Amount</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($otherPayments as $payment)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">{{ date('m/d/Y', strtotime($payment['date'])) }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 8px;" class="or-number">{{ $payment['or_number'] }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;" class="amount">₱
                                {{ number_format($payment['amount'], 2) }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">{{ $payment['type'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif


    <div class="summary">
        <div class="summary-row">
            <div class="summary-label">Total Payment:</div>
            <div class="summary-value">₱ {{ number_format($total_payment, 2) }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Balance:</div>
            <div class="summary-value">₱ {{ number_format($balance, 2) }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">{{ $cashierName }}</div>
                <div class="signature-title">{{ $cashierBranch }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">ALDIN M. DIAZ</div>
                <div class="signature-title">CEO</div>
            </div>
        </div>
    </div>
</body>

</html>