#!/bin/sh
set -e

# First arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ]; then
    # Install dependencies
    if [ -f composer.json ]; then
        composer install --no-interaction --optimize-autoloader
    fi

    # Wait for database to be ready
    until nc -z -v -w30 database 3306; do
        echo "Waiting for database connection..."
        sleep 1
    done

    # Run migrations
    if [ -f bin/console ]; then
        bin/console doctrine:database:create --if-not-exists --no-interaction
        bin/console doctrine:migrations:migrate --no-interaction
        bin/console cache:clear
    fi
fi

exec "$@"