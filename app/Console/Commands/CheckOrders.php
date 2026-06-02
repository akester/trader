<?php

namespace App\Console\Commands;

use App\Coinbase;
use App\Models\Trade;
use App\Notifications\TradeComplete;
use App\Notifications\TradeFailed;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

#[Signature('app:orders:check')]
#[Description('Check the status of outstanding orders')]
class CheckOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $coinbase = new Coinbase();

        $trades = Trade::where('status', 'pending')
            ->get();
        foreach ($trades as $trade) {
            if ($trade['coinbase_id'] == '') {
                $this->warn('Trade #' . $trade['id'] . ' has no Coinbase ID, will not do anything');
                continue;
            }
            
            $order = $coinbase->getOrder($trade['coinbase_id']);

            switch ($order['status']) {
                case 'FILLED':
                    $trade->update([
                        'status' => 'filled',
                        'total_fees' => $order['total_fees'],
                        'to_amount' => $order['filled_value']
                    ]);
                    $trade->save();
                    $this->info('Trade ID ' . $trade->coinbase_id . ' is completed.');
                    Log::info('Trade ID ' . $trade->coinbase_id . ' is completed.');
                    Notification::route('mail', config('app.notify-email'))->notify(new TradeComplete($trade));
                    break;
                case 'CANCELLED':
                case 'EXPIRED':
                case 'FAILED':
                    $trade->update([
                        'status' => 'failed',
                    ]);
                    $trade->save();
                    $this->info('Trade ID ' . $trade->coinbase_id . ' is failed.');
                    Log::info('Trade ID ' . $trade->coinbase_id . ' is failed.');
                    Notification::route('mail', config('app.notify-email'))->notify(new TradeFailed($trade));
                    break;
                
                // The other statuses are incomplete, so ignore them.
            }
        }
    }
}
