<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Trader') }}</title>

        @vite(['resources/css/app.scss', 'resources/js/app.js'])

        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    </head>

    <body>
        <div id="app">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <h1>{{ config('app.name', 'Trader') }}</h1>
                    </div>
                </div>

                @foreach ($trades as $trade)
                <div class="row trade">
                    <div class="col-8 trade-type">
                        {{ $trade->getType() }}
                    </div>
                    <div class="col-4 trade-volume">
                        <span class="left">
                            {{ $trade->from_amount }}
                            <span class="token">
                                {{ strtoupper($trade->from_token) }}
                            </span>
                        </span>

                        <span class="right">
                            {{ $trade->to_amount }}
                            <span class="token">
                                {{ strtoupper($trade->to_token) }}
                            </span>
                        </span>
                    </div>
                    <div class="col meta">
                        <div class="date">
                            <strong>Trade Date:</strong>
                            {{ $trade->created_at }}
                        </div>
                        <div class="id">
                            <strong>Coinbase ID:</strong>
                            {{ $trade->coinbase_id }}
                        </div>
                        <div class="status">
                            <strong>Status:</strong>
                            {{ $trade->status }}
                        </div>
                        <div class="fees">
                            <strong>Fees:</strong>
                            {{ $trade->total_fees }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </body>
</html>