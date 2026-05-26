<x-mail::message>
# Trade failed

{{ config('app.name') }} attempted a trade, but it failed.
It will be retried on the next normal trade run if any balance
remains.

<x-mail::table>
|                    |                           |
|--------------------|---------------------------|
| Date               | {{ $trade->created_at }}  |
| Coinbase Trade ID  | {{ $trade->coinbase_id }} |
| Attempted Sale     | {{ $trade->from_amount }} |
</x-mail::table>
</x-mail::message>
