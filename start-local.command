#!/bin/zsh
set -eu
cd "$(dirname "$0")"
export PATH="/opt/homebrew/opt/php@8.3/bin:$PATH"
exec php artisan serve --host=127.0.0.1 --port=8084
