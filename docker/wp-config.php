<?php
/**
 * WordPress configuration for the container. Everything environment-specific comes from env vars
 * (set in Render): WP_SALT, optional WP_HOME; RENDER_EXTERNAL_URL is provided by Render itself.
 */

// SQLite via the "SQLite Database Integration" drop-in (wp-content/db.php). DB_* are placeholders.
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', '' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
define( 'DB_FILE', 'wordpress.sqlite' );

// Auth keys and salts derived from one random secret (Render generates WP_SALT).
$tkh_salt = getenv( 'WP_SALT' ) ? getenv( 'WP_SALT' ) : 'change-me-local-only';
foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as $tkh_key ) {
	define( $tkh_key, hash( 'sha256', $tkh_salt . '|' . $tkh_key ) );
}

$table_prefix = 'wp_';

// Public URL: explicit WP_HOME, else the onrender.com URL Render injects.
$tkh_url = getenv( 'WP_HOME' ) ? getenv( 'WP_HOME' ) : ( getenv( 'RENDER_EXTERNAL_URL' ) ? getenv( 'RENDER_EXTERNAL_URL' ) : 'http://localhost:10000' );
define( 'WP_HOME', rtrim( $tkh_url, '/' ) );
define( 'WP_SITEURL', rtrim( $tkh_url, '/' ) );

// Render terminates TLS at its proxy.
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

// The container filesystem is rebuilt on every deploy: code and plugin changes ship through Git
// (Dockerfile), so in-dashboard code editing and auto-updates are off.
define( 'DISALLOW_FILE_EDIT', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_POST_REVISIONS', 5 );
define( 'WP_DEBUG', false );
define( 'WP_ENVIRONMENT_TYPE', 'production' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
