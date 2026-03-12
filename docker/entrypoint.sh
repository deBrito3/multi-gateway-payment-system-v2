#!/bin/bash
set -e

echo "Waiting for MySQL..."
while ! php -r "new PDO('mysql:host=mysql;port=3306;dbname=payment_system', 'root', 'root');" 2>/dev/null; do
    sleep 1
done
echo "MySQL is ready."

php artisan key:generate --no-interaction --force
php artisan migrate --force
php artisan db:seed --force

php artisan serve --host=0.0.0.0 --port=8000
