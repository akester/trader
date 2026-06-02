<?php

namespace App\Console\Commands;

use App\Coinbase;
use App\Models\Price;
use App\Models\Trade;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:storj:check-prices')]
#[Description('Get the current STORJ/USDC price from Coinbase API and store in database')]
class GetStorjPrices extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if any order was created in the past 48 hours
        $fortyEightHoursAgo = now()->subHours(48);
        $recentOrders = Trade::where('created_at', '>=', $fortyEightHoursAgo)->exists();

        if (!$recentOrders) {
            $this->info('No recent orders in the past 48 hours, will not get prices');
            return 0;
        }

        // Get the current STORJ/USDC price from Coinbase API
        $coinbase = new Coinbase();
        $pairResponse = $coinbase->getTradePair('STORJ', 'USDC');

        // Extract price from the trade pair data
        $priceData = $pairResponse['price'];
        $token = 'STORJ-USD';
        $price = (float) $priceData;

        // Store the price in the database (create new record, duplicates allowed)
        Price::create([
            'token' => $token,
            'price' => $price,
        ]);

        $this->info('Current STORJ/USDC price: ' . $price);
        Log::info("Created price record for $token: $price");

        return 0;
    }
}
