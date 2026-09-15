#!/bin/sh

set -eu

prepare_application() {
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs

    php artisan optimize
}

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
    su-exec www-data php artisan optimize

    if [ "${1:-}" = "supervisord" ]; then
        exec "$@"
    fi

    exec su-exec www-data "$@"
fi

prepare_application
exec "$@"
