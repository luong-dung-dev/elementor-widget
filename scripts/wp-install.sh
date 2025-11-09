#!/usr/bin/env bash
set -euo pipefail

# Load variables from .env if present
if [ -f ./.env ]; then
  export $(grep -v '^#' ./.env | xargs)
fi

docker compose run --rm wpcli \
  wp core install \
  --url="${WP_SITE_URL:-http://localhost:8080}" \
  --title="${WP_SITE_TITLE:-My WordPress}" \
  --admin_user="${WP_ADMIN_USER:-admin}" \
  --admin_password="${WP_ADMIN_PASSWORD:-admin123}" \
  --admin_email="${WP_ADMIN_EMAIL:-admin@example.com}"

echo "✅ Installation complete. Login: ${WP_SITE_URL:-http://localhost:8080}/wp-admin"


