<?php
/**
 * Language helpers. Polylang handles VI/EN content; these helpers resolve the theme's fixed
 * references (category, region, tag and page slugs in Vietnamese, the default language) to the
 * matching translation in the current language. Without Polylang they fall back to Vietnamese.
 */

defined( 'ABSPATH' ) || exit;

// Make the region taxonomy translatable in Polylang without a manual settings step.
add_filter(
	'pll_get_taxonomies',
	function ( $taxonomies, $is_settings ) {
		if ( ! $is_settings ) {
			$taxonomies['khu_vuc'] = 'khu_vuc';
		}
		return $taxonomies;
	},
	10,
	2
);

function tkh_has_pll() {
	return function_exists( 'pll_current_language' ) && function_exists( 'pll_get_term' );
}

function tkh_lang() {
	if ( ! tkh_has_pll() ) {
		return 'vi';
	}
	$lang = pll_current_language();
	return $lang ? $lang : pll_default_language();
}

function tkh_home_url() {
	return function_exists( 'pll_home_url' ) ? pll_home_url( tkh_lang() ) : home_url( '/' );
}

/**
 * Term identified by its default-language (Vietnamese) slug, translated to the current language.
 */
function tkh_term( $slug, $taxonomy ) {
	static $cache = array();
	$key = $taxonomy . ':' . $slug . ':' . tkh_lang();
	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}
	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'slug' => $slug, 'hide_empty' => false, 'lang' => '' ) );
	$term  = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : null;
	if ( $term && tkh_has_pll() ) {
		$translated = pll_get_term( $term->term_id, tkh_lang() );
		if ( $translated ) {
			$term = get_term( $translated, $taxonomy );
		}
	}
	$cache[ $key ] = $term instanceof WP_Term ? $term : null;
	return $cache[ $key ];
}

/**
 * Default-language slug of a term — the key used for icons and other fixed lookups.
 */
function tkh_term_key( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	if ( tkh_has_pll() ) {
		$default = pll_get_term( $term->term_id, pll_default_language() );
		if ( $default && (int) $default !== (int) $term->term_id ) {
			$source = get_term( $default, $term->taxonomy );
			return $source instanceof WP_Term ? $source->slug : $term->slug;
		}
	}
	return $term->slug;
}

/**
 * Permalink of a page (identified by its Vietnamese slug) in the current language.
 */
function tkh_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		return '';
	}
	$id = $page->ID;
	if ( tkh_has_pll() && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id, tkh_lang() );
		$id         = $translated ? $translated : $id;
	}
	return get_permalink( $id );
}

/**
 * Language switcher items: [slug, label, url, is_current, has_translation].
 */
function tkh_languages() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return array();
	}
	$items = array();
	foreach ( (array) pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0 ) ) as $lang ) {
		$items[] = array(
			'slug'    => $lang['slug'],
			'label'   => 'vi' === $lang['slug'] ? 'VN' : strtoupper( $lang['slug'] ),
			'name'    => $lang['name'],
			'url'     => $lang['url'],
			'current' => ! empty( $lang['current_lang'] ),
			'missing' => ! empty( $lang['no_translation'] ),
			'locale'  => isset( $lang['locale'] ) ? $lang['locale'] : '',
		);
	}
	return $items;
}

/**
 * Locale string for dates and Open Graph (vi_VN / en_US).
 */
function tkh_locale() {
	return 'en' === tkh_lang() ? 'en_US' : 'vi_VN';
}
