#!/bin/bash

cd "$(dirname "$0")/.."
source tools/common.sh

print_warning 'tools/prepare.sh'
tools/prepare.sh
print_warning 'tools/migrate_db.sh'
tools/migrate_db.sh
