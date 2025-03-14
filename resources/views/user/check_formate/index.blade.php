<!DOCTYPE html>
<html>

<head>
    <title>Cheque Format</title>
    <style>
        @font-face {
            font-family: 'MICRCheckPrixa';
            src: url("{{ asset('storage/fonts/MICRCheckPrixa.eot?#iefix') }}") format('embedded-opentype'),
                url("{{ asset('storage/fonts/MICRCheckPrixa.woff2') }}") format('woff2'),
                url("{{ asset('storage/fonts/MICRCheckPrixa.woff') }}") format('woff'),
                url("{{ asset('storage/font/MICRCheckPrixa.ttf') }}") format('truetype');
        }

        body {
            margin: 20px;
            font-family: Arial, sans-serif;
        }

        td {
            padding: 0;
        }
    </style>
</head>

<body>
    <table border="0" width="100%" cellspacing="0" cellpadding="5"
        style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5;">
        <tr>
            <td style="text-align: left;" colspan="3"> {{ $data['payor_name'] }}<br>
                {{ $data['address1'] }}<br>
                @if (!empty($data['address1']))
                    {{ $data['address1'] }}<br>
                @endif
                {{ $data['city'] }}, {{ $data['state'] }} {{ $data['zip'] }}
            </td>
            <td style="text-align: left;"></td>
            <td style="text-align: right;"><span>{{ $data['check_number'] }}</span><br><span
                    style="font-size: 12px;">DATE: {{ $data['check_date'] }}</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;height: 24px;"></td>
        </tr>
        <tr>
            <td style="padding: 0;font-size: 17=5px;line-height: 21px;width: 89px;color: #000;"><span>PAY TO THE ORDER
                    OF</span></td>
            <td style="width: 10px;"></td>
            <td
                style="border-bottom: 2px solid black; padding: 0; font-size: 12px;padding: 2px 10px;vertical-align: bottom;">
                <span>{{ $data['payee_name'] }}</span>
            </td>
            <td style="width: 30px;text-align: right;padding-right: 5px;font-size: 12px;vertical-align: bottom;">
                <span style="height: 23px;display: block;">$</span>
            </td>
            <td style="width: 130px;vertical-align: bottom;"><span
                    style="text-align:right;border: 2px solid black;padding: 2px 4px;font-size: 12px;height: 20px;display: block;">
                    ***{{ $data['amount'] }}</span></td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5"
        style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5;">
        <tr style="height: 50px;">
            <td
                style="border-bottom: 2px solid black; padding: 0; font-size: 12px;padding: 10px;vertical-align: bottom;">
                {{ $data['amount_word'] }} + 0.00***</td>
            <td style=" padding: 0; font-size: 15px;width: 90px;text-align: right;vertical-align: bottom;">DOLLARS</td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5"
        style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5;">
        <tr style="height: 35px;font-size: 12px;">
            <td>{{ $data['bank_name'] }}</td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5"
        style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5;">
        <tr style="height: 130px;font-size: 12px;">
            <td
                style="border-bottom: 2px solid black; padding: 0; font-size: 15px;padding: 10px;vertical-align: bottom;">
                <span>{{ $data['memo'] }}</span>
            </td>
            <td style="width: 50px;"></td>
            <td style="border-bottom: 2px solid black; padding: 0; font-size: 12px;padding: 10px; width: 300px;">
                @if (!empty($data['signature']))
                    <img width="100px" src="{{ asset('sign/' . $data['signature']) }}" alt="signature img">
                @else
                    SIGNATURE NOT REQUIRED<br>
                    Your depositor has authorized this payment to payee.<br>
                    Payee to hold you harmless for payment of this document.<br>
                    This document shall be deposited only to the credit of payee.
                @endif
            </td>
        </tr>
        <tr>
            <td style="height: 15px;"></td>
            <td style="width: 40px;height: 15px;"></td>
            <td style="height: 15px;"></td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5"
        style="border-collapse: collapse; font-family: 'MICRCheckPrixa';">
        <tr style="height: 50px;font-size: 15px;">
            <td style="font-size: 30px;padding: 10px;text-align: center;  font-family: 'MICRCheckPrixa';">
                "{{ $data['routing_number'] }} :{{ $data['account_number'] }}: {{ $data['check_number'] }}"
            </td>
        </tr>
    </table>
</body>

</html>
