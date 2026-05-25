<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Trader') }}</title>

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>

    <body>
        <div id="app">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <h1>{{ config('app.name', 'Trader') }}
                    </div>
                </div>

                
            </div>
        </div>
    </body>
</html>