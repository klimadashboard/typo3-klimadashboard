#!/usr/bin/env bash
# Sets up the local TYPO3 v14 demo for the Klimadashboard extension with DDEV.
# Idempotent: re-running resets the database, admin user and demo content.
set -euo pipefail
cd "$(dirname "$0")/.."

ENV_FILE=dev/.env.local
if [ ! -f "$ENV_FILE" ]; then
	# Local test credentials only — never reuse them anywhere else
	PASSWORD="Kd-$(openssl rand -hex 12)-X1"
	printf 'TYPO3_ADMIN_USERNAME=admin\nTYPO3_ADMIN_PASSWORD=%s\n' "$PASSWORD" > "$ENV_FILE"
	chmod 600 "$ENV_FILE"
fi
# shellcheck disable=SC1090
source "$ENV_FILE"

ddev start
ddev composer install --no-interaction

ddev exec --dir /var/www/html/dev vendor/bin/typo3 setup --no-interaction --force \
	--driver=mysqli --host=db --port=3306 --dbname=db --username=db --password=db \
	--admin-username="$TYPO3_ADMIN_USERNAME" --admin-user-password="$TYPO3_ADMIN_PASSWORD" \
	--admin-email=admin@example.com --project-name="Klimadashboard TYPO3 Demo" \
	--server-type=apache

ddev exec --dir /var/www/html/dev vendor/bin/typo3 extension:setup
ddev mysql < dev/demo/content.sql
ddev exec --dir /var/www/html/dev vendor/bin/typo3 cache:flush

echo
echo "Frontend: $(ddev describe -j | python3 -c 'import json,sys;print(json.load(sys.stdin)["raw"]["primary_url"])')/"
echo "Backend:  $(ddev describe -j | python3 -c 'import json,sys;print(json.load(sys.stdin)["raw"]["primary_url"])')/typo3/"
echo "Login:    see $ENV_FILE"
