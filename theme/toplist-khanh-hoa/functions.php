<?php
/**
 * Toplist Khánh Hòa theme bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'TKH_DIR', get_template_directory() );
define( 'TKH_URI', get_template_directory_uri() );

require TKH_DIR . '/inc/i18n.php';
require TKH_DIR . '/inc/setup.php';
require TKH_DIR . '/inc/place-meta.php';
require TKH_DIR . '/inc/query-filters.php';
require TKH_DIR . '/inc/newsletter.php';
require TKH_DIR . '/inc/template-tags.php';
require TKH_DIR . '/inc/article-tools.php';
require TKH_DIR . '/inc/seo.php';
require TKH_DIR . '/inc/demo-import.php';
