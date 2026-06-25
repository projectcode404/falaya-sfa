#!/bin/bash
set -e

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/codeivan/falaya-sfa/backups"
BACKUP_FILE="${BACKUP_DIR}/falaya_db_${TIMESTAMP}.sql.gz"
RETAIN_DAYS=7

mkdir -p "$BACKUP_DIR"

echo "[$(date)] Starting backup..."

# Backup via docker exec (pg_dump tidak ada di host)
docker exec postgres_db bash -c "
PGPASSWORD='$(grep "^DB_PASSWORD" /home/codeivan/falaya-sfa/src/.env | cut -d'=' -f2)' \
pg_dump -U falaya_user -d falaya_db \
    --no-owner --no-acl --format=plain \
    | gzip
" > "$BACKUP_FILE"

SIZE=$(du -sh "$BACKUP_FILE" | cut -f1)
echo "[$(date)] Backup complete: ${BACKUP_FILE} (${SIZE})"

# Hapus backup lebih dari RETAIN_DAYS hari
find "$BACKUP_DIR" -name "falaya_db_*.sql.gz" -mtime +${RETAIN_DAYS} -delete
echo "[$(date)] Old backups cleaned up (retain ${RETAIN_DAYS} days)"
