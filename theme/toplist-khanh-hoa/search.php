<?php
/**
 * Search results with full filter console.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$q = get_search_query( false );

get_template_part(
	'parts/listing',
	null,
	array(
		'eyebrow'     => __( 'Tìm kiếm địa điểm', 'toplist-khanh-hoa' ),
		'icon'        => 'search',
		/* translators: %s: search keywords */
		'title'       => '' !== $q ? sprintf( __( 'Kết quả cho “%s”', 'toplist-khanh-hoa' ), $q ) : __( 'Tìm địa điểm ở Khánh Hòa', 'toplist-khanh-hoa' ),
		'desc'        => __( 'Lọc theo danh mục, khu vực, điểm đánh giá và mức giá để chọn nơi phù hợp cho chuyến đi.', 'toplist-khanh-hoa' ),
		'crumbs'      => array( array( __( 'Tìm kiếm', 'toplist-khanh-hoa' ), null ) ),
		'show_search' => true,
	)
);

get_footer();
