@extends('layouts/layoutMaster')

@section('title', 'Edit Company')

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
                    <h5 class="mb-0">Edit Company</h5>
                    <a href="{{ route('user.company') }}" class="btn btn-primary mr-4"><i class="fa-solid fa-arrow-left"></i>
                        &nbsp;
                        Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.company.update', ['id' => $company->CompanyID]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $company->Name }}" />
                                @if ($errors->has('name'))
                                    <span class="text-danger">
                                        {{ $errors->first('name') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="name">LOGO</label>
                            <div class="col-sm-10">
                                <input type="file" name="logo" id="logo" class="form-control"
                                    value="{{ old('logo') }}" />
                                @if ($errors->has('logo'))
                                    <span class="text-danger">
                                        {{ $errors->first('logo') }}
                                    </span>
                                @endif

                                <div
                                    style="padding: 14px;margin-top: 20px;border: 1px solid;width: 130px;text-align: center;">
                                    <img src="{{ asset('storage/' . $company->Logo) }}" alt="Company Logo"
                                        style="width:100px;">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="email">Email</label>
                            <div class="col-sm-10">
                                <input type="text" name="email" id="email" class="form-control"
                                    value="{{ $company->Email }}" />
                                @if ($errors->has('email'))
                                    <span class="text-danger">
                                        {{ $errors->first('email') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address1">Address 1</label>
                            <div class="col-sm-10">
                                <textarea id="address1" name="address1" class="form-control">{{ $company->Address1 }}</textarea>
                                @if ($errors->has('address1'))
                                    <span class="text-danger">
                                        {{ $errors->first('address1') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="address1">Address 2</label>
                            <div class="col-sm-10">
                                <textarea id="address1" name="address2" class="form-control">{{ $company->Address2 }}</textarea>
                                @if ($errors->has('address1'))
                                    <span class="text-danger">
                                        {{ $errors->first('address2') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="city">City</label>
                            <div class="col-sm-10">
                                <input type="text" name="city" id="city" class="form-control"
                                    value="{{ $company->City }}" />
                                @if ($errors->has('city'))
                                    <span class="text-danger">
                                        {{ $errors->first('city') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="state">State</label>
                            <div class="col-sm-10">
                                <input type="text" name="state" id="state" class="form-control"
                                    value="{{ $company->State }}" />
                                @if ($errors->has('state'))
                                    <span class="text-danger">
                                        {{ $errors->first('state') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="zip">Zip</label>
                            <div class="col-sm-10">
                                <input type="text" name="zip" id="zip" class="form-control"
                                    value="{{ $company->Zip }}" />
                                @if ($errors->has('zip'))
                                    <span class="text-danger">
                                        {{ $errors->first('zip') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="page_url">Page URL</label>
                            <div class="col-sm-10">
                                <input type="text" name="page_url" id="page_url" class="form-control"
                                    value="{{ $company->PageURL }}" />
                                @if ($errors->has('page_url'))
                                    <span class="text-danger">
                                        {{ $errors->first('page_url') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="page_description">Page Description</label>
                            <div class="col-sm-10">
                                <textarea id="page_description" name="page_description" class="form-control">{{ $company->PageDescription }}</textarea>
                                @if ($errors->has('page_description'))
                                    <span class="text-danger">
                                        {{ $errors->first('page_description') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="bank_name">Bank Name</label>
                            <div class="col-sm-10">
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    value="{{ $company->BankName }}" />
                                @if ($errors->has('bank_name'))
                                    <span class="text-danger">
                                        {{ $errors->first('bank_name') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="account_number">Account Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="account_number" id="account_number" class="form-control"
                                    value="{{ $company->AccountNumber }}" />
                                @if ($errors->has('account_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('account_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="routing_number">Routing Number</label>
                            <div class="col-sm-10">
                                <input type="text" name="routing_number" id="routing_number" class="form-control"
                                    value="{{ $company->RoutingNumber }}" />
                                @if ($errors->has('routing_number'))
                                    <span class="text-danger">
                                        {{ $errors->first('routing_number') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-sm-2 col-form-label" for="status">Status</label>
                            <div class="col-sm-10">
                                <select id="status" name="status" class="form-control form-select">
                                    <option value="active" {{ $company->Status == 'Active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ $company->Status == 'Inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                                @if ($errors->has('status'))
                                    <span class="text-danger">
                                        {{ $errors->first('status') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
