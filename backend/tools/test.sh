#!/bin/bash

cd "$(dirname "$0")/.."
source tools/common.sh

test -d build/generated-classes || exec_cmd mkdir -p build/generated-classes

export CHAT_DB_SUFFIX='_test'
exec_cmd php composer.phar install
exec_cmd sed -e "s/{{env}}/${CHAT_ENV}/" config/env.php.in >config/env.php
exec_cmd vendor/bin/propel config:convert --output-file=config-test.php
exec_cmd vendor/bin/propel sql:build
exec_cmd vendor/bin/propel sql:insert

exec_cmd vendor/bin/phpunit
