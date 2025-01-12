<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plan </title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .plan-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1200px;
            margin: 20px;
        }

        .plan {
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            padding: 20px;
            transition: 0.3s;
        }

        .plan:hover {
            transform: scale(1.05);
            border-color: #007BFF;
        }

        .plan h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #333;
        }

        .plan .price {
            font-size: 2em;
            font-weight: bold;
            color: #7367f0;
            margin: 15px 0;
        }

        .plan .description {
            font-size: 0.9em;
            color: #666;
            margin: 15px 0;
            margin-bottom: 30px;
        }

        .plan ul {
            list-style: none;
            text-align: left;
            padding: 0;
            margin: 20px 0;
        }

        .plan ul li {
            margin: 10px 0;
            color: #555;
        }

        .plan .plan-button {
            background-color: #7367f0;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 1em;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            margin-top: 20px;
        }

        .plan .plan-button:hover {
            background-color: #7367f0;
        }

        @media (max-width: 768px) {
            .plan {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="plan-container">
        @foreach ($packages as $package)
            <div class="plan">
                <h3>{{ $package->Name }}</h3>
                <p class="price">${{ number_format($package->Price) }} / {{ number_format($package->Duration / 30.44) }}
                    Month
                </p>
                <p class="description">
                    {{ $package->Description }}
                </p>
                <a href="{{ route('user.select-package', ['id' => $userId, 'plan' => $package->PackageID]) }}"
                    class="plan-button">Select Plan</a>
            </div>
        @endforeach
    </div>
</body>

</html>
