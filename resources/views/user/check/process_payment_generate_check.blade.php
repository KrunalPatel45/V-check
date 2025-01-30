@extends('layouts/layoutMaster')

@section('title', 'Generate Checks')

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        .c_body {
            font-family: "Arial", sans-serif;
            margin: 50px 0 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            /* width: 850px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    height: 400px; */
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
            right: 103px;
            top: 148px;
            font-size: 12px;
            font-weight: bold;
        }

        .bank {
            margin-top: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .footer .memo {
            flex: 1;
            border-bottom: 1px solid black;
            padding-top: 40px;
        }

        .footer .signature {
            text-align: right;
            flex: 1;
            line-height: 1.5;
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

        @media print {
            .check {
                width: 850px;
                height: 400px;
            }
        }
    </style>
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
    <script>
        $(document).ready(function() {
            $('#payee').on('change', function() {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if (selectedValue === 'add_other_company') {
                    $('.new-company').removeClass('d-none');
                } else {
                    $('.new-company').addClass('d-none');
                    // var id = $(this).val();
                    $.ajax({
                        url: "{{ route('get_payee', ':id') }}".replace(':id', id),
                        method: 'GET',
                        success: function(response) {
                            $("#c_payee_name").text(response.payee.Name || "XXXXXX");
                        }
                    });
                }
            });

            $('#add-payee-btn').on('click', function(event) {
                event.preventDefault();

                // Collect form data manually
                let formData = {
                    _token: "{{ csrf_token() }}", // Include CSRF token manually
                    name: $('#payee-name').val(),
                    email: $('#payee-email').val(),
                    address1: $('#payee-address1').val(),
                    address2: $('#payee-address2').val(),
                    city: $('#payee-city').val(),
                    state: $('#payee-state').val(),
                    zip: $('#payee-zip').val(),
                    bank_name: $('#payee-bank_name').val(),
                    account_number: $('#payee-account_number').val(),
                    routing_number: $('#payee-routing_number').val(),
                };


                // Clear any previous error messages
                $('.text-danger').remove();

                // Send Ajax request
                $.ajax({
                    url: "{{ route('user.add-payee') }}",
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.errors) {
                            // Display validation errors
                            $.each(response.errors, function(key, value) {
                                console.log('#add-payee #' + key);

                                $('#payee-' + key).closest('.col-md-6').append(
                                    '<span class="text-danger">' + value[0] +
                                    '</span>'
                                );
                            });
                        } else if (response.success) {
                            // Success message
                            let newOption =
                                `<option value="${response.payee.CompanyID}" selected>${response.payee.Name}</option>`;
                            $('#payee').append(newOption).val(response.payee.CompanyID);

                            $("#c_payee_name").text(response.payee.Name || "XXXXXX");

                            $('.new-company').addClass('d-none');
                            $('#add-payee')[0].reset(); // Reset form
                        }
                    },
                    error: function(xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            $('#payor').on('change', function() {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if (selectedValue === 'add_other_payor') {
                    $('.new-payor').removeClass('d-none');
                } else {
                    $('.new-payor').addClass('d-none'); // Hide the form
                    $.ajax({
                        url: "{{ route('get_payor', ':id') }}".replace(':id', id),
                        method: 'GET',
                        success: function(response) {
                            $("#c_name").text(response.payor.Name || "XXXXXX XXX");
                            $("#c_address1").text(response.payor.Address1 || "XXXXXXX XXXX");
                            $("#c_address2").text(response.payor.Address2 || "XXXX XXXX XXXX");
                            $("#c_city").text(response.payor.City || "XXXXXX");
                            $("#c_zip").text(response.payor.Zip || "XXXXXX");
                            $("#c_state").text(response.payor.State || "XXXX");
                            $("#c_routing_number").text(response.payor.RoutingNumber ||
                                "XXXXXXXXX");
                            $("#c_account_number").text(response.payor.AccountNumber ||
                                "XXXXXXXXXX");
                            $("#c_bank_name").text(response.payor.BankName || "XXXXXXX");
                        }
                    });
                }
            });

            $('#add-payor-btn').on('click', function(event) {
                event.preventDefault();

                // Collect form data manually
                let formData = {
                    _token: "{{ csrf_token() }}", // Include CSRF token manually
                    name: $('#add-payor #name').val(),
                    email: $('#add-payor #email').val(),
                    address1: $('#add-payor #address1').val(),
                    address2: $('#add-payor #address2').val(),
                    city: $('#add-payor #city').val(),
                    state: $('#add-payor #state').val(),
                    zip: $('#add-payor #zip').val(),
                    bank_name: $('#add-payor #bank_name').val(),
                    account_number: $('#add-payor #account_number').val(),
                    routing_number: $('#add-payor #routing_number').val(),
                    type: $('#add-payor #type').val(),
                };

                // Clear any previous error messages
                $('.text-danger').remove();

                // Send Ajax request
                $.ajax({
                    url: "{{ route('user.add-payor') }}",
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.errors) {
                            // Display validation errors
                            $.each(response.errors, function(key, value) {
                                $('#add-payor #' + key).closest('.col-md-6').append(
                                    '<span class="text-danger">' + value[0] +
                                    '</span>'
                                );
                            });
                        } else if (response.success) {
                            // Success message
                            let newOption =
                                `<option value="${response.payor.EntityID}" selected>${response.payor.Name}</option>`;
                            $('#payor').append(newOption).val(response.payor.EntityID);

                            $("#c_name").text(response.payor.Name || "XXXXXX XXX");
                            $("#c_address1").text(response.payor.Address1 || "XXXXXXX XXXX");
                            $("#c_address2").text(response.payor.Address2 || "XXXX XXXX XXXX");
                            $("#c_city").text(response.payor.City || "XXXXXX");
                            $("#c_zip").text(response.payor.Zip || "XXXXXX");
                            $("#c_state").text(response.payor.State || "XXXX");
                            $("#c_routing_number").text(response.payor.RoutingNumber ||
                                "XXXXXXXXX");
                            $("#c_account_number").text(response.payor.AccountNumber ||
                                "XXXXXXXXXX");
                            $("#c_bank_name").text(response.payor.BankName || "XXXXXXX");


                            $('.new-payor').addClass('d-none');
                            $('#add-payor')[0].reset(); // Reset form
                        }
                    },
                    error: function(xhr, status, error) {
                        // Log the error for debugging
                        console.error('Error:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                        alert('An error occurred. Check the console for details.');
                    }
                });
            });

            //Print value on check
            $("#check_date").on("change", function() {
                const selectedDate = $(this).val();
                $("#c_check_date").text(selectedDate || "XX-XX-XXXX");
            });

            $("#check_number").on("input", function() {
                const check_number = $(this).val();
                $("#c_check_number").text(check_number || "XXXX");
                $("#c_check_number_1").text(check_number || "XXXX");
            });

            $("#amount").on("input", function() {
                const amount = $(this).val();

                $.ajax({
                    url: "{{ route('amount_word') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        amount: amount,
                    },
                    success: function(response) {
                        $("#c_amount").text(amount || "XXXX.XX");
                        $("#c_amount_word").text(response.word || "XXXXX XXXX XXXX");
                    }
                });
            });

            $("#memo").on("input", function() {
                const memo = $(this).val();
                $("#c_memo").text(memo || "XXXXXXX XXXX XXXX XX");
            });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <!-- Basic Layout -->
        <div class="col-xxl">
            <div class="card mb-6">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Generate Checks</h5>
                    <a href="{{ route('check.process_payment') }}" class="btn btn-primary mr-4"><i
                            class="fa-solid fa-arrow-left"></i>
                        &nbsp;
                        Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('check.process_payment_check_generate') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="check_date">Check Date</label>
                            <div class="col-sm-10">
                                <input type="text" id="check_date" name="check_date" class="form-control dob-picker"
                                    placeholder="YYYY-MM-DD" />
                                @if ($errors->has('check_date'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_date') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="check_number">Check Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="check_number" id="check_number" class="form-control"
                                    value="{{ old('check_number') }}" />
                                @if ($errors->has('check_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="amount">Amount</label>
                            <div class="col-sm-10">
                                <input type="text" name="amount" id="amount" class="form-control"
                                    value="{{ old('amount') }}" />
                                @if ($errors->has('amount'))
                                    <span class="text-danger">
                                        {{ $errors->first('amount') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="payee">Payee</label>
                            <div class="col-sm-10">
                                <select id="payee" name="payee" class="form-control form-select">
                                    <option value="" selected>Select Payee</option>
                                    @foreach ($payees as $payee)
                                        <option value="{{ $payee->CompanyID }}" id="added_company">{{ $payee->Name }}
                                        </option>
                                    @endforeach
                                    <option value="" id="add_other_company" style="font-weight: bold;">Add New Payee
                                    </option>
                                </select>
                                @if ($errors->has('payee'))
                                    <span class="text-danger">
                                        {{ $errors->first('payee') }}
                                    </span>
                                @endif
                                <div class="mb-6 mt-6 new-company d-none">
                                    <div class="card-body" id="add-payee">
                                        {{-- @csrf --}}
                                        <h5>Add Payee</h5>
                                        <div class="row g-6">
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-name">Name</label>
                                                <input type="text" name="name" id="payee-name" class="form-control"
                                                    value="{{ old('name') }}" />
                                                @if ($errors->has('name'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('name') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-email">Email</label>
                                                <input type="text" name="email" id="payee-email" class="form-control"
                                                    value="{{ old('email') }}" />
                                                @if ($errors->has('email'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('email') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-address1">Address 1</label>
                                                <textarea id="payee-address1" name="address1" class="form-control">{{ old('address1') }}</textarea>
                                                @if ($errors->has('address1'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('address1') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-address2">Address 2</label>
                                                <textarea id="payee-address2" name="address2" class="form-control">{{ old('address2') }}</textarea>
                                                @if ($errors->has('address1'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('address2') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-city">City</label>
                                                <input type="text" name="city" id="payee-city" class="form-control"
                                                    value="{{ old('city') }}" />
                                                @if ($errors->has('city'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('city') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-state">State</label>
                                                <input type="text" name="state" id="payee-state"
                                                    class="form-control" value="{{ old('state') }}" />
                                                @if ($errors->has('state'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('state') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-zip">Zip</label>
                                                <input type="text" name="zip" id="payee-zip" class="form-control"
                                                    value="{{ old('zip') }}" />
                                                @if ($errors->has('zip'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('zip') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-bank_name">Bank Name</label>
                                                <input type="text" name="bank_name" id="payee-bank_name"
                                                    class="form-control" value="{{ old('bank_name') }}" />
                                                @if ($errors->has('bank_name'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('bank_name') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-account_number">Account
                                                    Number</label>
                                                <input type="text" name="account_number" id="payee-account_number"
                                                    class="form-control" value="{{ old('account_number') }}" />
                                                @if ($errors->has('account_number'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('account_number') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="payee-routing_number">Routing
                                                    Number</label>
                                                <input type="text" name="routing_number" id="payee-routing_number"
                                                    class="form-control" value="{{ old('routing_number') }}" />
                                                @if ($errors->has('routing_number'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('routing_number') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="pt-6">
                                            <button class="btn btn-primary me-4" id="add-payee-btn">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="payor">Payor</label>
                            <div class="col-sm-10">
                                <select id="payor" name="payor" class="form-control form-select">
                                    <option value="" selected>Select Payor</option>
                                    @foreach ($payors as $payor)
                                        <option value="{{ $payor->EntityID }}">{{ $payor->Name }}</option>
                                    @endforeach
                                    <option value="" id="add_other_payor" style="font-weight: bold;">Add New Payor
                                    </option>
                                </select>
                                @if ($errors->has('payor'))
                                    <span class="text-danger">
                                        {{ $errors->first('payor') }}
                                    </span>
                                @endif
                                <div class="mb-6 mt-6 new-payor d-none">
                                    <div class="card-body" id="add-payor">
                                        <h5>Add Payor</h5>
                                        <div class="row g-6">
                                            <div class="col-md-6">
                                                <label class="form-label" for="name">Name</label>
                                                <input type="text" name="name" id="name" class="form-control"
                                                    value="{{ old('name') }}" />
                                                @if ($errors->has('name'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('name') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="email">Email</label>
                                                <input type="text" name="email" id="email" class="form-control"
                                                    value="{{ old('email') }}" />
                                                @if ($errors->has('email'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('email') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="address1">Address 1</label>
                                                <textarea id="address1" name="address1" class="form-control">{{ old('address1') }}</textarea>
                                                @if ($errors->has('address1'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('address1') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="address1">Address 2</label>
                                                <textarea id="address1" name="address2" class="form-control">{{ old('address2') }}</textarea>
                                                @if ($errors->has('address1'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('address2') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="city">City</label>
                                                <input type="text" name="city" id="city" class="form-control"
                                                    value="{{ old('city') }}" />
                                                @if ($errors->has('city'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('city') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="state">State</label>
                                                <input type="text" name="state" id="state" class="form-control"
                                                    value="{{ old('state') }}" />
                                                @if ($errors->has('state'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('state') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="zip">Zip</label>
                                                <input type="text" name="zip" id="zip" class="form-control"
                                                    value="{{ old('zip') }}" />
                                                @if ($errors->has('zip'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('zip') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="bank_name">Bank Name</label>
                                                <input type="text" name="bank_name" id="bank_name"
                                                    class="form-control" value="{{ old('bank_name') }}" />
                                                @if ($errors->has('bank_name'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('bank_name') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="account_number">Account Number</label>
                                                <input type="text" name="account_number" id="account_number"
                                                    class="form-control" value="{{ old('account_number') }}" />
                                                @if ($errors->has('account_number'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('account_number') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="routing_number">Routing Number</label>
                                                <input type="text" name="routing_number" id="routing_number"
                                                    class="form-control" value="{{ old('routing_number') }}" />
                                                @if ($errors->has('routing_number'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('routing_number') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="type">Tyoe</label>
                                                <select id="type" name="type" class="form-control form-select">
                                                    <option value="Client"
                                                        {{ old('type') == 'Client' ? 'selected' : '' }}>Client
                                                    </option>
                                                    <option value="Vendor"
                                                        {{ old('type') == 'Vendor' ? 'selected' : '' }}>Vendor
                                                    </option>
                                                    <option value="Both" {{ old('type') == 'Both' ? 'selected' : '' }}>
                                                        Both
                                                    </option>
                                                </select>
                                                @if ($errors->has('type'))
                                                    <span class="text-danger">
                                                        {{ $errors->first('type') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="pt-6">
                                            <button id="add-payor-btn" class="btn btn-primary me-4">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="memo">Memo</label>
                            <div class="col-sm-10">
                                <input type="text" name="memo" id="memo" class="form-control"
                                    value="{{ old('memo') }}" />
                                @if ($errors->has('memo'))
                                    <span class="text-danger">
                                        {{ $errors->first('memo') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary">Generate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xxl">
            <div class="card mb-6 c_body">
                <div class="check">
                    <div class="header">
                        <div class="address">
                            <span id="c_name">XXXXXX XXX</span><br>
                            <span id="c_address1">XXXXXXX XXXX</span><br>
                            <span id="c_address2">XXXX XXXX XXXX</span>
                            <span id="c_city">XXXXXX</span>, <span id="c_state">XXXX</span> <span
                                id="c_zip">XXXXXX</span>
                        </div>
                        <div class="date">
                            <div id="c_check_number">XXXX</div>
                            <div id="c_check_date">XX-XX-XXXX</div>
                        </div>
                    </div>

                    <div class="payee">
                        PAY TO THE ORDER OF <b> <span id="c_payee_name">XXXXXX</span></b>
                    </div>

                    <div class="amount">
                        <div class="words"><span id="c_amount_word">XXXXX XXXX XXXX</span>+ 0.00***</div>
                        <div class="number">***<span id="c_amount">XXXX.XX</span></div>
                    </div>

                    <div class="dollars">DOLLARS
                    </div>

                    <div class="bank" id="c_bank_name">XXXXXXX</div>

                    <div class="footer">
                        <div class="memo" id="c_memo">XXXXXXX XXXX XXXX XX</div>
                        <div class="signature">
                            SIGNATURE NOT REQUIRED<br>
                            Your depositor has authorized this payment to payee.<br>
                            Payee to hold you harmless for payment of this document.<br>
                            This document shall be deposited only to the credit of payee.
                        </div>
                    </div>

                    <div class="micr">
                        <span id="c_routing_number">XXXXXXXXX</span>
                        <span>●</span>
                        <span id="c_account_number">XXXXXXXXXX</span>
                        <span>●</span>
                        <span id="c_check_number_1">XXXX</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
