<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web-Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <style>
        @font-face {
            font-family: "Public Sans";
            src: url("./font/PublicSans-Regular.woff2") format("woff2"), url("./font/PublicSans-Regular.woff") format("woff"), url("./font/PublicSans-Regular.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Public Sans";
        }

        .container_area {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        header,
        .main_content {
            padding: 20px;
        }

        header .container_area {
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0;
        }

        .main_content {
            margin-top: 30px;
        }

        header .logo img {
            width: auto;
            max-width: 100%;
            object-fit: contain;
            height: 70px;
        }

        .content_left {
            width: 40%;
        }

        .content_right {
            width: calc(60% - 50px);
        }

        .content_right img {
            width: 100%;
            margin-bottom: 30px;
        }

        .content_left form {
            padding: 20px 20px 30px;
            border-radius: 10px;
            background-color: #f8f7fa;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0.1875rem 0.75rem #2f2b3d24;
        }

        form .w-50 {
            width: calc(50% - 10px);
        }

        form .check_num_date,
        form .city_state_zip {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        form label {
            color: #444050;
            font-size: 15px;
            line-height: 20px;
            display: block;
            margin-bottom: 5px;
        }

        form input {
            height: 40px;
            width: 100%;
            padding: 8px 15px;
            font-size: 15px;
            font-weight: 400;
            line-height: 20px;
            font-family: "Public Sans";
            color: #444050;
            background-color: transparent;
            border: 1px solid #d1d0d4;
            border-radius: 5px;
            outline: none !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        form input:hover {
            border-color: #444050 !important;
        }

        form input:focus,
        form input:focus-visible,
        form input:focus-within {
            border-color: #7367f0 !important;
            border-width: 2px !important;
            box-shadow: 0 2px 6px #7367f04d !important;
        }

        form .input_row {
            margin-bottom: 15px;
        }

        form .pay_to {
            padding: 10px;
            border-top: 1px solid #d1d0d4;
            border-bottom: 1px solid #d1d0d4;
        }

        form .pay_from {
            text-align: center;
            padding: 20px 10px 10px;
        }

        form .city_state_zip .w-33 {
            width: calc(33.33% - 13px);
        }

        .submit_btn {
            margin-top: 20px;
        }

        .submit_btn input {
            box-shadow: 0 0.125rem 0.375rem #7367f04d !important;
            color: #fff;
            background-color: #7367f0 !important;
            border: none !important;
            width: auto;
            padding: 8px 30px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.135s ease-in-out;
        }

        .submit_btn input:hover {
            background-color: #685dd8 !important;
        }

        @media (max-width: 1024px) {
            .content_left {
                width: 50%;
            }

            .content_right {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 900px) {
            .main_content .container_area {
                gap: 50px;
                flex-direction: column-reverse;
            }

            .content_left,
            .content_right {
                width: 100%;
            }

            .content_right {
                text-align: center;
            }

            .company_desc {
                text-align: left;
            }

            .content_right img {
                max-width: 600px;
            }
        }

        @media (max-width: 480px) {

            form .check_num_date,
            form .city_state_zip {
                align-items: flex-start;
                flex-direction: column;
                gap: 15px;
            }

            form .w-50,
            form .city_state_zip .w-33 {
                width: 100%;
            }

            address {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    @php
        $logo_img_path = asset($data->Logo);
    @endphp
    <header>
        <div class="container_area">
            <div class="logo">
                <img src="{{ $logo_img_path }}" alt="brand logo img" />
            </div>
            <div class="address">
                <address style="font-style: normal; text-align: right; font-weight: bold">
                    @if (!empty($company->Address2))
                        {{ $company->Address2 }}<br>
                    @endif
                    {{ $company->City }}, {{ $company->State }} {{ $company->Zip }}<br>
                </address>
            </div>
        </div>
    </header>
    <section class="main_content">
        @if (session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif
        <div class="container_area">
            <div class="content_left">
                <form method="POST" action="{{ route('store_web_form_data') }}">
                    @csrf
                    <input type="hidden" id="company_id" name="company_id" value="{{ $company->EntityID }}">
                    <div class="check_num_date input_row">
                        <div class="check_num w-50">
                            <label for="check_number">Check Number</label>
                            <input type="number" id="check_number" name="check_number"
                                value="{{ old('check_number') }}" tabindex="1" />
                        </div>
                        <div class="check_date w-50">
                            <label for="check_date">Check Date</label>
                            <input type="date" id="check_date" name="check_date" value="{{ old('check_date') }}"
                                tabindex="2" />
                        </div>
                    </div>
                    <div class="check_amt input_row">
                        <label for="amount">Amount $</label>
                        <input type="text" id="amount" name="amount" value="{{ old('amount') }}"
                            tabindex="3" />
                    </div>
                    <div class="pay_to">Pay To: {{ $company->Name }}</div>
                    <div class="pay_from">Pay From:</div>
                    <div class="company_name input_row">
                        <label for="name">Name: (First and last or Company Name)</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            tabindex="4" />
                    </div>
                    <div class="address input_row">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                            tabindex="5" />
                    </div>
                    <div class="city_state_zip input_row">
                        <div class="city w-33">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                tabindex="6" />
                        </div>
                        <div class="state w-33">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" value="{{ old('state') }}"
                                tabindex="6" />
                        </div>
                        <div class="zip w-33">
                            <label for="zip">Zip</label>
                            <input type="text" id="zip" name="zip" value="{{ old('zip') }}"
                                tabindex="6" />
                        </div>
                    </div>
                    <div class="bank_name input_row">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                            tabindex="7" />
                    </div>
                    <div class="routing_num input_row">
                        <label for="routing_number">Routing Number</label>
                        <input type="number" id="routing_number" name="routing_number"
                            value="{{ old('routing_number') }}" tabindex="8" />
                    </div>
                    <div class="account_num input_row">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number"
                            value="{{ old('account_number') }}" tabindex="10" />
                    </div>
                    <div class="account_num_verify input_row">
                        <label for="account_number_verify">Account Number (re-verify)</label>
                        <input type="text" id="account_number_verify" name="account_number_verify"
                            value="{{ old('account_number_verify') }}" tabindex="10" />
                    </div>
                    <div class="memo input_row">
                        <label for="Memo">Memo (optional)</label>
                        <input type="text" id="Editbox10" name="Memo" value="{{ old('Memo') }}"
                            tabindex="11" />
                    </div>
                    <div class="submit_btn">
                        <input type="submit" id="Button2" name="Submit" value="Submit" tabindex="12" />
                    </div>
                </form>
            </div>
            <div class="content_right">
                <img src="{{ asset('assets/img/check-sample.png') }}" alt="check format img" />
                <div class="company_desc">
                    @if (!empty($data->page_desc) && $data->page_desc != '<p><br></p>')
                        {!! $data->page_desc !!}
                    @else
                        <strong> What is a Virtual Check? </strong><br><br>
                        <p>
                            Using special software and the information provided, you authorize us to produce a legal
                            check
                            for
                            this transaction only. Our software prints this check that we deposit into our account just
                            as
                            we
                            would one you mailed us. The difference is time. Instead of waiting days for the mail - we
                            can
                            process your order in moments. You will then receive the check we produced with your other
                            cancelled
                            checks in your normal bank statement.

                            The requested information is not confidential as it is located on the front of your check.
                        </p>
                        <br>
                        <strong>How do I do this? Very simple. First, get out your check book.</strong><br><br>
                        <p>
                            Go to the next available check you would normally write. For your records, fill it out to us
                            in
                            the
                            amount due. Next, write VOID across this check. You will provide us with the actual check
                            number
                            -
                            but not the actual check. Next, use the graphic below to locate the information we will
                            need.
                            You
                            can place the numbers in red on your voided check for reference when you complete the Secure
                            Virtual
                            Check
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
    </script>
</body>

</html>
