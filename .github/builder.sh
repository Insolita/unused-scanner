#!/bin/sh
set -eu

box validate || exit 1
box compile  || exit 1

if [ ! -f "./unused_scanner.phar" ] || [ ! -x "./unused_scanner.phar" ]; then
  (>&2 echo "Phar build failed :-(")
  exit 1
fi