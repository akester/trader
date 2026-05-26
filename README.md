# Trader

Trader is an automated tool to sell STORJ tokens on Coinbase (almost) as soon as
you get them.

## Motiviation

STORJ pays its SNOs in STORJ token which has a side-effect of having significant
price changes quickly after payouts are sent.  For folks who take even a few
hours to trade to some other token or stablecoin, this can create a noticeable
drop in their payout and profit.

This tools connects to Coinbase and checks if your account has any STORJ every
30 minutes or so.  If it finds any, it sells them to USDC in an order and sends
a notification.

## Authentication

Right now, this uses a fixed API token for a single Coinbase account.   This
basically means you just self-host this for yourself.

At some point if there's interest, this could be adapted to connect to other
accounts and do for multiple users at once.
