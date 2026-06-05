# Trader

Trader is an automated tool to sell STORJ tokens on Coinbase (almost) as soon as
you get them.

## Motivation

STORJ pays its SNOs in STORJ token which has a side-effect of having significant
price changes quickly after payouts are sent.  For folks who take even a few
hours to trade to some other token or stablecoin, this can create a noticeable
drop in their payout and profit.

This tools connects to Coinbase and checks if your account has any STORJ every
30 minutes or so.  If it finds any, it sells them to USDC in an order and sends
a notification.

This tool uses Laravel mostly because:

* This was a quick write, and Laravel has a lot of the tools needed
  (notifications, frontend stuff) built in.
* I have templates to host sites running on a PHP stack ready, so it's easy to
  spin up in my K3S cluster.
* I used to write a lot of Laravel code in a past life, so I'm reasonably
  familiar with things.

## Theory

This tool will do an API call every 30 minutes to Coinbase to check the balance
of your STORJ wallet.  If the balance is over 1 STORJ, it will create a new
order to sell your STORJ for USDC at the current market price.  It then will
check the order it just created (usually the sells only take a moment), or check
back every 30 minutes to see if its been filled.

The order is using the Advanced Trade API, so it's visible in your Coinbase
account though it may prompt you to enable Advanced mode to actually see it. The
specific order type is an "Immediate Or Cancel" type, which should sell the
token at the quoted price or better immediately, or cancel the part of the order
that cannot be filled.  If the transaction fails or is a partial fill, any
remaining balance will be sold on the next run.

The orders are saved into a web dashboard that you can use to view the history,
see trade fees, etc.  There is no authentication or user management, but the
dashboard is read-only (there are no settings to change or even private data
outside of transaction history visible).

## Authentication

This uses a fixed API token for a single Coinbase account.   This basically
means you just self-host this for yourself.  Coinbase (rightfully) restricts
oAuth apps to vetted entities, so it's not possible for me to host a version for
others.

To get an API private key, follow [this
guide](https://docs.cdp.coinbase.com/get-started/authentication/overview).  Use
an ECSDA key, as we're using the Advanced Trade API.  You'll get a Key Name and
Secret, put both in .env.

## Running

This app is using Laravel, so most of the configuration in .env matches a normal
Laravel setup.  The only real features used are the Database, Vite, and
Notifications.  Things like the queue can be left as `file` driver for any
incidental use.

Copy `.env.example` to `.env` and set these required variables:

* `COINBASE_KEY` - The key name of your Coinbase key that was generated.
* `COINBASE_SECRET` - The secret key of your Coinbase API token.
* `NOTIFICATION_EMAIL` - Trader will send you an email when it makes a trade
  (assuming you have mail configured somehow), it just sends to this address.

You can also adjust to use a sqlite database (there's basically no DB traffic,
sqlite works just fine) to remove the need for a full MySQL server as well.

To run the server, install the needed dependancies, build the frontend and the like:

```
# Install Dependancies
composer install --optimize-autoloader --no-dev
yarn install

# Build frontend assets
yarn run build

# Set up the application
php artisan key:generate
php artisan migrate --force

php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

You then need to create a cron job that runs the scheudler every minute.  This
will watch the clock and run the sell commands on their schedule as needed:

```
* * * * * cd /path/to/code && php artisan schedule:run
```

At some point, I may docker-ize this setup into a Docker Compose deployment, but
I deploy this via some internal tooling so I would have to write that up.

## Roadmap

* Keep tabs on the price after a trade and show the gain/loss from selling
  quickly.