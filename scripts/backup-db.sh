#!/bin/bash

# Get current timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="../docker/postgres/backups"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Create the backup
docker exec postgres_db pg_dump -U laravel_user laravel > "$BACKUP_DIR/backup_$TIMESTAMP.sql"

echo "Backup created: backup_$TIMESTAMP.sql"
