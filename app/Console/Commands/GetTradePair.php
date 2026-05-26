<?php

namespace App\Console\Commands;

use App\Coinbase;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:debug:get-pair')]
#[Description('Get trade pair API info')]
class GetTradePAir extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coinbase = new Coinbase();
        print_r($coinbase->getTradePair('STORJ', 'USDC'));
    }
}
