#!/usr/bin/env bash
# Container entrypoint for Render.
#  1. Copy WordPress (with theme + plugins baked into the image) into the web root.
#  2. Restore the SQLite database and uploads from Supabase Storage, when configured.
#  3. On an empty database: install WordPress, activate Polylang + theme, import bilingual demo data.
#  4. Start Apache on $PORT; back up to Supabase every BACKUP_INTERVAL seconds when data changed,
#     every 6 hours regardless (keeps the free Supabase project active), and once more on shutdown.
set -euo pipefail

WEB=/var/www/html
DB_DIR="$WEB/wp-content/database"
DB="$DB_DIR/wordpress.sqlite"
PORT="${PORT:-10000}"
BUCKET="${SUPABASE_BUCKET:-wordpress-backup}"
BACKUP_INTERVAL="${BACKUP_INTERVAL:-600}"
SITE_URL="${WP_HOME:-${RENDER_EXTERNAL_URL:-http://localhost:$PORT}}"

log() { echo "[tkh-start] $*"; }
wpcli() { wp --path="$WEB" --allow-root "$@"; }

# --- 1. Code -------------------------------------------------------------------------------
cp -a /usr/src/wordpress/. "$WEB/"
mkdir -p "$DB_DIR" "$WEB/wp-content/uploads"
# Pretty permalinks (/category/..., /en/...) need the standard WordPress rewrite rules.
if [ ! -f "$WEB/.htaccess" ]; then
  cat > "$WEB/.htaccess" <<'EOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
EOF
fi

# --- 2. Restore ----------------------------------------------------------------------------
supabase_enabled() { [ -n "${SUPABASE_URL:-}" ] && [ -n "${SUPABASE_SERVICE_KEY:-}" ]; }

supabase() { # supabase <curl args...>  — adds auth headers for the Storage API
  local auth=(-H "apikey: $SUPABASE_SERVICE_KEY")
  # Legacy service_role keys are JWTs and also go in Authorization; new sb_secret_ keys must not.
  if [[ "$SUPABASE_SERVICE_KEY" != sb_secret_* ]]; then auth+=(-H "Authorization: Bearer $SUPABASE_SERVICE_KEY"); fi
  curl -fsS --retry 3 --max-time 120 "${auth[@]}" "$@"
}
object_url() { echo "${SUPABASE_URL%/}/storage/v1/object/$BUCKET/$1"; }

if supabase_enabled; then
  # Create the private bucket on first run (an "already exists" error is fine).
  supabase -X POST -H "Content-Type: application/json" \
    -d "{\"id\":\"$BUCKET\",\"name\":\"$BUCKET\",\"public\":false}" \
    "${SUPABASE_URL%/}/storage/v1/bucket" >/dev/null 2>&1 || true

  if supabase -o /tmp/restore.sqlite "$(object_url wordpress.sqlite)" 2>/dev/null; then
    mv /tmp/restore.sqlite "$DB"
    log "database restored from Supabase Storage"
  else
    log "no database backup in Supabase yet — starting fresh"
  fi
  if supabase -o /tmp/uploads.tar.gz "$(object_url uploads.tar.gz)" 2>/dev/null; then
    tar -xzf /tmp/uploads.tar.gz -C "$WEB/wp-content" && rm -f /tmp/uploads.tar.gz
    log "uploads restored"
  fi
else
  log "SUPABASE_URL / SUPABASE_SERVICE_KEY not set — data lives only until the next restart"
fi

# --- 3. Install on first boot ---------------------------------------------------------------
if ! wpcli core is-installed >/dev/null 2>&1; then
  log "installing WordPress at $SITE_URL"
  wpcli core install \
    --url="$SITE_URL" \
    --title="Toplist Khánh Hòa" \
    --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASSWORD:?Set WP_ADMIN_PASSWORD in Render}" \
    --admin_email="${WP_ADMIN_EMAIL:-admin@example.com}" \
    --skip-email
  wpcli plugin activate polylang
  wpcli theme activate toplist-khanh-hoa
  wpcli language core install vi || log "could not download Vietnamese core translations (continuing)"
  wpcli tkh demo-import
  [ -n "${WP_CONTACT_EMAIL:-}" ] && wpcli theme mod set tkh_contact_email "$WP_CONTACT_EMAIL"
fi
wpcli rewrite flush >/dev/null 2>&1 || true
chown -R www-data:www-data "$WEB/wp-content"

# --- 4. Serve + back up ---------------------------------------------------------------------
backup() {
  supabase_enabled && [ -f "$DB" ] || return 0
  sqlite3 "$DB" ".backup /tmp/backup.sqlite"
  supabase -X POST -H "x-upsert: true" -H "Content-Type: application/octet-stream" \
    --data-binary @/tmp/backup.sqlite "$(object_url wordpress.sqlite)" >/dev/null
  tar -czf /tmp/uploads-backup.tar.gz -C "$WEB/wp-content" uploads
  supabase -X POST -H "x-upsert: true" -H "Content-Type: application/gzip" \
    --data-binary @/tmp/uploads-backup.tar.gz "$(object_url uploads.tar.gz)" >/dev/null
  rm -f /tmp/backup.sqlite /tmp/uploads-backup.tar.gz
  log "backup uploaded to Supabase Storage ($BUCKET)"
}

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

apache2-foreground &
APACHE_PID=$!

if supabase_enabled; then
  backup || log "initial backup failed"
  (
    last_state=""
    elapsed=0
    while sleep "$BACKUP_INTERVAL"; do
      elapsed=$((elapsed + BACKUP_INTERVAL))
      state="$(stat -c %Y "$DB" 2>/dev/null)-$(find "$WEB/wp-content/uploads" -type f -newer "$DB" 2>/dev/null | wc -l)"
      if [ "$state" != "$last_state" ] || [ "$elapsed" -ge 21600 ]; then
        backup || log "periodic backup failed"
        last_state="$state"
        elapsed=0
      fi
    done
  ) &
  BACKUP_PID=$!
fi

shutdown() {
  log "shutting down"
  [ -n "${BACKUP_PID:-}" ] && kill "$BACKUP_PID" 2>/dev/null || true
  backup || log "final backup failed"
  kill -TERM "$APACHE_PID" 2>/dev/null || true
  wait "$APACHE_PID" || true
  exit 0
}
trap shutdown TERM INT

wait "$APACHE_PID"
