@extends('layouts/layoutMaster')

@section('title', 'Generate Checks')

<!-- Vendor Styles -->
@section('vendor-style')
    <style>
        .kbw-signature {
            width: 250px;
            height: 100px;
            border: none !important;
            margin: 20px;
        }

        #sig canvas {
            width: 250px;
            height: 100px;
            border: 1px solid #555;
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
            $('#payor').on('change', function() {
                id = $(this).val();
                const selectedValue = $(this).find('option:selected').attr(
                    'id');
                if (selectedValue == 'add_other_company') {
                    $('#payee_id').val('');
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
                    $('#payee_h').text('Add');

                    $('#payeeModel').modal('show');
                } else {
                    $.ajax({
                        url: "{{ route('get_payee', ':id') }}".replace(':id', id),
                        method: 'GET',
                        success: function(response) {

                            $('#payor-edit').removeClass('d-none');

                            $('#payee_id').val(response.payee.CompanyID);
                            $('#address').val(response.payee.Address1);
                            $('#city').val(response.payee.City);
                            $('#state').val(response.payee.State);
                            $('#zip').val(response.payee.Zip);
                            $('#account_number').val(response.payee.AccountNumber);
                            $('#routing_number').val(response.payee.RoutingNumber);
                            $('#confirm_account_number').val(response.payee.AccountNumber);

                            $('#payee-name').val(response.payee.Name);
                            $('#payee-email').val(response.payee.Email);
                            $('#payee-address1').val(response.payee.Address1);
                            $('#payee-address2').val(response.payee.Address2);
                            $('#payee-city').val(response.payee.City);
                            $('#payee-state').val(response.payee.State);
                            $('#payee-zip').val(response.payee.Zip);
                            $('#payee-bank_name').val(response.payee.BankName);
                            $('#payee-account_number').val(response.payee.AccountNumber);
                            $('#payee-routing_number').val(response.payee.RoutingNumber);

                            $('#payee_h').text('Add');
                        }
                    });
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

                            if (id) {
                                $('#payor option:selected').text(response.payee.Name);
                            } else {
                                let newOption =
                                    `<option value="${response.payee.CompanyID}" selected>${response.payee.Name}</option>`;
                                $('#payor').append(newOption).val(response.payee.CompanyID);
                            }

                            $('#payee_id').val(response.payee.CompanyID);
                            $('#address').val(response.payee.Address1);
                            $('#city').val(response.payee.City);
                            $('#state').val(response.payee.State);
                            $('#zip').val(response.payee.Zip);
                            $('#account_number').val(response.payee.AccountNumber);
                            $('#routing_number').val(response.payee.RoutingNumber);
                            $('#confirm_account_number').val(response.payee.AccountNumber);

                            $('#payee-name').val(response.payee.Name);
                            $('#payee-email').val(response.payee.Email);
                            $('#payee-address1').val(response.payee.Address1);
                            $('#payee-address2').val(response.payee.Address2);
                            $('#payee-city').val(response.payee.City);
                            $('#payee-state').val(response.payee.State);
                            $('#payee-zip').val(response.payee.Zip);
                            $('#payee-bank_name').val(response.payee.BankName);
                            $('#payee-account_number').val(response.payee.AccountNumber);
                            $('#payee-routing_number').val(response.payee.RoutingNumber);

                            $('#add-payee')[0].reset(); // Reset form // Reset form
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

            $('#payee').on('change', function() {
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
                            $('#payee-edit').removeClass('d-none');

                            $('#payor_id').val(response.payor.EntityID);
                            $('#add-payor #name').val(response.payor.Name);
                            $('#add-payor #email').val(response.payor.Email);
                            $('#add-payor #address1').val(response.payor.Address1);
                            $('#add-payor #address2').val(response.payor.Address2);
                            $('#add-payor #city').val(response.payor.City);
                            $('#add-payor #state').val(response.payor.State);
                            $('#add-payor #zip').val(response.payor.Zip);
                            $('#add-payor #bank_name').val(response.payor.BankName);
                            $('#add-payor #account_number').val(response.payor.AccountNumber);
                            $('#add-payor #routing_number').val(response.payor.RoutingNumber);
                            $('#payee_h').text('Edit');
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
                    type: 'Client',
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
                            if (id) {
                                $('#payee option:selected').text(response.payor.Name);
                            } else {
                                let newOption =
                                    `<option value="${response.payor.EntityID}" selected>${response.payor.Name}</option>`;
                                $('#payee').append(newOption).val(response.payor.EntityID);
                            }

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

            $("#check_number").on("input", function() {
                const check_number = $(this).val();
                $("#verify_check_number").val(check_number);
            });

            $('#payor-edit').on('click', function(e) {
                event.preventDefault();
                $('#payeeModel').modal('show');
                $('#payor_h').text('Edit');
            });
            $('#payee-edit').on('click', function(e) {
                event.preventDefault();
                $('#payorModel').modal('show');
                $('#payee_h').text('Edit');
            });
            $('#is_sign').change(function() {
                if ($(this).is(':checked')) {
                    $('.sing-box').removeClass('d-none'); // Show the signature field
                } else {
                    $('.sing-box').addClass('d-none'); // Hide the signature field
                }
            });
        });
    </script>
    <script type="text/javascript">
        var sig = $('#sig').signature({
            syncField: '#signature64',
            syncFormat: 'PNG'
        });

        var existingSignature = {!! json_encode(!empty($check->DigitalSignature) ? asset('sign/' . $check->DigitalSignature) : '') !!};

        if (existingSignature) {
            var img = new Image();
            img.src = existingSignature;
            img.onload = function() {
                var canvas = $('#sig canvas')[0]; // Get the canvas element
                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                $("#signature64").val(existingSignature); // Ensure the saved signature stays
            };
        }

        $('#clear').click(function(e) {

            e.preventDefault();

            sig.signature('clear');

            $("#signature64").val('');

        });
    </script>
@endsection

@section('content')
    <div class="card mb-6">
        <form action="{{ route('check.send_payment_check_generate') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-header d-flex align-items-center justify-content-between mb-5">
                <h5 class="mb-0">Create Check</h5>
                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                    &nbsp;&nbsp;
                    <a href="{{ route('check.send_payment') }}" class="btn btn-primary mr-4">
                        {{-- &nbsp; --}}
                        Back</a>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" id="id" name="id"
                    value="{{ !empty($check->CheckID) ? $check->CheckID : '' }}">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-8 d-flex align-items-center gap-1">
                                <select id="payor" name="payor" class="form-control">
                                    <option value="" selected>Select Payors</option>
                                    @foreach ($payors as $payor)
                                        <option value="{{ $payor->CompanyID }}" id="added_company"
                                            {{ old('payor', $check->CompanyID ?? '') == $payor->CompanyID ? 'selected' : '' }}>
                                            {{ $payor->Name }}
                                        </option>
                                    @endforeach
                                    <option value="" id="add_other_company" style="font-weight: bold;">Add New Payors
                                    </option>
                                </select>
                                <span id="payor-edit" class="{{ !empty($check->CompanyID) ? '' : 'd-none' }}"><i
                                        class="ti ti-pencil me-1"></i></span>
                            </div>
                        </div>
                        @if ($errors->has('payor'))
                            <span class="text-danger">
                                {{ $errors->first('payor') }}
                            </span>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="row text-end justify-content-end">
                            {{-- <label class="col-sm-12 col-form-label" for="check-number">Check Number:</label> --}}
                            <div class="col-sm-4 p-0">
                                <input type="text" id="check_number" name="check_number" class="form-control"
                                    placeholder="Check Number"
                                    value="{{ !empty($check->CheckNumber) && $check->CheckNumber ? $check->CheckNumber : old('check_number') }}">
                                @if ($errors->has('check_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-6">
                        <div class="row">
                            {{-- <label class="col-sm-12 col-form-label" for="street-address">Your Street Address:</label> --}}
                            <div class="col-sm-8">
                                <input type="text" id="address" name="address" class="form-control"
                                    placeholder="Your Street Address" disabled
                                    value="{{ !empty($old_payor->Address1) && $old_payor->Address1 ? $old_payor->Address1 : old('address') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row text-end justify-content-end">
                            {{-- <label class="col-sm-12 col-form-label" for="check_date">Date:</label> --}}
                            <div class="col-sm-4 p-0">
                                <input type="text" id="check_date" name="check_date" class="dob-picker form-control"
                                    placeholder="MM-DD-YYYY"
                                    value="{{ !empty($check->ExpiryDate) && $check->ExpiryDate ? $check->ExpiryDate : old('check_date') }}" />
                                @if ($errors->has('check_date'))
                                    <span class="text-danger">
                                        {{ $errors->first('check_date') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="city" name="city" class="form-control"
                                            placeholder="City" disabled
                                            value="{{ !empty($old_payor->City) && $old_payor->City ? $old_payor->City : old('city') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="state" name="state" class="form-control"
                                            placeholder="State" disabled
                                            value="{{ !empty($old_payor->State) && $old_payor->State ? $old_payor->State : old('state') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="row">
                                    <div class="col-sm-12" style="padding-right: 0">
                                        <input type="text" id="zip" name="zip" class="form-control"
                                            placeholder="Zip" disabled
                                            value="{{ !empty($old_payor->Zip) && $old_payor->Zip ? $old_payor->Zip : old('zip') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 46px !important;">
                    <div class="col-sm-6">
                        <label class="col-sm-4 col-form-label" for="account-name"
                            style="font-size: 15px;font-weight: bold;">Pay to the
                            Order
                            of:</label>
                        <div class="col-sm-8 d-flex align-items-center gap-1">
                            <select id="payee" name="payee" class="form-control">
                                <option value="" selected>Select Payee</option>
                                @foreach ($payees as $payee)
                                    <option value="{{ $payee->EntityID }}"
                                        {{ old('payee', $check->EntityID ?? '') == $payee->EntityID ? 'selected' : '' }}>
                                        {{ $payee->Name }}
                                    </option>
                                @endforeach
                                <option value="" id="add_other_payor" style="font-weight: bold;">Add New Payee
                                </option>
                            </select>
                            <span id="payee-edit" class="{{ !empty($check->EntityID) ? '' : 'd-none' }}"><i
                                    class="ti ti-pencil me-1"></i></span>
                            @if ($errors->has('payee'))
                                <br>
                                <span class="text-danger">
                                    {{ $errors->first('payee') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <label class="col-sm-4 col-form-label" for="amount"
                                style="font-size: 15px;font-weight: bold;text-align: right;">Amount: $</label>
                            <div class="col-sm-8">
                                <input type="text" id="amount" name="amount" style="font-size: 16px;"
                                    class="form-control"
                                    value="{{ !empty($check->Amount) && $check->Amount ? $check->Amount : old('amount') }}">
                                @if ($errors->has('amount'))
                                    <br>
                                    <span class="text-danger">
                                        {{ $errors->first('amount') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 40px">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-8">
                                <input type="text" id="memo" name="memo" placeholder="Memo"
                                    class="form-control"
                                    value="{{ !empty($check->Memo) && $check->Memo ? $check->Memo : old('memo') }}">
                                @if ($errors->has('memo'))
                                    <span class="text-danger">
                                        {{ $errors->first('memo') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row text-end justify-content-end">
                            <label class="switch switch-square" for="is_sign">
                                <input type="checkbox" class="switch-input" name="is_sign" id="is_sign"
                                    {{ !empty($check->DigitalSignatureRequired) ? 'checked' : '' }} />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                                <span class="switch-label">Sign is Required</span>
                            </label>
                            <div class="row justify-content-end sing-box {{ !empty($check->DigitalSignatureRequired) ? '' : 'd-none' }}"
                                style="margin-top: 10px;">
                                {{-- <label class="" for="">Signature:</label> --}}
                                <div class="col-sm-12" id="sig"></div>
                                <button id="clear" class="btn btn-danger btn-sm" style="max-width: 257px;">Clear
                                    Signature</button>
                                <textarea class="col-sm-12" id="signature64" name="signed" style="display: none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center" style="margin-top: 30px">
                    <div class="col-sm-3">
                        <input type="number" id="routing_number" name="routing_number" class="form-control"
                            placeholder="Routing Number" disabled
                            value="{{ !empty($old_payor->RoutingNumber) && $old_payor->RoutingNumber ? $old_payor->RoutingNumber : old('routing_number') }}">
                    </div>
                    <div class="col-sm-3">
                        <input type="number" id="account_number" name="account_number" class="form-control"
                            placeholder="Account Number" disabled
                            value="{{ !empty($old_payor->AccountNumber) && $old_payor->AccountNumber ? $old_payor->AccountNumber : old('account_number') }}">
                    </div>
                    <div class="col-sm-3">
                        <input type="number" id="verify_check_number" name="verify_check_number"
                            placeholder="Check Number" class="form-control" disabled
                            value="{{ !empty($check->CheckNumber) ? $check->CheckNumber : old('check_number') }}">
                    </div>
                </div>
                <div class="modal fade" id="payorModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1"><span class="payee_h">Add</span>
                                    Payor
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="payor_id" id="payor_id"
                                value="{{ !empty($old_payee->EntityID) ? $old_payee->EntityID : '' }}" />
                            <div class="modal-body">
                                <div class="row g-6" id="add-payor">
                                    <div class="col-md-6">
                                        <label class="form-label" for="name">Name</label>
                                        <input type="text" name="name" id="name" class="form-control"
                                            value="{{ !empty($old_payee->Name) ? $old_payee->Name : old('name') }}" />
                                        @if ($errors->has('name'))
                                            <span class="text-danger">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="text" name="email" id="email" class="form-control"
                                            value="{{ !empty($old_payee->Email) ? $old_payee->Email : old('email') }}" />
                                        @if ($errors->has('email'))
                                            <span class="text-danger">
                                                {{ $errors->first('email') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="address1">Address 1</label>
                                        <textarea id="address1" name="address1" class="form-control">{{ !empty($old_payee->Address1) ? $old_payee->Address1 : old('address1') }}</textarea>
                                        @if ($errors->has('address1'))
                                            <span class="text-danger">
                                                {{ $errors->first('address1') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="address2">Address 2</label>
                                        <textarea id="address2" name="address2" class="form-control">{{ !empty($old_payee->Address2) ? $old_payee->Address2 : old('address2') }}</textarea>
                                        @if ($errors->has('address2'))
                                            <span class="text-danger">
                                                {{ $errors->first('address2') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" name="city" id="city" class="form-control"
                                            value="{{ !empty($old_payee->City) ? $old_payee->City : old('city') }}" />
                                        @if ($errors->has('city'))
                                            <span class="text-danger">
                                                {{ $errors->first('city') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="state">State</label>
                                        <input type="text" name="state" id="state" class="form-control"
                                            value="{{ !empty($old_payee->State) ? $old_payee->State : old('state') }}" />
                                        @if ($errors->has('state'))
                                            <span class="text-danger">
                                                {{ $errors->first('state') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="zip">Zip</label>
                                        <input type="text" name="zip" id="zip" class="form-control"
                                            value="{{ !empty($old_payee->Zip) ? $old_payee->Zip : old('zip') }}" />
                                        @if ($errors->has('zip'))
                                            <span class="text-danger">
                                                {{ $errors->first('zip') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="bank_name">Bank Name</label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control"
                                            value="{{ !empty($old_payee->BankName) ? $old_payee->BankName : old('bank_name') }}" />
                                        @if ($errors->has('bank_name'))
                                            <span class="text-danger">
                                                {{ $errors->first('bank_name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="account_number">Account Number</label>
                                        <input type="text" name="account_number" id="account_number"
                                            class="form-control"
                                            value="{{ !empty($old_payee->AccountNumber) ? $old_payee->AccountNumber : old('account_number') }}" />
                                        @if ($errors->has('account_number'))
                                            <span class="text-danger">
                                                {{ $errors->first('account_number') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="routing_number">Routing Number</label>
                                        <input type="text" name="routing_number" id="routing_number"
                                            class="form-control"
                                            value="{{ !empty($old_payee->RoutingNumber) ? $old_payee->RoutingNumber : old('routing_number') }}" />
                                        @if ($errors->has('routing_number'))
                                            <span class="text-danger">
                                                {{ $errors->first('routing_number') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="type" id="type" value="Client" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button id="add-payor-btn" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="payeeModel" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1"><span class="payor_h">Add</span>
                                    Payor
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="payee_id" id="payee_id"
                                value="{{ !empty($old_payor->CompanyID) ? $old_payor->CompanyID : '' }}">
                            <div class="modal-body">
                                <div class="row g-6" id="add-payee">
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-name">Name</label>
                                        <input type="text" name="name" id="payee-name" class="form-control"
                                            value="{{ !empty($old_payor->Name) ? $old_payor->Name : old('name') }}" />
                                        @if ($errors->has('name'))
                                            <span class="text-danger">
                                                {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-email">Email</label>
                                        <input type="text" name="email" id="payee-email" class="form-control"
                                            value="{{ !empty($old_payor->Email) ? $old_payor->Email : old('email') }}" />
                                        @if ($errors->has('email'))
                                            <span class="text-danger">
                                                {{ $errors->first('email') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-address1">Address 1</label>
                                        <textarea id="payee-address1" name="address1" class="form-control">{{ !empty($old_payor->Address1) ? $old_payor->Address1 : old('address1') }}</textarea>
                                        @if ($errors->has('address1'))
                                            <span class="text-danger">
                                                {{ $errors->first('address1') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-address2">Address 2</label>
                                        <textarea id="payee-address2" name="address2" class="form-control">{{ !empty($old_payor->Address2) ? $old_payor->Address2 : old('address2') }}</textarea>
                                        @if ($errors->has('address1'))
                                            <span class="text-danger">
                                                {{ $errors->first('address2') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-city">City</label>
                                        <input type="text" name="city" id="payee-city" class="form-control"
                                            value="{{ !empty($old_payor->City) ? $old_payor->City : old('city') }}" />
                                        @if ($errors->has('city'))
                                            <span class="text-danger">
                                                {{ $errors->first('city') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-state">State</label>
                                        <input type="text" name="state" id="payee-state" class="form-control"
                                            value="{{ !empty($old_payor->State) ? $old_payor->State : old('state') }}" />
                                        @if ($errors->has('state'))
                                            <span class="text-danger">
                                                {{ $errors->first('state') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-zip">Zip</label>
                                        <input type="text" name="zip" id="payee-zip" class="form-control"
                                            value="{{ !empty($old_payor->Zip) ? $old_payor->Zip : old('zip') }}" />
                                        @if ($errors->has('zip'))
                                            <span class="text-danger">
                                                {{ $errors->first('zip') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="payee-bank_name">Bank Name</label>
                                        <input type="text" name="bank_name" id="payee-bank_name" class="form-control"
                                            value="{{ !empty($old_payor->BankName) ? $old_payor->BankName : old('bank_name') }}" />
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
                                            class="form-control"
                                            value="{{ !empty($old_payor->AccountNumber) ? $old_payor->AccountNumber : old('account_number') }}" />
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
                                            class="form-control"
                                            value="{{ !empty($old_payor->RoutingNumber) ? $old_payor->RoutingNumber : old('routing_number') }}" />
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
                                <button type="button" class="btn btn-label-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button id="add-payee-btn" type="button" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
