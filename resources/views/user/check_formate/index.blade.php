<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Design</title>
    <style>
        .c_body {
            font-family: "Arial", sans-serif;
            margin: 50px 0 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: auto;
            background-color: #fff;
            -webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            -moz-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .check {
            width: 100%;
            height: 400px;
            background: white;
            border: 1px solid black;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .address {
            line-height: 1.5;
        }

        .date {
            text-align: right;
            font-size: 12px;
        }

        .payee {
            margin-top: 40px;
            font-size: 12px;
        }

        .payee b {
            margin-left: 10px;
        }

        .amount {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 14px;
        }

        .amount .words {
            flex: 1;
            border-bottom: 1px solid black;
            padding-right: 10px;
        }

        .amount .number {
            width: 200px;
            text-align: right;
            border-bottom: 1px solid black;
        }

        .dollars {
            position: absolute;
            right: 65px;
            top: 160px;
            font-size: 12px;
            font-weight: bold;
        }

        .bank {
            margin-top: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            font-size: 10px;
        }

        .footer .memo {
            flex: 1;
            border-bottom: 1px solid black;
            padding-top: 40px;
        }

        .footer .signature {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            position: relative;
        }

        .signature img {
            max-width: 150px;
            /* Adjust size if necessary */
            height: auto;
            margin-bottom: 5px;
            /* Space between image and line */
        }

        .signature-line {
            width: 200px;
            /* Adjust width */
            border-top: 1px solid black;
            margin-top: 5px;
        }

        .signature-text {
            font-size: 10px;
            margin-top: 2px;
            text-align: center;
        }


        .micr {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-family: "OCR A", monospace;
            font-size: 16px;
        }

        .micr span {
            margin: 0 2px;
        }

        /* @media print {
            .check {
                width: 850px;
                height: 400px;
            }
        } */
    </style>
</head>

<body>
    <div class="check">
        <div class="header">
            <div class="address">
                {{ $data['payor_name'] }}<br>
                {{ $data['address1'] }}<br>
                @if (!empty($data['address1']))
                    {{ $data['address1'] }}<br>
                @endif
                {{ $data['city'] }}, {{ $data['state'] }} {{ $data['zip'] }}
            </div>
            <div class="date">
                <div>{{ $data['check_number'] }}</div>
                <div>DATE: {{ $data['check_date'] }}</div>
            </div>
        </div>

        <div class="payee">
            PAY TO THE ORDER OF <b>{{ $data['payee_name'] }}</b>
        </div>

        <div class="amount">
            <div class="words">{{ $data['amount_word'] }} + 0.00***</div>
            <div class="number">***{{ $data['amount'] }}</div>
        </div>

        <div class="dollars">DOLLARS</div>

        <div class="bank">{{ $data['bank_name'] }}</div>

        <div class="footer">
            <div class="memo">{{ $data['memo'] }}</div>
            <div class="signature">

                @if (!empty($data['signature']))
                    <img src="{{ asset('sign/' . $data['signature']) }}">
                    <div class="signature-line"></div>
                    <div class="signature-text">Authorized Signature</div>
                @else
                    SIGNATURE NOT REQUIRED<br>
                    Your depositor has authorized this payment to payee.<br>
                    Payee to hold you harmless for payment of this document.<br>
                    This document shall be deposited only to the credit of payee.
                @endif
            </div>
        </div>

        <div class="micr">
            <span>{{ $data['routing_number'] }}</span>
            <span>●</span>
            <span>{{ $data['account_number'] }}</span>
            <span>●</span>
            <span>{{ $data['account_number'] }}</span>
        </div>
    </div>

</body>

</html>
