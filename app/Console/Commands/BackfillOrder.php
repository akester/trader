<?php

namespace App\Console\Commands;

use App\Coinbase;
use App\Models\Trade;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:debug:backfill')]
#[Description('Backfill the most recent order in Coinbase into the app for testing and debugging.')]
class BackfillOrder extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coinbase = new Coinbase();

        $orders = $coinbase->getOrders();
        $order = $orders[0];

        $trade = new Trade([
            'uuid' => $order['client_order_id'],
            'type' => 'sell-storj',
            'from_token' => 'STORJ',
            'to_token' => 'USDC',
            'from_amount' => $order['order_configuration']['market_market_ioc']['base_size'],
            'status' => 'pending',
            'coinbase_id' => $order['order_id'],
        ]);
        $trade->save();

        $this->info('Created trade from Coinbase order ' . $trade->coinbase_id);
    }
}
