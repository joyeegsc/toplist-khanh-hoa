<?php
/**
 * Fallback template.
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'parts/listing',
	null,
	array(
		'eyebrow' => __( 'Cẩm nang Khánh Hòa', 'toplist-khanh-hoa' ),
		'icon'    => 'travel_explore',
		'title'   => __( 'Tất cả địa điểm', 'toplist-khanh-hoa' ),
		'desc'    => '',
		'crumbs'  => array( array( __( 'Tất cả địa điểm', 'toplist-khanh-hoa' ), null ) ),
	)
);

get_footer();
