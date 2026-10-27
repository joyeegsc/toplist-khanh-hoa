<?php
/**
 * Listing filters for archives and search: minimum rating, price level, sort order.
 * Category (category_name), region (khu_vuc) and tag (tag) use WordPress' native query vars.
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'query_vars',
	function ( $vars ) {
		return array_merge( $vars, array( 'rating', 'price_level', 'sort' ) );
	}
);

function tkh_sort_options() {
	return array(
		''       => __( 'Phổ biến nhất', 'toplist-khanh-hoa' ),
		'rating' => __( 'Đánh giá cao nhất', 'toplist-khanh-hoa' ),
		'newest' => __( 'Mới cập nhật', 'toplist-khanh-hoa' ),
	);
}

function tkh_rating_options() {
	return array(
		''    => __( 'Mọi đánh giá', 'toplist-khanh-hoa' ),
		'4.5' => __( 'Từ 4.5 ★', 'toplist-khanh-hoa' ),
		'4'   => __( 'Từ 4.0 ★', 'toplist-khanh-hoa' ),
		'3'   => __( 'Từ 3.0 ★', 'toplist-khanh-hoa' ),
	);
}

/**
 * True when the listing is narrowed by filters/sort — such URLs are kept out of the index.
 */
function tkh_is_filtered_listing() {
	foreach ( array( 'rating', 'price_level', 'sort' ) as $var ) {
		if ( '' !== (string) get_query_var( $var ) ) {
			return true;
		}
	}
	$base  = tkh_archive_base();
	$extra = array( 'category' => array( 'khu_vuc', 'tag' ), 'khu_vuc' => array( 'category_name', 'tag' ), 'post_tag' => array( 'khu_vuc', 'category_name' ) );
	foreach ( isset( $extra[ $base ] ) ? $extra[ $base ] : array() as $var ) {
		if ( '' !== (string) get_query_var( $var ) ) {
			return true;
		}
	}
	return false;
}

add_action(
	'pre_get_posts',
	function ( WP_Query $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( ! ( $query->is_archive() || $query->is_search() ) ) {
			return;
		}

		if ( $query->is_search() ) {
			$query->set( 'post_type', 'post' );
		}
		$query->set( 'posts_per_page', 12 );

		$meta_query = array();

		$rating = (string) $query->get( 'rating' );
		if ( isset( tkh_rating_options()[ $rating ] ) && '' !== $rating ) {
			$meta_query[] = array(
				'key'     => '_tkh_rating',
				'value'   => (float) $rating,
				'compare' => '>=',
				'type'    => 'DECIMAL(3,1)',
			);
		}

		$level = (string) $query->get( 'price_level' );
		if ( isset( tkh_price_levels()[ $level ] ) ) {
			$meta_query[] = array(
				'key'   => '_tkh_price_level',
				'value' => $level,
			);
		}

		if ( $meta_query ) {
			$query->set( 'meta_query', $meta_query );
		}

		// Default order is "most popular" (review count); "reviews" is kept for older links.
		$sort = (string) $query->get( 'sort' );
		if ( 'rating' === $sort ) {
			$query->set( 'meta_key', '_tkh_rating' );
			$query->set( 'orderby', array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ) );
		} elseif ( 'newest' !== $sort && ! ( $query->is_search() && '' !== $query->get( 's' ) && '' === $sort ) ) {
			$query->set( 'meta_key', '_tkh_review_count' );
			$query->set( 'orderby', array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ) );
		}
	}
);

/**
 * Active filters as removable chips: [label, url without that filter].
 * The page's own taxonomy (e.g. the category of a category archive) is not a filter.
 */
function tkh_active_filters() {
	$filters = tkh_current_filters();
	$base    = tkh_archive_base();
	$items   = array();
	$remove  = function ( $key ) {
		$url = preg_replace( '#/page/\d+/?#', '/', remove_query_arg( array( $key, 'paged' ) ) );
		return 's' === $key ? add_query_arg( 's', '', $url ) : $url;
	};
	if ( '' !== $filters['s'] ) {
		$items[] = array( '“' . $filters['s'] . '”', $remove( 's' ) );
	}
	$terms = array( 'category_name' => 'category', 'khu_vuc' => 'khu_vuc', 'tag' => 'post_tag' );
	foreach ( $terms as $key => $taxonomy ) {
		if ( '' !== $filters[ $key ] && $taxonomy !== $base ) {
			$term    = get_term_by( 'slug', basename( $filters[ $key ] ), $taxonomy );
			$items[] = array( $term ? $term->name : $filters[ $key ], $remove( $key ) );
		}
	}
	$labels = array(
		'rating'      => tkh_rating_options(),
		'price_level' => tkh_price_levels(),
		'sort'        => tkh_sort_options(),
	);
	foreach ( $labels as $key => $options ) {
		if ( '' !== $filters[ $key ] && isset( $options[ $filters[ $key ] ] ) ) {
			$items[] = array( $options[ $filters[ $key ] ], $remove( $key ) );
		}
	}
	return $items;
}

/**
 * Current listing filter values, read from the main query.
 */
function tkh_current_filters() {
	return array(
		's'             => get_search_query( false ),
		'category_name' => (string) get_query_var( 'category_name' ),
		'khu_vuc'       => (string) get_query_var( 'khu_vuc' ),
		'tag'           => (string) get_query_var( 'tag' ),
		'rating'        => (string) get_query_var( 'rating' ),
		'price_level'   => (string) get_query_var( 'price_level' ),
		'sort'          => (string) get_query_var( 'sort' ),
	);
}

/**
 * Which taxonomy the current archive URL belongs to ('category', 'post_tag' or 'khu_vuc').
 * A category archive filtered by ?khu_vuc= (or the reverse) sets both conditionals, so the
 * permalink path decides which one is the page itself and which one is a filter.
 */
function tkh_archive_base() {
	global $wp;
	// A search narrowed by ?category_name= also sets is_category(); it is still the search page.
	if ( is_search() ) {
		return '';
	}
	$request = (string) $wp->request;
	if ( tkh_has_pll() ) {
		// Strip the language directory (e.g. "en/") that Polylang prefixes to URLs.
		$request = preg_replace( '#^' . preg_quote( tkh_lang(), '#' ) . '(/|$)#', '', $request );
	}
	$bases = array(
		'khu_vuc'  => get_taxonomy( 'khu_vuc' )->rewrite['slug'],
		'category' => get_option( 'category_base' ) ? get_option( 'category_base' ) : 'category',
		'post_tag' => get_option( 'tag_base' ) ? get_option( 'tag_base' ) : 'tag',
	);
	foreach ( $bases as $taxonomy => $base ) {
		if ( 0 === strpos( $request . '/', trim( $base, '/' ) . '/' ) ) {
			return $taxonomy;
		}
	}
	if ( is_category() ) {
		return 'category';
	}
	if ( is_tag() ) {
		return 'post_tag';
	}
	return is_tax( 'khu_vuc' ) ? 'khu_vuc' : '';
}

/**
 * The term the current archive URL belongs to (see tkh_archive_base()).
 */
function tkh_archive_term() {
	$vars = array( 'category' => 'category_name', 'post_tag' => 'tag', 'khu_vuc' => 'khu_vuc' );
	$base = tkh_archive_base();
	if ( ! $base ) {
		return null;
	}
	$slug = basename( (string) get_query_var( $vars[ $base ] ) );
	$term = $slug ? get_term_by( 'slug', $slug, $base ) : null;
	return $term instanceof WP_Term ? $term : null;
}

/**
 * Heading/title for an archive term: "Ẩm Thực Khánh Hòa", "Ăn gì, chơi đâu ở Nha Trang", tag name.
 */
function tkh_archive_heading( WP_Term $term ) {
	if ( 'category' === $term->taxonomy ) {
		/* translators: %s: category name */
		return sprintf( __( '%s Khánh Hòa', 'toplist-khanh-hoa' ), $term->name );
	}
	if ( 'khu_vuc' === $term->taxonomy ) {
		/* translators: %s: region name */
		return sprintf( __( 'Ăn gì, chơi đâu ở %s', 'toplist-khanh-hoa' ), $term->name );
	}
	return $term->name;
}

/**
 * URL for the search listing with the given filters (empty values dropped).
 */
function tkh_search_url( array $args = array() ) {
	$args = array_merge( array( 's' => '' ), array_filter( $args, 'strlen' ) );
	return add_query_arg( array_map( 'rawurlencode', $args ), tkh_home_url() );
}
