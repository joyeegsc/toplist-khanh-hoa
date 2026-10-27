<?php
/**
 * Article helpers: table of contents from H2 headings, reading time, share links, map embed.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds ids to <h2> headings in rendered content and returns [content, headings].
 * Headings: list of [id, text].
 */
function tkh_toc( $content ) {
	$headings = array();
	$used     = array();
	$content  = preg_replace_callback(
		'#<h2(\s[^>]*)?>(.*?)</h2>#is',
		function ( $m ) use ( &$headings, &$used ) {
			$attrs = isset( $m[1] ) ? $m[1] : '';
			$text  = trim( wp_strip_all_tags( $m[2] ) );
			if ( preg_match( '#\sid=["\']([^"\']+)["\']#', $attrs, $id_match ) ) {
				$id = $id_match[1];
			} else {
				$id = sanitize_title( remove_accents( $text ) );
				$id = $id ? $id : 'muc';
				$n  = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = preg_replace( '/-\d+$/', '', $id ) . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			$headings[]  = array( $id, $text );
			return '<h2' . $attrs . ' class="scroll-mt-28">' . $m[2] . '</h2>';
		},
		$content
	);
	return array( $content, $headings );
}

/**
 * Estimated reading time in minutes (~200 words per minute).
 */
function tkh_reading_minutes( $post_id = null ) {
	$words = str_word_count( remove_accents( wp_strip_all_tags( get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() ) ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Share targets for the current post: [label, url, icon].
 */
function tkh_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
	return array(
		array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		array( 'X', 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url ),
	);
}

/**
 * Google Maps embed URL (no API key) for the place's name and address.
 */
function tkh_map_embed_url( $query ) {
	return 'https://maps.google.com/maps?q=' . rawurlencode( $query ) . '&z=15&output=embed&hl=' . ( 'en' === tkh_lang() ? 'en' : 'vi' );
}
