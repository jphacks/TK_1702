#!/bin/bash

cd "$(dirname "$0")/.."
source tools/common.sh

test -d build/generated-classes || exec_cmd mkdir -p build/generated-classes

exec_cmd chmod 777 static/thumb/ static/video/

exec_cmd sed -e "s/{{env}}/${CHAT_ENV}/" config/env.php.in >config/env.php
exec_cmd php composer.phar install $(env_if dev '' '--no-dev')
exec_cmd vendor/bin/propel model:build
exec_cmd vendor/bin/propel config:convert
exec_cmd php composer.phar dump-autoload $(env_if dev '' '-a')

print_message 'done.'
