@extends('layouts/layoutMaster')

@section('title', 'Account settings - Pages')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection
<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/pages-pricing.js', 'resources/assets/js/pages-account-settings-billing.js', 'resources/assets/js/app-invoice-list.js', 'resources/assets/js/modal-edit-cc.js', 'resources/assets/js/ui-modals.js'])

    <script>
        $(document).ready(function() {
            $('#invoice_data').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_invoice') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'PaymentHistoryID', // Hidden ID column for sorting
                        name: 'PaymentHistoryID',
                        visible: false // Hides the ID column
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'PaymentStatus',
                        name: 'PaymentStatus',
                    },
                    {
                        data: 'PaymentAmount',
                        name: 'PaymentAmount',
                    },
                    {
                        data: 'PaymentDate',
                        name: 'PaymentDate'
                    },
                ]
            });
        });
    </script>
@endsection

@section('content')
    @php
        $progress = $package_id != '-1' ? ($package_data['remainingDays'] * 100) / $package_data['total_days'] : 0;
    @endphp
    <div class="row">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="col-md-12">
            <div class="card mb-6">
                <!-- Current Plan -->
                <h5 class="card-header">Current Plan</h5>
                <div class="card-body">
                    <div class="row row-gap-6">
                        <div class="col-md-6 mb-1">
                            <div class="mb-6">
                                <h6 class="mb-1">Your Current Plan is
                                    {{ $package_id == '-1' ? 'Trial' : $package_data['package_name'] }}</h6>
                                <p>A simple start for everyone</p>
                            </div>
                            @if ($package_id != '-1')
                                <div class="mb-6">
                                    <h6 class="mb-1">Active until {{ $package_data['expiryDate'] }}</h6>
                                    <p>We will send you a notification upon Subscription expiration</p>
                                </div>
                            @endif
                        </div>
                        @if ($package_id != '-1')
                            <div class="col-md-6">
                                @if ($package_data['remainingDays'] <= 15)
                                    <div class="alert alert-warning mb-6" role="alert">
                                        <h5 class="alert-heading mb-1 d-flex align-items-center">
                                            <span class="alert-icon rounded"><i
                                                    class="ti ti-alert-triangle ti-md"></i></span>
                                            <span>We need your attention!</span>
                                        </h5>
                                        <span class="ms-11 ps-1">Your plan requires update</span>
                                    </div>
                                @endif
                                <div class="plan-statistics">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Days</h6>
                                        <h6 class="mb-1">{{ $package_data['remainingDays'] }} of
                                            {{ $package_data['total_days'] }} Days</h6>
                                    </div>
                                    <div class="progress rounded mb-1">
                                        <div class="progress-bar rounded" role="progressbar" aria-valuenow="25"
                                            aria-valuemin="0" aria-valuemax="100" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small>{{ $package_data['remainingDays'] }} days remaining
                                        until your plan requires
                                        update</small>
                                </div>
                                @if (!empty($package_data['downgrade_payment']))
                                    <div class="alert alert-warning mt-3" role="alert">
                                        Your subscription plan downgrade has been scheduled. The change will take effect on
                                        {{ \Carbon\Carbon::parse($package_data['downgrade_payment']->PaymentDate)->format('m-d-Y') }},
                                        after your current plan expires. You can continue to enjoy your current plan
                                        benefits
                                        until then
                                    </div>
                                @endif
                                @if (!empty($package_data['cancel_plan']))
                                    <div class="alert alert-danger mt-3" role="alert">
                                        Your subscription cancellation has been scheduled. The change will take effect after
                                        your current plan ends. You will continue to enjoy your current plan benefits until
                                        then.
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="col-12 d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#onboardingHorizontalSlideModal">
                                @if ($package_id != '-1')
                                    Change
                                @else
                                    Select
                                @endif
                                Plan
                            </button>
                            @if (empty($package_data['cancel_plan']) && $package_id != '-1')
                                <a class="btn btn-label-danger "
                                    href="{{ route('user_cancel_plan', ['id' => $user->UserID]) }}">Cancel
                                    Subscription</a>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /Current Plan -->
            </div>
            @if (false)
                <div class="card mb-6">
                    <h5 class="card-header">Payment Methods</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form id="creditCardForm" class="row g-6" onsubmit="return false">
                                    <div class="col-12 mb-2">
                                        <div class="form-check form-check-inline my-2 ms-2 me-6">
                                            <input name="collapsible-payment" class="form-check-input" type="radio"
                                                value="" id="collapsible-payment-cc" checked="" />
                                            <label class="form-check-label" for="collapsible-payment-cc">Credit/Debit/ATM
                                                Card</label>
                                        </div>
                                        <div class="form-check form-check-inline ms-2 my-2">
                                            <input name="collapsible-payment" class="form-check-input" type="radio"
                                                value="" id="collapsible-payment-cash" />
                                            <label class="form-check-label" for="collapsible-payment-cash">Paypal
                                                account</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label w-100" for="paymentCard">Card Number</label>
                                        <div class="input-group input-group-merge">
                                            <input id="paymentCard" name="paymentCard" class="form-control credit-card-mask"
                                                type="text" placeholder="1356 3215 6548 7898"
                                                aria-describedby="paymentCard2" />
                                            <span class="input-group-text cursor-pointer p-1" id="paymentCard2"><span
                                                    class="card-type"></span></span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="paymentName">Name</label>
                                        <input type="text" id="paymentName" class="form-control"
                                            placeholder="John Doe" />
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="paymentExpiryDate">Exp. Date</label>
                                        <input type="text" id="paymentExpiryDate" class="form-control expiry-date-mask"
                                            placeholder="MM/YY" />
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label" for="paymentCvv">CVV Code</label>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="paymentCvv" class="form-control cvv-code-mask"
                                                maxlength="3" placeholder="654" />
                                            <span class="input-group-text cursor-pointer" id="paymentCvv2"><i
                                                    class="ti ti-help text-muted" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Card Verification Value"></i></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch ms-2 my-2">
                                            <input type="checkbox" class="form-check-input" id="future-billing" />
                                            <label for="future-billing" class="switch-label">Save card for future
                                                billing?</label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-6">
                                        <button type="submit" class="btn btn-primary me-3">Save Changes</button>
                                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 mt-12 mt-md-0">
                                <h6 class="mb-6">My Cards</h6>
                                <div class="added-cards">
                                    <div class="cardMaster p-6 bg-lighter rounded mb-6">
                                        <div class="d-flex justify-content-between flex-sm-row flex-column">
                                            <div class="card-information me-2">
                                                <img class="mb-2 img-fluid"
                                                    src="{{ asset('assets/img/icons/payments/mastercard.png') }}"
                                                    alt="Master Card">
                                                <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                                                    <h6 class="mb-0 me-2">Tom McBride</h6>
                                                    <span class="badge bg-label-primary">Primary</span>
                                                </div>
                                                <span class="card-number">&#8727;&#8727;&#8727;&#8727;
                                                    &#8727;&#8727;&#8727;&#8727; 9856</span>
                                            </div>
                                            <div class="d-flex flex-column text-start text-lg-end">
                                                <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-4">
                                                    <button class="btn btn-sm btn-label-primary me-4"
                                                        data-bs-toggle="modal" data-bs-target="#editCCModal">Edit</button>
                                                    <button class="btn btn-sm btn-label-danger">Delete</button>
                                                </div>
                                                <small class="mt-sm-4 mt-2 order-sm-1 order-0">Card expires at
                                                    12/26</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="cardMaster p-6 bg-lighter rounded">
                                        <div class="d-flex justify-content-between flex-sm-row flex-column">
                                            <div class="card-information me-2">
                                                <img class="mb-2 img-fluid"
                                                    src="{{ asset('assets/img/icons/payments/visa.png') }}"
                                                    alt="Visa Card">
                                                <h6 class="mb-2">Mildred Wagner</h6>
                                                <span class="card-number">&#8727;&#8727;&#8727;&#8727;
                                                    &#8727;&#8727;&#8727;&#8727; 5896</span>
                                            </div>
                                            <div class="d-flex flex-column text-start text-lg-end">
                                                <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-4">
                                                    <button class="btn btn-sm btn-label-primary me-4"
                                                        data-bs-toggle="modal" data-bs-target="#editCCModal">Edit</button>
                                                    <button class="btn btn-sm btn-label-danger">Delete</button>
                                                </div>
                                                <small class="mt-sm-4 mt-2 order-sm-1 order-0">Card expires at
                                                    10/27</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal -->
                                @include('_partials/_modals/modal-edit-cc')
                                <!--/ Modal -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-6">
                    <!-- Billing Address -->
                    <h5 class="card-header">Billing Address</h5>
                    <div class="card-body">
                        <form id="formAccountSettings" onsubmit="return false">
                            <div class="row">
                                <div class="mb-4 col-sm-6">
                                    <label for="companyName" class="form-label">Company Name</label>
                                    <input type="text" id="companyName" name="companyName" class="form-control"
                                        placeholder="{{ config('variables.creatorName') }}" />
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="billingEmail" class="form-label">Billing Email</label>
                                    <input class="form-control" type="text" id="billingEmail" name="billingEmail"
                                        placeholder="john.doe@example.com" />
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="taxId" class="form-label">Tax ID</label>
                                    <input type="text" id="taxId" name="taxId" class="form-control"
                                        placeholder="Enter Tax ID" />
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="vatNumber" class="form-label">VAT Number</label>
                                    <input class="form-control" type="text" id="vatNumber" name="vatNumber"
                                        placeholder="Enter VAT Number" />
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="mobileNumber" class="form-label">Mobile</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">US (+1)</span>
                                        <input class="form-control mobile-number" type="text" id="mobileNumber"
                                            name="mobileNumber" placeholder="202 555 0111" />
                                    </div>
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="country" class="form-label">Country</label>
                                    <select id="country" class="form-select select2" name="country">
                                        <option selected>USA</option>
                                        <option>Canada</option>
                                        <option>UK</option>
                                        <option>Germany</option>
                                        <option>France</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-12">
                                    <label for="billingAddress" class="form-label">Billing Address</label>
                                    <input type="text" class="form-control" id="billingAddress" name="billingAddress"
                                        placeholder="Billing Address" />
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="state" class="form-label">State</label>
                                    <input class="form-control" type="text" id="state" name="state"
                                        placeholder="California" />
                                </div>
                                <div class="mb-4 col-sm-6">
                                    <label for="zipCode" class="form-label">Zip Code</label>
                                    <input type="text" class="form-control zip-code" id="zipCode" name="zipCode"
                                        placeholder="231465" maxlength="6" />
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-3">Save changes</button>
                                <button type="reset" class="btn btn-label-secondary">Discard</button>
                            </div>
                        </form>
                    </div>
                    <!-- /Billing Address -->
                </div>
            @endif
        </div>
        @if ($package_id != '-1')
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-header">Invoice paid</h5>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="table" id="invoice_data">
                            <thead>
                                <tr>
                                    <th class="d-none">ID</th>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Issued Date</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal-onboarding modal fade animate__animated" id="onboardingHorizontalSlideModal" tabindex="-1"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content text-center">
                    <div class="modal-header border-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="pricing-table">
                        @if (count($packages) > 0)
                            @foreach ($packages as $package)
                                <div
                                    class="pricing-card {{ $package->Name == 'PRO' || $package->Name == 'ENTERPRISE' ? 'popular' : '' }}{{ $user->CurrentPackageID != '-1' && $user->CurrentPackageID == $package->PackageID ? ' selected-plan' : '' }}">
                                    <h3>{{ $package->Name }}</h3>
                                    <p class="price">${{ $package->Price }} <span>monthly</span></p>
                                    <ul class="features">
                                        <li>Up to
                                            {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }}
                                            checks
                                            / month</li>
                                        <li>Email Support</li>
                                        <li>Unlimited Users</li>
                                        @if ($package->Name != 'BASIC')
                                            <li>Custom Webform*</li>
                                        @endif
                                        <li>3 mos History Storage</li>
                                    </ul>
                                    @if ($user->CurrentPackageID != '-1' && $user->CurrentPackageID == $package->PackageID)
                                        <p class="current-plan">Current Plan</p>
                                    @else
                                        @if ($user->CurrentPackageID != '-1')
                                            <a href="{{ route('user.select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                                class="plan-button">Select Plan</a>
                                        @else
                                            <a href="{{ route('user-select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                                class="plan-button">Select Plan</a>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
