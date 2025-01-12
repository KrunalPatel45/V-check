@php
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('layouts/layoutMaster')

@section('title', 'Company')

@section('content')
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Companies</h5>
            <a href="{{ route('user.company.add') }}" class="btn btn-primary mr-4"
                style="height: 40px !important;margin-right: 25px !important;"><i class="fa-solid fa-plus"></i> &nbsp; Add
                Company</a>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Logo</th>
                        <th>Email</th>
                        <th>Routing Number</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Zip</th>
                        <th>Status</th>
                        <th>CreatedAt</th>
                        <th>UpdatedAt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($companies as $key => $company)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $company->Name }}</td>
                            <td><img src="{{ asset('storage/' . $company->Logo) }}" alt="Company Logo" style="width: 50px;">
                            </td>
                            <td>{{ $company->Email }}</td>
                            <td>{{ $company->RoutingNumber }}</td>
                            <td>{{ $company->City }}</td>
                            <td>{{ $company->State }}</td>
                            <td>{{ $company->Zip }}</td>
                            <td><span
                                    class="badge {{ $company->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning' }} me-1">{{ $company->Status }}</span>
                            </td>
                            <td>{{ $company->CreatedAt }}</td>
                            <td>{{ $company->UpdatedAt }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('user.company.edit', ['id' => $company->CompanyID]) }}"
                                            class="dropdown-item" href="javascript:void(0);"><i
                                                class="ti ti-pencil me-1"></i>
                                            Edit</a>
                                        <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal"
                                            data-bs-target="#delete{{ $company->CompanyID }}"><i
                                                class="ti ti-trash me-1"></i>
                                            Delete</a>
                                    </div>
                                </div>

                                <div class="modal fade" id="delete{{ $company->CompanyID }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel1">Delete Package
                                                    {{ $company->Name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete this Company? </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                                <form
                                                    action="{{ route('user.company.delete', ['id' => $company->CompanyID]) }}"
                                                    method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
