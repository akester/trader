#!/bin/bash
set -e

php artisan config:clear
php artisan config:cache

php artisan migrate --force
