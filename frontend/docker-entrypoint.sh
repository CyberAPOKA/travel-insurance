#!/bin/sh
set -e

LOCK_HASH_FILE=node_modules/.package-lock.hash

if [ ! -d node_modules/.bin ] || [ ! -f "$LOCK_HASH_FILE" ] || ! cmp -s package-lock.json "$LOCK_HASH_FILE" 2>/dev/null; then
  npm ci
  cp package-lock.json "$LOCK_HASH_FILE"
fi

exec "$@"
