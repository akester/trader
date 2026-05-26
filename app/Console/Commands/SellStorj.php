<?php

namespace App\Console\Commands;

use App\Coinbase;
use App\Models\Trade;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

#[Signature('app:sell:storj')]
#[Description('Sell outstanding Storj balance')]
class SellStorj extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coinbase = new Coinbase();

        $balance = 10;

        $balance = $coinbase->getBalance('STORJ');
        if ($balance < 1) {
            $this->info('STORJ balance <1, will not do anything');
            return(0);
        }

        if (Coinbase::$MAX_TRADE_VOLUME && $balance > Coinbase::$MAX_TRADE_VOLUME) {
            $this->warn('Limiting trade to ' . Coinbase::$MAX_TRADE_VOLUME . ' STORJ as requested.');
            $balance = Coinbase::$MAX_TRADE_VOLUME;
        }

        $balance = round($balance, 3);

        // Check if we have an order in cooldown, this gives the first one time
        // to complete before we tell it to do another.
        $orders = $coinbase->getOrders();
        foreach ($orders as $order) {
            if ($order['status'] != 'filled' && strtotime($order['created_time']) > (time() - Coinbase::$COOLDOWN_MINS * 60) ) {
                $this->warn('We have >0 transactions not filled and in cooldown, will not do anything');
                return (1);
            }
        }

        // Create the new order in the database
        $trade = new Trade([
            'type' => 'storj-sale',
            'from_token' => 'STORJ',
            'to_token' => 'USDC',
            'from_amount' => (string) $balance,
            'status' => 'creating',
            'uuid' => Uuid::uuid4(),
        ]);

        $trade->save();

        // Create the order in the upstream API
        $order = $coinbase->createOrder('STORJ', 'USDC', $balance, $trade->uuid);

        if (!$order['success']) {
            $this->error('Failed to create order: ' . $order['error_response']['message']);
            Log::error("Failed to create sell order: ". $order['error_response']['message']);
            $trade->update([
                'status' => 'create-failed',
            ]);
            return (1);
        }

        $trade->update([
            'status' => 'pending',
            'coinbase_id' => $order['success_response']['order_id'],
        ]);
        $trade->save();
        $this->info('Order created: Sell ' . $balance . ' STORJ for USDC');
        Log::info("Created order " . $order['success_response']['order_id'] . " to sell " . $balance . " STORJ");
    }
}
