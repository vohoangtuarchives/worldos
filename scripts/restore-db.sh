#!/bin/bash

if [ -z "$1" ]; then
    echo "Please provide the backup file name"
    echo "Usage: ./restore-db.sh backup_filename.sql"
    exit 1
fi

BACKUP_FILE="../docker/postgres/backups/$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "Backup file not found: $BACKUP_FILE"
    exit 1
fi

# Restore the backup
docker exec -i postgres_db psql -U laravel_user laravel < "$BACKUP_FILE"

echo "Database restored from: $1"
