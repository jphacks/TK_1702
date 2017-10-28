#!/bin/bash

cd "$(dirname "$0")/.."
source tools/common.sh

if test -x vendor/bin/propel; then
	print_warning "WARNING: You want to reset all data."
	print_warning "To continue, press Enter"
	read

	if vendor/bin/propel sql:build; then
		vendor/bin/propel sql:insert && print_message 'done.'
	fi
else
	print_error "Run 'tools/prepare.sh' first."
fi
