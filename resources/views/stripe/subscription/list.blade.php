<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Subscriptions</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
</head>
<body>
    <div class="container">
        <h1>Subscriptions</h1>
        <table class="table" id="subscriptions-table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Customer Email</th>
                    <th scope="col">Customer Name</th>
                    <th scope="col">Product</th>
                    <th scope="col">Created</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#subscriptions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('stripe.subscription.get') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'customer.email', name: 'customer.email' },
                    { data: 'customer.name', name: 'customer.name' },
                    { data: 'product.name', name: 'product.name' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                     { data: 'actions', name: 'actions' },
                ]
            });
        })
    </script>    
</body>
</html>