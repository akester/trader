<?php

use App\Console\Commands\CheckOrders;
use App\Console\Commands\SellStorj;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SellStorj::class)->everyFifteenMinutes();
Schedule::command(CheckOrders::class)->everyFifteenMinutes();
