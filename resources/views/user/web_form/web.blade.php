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
            src: url("../../font/PublicSans-Regular.woff2") format("woff2"),
                /* Modern browsers */
                url("../../font/PublicSans-Regular.woff") format("woff"),
                /* Most fallback */
                url("../../font/PublicSans-Regular.ttf") format("truetype");
            /* Legacy fallback */
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
            font-family: "Public Sans", sans-serif;
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
            background-color: #ffffff;
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
            font-family: "Public Sans", sans-serif !important;
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

        .error {
            color: red;
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

        /* For Chrome, Safari, Edge, Opera */
        .no-spinner::-webkit-inner-spin-button,
        .no-spinner::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* For Firefox */
        .no-spinner {
            -moz-appearance: textfield;
        }

        .footer-section {
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px
        }

        .footer-section p {
            width: 100%;
            text-align: center;
            margin: 30px 0 0;
            padding: 20px;
        }

        .footer-section a {
            text-decoration: none;
            font-weight: 600;
            color: #7367f0;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#check_date", {
            dateFormat: "m/d/Y",
        });
    </script>
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
                    @if (!empty($company->Address1))
                        {{ $company->Address1 }}<br>
                    @endif
                    {{ $company->City }}, {{ $company->State }} {{ $company->Zip }}<br>
                    @if(!empty($company->PhoneNumber))
                          Phone Number: {{ $company->PhoneNumber }}
                    @endif
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
                                value="{{ old('check_number') }}" tabindex="1" class="no-spinner" />
                            @error('check_number')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="check_date w-50">
                            <label for="check_date">Check Date</label>
                            <input type="date" id="check_date" name="check_date" value="{{ old('check_date') }}"
                                tabindex="2" />
                            @error('check_date')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="check_amt input_row">
                        <label for="amount">Amount $</label>
                        <input type="text" id="amount" name="amount" value="{{ old('amount') }}"
                            tabindex="3" />
                        @error('amount')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pay_to">Pay To: {{ $company->Name }}</div>

                    <div class="pay_from">Pay From:</div>

                    <div class="company_name input_row">
                        <label for="name">Name: (First and last or Company Name)</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            tabindex="4" />
                        @error('name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="email input_row">
                        <label for="email">Email</label>
                        <input type="text" id="email" name="email" value="{{ old('email') }}"
                            tabindex="13" />
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="address input_row">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                            tabindex="5" />
                        @error('address')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="address input_row">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
                            tabindex="5" />
                        @error('phone_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="city_state_zip input_row">
                        <div class="city w-33">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                tabindex="6" />
                            @error('city')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="state w-33">
                            <label for="state">State</label>
                            <select name="state" id="state" class="form-control">
                                <option value="">-- Select State --</option>
                                @php
                                    $states = [
                                        'Alabama',
                                        'Alaska',
                                        'Arizona',
                                        'Arkansas',
                                        'California',
                                        'Colorado',
                                        'Connecticut',
                                        'Delaware',
                                        'Florida',
                                        'Georgia',
                                        'Hawaii',
                                        'Idaho',
                                        'Illinois',
                                        'Indiana',
                                        'Iowa',
                                        'Kansas',
                                        'Kentucky',
                                        'Louisiana',
                                        'Maine',
                                        'Maryland',
                                        'Massachusetts',
                                        'Michigan',
                                        'Minnesota',
                                        'Mississippi',
                                        'Missouri',
                                        'Montana',
                                        'Nebraska',
                                        'Nevada',
                                        'New Hampshire',
                                        'New Jersey',
                                        'New Mexico',
                                        'New York',
                                        'North Carolina',
                                        'North Dakota',
                                        'Ohio',
                                        'Oklahoma',
                                        'Oregon',
                                        'Pennsylvania',
                                        'Rhode Island',
                                        'South Carolina',
                                        'South Dakota',
                                        'Tennessee',
                                        'Texas',
                                        'Utah',
                                        'Vermont',
                                        'Virginia',
                                        'Washington',
                                        'West Virginia',
                                        'Wisconsin',
                                        'Wyoming',
                                    ];
                                @endphp

                                @foreach ($states as $state)
                                    <option value="{{ $state }}"
                                        {{ old('state') == $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            </select>
                            @error('state')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="zip w-33">
                            <label for="zip">Zip</label>
                            <input type="text" id="zip" name="zip" value="{{ old('zip') }}"
                                tabindex="8" />
                            @error('zip')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="bank_name input_row">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                            tabindex="9" />
                        @error('bank_name')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="routing_num input_row">
                        <label for="routing_number">Routing Number</label>
                        <input type="number" id="routing_number" name="routing_number"
                            value="{{ old('routing_number') }}" tabindex="10" class="no-spinner" maxlength="9"
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0,9);" />
                        @error('routing_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="account_num input_row">
                        <label for="account_number">Account Number</label>
                        <input type="number" id="account_number" name="account_number" class="no-spinner"
                            value="{{ old('account_number') }}" tabindex="11" />
                        @error('account_number')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="account_num_verify input_row">
                        <label for="account_number_verify">Account Number (re-verify)</label>
                        <input type="number" id="account_number_verify" name="account_number_verify"
                            class="no-spinner" value="{{ old('account_number_verify') }}" tabindex="12" />
                        @error('account_number_verify')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="memo input_row">
                        <label for="Memo">Memo (optional)</label>
                        <input type="text" id="Memo" name="Memo" value="{{ old('Memo') }}"
                            tabindex="13" />
                        @error('Memo')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="submit_btn">
                        <input type="submit" id="Button2" name="Submit" value="Submit" tabindex="14" />
                    </div>
                </form>
            </div>
            <div class="content_right">
                <img src="{{ asset('assets/img/check-sample.jpg') }}" alt="check format img" />
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
    <footer class="footer-section">
        <p>Power By <a href="https://echecksystems.com/" target="_blank">Echeck Systems</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
    </script>
</body>

</html>
