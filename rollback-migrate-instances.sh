#!/bin/sh

set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
TARGETS="
../ebfakturka1
../ebfakturka2
../ebfakturka3
../ebfakturka4
"

if ! command -v php >/dev/null 2>&1; then
  echo "Brak polecenia php w PATH." >&2
  exit 1
fi

for target in $TARGETS; do
  TARGET_DIR=$(CDPATH= cd -- "${SCRIPT_DIR}/${target}" 2>/dev/null && pwd) || {
    echo "Katalog docelowy nie istnieje: ${target}" >&2
    exit 1
  }

  if [ ! -f "${TARGET_DIR}/artisan" ]; then
    echo "Brak pliku artisan w ${TARGET_DIR} - pomijam." >&2
    continue
  fi

  echo "Rollback ostatniej migracji w ${TARGET_DIR}"
  (cd "${TARGET_DIR}" && php artisan migrate:rollback --step=1 --force)

  echo "Migracje w ${TARGET_DIR}"
  (cd "${TARGET_DIR}" && php artisan migrate --force)

done

echo "Gotowe."
