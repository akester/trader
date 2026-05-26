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
                <div class="row">
                    <div class="col">
                        {{ dump($trade) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </body>
</html>