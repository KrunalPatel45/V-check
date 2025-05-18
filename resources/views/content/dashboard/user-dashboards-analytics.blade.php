@extends('layouts/layoutMaster')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/swiper/swiper.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss'])
@endsection

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/cards-advance.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/swiper/swiper.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/dashboards-analytics.js'])
    @vite('resources/assets/js/app-academy-dashboard.js')
@endsection

@section('content')
    @php
        $progress = !empty($package_data['remainingDays'])
            ? ($package_data['remainingDays'] * 100) / $package_data['total_days']
            : 0;
    @endphp
    <div class="row g-6">
        <!-- Average Daily Sales -->
        {{-- <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Companies</h5>
                    <p class="mb-0 text-body">Total Number of Companies</p>
                    <h4 class="mb-0">{{ $total_companies }}</h4>
                </div>
            </div>
        </div> --}}
        <div class="col-xl-6 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Payors</h5>
                    <p class="mb-0 text-body">Total Number of Payors</p>
                    <h4 class="mb-0">{{ $total_vendor }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Payees</h5>
                    <p class="mb-0 text-body">Total Number of Payees</p>
                    <h4 class="mb-0">{{ $total_client }}</h4>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Total Checks</h5>
                    <p class="mb-0 text-body">Total Number of Checks</p>
                    <h4 class="mb-0">{{ $given_checks }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Used Checks</h5>
                    <p class="mb-0 text-body">Total Number of Used Checks</p>
                    <h4 class="mb-0">{{ $used_checks }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-3 card-title">Remaining Checks</h5>
                    <p class="mb-0 text-body">Total Number of Remaining Checks</p>
                    <h4 class="mb-0">{{ $remaining_checks }}</h4>
                </div>
            </div>
        </div>

        <div class="col-xxl">
            <div class="card mb-6">
                <h5 class="card-header">Current Plan</h5>
                <div class="card-body">
                    <div class="row row-gap-4 row-gap-xl-0">
                        <div class="col-xl-6 order-1 order-xl-0">
                            <div class="mb-4">
                                <h6 class="mb-1">Your Current Plan is
                                    {{ $package == '-1' ? 'Trial' : $package_data['package_name'] }}</h6>
                                <p>A simple start for everyone</p>
                            </div>
                            @if ($package != '-1')
                                <div class="mb-4">
                                    <h6 class="mb-1">Active until {{ $package_data['expiryDate'] }}</h6>
                                    <p>We will send you a notification upon Subscription expiration</p>
                                </div>
                            @endif
                        </div>
                        @if ($package != '-1')
                            <div class="col-xl-6 order-0 order-xl-0">
                                <div class="plan-statistics">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">Days</h6>
                                        <h6 class="mb-1">{{ $package_data['remainingDays'] }} of
                                            {{ $package_data['total_days'] }} Days</h6>
                                    </div>
                                    <div class="progress mb-1 bg-label-primary" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                            aria-valuemax="100" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small>Your plan requires update</small>

                                    @if (!empty($paymentSubscription->NextPackageID))
                                        <div class="alert alert-warning mt-3" role="alert">
                                            Your subscription plan downgrade has been scheduled. The change will take effect
                                            on
                                            after your current plan expires. You can continue to enjoy your current plan
                                            benefits
                                            until then
                                        </div>
                                    @endif
                                    @if ($paymentSubscription->Status == 'Canceled')
                                        <div class="alert alert-danger mt-3" role="alert">
                                            Your subscription cancellation has been scheduled. The change will take effect
                                            after
                                            your current plan ends. You will continue to enjoy your current plan benefits
                                            until
                                            then.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
