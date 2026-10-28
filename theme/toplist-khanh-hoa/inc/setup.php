<?php
/**
 * Theme supports, assets, the "khu_vuc" (region) taxonomy and footer contact settings.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Category default-language slugs shown in the main navigation, in display order.
 * Each entry: slug => [icon, card badge classes, schema.org type of the places in it].
 */
function tkh_nav_categories() {
	return array(
		'am-thuc'  => array( 'icon' => 'restaurant', 'badge' => 'bg-secondary-container text-on-secondary', 'schema' => 'Restaurant' ),
		'du-lich'  => array( 'icon' => 'landscape', 'badge' => 'bg-[#023e8a]/85 text-on-primary', 'schema' => 'TouristAttraction' ),
		'luu-tru'  => array( 'icon' => 'hotel', 'badge' => 'bg-deep-ocean text-on-primary', 'schema' => 'Hotel' ),
		'giai-tri' => array( 'icon' => 'attractions', 'badge' => 'bg-tertiary-container text-on-tertiary', 'schema' => 'EntertainmentBusiness' ),
		'cafe'     => array( 'icon' => 'local_cafe', 'badge' => 'bg-primary text-on-primary', 'schema' => 'CafeOrCoffeeShop' ),
	);
}

/**
 * Every Material Symbols icon the theme renders. The font request is limited to these
 * (Google Fonts "icon_names"), which cuts the icon font from megabytes to a few kilobytes.
 */
function tkh_icon_names() {
	return array(
		'arrow_back', 'arrow_forward', 'arrow_upward', 'attractions', 'auto_awesome', 'beach_access', 'call', 'category', 'check',
		'chevron_left', 'chevron_right', 'close', 'edit_note', 'expand_more', 'explore', 'favorite', 'filter_alt_off', 'forum', 'send',
		'flight_takeoff', 'hotel', 'landscape', 'lightbulb', 'link', 'local_cafe', 'local_fire_department',
		'location_on', 'mail', 'map', 'menu', 'near_me', 'payments', 'pin_drop', 'restaurant', 'restaurant_menu',
		'reviews', 'rss_feed', 'sailing', 'savings', 'schedule', 'search', 'sell', 'share', 'sort', 'star',
		'temple_buddhist', 'timer', 'toc', 'travel_explore', 'trending_up', 'tune', 'update', 'verified',
		'verified_user', 'villa', 'wb_twilight',
	);
}

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'toplist-khanh-hoa', TKH_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'custom-logo', array( 'height' => 88, 'width' => 260, 'flex-width' => true, 'flex-height' => true ) );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_image_size( 'tkh-card', 800, 500, true );
		add_image_size( 'tkh-hero', 1920, 900, true );
	}
);

add_action(
	'init',
	function () {
		register_taxonomy(
			'khu_vuc',
			'post',
			array(
				'labels'            => array(
					'name'          => __( 'Khu vực', 'toplist-khanh-hoa' ),
					'singular_name' => __( 'Khu vực', 'toplist-khanh-hoa' ),
					'search_items'  => __( 'Tìm khu vực', 'toplist-khanh-hoa' ),
					'all_items'     => __( 'Tất cả khu vực', 'toplist-khanh-hoa' ),
					'parent_item'   => __( 'Khu vực cha', 'toplist-khanh-hoa' ),
					'edit_item'     => __( 'Sửa khu vực', 'toplist-khanh-hoa' ),
					'add_new_item'  => __( 'Thêm khu vực', 'toplist-khanh-hoa' ),
					'menu_name'     => __( 'Khu vực', 'toplist-khanh-hoa' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'query_var'         => 'khu_vuc',
				'rewrite'           => array( 'slug' => 'khu-vuc', 'hierarchical' => true ),
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$css   = TKH_DIR . '/assets/css/main.css';
		$js    = TKH_DIR . '/assets/js/main.js';
		$icons = tkh_icon_names();
		sort( $icons );

		wp_enqueue_style( 'tkh-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null );
		wp_enqueue_style( 'tkh-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=' . implode( ',', $icons ) . '&display=block', array(), null );
		wp_enqueue_style( 'tkh-main', TKH_URI . '/assets/css/main.css', array(), file_exists( $css ) ? filemtime( $css ) : null );
		wp_enqueue_script( 'tkh-main', TKH_URI . '/assets/js/main.js', array(), file_exists( $js ) ? filemtime( $js ) : null, array( 'strategy' => 'defer', 'in_footer' => true ) );
		$chat = TKH_DIR . '/assets/js/chat.js';
		wp_enqueue_script( 'tkh-chat', TKH_URI . '/assets/js/chat.js', array(), file_exists( $chat ) ? filemtime( $chat ) : null, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'tkh-chat',
			'tkhChat',
			array(
				'endpoint'    => rest_url( 'tkh/v1/chat' ),
				'lang'        => tkh_lang(),
				'suggestions' => array(
					__( 'Ăn hải sản ngon ở đâu tại Nha Trang?', 'toplist-khanh-hoa' ),
					__( 'Lịch trình 2 ngày đi đảo Bình Ba', 'toplist-khanh-hoa' ),
					__( 'Resort nào gần sân bay Cam Ranh?', 'toplist-khanh-hoa' ),
					__( 'Đi đâu vui cho gia đình có trẻ nhỏ?', 'toplist-khanh-hoa' ),
				),
				'i18n'        => array(
					'welcome'  => esc_html__( 'Xin chào! Mình là trợ lý du lịch Khánh Hòa. Bạn muốn ăn gì, chơi đâu hay nghỉ ở đâu? Mình sẽ gợi ý kèm bài review trên site.', 'toplist-khanh-hoa' ),
					'sources'  => __( 'Bài viết liên quan', 'toplist-khanh-hoa' ),
					'thinking' => __( 'Đang tìm câu trả lời…', 'toplist-khanh-hoa' ),
					'error'    => esc_html__( 'Xin lỗi, trợ lý đang bận. Bạn thử lại sau ít phút hoặc dùng ô tìm kiếm của site nhé.', 'toplist-khanh-hoa' ),
				),
			)
		);
		wp_localize_script(
			'tkh-main',
			'tkhI18n',
			array(
				'favAdd'    => __( 'Lưu vào yêu thích', 'toplist-khanh-hoa' ),
				'favRemove' => __( 'Bỏ khỏi yêu thích', 'toplist-khanh-hoa' ),
				'copied'    => __( 'Đã sao chép liên kết', 'toplist-khanh-hoa' ),
			)
		);
	}
);

add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		}
		return $urls;
	},
	10,
	2
);

// Travel assistant widget on every front-end page.
add_action( 'wp_footer', fn() => get_template_part( 'parts/chatbot' ), 5 );

// Theme favicon until a Site Icon is set in the Customizer.
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( TKH_URI . '/assets/favicon.svg' ) );
		}
		echo '<meta name="theme-color" content="#0077b6">' . "\n";
	},
	2
);

/**
 * Footer contact details, editable under Appearance → Customize → Thông tin liên hệ.
 */
function tkh_contact_fields() {
	return array(
		'tkh_contact_address' => array( __( 'Địa chỉ', 'toplist-khanh-hoa' ), 'TP. Nha Trang, Tỉnh Khánh Hòa, Việt Nam' ),
		'tkh_contact_phone'   => array( __( 'Số điện thoại', 'toplist-khanh-hoa' ), '' ),
		'tkh_contact_email'   => array( __( 'Email liên hệ', 'toplist-khanh-hoa' ), '' ),
		'tkh_contact_hours'   => array( __( 'Giờ hỗ trợ', 'toplist-khanh-hoa' ), '08:00 - 18:00 (Thứ 2 - Thứ 7)' ),
	);
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section( 'tkh_contact', array( 'title' => __( 'Thông tin liên hệ', 'toplist-khanh-hoa' ), 'priority' => 30 ) );
		foreach ( tkh_contact_fields() as $id => $field ) {
			$wp_customize->add_setting( $id, array( 'default' => $field[1], 'sanitize_callback' => 'tkh_contact_email' === $id ? 'sanitize_email' : 'sanitize_text_field' ) );
			$wp_customize->add_control( $id, array( 'label' => $field[0], 'section' => 'tkh_contact', 'type' => 'tkh_contact_email' === $id ? 'email' : 'text' ) );
		}
	}
);

function tkh_contact( $key ) {
	$fields = tkh_contact_fields();
	return (string) get_theme_mod( 'tkh_contact_' . $key, $fields[ 'tkh_contact_' . $key ][1] );
}

add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more', fn() => '…' );
