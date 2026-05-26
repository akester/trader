<x-mail::message>
# Trade complete

{{ config('app.name') }} completed a trade on your behalf:

<x-mail::table>
|                    |                           |
|--------------------|---------------------------|
| Date               | {{ $trade->created_at }}  |
| Coinbase Trade ID  | {{ $trade->coinbase_id }} |
| Sold STORJ         | {{ $trade->from_amount }} |
| Recieved USDC      | {{ $trade->to_amount }}   |
| Total Fees         | {{ $trade->total_fees }}  |
</x-mail::table>
</x-mail::message>
