<?php

namespace App\Console\Commands;

use App\Coinbase;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:get-jwt')]
#[Description('Get a coinbase JWT for testing')]
class GetCoinbaseJWT extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coinbase = new Coinbase();
        $jwtToken = $coinbase->getJWT();
        $this->info($jwtToken);
    }
}
