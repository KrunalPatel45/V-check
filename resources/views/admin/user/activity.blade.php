<!DOCTYPE html>
<html>

<head>
    <title>User Report</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .container {
            width: 1000px;
            margin: auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 5px;
        }

        .section {
            margin-top: 25px;
        }

        .section-title {
            background: #222;
            color: #fff;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-danger {
            color: red;
            font-weight: bold;
        }

        .text-success {
            color: green;
            font-weight: bold;
        }

        .no-border td {
            border: none;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {

            html,
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100% !important;
                padding: 0 10px !important;
                /* prevent edge cut */
                box-sizing: border-box;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                /* important */
            }

            th,
            td {
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>User Detail Report</h2>
        <p><strong>Generated On:</strong> {{ date('m/d/Y h:i A') }}</p>

        <!-- ================= USER DETAILS ================= -->
        <div class="section">
            <div class="section-title">User Details</div>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Company</th>
                    <th>Phone</th>
                </tr>
                <tr>
                    <td>{{ $user->UserID }}</td>
                    <td>{{ $user->Email }}</td>
                    <td>{{ $user->FirstName }}</td>
                    <td>{{ $user->LastName }}</td>
                    <td>{{ $user->CompanyName }}</td>
                    <td>{{ $user->PhoneNumber }}</td>
                </tr>
            </table>

            <br>

            <table>
                <tr>
                    <th>Membership Plan</th>
                    <th>Status</th>
                    <th>Trial Start</th>
                    <th>Trial End</th>
                    <th>First Billing Date</th>
                    <th>IP Address</th>
                </tr>
                <tr>
                    <td>{{ $currentSubscription->package->Name }}</td>

                    @if($currentSubscription->Status == 'Canceled')
                        <td class="text-danger">Canceled</td>
                    @elseif($currentSubscription->Status == 'Pending')
                        <td class="text-alert">Pending</td>
                    @elseif($currentSubscription->Status == 'Inactive')
                        <td class="text-danger">Inactive</td>
                    @elseif($currentSubscription->Status == 'Active')
                        <td class="text-success">Active</td>
                    @else
                        <td>-</td>
                    @endif

                    <td>{{ date('m/d/Y', strtotime($currentSubscription->PaymentStartDate)) }}</td>
                    <td>{{ date('m/d/Y', strtotime($currentSubscription->NextRenewalDate)) }}</td>
                    <td>{{ $firstBillingDate }}</td>
                    <td>{{ $IPAddress }}</td>
                </tr>
            </table>
        </div>


        <!-- ================= MEMBERSHIP HISTORY ================= -->
        <div class="section">
            <div class="section-title">Membership History</div>
            <table>
                <tr>
                    <th>Plan</th>
                    <th>IP Address</th>
                    <th>System Generated</th>
                    <th>Created At</th>
                </tr>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td>{{ ($subscription->PackageID == -1) ? 'Free Trial' : $subscription->package?->Name }}</td>
                        <td>{{ $subscription->IPAddress ?? '-' }}</td>
                        <td>{{ $subscription->SystemGenerated ?? '-' }}</td>
                        <td>{{ $subscription?->PaymentStartDate ? \Carbon\Carbon::parse($subscription->PaymentStartDate)->format('m/d/Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No past subscriptions found.</td>
                    </tr>
                @endforelse
            </table>
        </div>


        <!-- ================= PAYMENT INFO ================= -->
        <div class="section">
            <div class="section-title">Payment Information</div>
            <table>
                <tr>
                    <th>Card Holder</th>
                    <th>Card Number</th>
                    <th>Exp</th>
                    <th>Billing Address</th>
                </tr>
                @forelse($cardList as $card)
                
                    <tr>
                        <td>{{ $card['card_holder'] ?? '-' }}</td>
                        <td>{{ 'xxxxxx'.$card['last4'] ?? '-' }}</td>
                        <td>{{ $card['exp_month'] ?? '-' }}/{{ $card['exp_year'] ?? '-' }}</td>
                        <td>{{ $card['address'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No payment information found.</td>
                    </tr>
                @endforelse
            </table>
        </div>

        <!-- ================= BILLING HISTORY ================= -->
        <div class="section">
            <div class="section-title">Billing History</div>
            <table>
                <tr>
                    <th>Billing ID</th>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Charged</th>
                    <th>Error Message</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Date</th>
                </tr>
                <tr>
                    <td>27711</td>
                    <td>Mini</td>
                    <td>$1.99</td>
                    <td class="text-success">True</td>
                    <td>-</td>
                    <td>8/13/2020</td>
                    <td>9/12/2020</td>
                    <td>8/13/2020 3:17 PM</td>
                </tr>
                <tr>
                    <td>29186</td>
                    <td>Silver</td>
                    <td>$9.95</td>
                    <td class="text-danger">False</td>
                    <td>DECLINED</td>
                    <td>10/22/2020</td>
                    <td>11/21/2020</td>
                    <td>10/22/2020 12:41 AM</td>
                </tr>
            </table>
        </div>

    </div>

</body>

</html>