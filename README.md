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

At some point, I may docker-ize this setup into a Docker Compose deployment, but
I deploy this some internal tooling so I would have to write that up.
