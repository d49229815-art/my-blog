#!/usr/bin/env bash
set -euo pipefail

stamp="${1:?Usage: create-cloud-backup.sh UTC_TIMESTAMP}"
if [[ ! "$stamp" =~ ^[0-9]{8}T[0-9]{6}Z$ ]]; then
  echo "Timestamp must use YYYYMMDDTHHMMSSZ." >&2
  exit 2
fi

backup="/var/backups/typecho/manual-post-python-${stamp}"
if [[ -e "$backup" ]]; then
  echo "Backup already exists: $backup" >&2
  exit 3
fi
install -d -m 0700 -o root -g root "$backup"
umask 077
mariadb-dump --single-transaction --routines --events --databases typecho | gzip -9 > "$backup/database.sql.gz"
tar -czf "$backup/site-code.tar.gz" \
  --exclude='./usr/uploads' \
  --exclude='./var/cache' \
  -C /var/www/typecho .
chmod 0600 "$backup/database.sql.gz" "$backup/site-code.tar.gz"
(cd "$backup" && sha256sum database.sql.gz site-code.tar.gz > SHA256SUMS && sha256sum -c SHA256SUMS)