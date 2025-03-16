@extends('layouts/layoutMaster')

@section('title', 'User View - Pages')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-user-view.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/js/ui-modals.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/modal-edit-user.js', 'resources/assets/js/app-user-view.js', 'resources/assets/js/app-user-view-account.js', 'resources/assets/js/pages-profile.js'])
    <script>
        $(document).ready(function() {
            $('#companyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.user.company', ['id' => $user->UserID]) }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $('#clientTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.user.client', ['id' => $user->UserID]) }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'Status',
                        name: 'Status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'CreatedAt',
                        name: 'CreatedAt'
                    },
                ]
            });

            $('#vendorTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.user.vendor', ['id' => $user->UserID]) }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'Name',
                        name: 'Name'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'Status',
                        name: 'Status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'CreatedAt',
                        name: 'CreatedAt'
                    },
                ]
            });

            $('#invoice_data').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.user.invoice', ['id' => $user->UserID]) }}",
                columns: [{
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
    <div class="row">
        <!-- User Sidebar -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('profile_success'))
            <div class="alert alert-success">
                {{ session('profile_success') }}
            </div>
        @endif
        @if (session('pass_success'))
            <div class="alert alert-success">
                {{ session('pass_success') }}
            </div>
        @endif
        <div class="col-xl-4 col-lg-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card mb-6">
                <div class="card-body pt-12">
                    <div class="user-avatar-section">
                        <div class=" d-flex align-items-center flex-column">
                            <img class="img-fluid rounded mb-4" src="{{ asset('assets/img/avatars/1.png') }}" height="120"
                                width="120" alt="User avatar" />
                            <div class="user-info text-center">
                                <h5>{{ $user->FirstName }} {{ $user->LastName }}</h5>
                                {{-- <span class="badge bg-label-secondary">Author</span> --}}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4">
                        <div class="d-flex align-items-center me-5 gap-4">
                            <div class="avatar">
                                <div class="avatar-initial bg-label-primary rounded">
                                    <i class='ti ti-checkbox ti-lg'></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $check_used }}</h5>
                                <span>Check Used</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="avatar">
                                <div class="avatar-initial bg-label-primary rounded">
                                    <i class='ti ti-briefcase ti-lg'></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $remaining_checks }}</h5>
                                <span>Check Unused</span>
                            </div>
                        </div>
                    </div>
                    <h5 class="pb-4 border-bottom mb-4">Details</h5>
                    <div class="info-container">
                        <ul class="list-unstyled mb-6">
                            <li class="mb-2">
                                <span class="h6">Username:</span>
                                <span>{{ $user->Username }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6">Email:</span>
                                <span>{{ $user->Email }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6">Status:</span>
                                <span>{{ $user->Status }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6">Phone Number:</span>
                                <span>{{ $user->PhoneNumber }}</span>
                            </li>
                        </ul>
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('admin.user_profile_edit', ['id' => $user->UserID]) }}"
                                class="btn btn-primary me-4">Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ User Sidebar -->


        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 order-0 order-md-1">
            <!-- User Pills -->
            <div class="nav-align-top">
                <ul class="nav nav-pills flex-column flex-md-row flex-wrap mb-6 row-gap-2">
                    <li class="nav-item"><a class="nav-link {{ $type == 'default' ? 'active' : '' }}"
                            href="{{ route('admin.user.edit', ['id' => $user->UserID]) }}"><i
                                class="ti ti-user-check ti-sm me-1_5"></i>Companies</a></li>
                    <li class="nav-item"><a class="nav-link {{ $type == 'security' ? 'active' : '' }}"
                            href="{{ route('admin.user.edit', ['id' => $user->UserID, 'type' => 'security']) }}"><i
                                class="ti ti-lock ti-sm me-1_5"></i>Security</a></li>
                    <li class="nav-item"><a class="nav-link {{ $type == 'billing' ? 'active' : '' }}"
                            href="{{ route('admin.user.edit', ['id' => $user->UserID, 'type' => 'billing']) }}"><i
                                class="ti ti-bookmark ti-sm me-1_5"></i>Billing &
                            Plans</a></li>
                    {{-- <li class="nav-item"><a class="nav-link {{ $type == 'client' ? 'active' : '' }}"
                            href="{{ route('admin.user.edit', ['id' => $user->UserID, 'type' => 'client']) }}"><i
                                class="ti ti-bell ti-sm me-1_5"></i>Payees</a></li>
                    <li class="nav-item"><a class="nav-link {{ $type == 'vendor' ? 'active' : '' }}"
                            href="{{ route('admin.user.edit', ['id' => $user->UserID, 'type' => 'vendor']) }}"><i
                                class="ti ti-link ti-sm me-1_5"></i>Payors</a>
                    </li> --}}
                </ul>
            </div>
            @if ($type == 'default')
                <div class="card mb-6">
                    <div class="card-datatable table-responsive">
                        <table class="table border-top" id="companyTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                @if (false)
                    <div class="card mb-6">
                        <h5 class="card-header">User Activity Timeline</h5>
                        <div class="card-body pt-1">
                            <ul class="timeline mb-0">
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-primary"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">12 Invoices have been paid</h6>
                                            <small class="text-muted">12 min ago</small>
                                        </div>
                                        <p class="mb-2">
                                            Invoices have been paid to the company
                                        </p>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="badge bg-lighter rounded d-flex align-items-center">
                                                <img src="{{ asset('assets/img/icons/misc/pdf.png') }}" alt="img"
                                                    width="15" class="me-2">
                                                <span class="h6 mb-0 text-body">invoices.pdf</span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-success"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">Client Meeting</h6>
                                            <small class="text-muted">45 min ago</small>
                                        </div>
                                        <p class="mb-2">
                                            Project meeting with john @10:15am
                                        </p>
                                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                            <div class="d-flex flex-wrap align-items-center mb-50">
                                                <div class="avatar avatar-sm me-2">
                                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar"
                                                        class="rounded-circle" />
                                                </div>
                                                <div>
                                                    <p class="mb-0 small fw-medium">Lester McCarthy (Client)</p>
                                                    <small>CEO of {{ config('variables.creatorName') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-info"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">Create a new project for client</h6>
                                            <small class="text-muted">2 Day Ago</small>
                                        </div>
                                        <p class="mb-2">
                                            6 team members in a project
                                        </p>
                                        <ul class="list-group list-group-flush">
                                            <li
                                                class="list-group-item d-flex justify-content-between align-items-center flex-wrap border-top-0 p-0">
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <ul
                                                        class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                            data-bs-placement="top" title="Vinnie Mostowy"
                                                            class="avatar pull-up">
                                                            <img class="rounded-circle"
                                                                src="{{ asset('assets/img/avatars/5.png') }}"
                                                                alt="Avatar" />
                                                        </li>
                                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                            data-bs-placement="top" title="Allen Rieske"
                                                            class="avatar pull-up">
                                                            <img class="rounded-circle"
                                                                src="{{ asset('assets/img/avatars/12.png') }}"
                                                                alt="Avatar" />
                                                        </li>
                                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                            data-bs-placement="top" title="Julee Rossignol"
                                                            class="avatar pull-up">
                                                            <img class="rounded-circle"
                                                                src="{{ asset('assets/img/avatars/6.png') }}"
                                                                alt="Avatar" />
                                                        </li>
                                                        <li class="avatar">
                                                            <span
                                                                class="avatar-initial rounded-circle pull-up text-heading"
                                                                data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                                title="3 more">+3</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            @endif
            @if ($type == 'security')
                <form id="change-password" action="{{ route('admin.user.change-password') }}" method="POST">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id" value="{{ $user->UserID }}">
                    <div class="card mb-6">
                        <h5 class="card-header">Change Password</h5>
                        <div class="card-body">
                            <form id="formChangePassword" method="POST" onsubmit="return false">
                                <div class="alert alert-warning alert-dismissible" role="alert">
                                    <h5 class="alert-heading mb-1">Ensure that these requirements are met</h5>
                                    <span>Minimum 8 characters long, uppercase & symbol</span>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                                <div class="row gx-6">
                                    <div class="mb-4 col-12 col-sm-6 form-password-toggle">
                                        <label class="form-label" for="new_password">New Password</label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="password" id="new_password"
                                                name="new_password"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="ti ti-eye-off"></i></span>
                                            @if ($errors->has('new_password'))
                                                <span class="text-danger">
                                                    {{ $errors->first('new_password') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-4 col-12 col-sm-6 form-password-toggle">
                                        <label class="form-label" for="new_password_confirmation">Confirm New
                                            Password</label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="password" name="new_password_confirmation"
                                                id="new_password_confirmation"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="ti ti-eye-off"></i></span>
                                            @if ($errors->has('new_password_confirmation'))
                                                <span class="text-danger">
                                                    {{ $errors->first('new_password_confirmation') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary me-2">Change Password</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
                @if (false)
                    <div class="card mb-6">
                        <h5 class="card-header">Recent Devices</h5>
                        <div class="table-responsive table-border-bottom-0">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="text-truncate">Browser</th>
                                        <th class="text-truncate">Device</th>
                                        <th class="text-truncate">Location</th>
                                        <th class="text-truncate">Recent Activities</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-truncate"><i class='ti ti-brand-windows ti-md text-info me-4'></i>
                                            <span class="text-heading">Chrome on Windows</span>
                                        </td>
                                        <td class="text-truncate">HP Spectre 360</td>
                                        <td class="text-truncate">Switzerland</td>
                                        <td class="text-truncate">10, July 2021 20:07</td>
                                    </tr>
                                    <tr>
                                        <td class="text-truncate"><i
                                                class='ti ti-device-mobile ti-md text-danger me-4'></i>
                                            <span class="text-heading">Chrome on iPhone</span>
                                        </td>
                                        <td class="text-truncate">iPhone 12x</td>
                                        <td class="text-truncate">Australia</td>
                                        <td class="text-truncate">13, July 2021 10:10</td>
                                    </tr>
                                    <tr>
                                        <td class="text-truncate"><i
                                                class='ti ti-brand-android ti-md text-success me-4'></i>
                                            <span class="text-heading">Chrome on Android</span>
                                        </td>
                                        <td class="text-truncate">Oneplus 9 Pro</td>
                                        <td class="text-truncate">Dubai</td>
                                        <td class="text-truncate">14, July 2021 15:15</td>
                                    </tr>
                                    <tr>
                                        <td class="text-truncate"><i class='ti ti-brand-apple ti-md me-4'></i> <span
                                                class="text-heading">Chrome on MacOS</span></td>
                                        <td class="text-truncate">Apple iMac</td>
                                        <td class="text-truncate">India</td>
                                        <td class="text-truncate">16, July 2021 16:17</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
            @if ($type == 'billing')
                <div class="card mb-6 border border-2 border-primary rounded primary-shadow">
                    @php
                        $progress = ($package_data['remainingDays'] * 100) / $package_data['total_days'];
                    @endphp
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-label-primary">{{ $package->Name }}</span>
                            <div class="d-flex justify-content-center">
                                <sub class="h5 pricing-currency mb-auto mt-1 text-primary">$</sub>
                                <h1 class="mb-0 text-primary">{{ $package->Price }}</h1>
                                <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">month</sub>
                            </div>
                        </div>
                        <p>
                            {{ $package->Description }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="h6 mb-0">Days</span>
                            <span class="h6 mb-0">{{ $package_data['remainingDays'] }} of
                                {{ $package_data['total_days'] }}
                                Days</span>
                        </div>
                        <div class="progress mb-1 bg-label-primary" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;"
                                aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small>{{ $package_data['remainingDays'] }} days remaining</small>
                        @if (!empty($package_data['downgrade_payment']))
                            <div class="alert alert-warning mt-3" role="alert">
                                Your subscription plan downgrade has been scheduled. The change will take effect on
                                {{ \Carbon\Carbon::parse($package_data['downgrade_payment']->PaymentDate)->format('m-d-Y') }},
                                after your current plan expires. You can continue to enjoy your current plan benefits until
                                then
                            </div>
                        @endif
                        @if (!empty($package_data['cancel_plan']))
                            <div class="alert alert-danger mt-3" role="alert">
                                Your subscription cancellation has been scheduled. The change will take effect after
                                your current plan ends. You will continue to enjoy your current plan benefits until
                                then.
                            </div>
                        @endif
                        <div class="d-grid w-100 mt-6">
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#onboardingHorizontalSlideModal">Change
                                Plan</button>
                        </div>
                    </div>
                </div>
                @if (false)
                    <div class="card card-action mb-6">
                        <div class="card-header align-items-center">
                            <h5 class="card-action-title mb-0">Payment Methods</h5>
                            <div class="card-action-element">
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal"
                                    data-bs-target="#addNewCCModal"><i class="ti ti-plus ti-14px me-1_5"></i>Add
                                    Card</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="added-cards">
                                <div class="cardMaster border p-6 rounded mb-4">
                                    <div class="d-flex justify-content-between flex-sm-row flex-column">
                                        <div class="card-information">
                                            <img class="mb-2 img-fluid"
                                                src="{{ asset('assets/img/icons/payments/mastercard.png') }}"
                                                alt="Master Card">
                                            <div class="d-flex align-items-center mb-2">
                                                <h6 class="mb-0 me-2">Kaith Morrison</h6>
                                                <span class="badge bg-label-primary me-1">Popular</span>
                                            </div>
                                            <span class="card-number">&#8727;&#8727;&#8727;&#8727;
                                                &#8727;&#8727;&#8727;&#8727;
                                                &#8727;&#8727;&#8727;&#8727; 9856</span>
                                        </div>
                                        <div class="d-flex flex-column text-start text-lg-end">
                                            <div class="d-flex order-sm-0 order-1">
                                                <button class="btn btn-sm btn-label-primary me-4" data-bs-toggle="modal"
                                                    data-bs-target="#editCCModal">Edit</button>
                                                <button class="btn btn-sm btn-label-danger">Delete</button>
                                            </div>
                                            <small class="mt-sm-4 mt-2 order-sm-1 order-0 text-sm-end mb-2">Card expires at
                                                12/24</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="cardMaster border p-6 rounded mb-4">
                                    <div class="d-flex justify-content-between flex-sm-row flex-column">
                                        <div class="card-information">
                                            <img class="mb-2 img-fluid"
                                                src="{{ asset('assets/img/icons/payments/visa.png') }}"
                                                alt="Master Card">
                                            <h6 class="mb-2 me-2">Tom McBride</h6>
                                            <span class="card-number">&#8727;&#8727;&#8727;&#8727;
                                                &#8727;&#8727;&#8727;&#8727;
                                                &#8727;&#8727;&#8727;&#8727; 6542</span>
                                        </div>
                                        <div class="d-flex flex-column text-start text-lg-end">
                                            <div class="d-flex order-sm-0 order-1">
                                                <button class="btn btn-sm btn-label-primary me-4" data-bs-toggle="modal"
                                                    data-bs-target="#editCCModal">Edit</button>
                                                <button class="btn btn-sm btn-label-danger">Delete</button>
                                            </div>
                                            <small class="mt-sm-4 mt-2 order-sm-1 order-0 text-sm-end mb-2">Card expires at
                                                02/24</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="cardMaster border p-6 rounded">
                                    <div class="d-flex justify-content-between flex-sm-row flex-column">
                                        <div class="card-information">
                                            <img class="mb-2 img-fluid"
                                                src="{{ asset('assets/img/icons/payments/american-express-logo.png') }}"
                                                alt="Visa Card">
                                            <div class="d-flex align-items-center mb-2">
                                                <h6 class="mb-0 me-2">Mildred Wagner</h6>
                                                <span class="badge bg-label-danger me-1">Expired</span>
                                            </div>
                                            <span class="card-number">&#8727;&#8727;&#8727;&#8727;
                                                &#8727;&#8727;&#8727;&#8727;
                                                &#8727;&#8727;&#8727;&#8727; 5896</span>
                                        </div>
                                        <div class="d-flex flex-column text-start text-lg-end">
                                            <div class="d-flex order-sm-0 order-1">
                                                <button class="btn btn-sm btn-label-primary me-4" data-bs-toggle="modal"
                                                    data-bs-target="#editCCModal">Edit</button>
                                                <button class="btn btn-sm btn-label-danger">Delete</button>
                                            </div>
                                            <small class="mt-sm-4 mt-2 order-sm-1 order-0 text-sm-end mb-2">Card expires at
                                                08/20</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="card mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-header">Invoice paid</h5>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="table" id="invoice_data">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Issued Date</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @endif
            @if ($type == 'client')
                <div class="card mb-6">
                    <div class="card-datatable table-responsive">
                        <table class="table border-top" id="clientTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>CreatedAt</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @endif
            @if ($type == 'vendor')
                <div class="card mb-6">
                    <div class="card-datatable table-responsive">
                        <table class="table border-top" id="vendorTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>CreatedAt</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal-onboarding modal fade animate__animated" id="onboardingHorizontalSlideModal" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content text-center">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="pricing-table">
                    @foreach ($packages as $package)
                        <div
                            class="pricing-card {{ $package->Name == 'PRO' || $package->Name == 'ENTERPRISE' ? 'popular' : '' }}{{ $user->CurrentPackageID == $package->PackageID ? ' selected-plan' : '' }}">
                            <h3>{{ $package->Name }}</h3>
                            <p class="price">${{ $package->Price }} <span>monthly</span></p>
                            <ul class="features">
                                <li>Up to {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }}
                                    checks
                                    / month</li>
                                <li>Email Support</li>
                                <li>Unlimited Users</li>
                                @if ($package->Name != 'BASIC')
                                    <li>Custom Webform*</li>
                                @endif
                                <li>3 mos History Storage</li>
                            </ul>
                            @if ($user->CurrentPackageID == $package->PackageID)
                                <p class="current-plan">Current Plan</p>
                            @else
                                <a href="{{ route('admin.user.select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                    class="plan-button">Select Plan</a>
                            @endif
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    @include('_partials/_modals/modal-edit-user')
    @include('_partials/_modals/modal-upgrade-plan')
    <!-- /Modal -->
@endsection
