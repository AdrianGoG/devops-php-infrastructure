#!/bin/bash
# Runs the test suite of one application, inside its own container.
#
#   ./run-tests.sh VM3-Application-Server-2/app-crm
#
# The tests run on VM1, before anything is deployed, and on the same PHP version
# the application uses in production - that is the point of running them in the
# application's own container instead of with whatever PHP VM1 happens to have.
#
# Only the mysql service is started; nginx is not needed and its port would
# collide with the other applications that use the same one.

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

# The containers are stopped whatever happens, including when the tests fail or
# the build is aborted. Without this they pile up on VM1 from build to build.
cleanup() {
    docker compose down --remove-orphans > /dev/null 2>&1 || true
}
trap cleanup EXIT

# Which command runs the tests. app-crm has no Composer, app-api has no artisan.
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

# .env is not in the repository, so a fresh checkout has none and the
# application falls back to its built in defaults - which is how app-crm ends up
# trying to reach MySQL without a password.
if [ -f src/.env.example ] && [ ! -f src/.env ]; then
    echo "--- $APP_NAME: creating .env from .env.example"
    cp src/.env.example src/.env
fi

echo "--- $APP_NAME: installing the dependencies"
if [ -f src/composer.json ]; then
    docker compose run --rm php composer install --no-interaction --no-progress
fi

# A Laravel application refuses to boot without an application key, and the one
# in .env.example is deliberately empty.
if [ -f src/artisan ]; then
    echo "--- $APP_NAME: generating the application key"
    docker compose run --rm php php artisan key:generate --force > /dev/null
fi

echo "--- $APP_NAME: running the tests"
# DB_HOST and DB_PORT are passed here because the test configuration points at
# 127.0.0.1 and the published port, which is right from the host but wrong from
# inside the container, where the database is simply "mysql".
set +e
docker compose run --rm \
    -e DB_HOST=mysql \
    -e DB_PORT=3306 \
    php $TEST_CMD
RESULT=$?
set -e

exit $RESULT