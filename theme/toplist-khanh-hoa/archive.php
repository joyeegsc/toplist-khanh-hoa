<?php
/**
 * Category, region (khu_vuc), tag and other archives.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$term = tkh_archive_term();
$args = array(
	'eyebrow' => __( 'Cẩm nang Khánh Hòa', 'toplist-khanh-hoa' ),
	'icon'    => 'travel_explore',
	'title'   => wp_strip_all_tags( get_the_archive_title() ),
	'desc'    => '',
	'crumbs'  => array( array( wp_strip_all_tags( get_the_archive_title() ), null ) ),
	'term'    => $term,
);

if ( $term ) {
	$args['desc']   = wp_strip_all_tags( $term->description );
	$args['crumbs'] = array( array( $term->name, null ) );
	$args['title']  = tkh_archive_heading( $term );
	if ( 'category' === $term->taxonomy ) {
		$args['icon']    = tkh_category_info( $term )['icon'];
		$args['eyebrow'] = __( 'Chuyên mục', 'toplist-khanh-hoa' );
	} elseif ( 'khu_vuc' === $term->taxonomy ) {
		$args['icon']    = 'location_on';
		$args['eyebrow'] = __( 'Khám phá theo khu vực', 'toplist-khanh-hoa' );
	} else {
		$args['icon']    = 'sell';
		$args['eyebrow'] = __( 'Chủ đề', 'toplist-khanh-hoa' );
	}
}

get_template_part( 'parts/listing', null, $args );

get_footer();
