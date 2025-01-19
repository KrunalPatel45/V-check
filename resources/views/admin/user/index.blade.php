@extends('layouts/layoutMaster')

@section('title', 'Package')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('content')
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <h5 class="card-header">Users</h5>
        <div class="card-datatable table-responsive pt-0">
            <table class="table" id="users-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>PhoneNumber</th>
                        <th>Subscription Plan</th>
                        <th>Plan Price</th>
                        <th>Status</th>
                        <th>CreatedAt</th>
                        <th>UpdatedAt</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.users') }}", // Your route to fetch data
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    }, // Automatically generated index column
                    {
                        data: 'FirstName',
                        name: 'FirstName'
                    },
                    {
                        data: 'Username',
                        name: 'Username'
                    },
                    {
                        data: 'Email',
                        name: 'Email'
                    },
                    {
                        data: 'PhoneNumber',
                        name: 'PhoneNumber'
                    },
                    {
                        data: 'package',
                        name: 'package'
                    },
                    {
                        data: 'package_price',
                        name: 'package_price'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    }
                ],
                columnDefs: [{
                    targets: [0, 7, 8,
                        9
                    ], // You can customize the columns for no sorting or searching
                    orderable: false
                }]
            });
        });
    </script>
@endsection
