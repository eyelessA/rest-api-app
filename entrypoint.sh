#!/bin/bash

composer install --no-dev --optimize-autoloader

php artisan key:generate

php artisan migrate:fresh --seed

php-fpm
