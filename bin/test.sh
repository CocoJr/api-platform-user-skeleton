#!/bin/bash

APP_ENV=test

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

$DIR/build-dev.sh
php $DIR/phpunit 2>/dev/null
