# WordPress + Toplist Khánh Hòa theme for Render (free web service, Docker runtime).
# Database: SQLite (official "SQLite Database Integration" drop-in), backed up to Supabase Storage
# when SUPABASE_URL / SUPABASE_SERVICE_KEY are set. See docs/deploy-render-supabase.md.

# --- Stage 1: build theme assets (Tailwind CSS, bilingual demo data, English translations) ---
FROM node:22-alpine AS assets
WORKDIR /build
COPY package.json tailwind.config.js ./
COPY src ./src
COPY content ./content
COPY scripts ./scripts
COPY theme ./theme
RUN npm install --no-save --no-package-lock --no-audit --no-fund tailwindcss@3.4.17 \
 && node scripts/build-content.js \
 && npx tailwindcss -i src/styles.css -o theme/toplist-khanh-hoa/assets/css/main.css --minify

# --- Stage 2: WordPress runtime ---
FROM wordpress:php8.3-apache

ARG WP_CONTENT=/usr/src/wordpress/wp-content
ARG SQLITE_PLUGIN=/var/www/html/wp-content/plugins/sqlite-database-integration

RUN apt-get update \
 && apt-get install -y --no-install-recommends sqlite3 unzip less \
 && rm -rf /var/lib/apt/lists/* \
 && curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
 && chmod +x /usr/local/bin/wp \
 && curl -fsSL -o /tmp/polylang.zip https://downloads.wordpress.org/plugin/polylang.latest-stable.zip \
 && curl -fsSL -o /tmp/sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip \
 && unzip -q /tmp/polylang.zip -d "$WP_CONTENT/plugins/" \
 && unzip -q /tmp/sqlite.zip -d "$WP_CONTENT/plugins/" \
 && sed -e "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#${SQLITE_PLUGIN}#" -e "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
      "$WP_CONTENT/plugins/sqlite-database-integration/db.copy" > "$WP_CONTENT/db.php" \
 && rm -f /tmp/*.zip \
 && a2enmod rewrite expires headers

COPY --from=assets /build/theme/toplist-khanh-hoa "$WP_CONTENT/themes/toplist-khanh-hoa"
COPY docker/wp-config.php /usr/src/wordpress/wp-config.php
COPY docker/apache-performance.conf /etc/apache2/conf-enabled/tkh-performance.conf
COPY docker/start.sh /usr/local/bin/tkh-start
RUN sed -i 's/\r$//' /usr/local/bin/tkh-start && chmod +x /usr/local/bin/tkh-start

ENV PORT=10000
EXPOSE 10000
ENTRYPOINT []
CMD ["tkh-start"]
