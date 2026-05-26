#!/bin/bash
set -e

# This confuses Laravel I think, we just flatten everything into one big file on the fly
rm .env.prod

php artisan config:clear
php artisan cache:clear
php artisan config:cache

php artisan migrate --force
