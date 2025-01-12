@extends('layouts/layoutMaster')

@section('title', 'Package')

@section('content')
    <div class="card">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <h5 class="card-header">Users</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
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
                <tbody class="table-border-bottom-0">
                    @foreach ($users as $key => $user)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $user->FirstName }} {{ $user->LastName }}</td>
                            <td>{{ $user->Username }}</td>
                            <td>{{ $user->Email }}</td>
                            <td>{{ $user->PhoneNumber }}</td>
                            <td>{{ $user->package }}</td>
                            <td>${{ $user->package_price }}</td>
                            <td><span
                                    class="badge {{ $user->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning' }} me-1">{{ $user->Status }}</span>
                            </td>
                            <td>{{ $user->CreatedAt }}</td>
                            <td>{{ $user->UpdatedAt }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
