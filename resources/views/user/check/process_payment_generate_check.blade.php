@extends('layouts/layoutMaster')

@section('title', 'Generate Checks')

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .form-container {
            background: #fffacd;
            border: 2px solid green;
            /* max-width: 90%; */
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-title {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: green;
            font-weight: bold;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        label {
            font-weight: bold;
            flex: 1;
            min-width: 120px;
        }

        input,
        select {
            /* flex: 2; */
            /* padding: 8px; */
            border: 1px solid #ccc;
            border-radius: 4px;
            min-width: 200px;
        }

        .pay-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            /* border: 1px solid #ccc; */
            /* border-radius: 4px; */
            font-size: 1.2rem;
            margin: 20px 0;
        }

        .pay-amount {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        button {
            background-color: green;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: darkgreen;
        }

        .fileds {
            display: flex;
            flex-direction: column;
        }

        .fileds-row {
            gap: 10px;
        }

        .payor-filed {
            min-width: 350px !important;
        }

        .address {
            justify-content: normal !important;
            gap: 6px !important;
        }

        .address .fileds input {
            min-width: 100px;
            width: 100px;
        }

        .dob-picker {
            background: #ffffff !important;
            min-width: 200px !important;
        }

        .f-basic {
            flex-basis: 60% !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .j-center {
            justify-content: center !important;
            margin: 20px 0 !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            label {
                text-align: left;
            }

            input,
            select {
                flex: 1;
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
    @vite(['resources/assets/js/ui-modals.js'])
    <script>
        $(document).ready(function() {
            $('#payee').on('change', function() {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if (selectedValue == 'add_other_company') {
                    $('#payeeModel').modal('show');
                }
            });

            $('#add-payee-btn').on('click', function(event) {
                event.preventDefault();
                var id = $('#payee_id').val();

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
                    id: id
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
                            $('#payeeModel').modal('hide');
                            // Success message
                            let newOption =
                                `<option value="${response.payee.CompanyID}" selected>${response.payee.Name}</option>`;
                            $('#payee').append(newOption).val(response.payee.CompanyID);

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
                    $('#payorModel').modal('show');
                    $('#payor_id').val('');
                    $('#add-payor #name').val('');
                    $('#add-payor #email').val('');
                    $('#add-payor #address1').val('');
                    $('#add-payor #address2').val('');
                    $('#add-payor #city').val('');
                    $('#add-payor #state').val('');
                    $('#add-payor #zip').val('');
                    $('#add-payor #bank_name').val('');
                    $('#add-payor #account_number').val('');
                    $('#add-payor #routing_number').val('');
                    $('#payor_h').text('Add');
                } else {
                    $.ajax({
                        url: "{{ route('get_payor', ':id') }}".replace(':id', id),
                        method: 'GET',
                        success: function(response) {
                            $('#payor_id').val(response.payor.EntityID);
                            $('#address').val(response.payor.Address1);
                            $('#city').val(response.payor.City);
                            $('#state').val(response.payor.State);
                            $('#zip').val(response.payor.Zip);
                            $('#account_number').val(response.payor.AccountNumber);
                            $('#routing_number').val(response.payor.RoutingNumber);
                            $('#confirm_account_number').val(response.payor.AccountNumber);
                        }
                    });
                }
            });

            $('#add-payor-btn').on('click', function(event) {
                event.preventDefault();
                var id = $('#payor_id').val();

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
                    type: 'Vendor',
                    id: id
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

                            $('#payorModel').modal('hide');
                            // Success message
                            let newOption =
                                `<option value="${response.payor.EntityID}" selected>${response.payor.Name}</option>`;
                            $('#payor').append(newOption).val(response.payor.EntityID);

                            $('#payor_id').val(response.payor.EntityID);
                            $('#address').val(response.payor.Address1);
                            $('#city').val(response.payor.City);
                            $('#state').val(response.payor.State);
                            $('#zip').val(response.payor.Zip);
                            $('#account_number').val(response.payor.AccountNumber);
                            $('#routing_number').val(response.payor.RoutingNumber);
                            $('#confirm_account_number').val(response.payor.AccountNumber);
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
    <div class="form-container">
        <form action="{{ route('check.process_payment_check_generate') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <div class="fileds">
                    <label for="account-name">Account Holder's Name:</label>
                    <select id="payor" name="payor" class="payor-filed">
                        <option value="" selected>Select Payors</option>
                        @foreach ($payors as $payor)
                            <option value="{{ $payor->EntityID }}" id="added_company">{{ $payor->Name }}
                            </option>
                        @endforeach
                        <option value="" id="add_other_payor" style="font-weight: bold;">Add New Payors
                        </option>
                    </select>
                    @if ($errors->has('payor'))
                        <span class="text-danger">
                            {{ $errors->first('payor') }}
                        </span>
                    @endif
                </div>
                <div class="fileds">
                    <label for="check-number">Check Number:</label>
                    <input type="text" id="check_number" name="check_number">
                    @if ($errors->has('check_number'))
                        <span class="text-danger">
                            {{ $errors->first('check_number') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="fileds">
                    <label for="street-address">Your Street Address:</label>
                    <input type="text" id="address" name="address" class="payor-filed">
                </div>
                <div class="fileds">
                    <label for="phone">Date:</label>
                    <input type="text" id="check_date" name="check_date" class="dob-picker" placeholder="MM-DD-YYYY" />
                    @if ($errors->has('check_date'))
                        <span class="text-danger">
                            {{ $errors->first('check_date') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-row address">
                <div class="fileds">
                    <label for="city">Your City:</label>
                    <input type="text" id="city" name="city">
                </div>
                <div class="fileds">
                    <label for="state">Your State:</label>
                    <input type="text" id="state" name="state">
                </div>
                <div class="fileds">
                    <label for="zip">Your Zip:</label>
                    <input type="text" id="zip" name="zip">
                </div>
            </div>

            <div class="pay-section">
                <div class="fileds-row f-basic">
                    <label for="account-name">Pay to the Order of:</label>
                    <select id="payee" name="payee" class="payor-filed">
                        <option value="" selected>Select Payee</option>
                        @foreach ($payees as $payee)
                            <option value="{{ $payee->CompanyID }}" id="added_company">{{ $payee->Name }}
                            </option>
                        @endforeach
                        <option value="" id="add_other_company" style="font-weight: bold;">Add New Payee
                        </option>
                    </select>
                    @if ($errors->has('payee'))
                        <br>
                        <span class="text-danger">
                            {{ $errors->first('payee') }}
                        </span>
                    @endif
                </div>
                <div class="fileds-row">
                    <label for="amount" class="text-right">Amount: $</label>
                    <input type="text" id="amount" name="amount">
                    @if ($errors->has('amount'))
                        <br>
                        <span class="text-danger">
                            {{ $errors->first('amount') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-row">
                <div class="fileds-row">
                    <label for="memo" style="min-width: 0;">Memo:</label>
                    <input type="text" id="memo" name="memo">
                    @if ($errors->has('memo'))
                        <span class="text-danger">
                            {{ $errors->first('memo') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-row j-center">
                <div class="fileds">
                    <label for="routing-number" class="text-center">Routing #:</label>
                    <input type="text" id="routing_number" name="routing_number">
                </div>
                <div class="fileds">
                    <label for="checking-number" class="text-center">Checking Account #:</label>
                    <input type="text" id="account_number" name="account_number">
                </div>
                <div class="fileds">
                    <label for="confirm_account_number" class="text-center">Confirm Account #:</label>
                    <input type="text" id="confirm_account_number" name="confirm_account_number">
                </div>
            </div>

            <div class="form-row">
                <button type="submit">Generate</button>
            </div>
        </form>

        <div class="modal fade" id="payorModel" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Add Payor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-6" id="add-payor">
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
                                <label class="form-label" for="address2">Address 2</label>
                                <textarea id="address2" name="address2" class="form-control">{{ old('address2') }}</textarea>
                                @if ($errors->has('address2'))
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
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    value="{{ old('bank_name') }}" />
                                @if ($errors->has('bank_name'))
                                    <span class="text-danger">
                                        {{ $errors->first('bank_name') }}
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="account_number">Account Number</label>
                                <input type="text" name="account_number" id="account_number" class="form-control"
                                    value="{{ old('account_number') }}" />
                                @if ($errors->has('account_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('account_number') }}
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="routing_number">Routing Number</label>
                                <input type="text" name="routing_number" id="routing_number" class="form-control"
                                    value="{{ old('routing_number') }}" />
                                @if ($errors->has('routing_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('routing_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="type" id="type" value="Vendor" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="add-payor-btn" type="button" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="payeeModel" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Add Payor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-6" id="add-payee">
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
                                <input type="text" name="state" id="payee-state" class="form-control"
                                    value="{{ old('state') }}" />
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
                                <input type="text" name="bank_name" id="payee-bank_name" class="form-control"
                                    value="{{ old('bank_name') }}" />
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
                        <input type="hidden" name="type" id="type" value="Vendor" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="add-payee-btn" type="button" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>s
    </div>
@endsection
