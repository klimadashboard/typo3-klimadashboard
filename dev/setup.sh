#!/usr/bin/env bash
# Sets up the local TYPO3 demo for the Klimadashboard extension with DDEV.
#
#   dev/setup.sh        TYPO3 14.3 (default)
#   dev/setup.sh 13     TYPO3 13.4
#   dev/setup.sh 12     TYPO3 12.4
#   dev/setup.sh 11     TYPO3 11.5
#
# All versions share one DDEV project; switching rewrites .ddev/config.local.yaml and
# resets the database. Re-running resets the admin user and demo content.
set -euo pipefail
cd "$(dirname "$0")/.."

VERSION="${1:-14}"
case "$VERSION" in
	14) PHP_VERSION=8.4 ;;
	13) PHP_VERSION=8.3 ;;
	12) PHP_VERSION=8.2 ;;
	11) PHP_VERSION=8.1 ;;
	*) echo "Usage: $0 [11|12|13|14]" >&2; exit 1 ;;
esac
ROOT="dev/v$VERSION"
CONTAINER_ROOT="/var/www/html/$ROOT"

ENV_FILE=dev/.env.local
if [ ! -f "$ENV_FILE" ]; then
	# Local test credentials only — never reuse them anywhere else
	PASSWORD="Kd-$(openssl rand -hex 12)-X1"
	printf 'TYPO3_ADMIN_USERNAME=admin\nTYPO3_ADMIN_PASSWORD=%s\n' "$PASSWORD" > "$ENV_FILE"
	chmod 600 "$ENV_FILE"
fi
# shellcheck disable=SC1090
source "$ENV_FILE"

cat > .ddev/config.local.yaml <<YAML
# Written by dev/setup.sh — TYPO3 $VERSION
composer_root: $ROOT
docroot: $ROOT/public
php_version: "$PHP_VERSION"
YAML

ddev restart
ddev composer install --no-interaction
ddev mysql -e 'DROP DATABASE IF EXISTS db; CREATE DATABASE db'

if [ "$VERSION" = 11 ]; then
	ddev exec --dir "$CONTAINER_ROOT" vendor/bin/typo3cms install:setup --no-interaction --force \
		--database-driver=mysqli --database-host-name=db --database-port=3306 --database-name=db \
		--database-user-name=db --database-user-password=db --use-existing-database \
		--admin-user-name="$TYPO3_ADMIN_USERNAME" --admin-password="$TYPO3_ADMIN_PASSWORD" \
		--site-name="Klimadashboard TYPO3 Demo (v11)" --site-setup-type=no --web-server-config=apache
	ddev exec --dir "$CONTAINER_ROOT" vendor/bin/typo3cms extension:setup
	ddev mysql < dev/demo/content.sql
	ddev mysql < dev/demo/sys_template.sql
	ddev exec --dir "$CONTAINER_ROOT" vendor/bin/typo3cms cache:flush
else
	ddev exec --dir "$CONTAINER_ROOT" vendor/bin/typo3 setup --no-interaction --force \
		--driver=mysqli --host=db --port=3306 --dbname=db --username=db --password=db \
		--admin-username="$TYPO3_ADMIN_USERNAME" --admin-user-password="$TYPO3_ADMIN_PASSWORD" \
		--admin-email=admin@example.com --project-name="Klimadashboard TYPO3 Demo" \
		--server-type=apache
	ddev exec --dir "$CONTAINER_ROOT" vendor/bin/typo3 extension:setup
	ddev mysql < dev/demo/content.sql
	if [ "$VERSION" = 12 ]; then
		# No site sets before TYPO3 v13: include the TypoScript through a template record
		ddev mysql < dev/demo/sys_template.sql
	fi
	ddev exec --dir "$CONTAINER_ROOT" vendor/bin/typo3 cache:flush
fi

URL=$(ddev describe -j | python3 -c 'import json,sys;print(json.load(sys.stdin)["raw"]["primary_url"])')
echo
echo "TYPO3 $VERSION is ready."
echo "Frontend: $URL/"
echo "Backend:  $URL/typo3/"
echo "Login:    see $ENV_FILE"
