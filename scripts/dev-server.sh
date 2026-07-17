#!/bin/zsh
# Start FIT-FLIPPED dev server (PHP 8.5) when XAMPP mod_php is too old.
set -e
ROOT="/Applications/XAMPP/xamppfiles/htdocs/fitcoch"
cd "$ROOT"
brew services start php >/dev/null 2>&1 || true
echo "FIT-FLIPPED dev server: http://127.0.0.1:8080"
echo "Press Ctrl+C to stop."
exec /opt/homebrew/bin/php -S 127.0.0.1:8080 -t public public/index.php
