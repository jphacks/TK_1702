#!/bin/bash

cd "$(dirname "$0")/.."
source tools/common.sh

if test -x vendor/bin/propel; then
	if vendor/bin/propel migration:diff; then
		vendor/bin/propel migration:migrate && print_message 'done.'
	else
		print_error "You may exec 'rm -rf db/generated-migrations'"
	fi
else
	print_error "Run 'tools/prepare.sh' first."
fi
