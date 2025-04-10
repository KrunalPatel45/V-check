@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Receive Payment')
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
        @if (session('info'))
            <div class="alert alert-danger">
                {{ session('info') }}
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Receive Payment</h5>
            <a href="{{ route('check.process_payment.check') }}" class="btn btn-primary mr-4"
                style="height: 40px !important;margin-right: 25px !important;">
                <i class="fa-solid fa-plus"></i> &nbsp; Create Check
            </a>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <table id="receive_payment_checks" class="table">
                <thead>
                    <tr>
                        <th style="d-none">ID</th>
                        <th>#</th>
                        <th style="width: 50px;!important">Check Number</th>
                        <th>Payee</th>
                        <th>Payor</th>
                        <th>Amount</th>
                        <th>Print Date</th>
                        <th>Actions</th>
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
            $('#receive_payment_checks').DataTable({
                processing: true,
                serverSide: true,
                pageLength: "{{ config('app.rp_per_page') }}",
                lengthChange: false,
                ajax: "{{ route('check.process_payment') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'CheckID', // Hidden ID column for sorting
                        name: 'CheckID',
                        visible: false // Hides the ID column
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'CheckNumber',
                        name: 'CheckNumber'
                    },
                    {
                        data: 'CompanyID',
                        name: 'CompanyID'
                    },
                    {
                        data: 'EntityID',
                        name: 'EntityID'
                    },
                    {
                        data: 'Amount',
                        name: 'Amount',
                    },
                    {
                        data: 'IssueDate',
                        name: 'IssueDate'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                    },
                ]
            });

            $('body').on('change', '#change_status', function() {
                var selectedValue = $(this).val();
                var id = $(this).data('id');

                $.ajax({
                    url: "{{ route('change_status') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: {
                        value: selectedValue,
                        id: id,
                        page: 1
                    },
                    success: function(response) {
                        sessionStorage.setItem('success', response.message);

                        // Redirect to the appropriate URL
                        window.location.href = response.redirectUrl;
                    },
                    error: function() {
                        console.log('Error fetching data');
                    }
                });
            });

            window.onload = function() {
                if (sessionStorage.getItem('success')) {
                    let successMessage = sessionStorage.getItem('success');
                    sessionStorage.removeItem('success'); // Clear the sessionStorage after use
                    // Display the success message in the alert section (you can also append it manually)
                    let alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerText = successMessage;
                    document.body.insertBefore(alertDiv, document.body
                        .firstChild); // Display the alert at the top of the page
                }
            }
        });
    </script>
@endsection
