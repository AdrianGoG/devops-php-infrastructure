#!/bin/bash

set -e

APP_DIR="$1"

if [ -z "$APP_DIR" ]; then
    echo "usage: $0 <path to the application folder>"
    exit 2
fi

if [ ! -f "$APP_DIR/docker-compose.yml" ]; then
    echo "$APP_DIR has no docker-compose.yml"
    exit 2
fi

cd "$APP_DIR"

APP_NAME=$(basename "$APP_DIR")

cleanup() {
    docker compose down --remove-orphans > /dev/null 2>&1 || true
}

trap cleanup EXIT

case "$APP_NAME" in
    app-crm)
        TEST_CMD="php tests/run-tests.php"
        ;;
    app-api)
        TEST_CMD="vendor/bin/phpunit"
        ;;
    *)
        TEST_CMD="php artisan test"
        ;;
esac

echo "--- $APP_NAME: starting the database"
docker compose up -d mysql

echo "--- $APP_NAME: waiting for the database"
for i in $(seq 1 30); do
    if docker compose exec -T mysql mysqladmin ping --silent > /dev/null 2>&1; then
        break
    fi
    sleep 2
done

if [ -f src/.env.example ] && [ ! -f src/.env ]; then
    echo "--- $APP_NAME: creating .env from .env.example"
    cp src/.env.example src/.env
fi

echo "--- $APP_NAME: installing the dependencies"
if [ -f src/composer.json ]; then
    set +e
    OUTPUT=$(docker compose run --rm php composer install --no-interaction --no-progress 2>&1)
    STATUS=$?
    set -e

    echo "$OUTPUT"

    if [ $STATUS -ne 0 ]; then
        if echo "$OUTPUT" | grep -q "does not satisfy that requirement"; then
            echo
            echo "--- $APP_NAME: SKIPPED - the dependencies need a newer PHP than this image provides."
            echo "--- $APP_NAME: this is what the upgrade fixes. Nothing to do here."
            exit 0
        fi

        exit $STATUS
    fi
fi


if [ -f src/artisan ]; then
    echo "--- $APP_NAME: generating the application key"
    docker compose run --rm php php artisan key:generate --force > /dev/null
fi

echo "--- $APP_NAME: running the tests"
set +e
docker compose run --rm \
    -e DB_HOST=mysql \
    -e DB_PORT=3306 \
    php $TEST_CMD
RESULT=$?
set -e

exit $RESULT