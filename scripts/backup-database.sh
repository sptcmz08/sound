#!/usr/bin/env bash
set -euo pipefail
PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$PROJECT_DIR/storage/app/backups}"
mkdir -p "$BACKUP_DIR"
set -a
# shellcheck disable=SC1091
source "$PROJECT_DIR/.env"
set +a
STAMP="$(date +%Y%m%d-%H%M%S)"
MYSQL_PWD="${DB_PASSWORD:-}" mysqldump --single-transaction --quick --routines --triggers -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$BACKUP_DIR/database-$STAMP.sql.gz"
find "$BACKUP_DIR" -type f -name 'database-*.sql.gz' -mtime +"${BACKUP_RETENTION_DAYS:-14}" -delete
echo "Backup created: $BACKUP_DIR/database-$STAMP.sql.gz"
