<!DOCTYPE html>
<html>

<head>
    <title>Cheque Format</title>
    <style>
        @font-face {
            font-family: "MICRCheckPrixa";
            src: url("./font/MICRCheckPrixa.eot");
            src: url("./font/MICRCheckPrixa.eot?#iefix") format("embedded-opentype"), url("./font/MICRCheckPrixa.woff2") format("woff2"), url("./font/MICRCheckPrixa.woff") format("woff"), url("./font/MICRCheckPrixa.ttf") format("truetype");
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

<body style="margin: 0; font-family: Arial, sans-serif">
    <table style="background-color: #ecedf6; padding: 20px 30px" width="100%">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5">
                    <tr>
                        <td style="text-align: left" colspan="2">
                            <span
                                style="background-color: #fff; width: 100px; display: inline-block; vertical-align: top; padding: 10px; margin-right: 10px">
                                <img src="{{ asset('assets/img/favicon/logo.png') }}" alt="company logo"
                                    style="width: 100px" />
                            </span>
                            <span style="display: inline-block">
                                <span class="company name"
                                    style="font-size: 20px; font-weight: bold">{{ $data['payor_name'] }}</span>
                                <br />
                                @if (!empty($data['address1']))
                                    {{ $data['address1'] }}<br>
                                @endif
                                @if (!empty($data['address2']))
                                    {{ $data['address2'] }}<br>
                                @endif
                                {{ $data['city'] }}, {{ $data['state'] }} {{ $data['zip'] }}
                            </span>
                        </td>
                        <td></td>
                        <td style="text-align: right; width: 350px">
                            <span
                                style="font-size: 20px; font-weight: bold">{{ $data['check_number'] }}</span><br /><br /><span
                                style="font-size: 16px">DATE: <span
                                    style="border-bottom: 1px solid #000; font-size: 22px">{{ $data['check_date'] }}</span>
                                <br />
                                void after 90 days</span>
                        </td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="text-align: right; height: 24px"></td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="padding: 0; width: 105px; color: #000"><span
                                style="font-size: 20px; line-height: 21px">PAY </span><span
                                style="font-size: 16px; line-height: 17px">TO THE ORDER OF</span></td>
                        <td
                            style="border-bottom: 1px solid black; padding: 0; font-size: 22px; padding: 2px 10px 7px 10px; vertical-align: bottom">
                            <span>{{ $data['payee_name'] }}</span>
                        </td>
                        <td style="width: 15px; text-align: right; font-size: 20px; vertical-align: middle"></td>
                        <td
                            style="width: 250px; background-color: #fff; vertical-align: middle; text-align: left; padding: 10px 10px; font-size: 24px; font-weight: bold">
                            $ ***{{ $data['amount'] }}</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5">
                    <tr style="height: 50px">
                        <td
                            style="border-bottom: 1px solid black; padding: 0 0 5px 0; font-size: 22px; vertical-align: bottom">
                            {{ $data['amount_word'] }} + 0.00***</td>
                        <td style="padding: 0; font-size: 17px; width: 65px; text-align: right; vertical-align: bottom">
                            Dollars</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5">
                    <tr style="height: 50px; font-size: 18px">
                        <td style="vertical-align: bottom">{{ $data['bank_name'] }}</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5">
                    <tr style="height: 90px; font-size: 12px">
                        <td
                            style="border-bottom: 1px solid black; padding: 0; font-size: 22px; padding: 10px; vertical-align: bottom; width: 60%">
                            <span>{{ $data['memo'] }}</span>
                        </td>
                        <td style="width: 50px"></td>
                        <td style="font-size: 12px; padding: 10px; background-color: #fff; border-radius: 10px">
                            @if (!empty($data['signature']))
                                <img width="100px" src="{{ asset('sign/' . $data['signature']) }}"
                                    alt="signature img" />
                            @else
                                SIGNATURE NOT REQUIRED<br />
                                Your depositor has authorized this payment to payee.<br />
                                Payee to hold you harmless for payment of this document.<br />
                                This document shall be deposited only to the credit of payee.
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <td style="height: 15px"></td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="5"
                    style="border-collapse: collapse; font-family: 'MICRCheckPrixa'">
                    <tr style="height: 50px; font-size: 15px">
                        <td style="font-size: 30px; padding: 10px; text-align: center; font-family: 'MICRCheckPrixa'">
                            "{{ $data['routing_number'] }} :{{ $data['account_number'] }}:
                            {{ $data['check_number'] }}"</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <td style="height: 15px"></td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <td>
                <span style="width: 100%; display: block">
                    <img src="https://media-hosting.imagekit.io/5fe20a757a824220/Group%2012068.png?Expires=1838371412&Key-Pair-Id=K2ZIVPTIP2VGHC&Signature=x-5Vw-~hPvhy118lrhPdcCBuacshYFKFAIWidFm2W9~gfnmf1G7xBG-m-xtde2EmPDBY-MN3hxnffHPh4WGRXKktjbhoVFwa5d2Iil4vGvlVt~QRAG8h261mnhokrkjQtZfxGHD9NjbAiNBISjEC967YRBmaq0YlRDr8my-lr~PgHuO1btjkQJSn-b5osrPTpnAMEa6Z4rpTJlv8hOOnf-z7ni40deu15pThCqjvM0PbiP4e58~vPWqO3Iw9EDUasOXoV7o2MhyRs4LJNNq~d8uc4GcGKwN0pOpQa8KAPDZ489BbKxGptymvTR8LELM7hmT17Qzc6PAQ45ebB6aYEg__"
                        alt="" width="100%" />
                </span>
            </td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="color: #229973; font-size: 24px; font-weight: bold">Check appears upside down
                            intentionally</td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="font-size: 22px; color: #000; text-align: left">How to use this check</td>
                        <td style="font-size: 18px; color: #000; text-align: right"><span
                                style="font-weight: bold">Need help? </span>Visit eChecks.com or call 1-000-000-0000
                        </td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="height: 10px"></td>
                    </tr>
                </table>
                <table width="100%" style="border-collapse: collapse; border: 2px solid #b2c6cd">
                    <thead style="font-size: 20px; background: #e1eef3">
                        <tr>
                            <th style="border: 2px solid #b2c6cd; padding: 10px 20px; width: 30%; text-align: left">
                                <span style="font-size: 17px">Step 1</span> <br />
                                Print the check
                            </th>
                            <th style="border: 2px solid #b2c6cd; padding: 10px 20px; width: 40%; text-align: left">
                                <span style="font-size: 17px">Step 2</span> <br />
                                Validate it printed correctly
                            </th>
                            <th style="border: 2px solid #b2c6cd; padding: 10px 20px; width: 30%; text-align: left">
                                <span style="font-size: 17px">Step 3</span> <br />
                                Deposit like normal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td
                                style="border: 2px solid #b2c6cd; padding: 20px; width: 30%; vertical-align: top; font-size: 18px">
                                <div style="margin-bottom: 5px">
                                    <span style="width: 20px; height: 15px; display: inline-block">
                                        <img src="https://media-hosting.imagekit.io/6c238744c9f24e07/checkmark-xxl.png?Expires=1838370373&Key-Pair-Id=K2ZIVPTIP2VGHC&Signature=vOWw-4qEm2LxtfLTdzubSoj5cU5nH8-FC0ygCUajKVsuMfs3kqz3QddWRUFcHIjSDFqkb9GpdI7W-ZLtM-FICWrR0niYIK0Cwj4vbsOCEPXFZ6f7g1WKWCL0CdOMBn5apICbSOyqSgrOsdeDHDd5ghvWcnyJsVT7g7Ue7vSEhll5TZAcu3hNF-nHVQhZ38E8CzflWOvZ~PzdIdbSXjAp8CBibnhZ-u6zRgcL0bjNfNauj1QeYcP4Va30T~XUzAXCpo12aB~pz0pa8hLIEIIwBxsHapWzXcm3lr~FxaEJGchenlYiueKAHedN~v~icJgk63FrnYt0XVTJix7qVTknLw__"
                                            alt="check icon" style="width: 15px" />
                                    </span>
                                    <strong>Any printer works</strong>
                                </div>
                                <div style="margin-bottom: 5px">
                                    <span style="width: 20px; height: 15px; display: inline-block">
                                        <img src="https://media-hosting.imagekit.io/6c238744c9f24e07/checkmark-xxl.png?Expires=1838370373&Key-Pair-Id=K2ZIVPTIP2VGHC&Signature=vOWw-4qEm2LxtfLTdzubSoj5cU5nH8-FC0ygCUajKVsuMfs3kqz3QddWRUFcHIjSDFqkb9GpdI7W-ZLtM-FICWrR0niYIK0Cwj4vbsOCEPXFZ6f7g1WKWCL0CdOMBn5apICbSOyqSgrOsdeDHDd5ghvWcnyJsVT7g7Ue7vSEhll5TZAcu3hNF-nHVQhZ38E8CzflWOvZ~PzdIdbSXjAp8CBibnhZ-u6zRgcL0bjNfNauj1QeYcP4Va30T~XUzAXCpo12aB~pz0pa8hLIEIIwBxsHapWzXcm3lr~FxaEJGchenlYiueKAHedN~v~icJgk63FrnYt0XVTJix7qVTknLw__"
                                            alt="check icon" style="width: 15px" />
                                    </span>
                                    <strong> Black or color ink </strong>
                                </div>
                                <div>
                                    <span style="width: 20px; height: 15px; display: inline-block">
                                        <img src="https://media-hosting.imagekit.io/6c238744c9f24e07/checkmark-xxl.png?Expires=1838370373&Key-Pair-Id=K2ZIVPTIP2VGHC&Signature=vOWw-4qEm2LxtfLTdzubSoj5cU5nH8-FC0ygCUajKVsuMfs3kqz3QddWRUFcHIjSDFqkb9GpdI7W-ZLtM-FICWrR0niYIK0Cwj4vbsOCEPXFZ6f7g1WKWCL0CdOMBn5apICbSOyqSgrOsdeDHDd5ghvWcnyJsVT7g7Ue7vSEhll5TZAcu3hNF-nHVQhZ38E8CzflWOvZ~PzdIdbSXjAp8CBibnhZ-u6zRgcL0bjNfNauj1QeYcP4Va30T~XUzAXCpo12aB~pz0pa8hLIEIIwBxsHapWzXcm3lr~FxaEJGchenlYiueKAHedN~v~icJgk63FrnYt0XVTJix7qVTknLw__"
                                            alt="check icon" style="width: 15px" />
                                    </span>
                                    <strong> Basic white paper </strong>
                                </div>
                            </td>
                            <td style="border: 2px solid #b2c6cd; padding: 20px; width: 40%; font-size: 16px">
                                <div>
                                    <span>
                                        <span style="width: 20px; height: 15px; display: inline-block">
                                            <img src="https://media-hosting.imagekit.io/6c238744c9f24e07/checkmark-xxl.png?Expires=1838370373&Key-Pair-Id=K2ZIVPTIP2VGHC&Signature=vOWw-4qEm2LxtfLTdzubSoj5cU5nH8-FC0ygCUajKVsuMfs3kqz3QddWRUFcHIjSDFqkb9GpdI7W-ZLtM-FICWrR0niYIK0Cwj4vbsOCEPXFZ6f7g1WKWCL0CdOMBn5apICbSOyqSgrOsdeDHDd5ghvWcnyJsVT7g7Ue7vSEhll5TZAcu3hNF-nHVQhZ38E8CzflWOvZ~PzdIdbSXjAp8CBibnhZ-u6zRgcL0bjNfNauj1QeYcP4Va30T~XUzAXCpo12aB~pz0pa8hLIEIIwBxsHapWzXcm3lr~FxaEJGchenlYiueKAHedN~v~icJgk63FrnYt0XVTJix7qVTknLw__"
                                                alt="check icon" style="width: 15px" />
                                        </span>
                                        <strong style="font-size: 18px">Correct if bank numbers are :</strong> <br />
                                        <span style="width: 20px; display: inline-block"> </span>
                                        Centered in white space <br />
                                        <span style="width: 20px; display: inline-block"> </span>
                                        Parallel to edge of the page <br />
                                        <span style="width: 20px; display: inline-block"> </span>
                                        Clearly printed in dark blank ink
                                    </span>
                                </div>
                                <br />
                                <div>
                                    <span>
                                        <span style="width: 20px; height: 15px; display: inline-block">
                                            <img src="https://media-hosting.imagekit.io/881669345e5a4e3a/x-mark-xxl.png?Expires=1838370373&Key-Pair-Id=K2ZIVPTIP2VGHC&Signature=g8f8YRDBZ3t-vJ38fcSbkK6tKMZ0ipYAKsta7Q~VU9ChBK7c2PSsBSwKbYR1GzIskfAgFa0TGvFjvpML9exf5VdilPxzqLC331yGO6-h9par6qpIlfudRF8UHIPJrzL7dt2mnPVp~3Xn4jXBQYEb3HnDDtwOCjsMPCeJGiTY9FUz-Rd54P-B-CIG1vfPO0mYVMgAHBRtwrfvRUW6Hw3b~fzeMNgrcgWjDmMn31LzsMddvQVkwV7d4MmGEzGvtaJ2lNcAL3svNbJp1pf4KuBCCVobQ-s7knrQ9qaBaa-kqSnbeScTvS0jPzbEScm312aPm5OEbKWBLR5Gf2b5EjIAhA__"
                                                alt="x icon" style="width: 15px" />
                                        </span>
                                        <strong style="font-size: 18px">Reprint if bank numbers are :</strong> <br />
                                        <span style="width: 20px; display: inline-block"> </span>
                                        Cut off, skewed , or off-center <br />
                                        <span style="width: 20px; display: inline-block"> </span>
                                        Smudged or wrinkled<br />
                                        <span style="width: 20px; display: inline-block"> </span>
                                        Too light to read
                                    </span>
                                </div>
                            </td>
                            <td
                                style="font-size: 18px; border: 2px solid #b2c6cd; padding: 20px; width: 30%; vertical-align: top">
                                <div>
                                    <ol style="margin: 0">
                                        <li style="margin-bottom: 5px">
                                            <strong> Cut on the dotted line above </strong>
                                        </li>
                                        <li style="margin-bottom: 5px">
                                            <strong>Endorse the back</strong>
                                        </li>
                                        <li>
                                            <strong>Deposit like normal </strong><br />
                                            <span style="font-size: 16px">
                                                In-person at a bank or credit union Using an ATM <br />
                                                Via smartphone mobile deposit <br />
                                                With an office check scanner <br />
                                            </span>
                                        </li>
                                    </ol>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="padding: 20px" colspan="3">
                                <strong style="font-size: 20px"> Does your financial institution have questions about
                                    this check? </strong>
                                <br />
                                <ul style="font-size: 18px">
                                    <li>This check was printed from an authorized check record. It is not a Check 21
                                        Image Replacement Document.</li>
                                    <li>
                                        To confirm this check was issued by the account holder and details (pay to,
                                        amount, routing/account number) remail unmodified, the item's authenticity can
                                        be verified using the Deluxe Inc. Check Verification service at
                                        <a href="https://echecks.com/verify"
                                            style="color: #4f7edb; text-decoration: none"
                                            target="_blank">https://echecks.com/verify.</a>
                                    </li>
                                </ul>
                                <strong style="font-size: 24px; font-weight: bold"> Questions? Visit
                                    <span>eChecks.com</span> or call 1-000-000-0000 </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="height: 30px"></td>
                    </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="5">
                    <tr>
                        <td style="width: 50%">
                            <span style="font-size: 30px">For your records</span>
                            <br />
                            <br />
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Issued date: </strong>
                                <span>{{ $data['check_date'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Check number: </strong>
                                <span>{{ $data['check_number'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>From: </strong>
                                <span>{{ $data['payor_name'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Amount: </strong>
                                <span>${{ $data['amount'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Payable to: </strong>
                                <span>{{ $data['payee_name'] }}</span>
                            </div>
                            <div style="margin-bottom: 5px; font-size: 20px">
                                <strong>Delivery email: </strong>
                                <span>{{ $data['email'] }}</span>
                            </div>
                            <div style="font-size: 20px">
                                <strong>Memo: </strong>
                                <span>{{ $data['memo'] }}</span>
                            </div>
                        </td>
                        <td style="width: 50%; vertical-align: top; padding-left: 20px">
                            <span style="font-size: 20px; line-height: 1.3; width: 85%; display: inline-block"> Are you
                                a business? To save time, money, and resources, make payments using Deluxe Payment
                                Exchange. Call 1-000-000-0000 to get started today! </span>
                            <br />
                            <br />
                            <div style="text-align: right">
                                <img src="{{ asset('assets/img/favicon/logo.png') }}" alt="company logo"
                                    style="width: 200px" />
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
