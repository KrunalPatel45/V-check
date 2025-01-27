@extends('layouts/layoutMaster')

@section('title', 'Generate Checks')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-layouts.js'])
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
                    <form action="{{ route('check.send_payment_check_generate') }}" method="POST"
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
                                        <option value="{{ $payee->EntityID }}">{{ $payee->Name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('payee'))
                                    <span class="text-danger">
                                        {{ $errors->first('payee') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="payor">Payor</label>
                            <div class="col-sm-10">
                                <select id="payor" name="payor" class="form-control form-select">
                                    <option value="" selected>Select Payor</option>
                                    @foreach ($payors as $payor)
                                        <option value="{{ $payor->CompanyID }}">{{ $payor->Name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('payor'))
                                    <span class="text-danger">
                                        {{ $errors->first('payor') }}
                                    </span>
                                @endif
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
    </div>
@endsection
@section('page-script')
    <script>
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        const signatureInput = document.getElementById('signature');
        const clearButton = document.getElementById('clear-btn');

        // Save the signature data as an image in the hidden input
        // document.getElementById('signature-form').addEventListener('submit', function(e) {
        //     if (!signaturePad.isEmpty()) {
        //         signatureInput.value = signaturePad.toDataURL(); // Converts signature to a Base64 image
        //     } else {
        //         alert('Please provide a signature first.');
        //         e.preventDefault();
        //     }
        // });

        // Clear the signature pad
        clearButton.addEventListener('click', () => {
            signaturePad.clear();
        });
    </script>
@endsection
