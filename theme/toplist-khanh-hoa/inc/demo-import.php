<?php
/**
 * Bilingual demo content importer (demo/content.json): Tools → Dữ liệu mẫu, or `wp tkh demo-import`.
 * Creates the Polylang languages (vi default without URL prefix, en under /en/), translated
 * categories/regions/tags, 20 posts and the static pages, and links every translation pair.
 * Idempotent: items that already exist (matched by slug) are updated, not duplicated.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run the import. Returns a summary array or WP_Error.
 */
function tkh_demo_import() {
	$file = TKH_DIR . '/demo/content.json';
	$data = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions -- local theme file.
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'tkh_demo_missing', __( 'Không đọc được demo/content.json.', 'toplist-khanh-hoa' ) );
	}

	$bilingual = tkh_demo_setup_languages();
	$langs     = $bilingual ? array( 'vi', 'en' ) : array( 'vi' );
	$summary   = array( 'terms' => 0, 'posts' => 0, 'pages' => 0, 'languages' => $langs );

	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
	update_option( 'date_format', 'd/m/Y' );
	update_option( 'blogname', 'Toplist Khánh Hòa' );
	update_option( 'blogdescription', 'Cẩm nang du lịch, ẩm thực và lưu trú Khánh Hòa' );
	update_option( 'blog_public', 1 );
	update_option( 'default_comment_status', 'closed' );
	wp_update_user( array( 'ID' => tkh_demo_author(), 'display_name' => 'Toplist Khánh Hòa', 'nickname' => 'Toplist Khánh Hòa' ) );
	tkh_demo_translate_options( array( 'Cẩm nang du lịch, ẩm thực và lưu trú Khánh Hòa' => 'Khanh Hoa travel, food and stay guide' ) );

	// Terms: [taxonomy][vi slug][lang] => term_id.
	$term_ids = array();
	foreach ( array( 'category' => 'categories', 'khu_vuc' => 'regions', 'post_tag' => 'tags' ) as $taxonomy => $key ) {
		foreach ( $data[ $key ] as $term ) {
			$ids = array();
			foreach ( $langs as $lang ) {
				$ids[ $lang ] = tkh_demo_upsert_term( $taxonomy, $term[ $lang ], $lang );
				++$summary['terms'];
			}
			tkh_demo_link( 'term', $ids );
			$term_ids[ $taxonomy ][ $term['key'] ] = $ids;
		}
	}

	foreach ( $data['posts'] as $post ) {
		$ids = array();
		foreach ( $langs as $lang ) {
			$ids[ $lang ] = tkh_demo_upsert_post( $post, $lang, $term_ids );
			++$summary['posts'];
		}
		tkh_demo_link( 'post', $ids );
	}

	foreach ( $data['pages'] as $page ) {
		$ids = array();
		foreach ( $langs as $lang ) {
			if ( empty( $page[ $lang ] ) ) {
				continue;
			}
			$ids[ $lang ] = tkh_demo_upsert_page( $page, $lang );
			++$summary['pages'];
		}
		tkh_demo_link( 'post', $ids );
	}

	tkh_demo_remove_wp_samples();
	flush_rewrite_rules();
	update_option( 'tkh_demo_imported', current_time( 'mysql' ) );
	return $summary;
}

/**
 * Ensures Polylang has Vietnamese (default) and English. Returns false when Polylang is inactive.
 */
function tkh_demo_setup_languages() {
	if ( ! function_exists( 'PLL' ) || ! function_exists( 'pll_set_post_language' ) ) {
		return false;
	}
	$model    = PLL()->model;
	$existing = wp_list_pluck( $model->get_languages_list(), 'slug' );
	$wanted   = array(
		array( 'name' => 'Tiếng Việt', 'slug' => 'vi', 'locale' => 'vi', 'rtl' => 0, 'term_group' => 0, 'flag' => 'vn' ),
		array( 'name' => 'English', 'slug' => 'en', 'locale' => 'en_US', 'rtl' => 0, 'term_group' => 1, 'flag' => 'us' ),
	);
	foreach ( $wanted as $language ) {
		if ( in_array( $language['slug'], $existing, true ) ) {
			continue;
		}
		if ( isset( $model->languages ) && is_object( $model->languages ) && method_exists( $model->languages, 'add' ) ) {
			$model->languages->add( $language );
		} elseif ( method_exists( $model, 'add_language' ) ) {
			$model->add_language( $language );
		}
	}

	// Vietnamese at the root, English under /en/, no browser-language redirects (crawlers see stable URLs).
	$settings = array( 'default_lang' => 'vi', 'force_lang' => 1, 'hide_default' => 1, 'rewrite' => 1, 'browser' => 0, 'redirect_lang' => 0 );
	if ( isset( PLL()->options ) && PLL()->options instanceof ArrayAccess ) {
		foreach ( $settings as $key => $value ) {
			PLL()->options[ $key ] = $value;
		}
		if ( method_exists( PLL()->options, 'save' ) ) {
			PLL()->options->save();
		}
	} else {
		update_option( 'polylang', array_merge( (array) get_option( 'polylang', array() ), $settings ) );
		if ( is_array( PLL()->options ) ) {
			PLL()->options = array_merge( PLL()->options, $settings );
		}
	}
	if ( method_exists( $model, 'clean_languages_cache' ) ) {
		$model->clean_languages_cache();
	}
	return in_array( 'en', wp_list_pluck( $model->get_languages_list(), 'slug' ), true );
}

/**
 * Stores English translations for option strings (site tagline) in Polylang's string table.
 */
function tkh_demo_translate_options( array $pairs ) {
	if ( ! class_exists( 'PLL_MO' ) || ! function_exists( 'PLL' ) ) {
		return;
	}
	$language = PLL()->model->get_language( 'en' );
	if ( ! $language ) {
		return;
	}
	$mo = new PLL_MO();
	$mo->import_from_db( $language );
	foreach ( $pairs as $original => $translation ) {
		$mo->add_entry( $mo->make_entry( $original, $translation ) );
	}
	$mo->export_to_db( $language );
}

function tkh_demo_author() {
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID', 'orderby' => 'ID' ) );
	return $admins ? (int) $admins[0] : 1;
}

/**
 * Translation group: lang => id. Links posts or terms when Polylang is active.
 */
function tkh_demo_link( $type, array $ids ) {
	$ids = array_filter( $ids );
	if ( count( $ids ) < 2 ) {
		return;
	}
	if ( 'term' === $type && function_exists( 'pll_save_term_translations' ) ) {
		pll_save_term_translations( $ids );
	} elseif ( 'post' === $type && function_exists( 'pll_save_post_translations' ) ) {
		pll_save_post_translations( $ids );
	}
}

function tkh_demo_upsert_term( $taxonomy, array $term, $lang ) {
	$existing = get_terms( array( 'taxonomy' => $taxonomy, 'slug' => $term['slug'], 'hide_empty' => false, 'lang' => '' ) );
	if ( ! is_wp_error( $existing ) && $existing ) {
		$id = (int) $existing[0]->term_id;
		wp_update_term( $id, $taxonomy, array( 'name' => $term['name'], 'description' => isset( $term['description'] ) ? $term['description'] : '' ) );
	} else {
		$result = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'], 'description' => isset( $term['description'] ) ? $term['description'] : '' ) );
		$id     = is_wp_error( $result ) ? 0 : (int) $result['term_id'];
	}
	if ( $id && function_exists( 'pll_set_term_language' ) ) {
		pll_set_term_language( $id, $lang );
	}
	return $id;
}

function tkh_demo_find( $slug, $post_type ) {
	$found = get_posts( array( 'name' => $slug, 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => 1, 'lang' => '', 'suppress_filters' => false ) );
	return $found ? (int) $found[0]->ID : 0;
}

function tkh_demo_upsert_post( array $post, $lang, array $term_ids ) {
	$local = $post[ $lang ];
	$args  = array(
		'ID'             => tkh_demo_find( $local['slug'], 'post' ),
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'post_name'      => $local['slug'],
		'post_title'     => $local['title'],
		'post_content'   => $local['content'],
		'post_excerpt'   => $local['excerpt'],
		'post_date'      => $post['date'],
		'post_author'    => tkh_demo_author(),
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	);
	$id = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $id, $lang );
	}
	$pick = function ( $taxonomy, array $keys ) use ( $term_ids, $lang ) {
		$ids = array();
		foreach ( $keys as $key ) {
			if ( ! empty( $term_ids[ $taxonomy ][ $key ][ $lang ] ) ) {
				$ids[] = (int) $term_ids[ $taxonomy ][ $key ][ $lang ];
			}
		}
		return $ids;
	};
	wp_set_object_terms( $id, $pick( 'category', array( $post['category'] ) ), 'category' );
	wp_set_object_terms( $id, $pick( 'khu_vuc', array( $post['region'] ) ), 'khu_vuc' );
	wp_set_object_terms( $id, $pick( 'post_tag', $post['tags'] ), 'post_tag' );

	update_post_meta( $id, '_tkh_image', $post['image'] );
	foreach ( array_merge( $post['meta'], $local['meta'] ) as $key => $value ) {
		if ( '' === $value || null === $value ) {
			delete_post_meta( $id, '_tkh_' . $key );
		} else {
			update_post_meta( $id, '_tkh_' . $key, $value );
		}
	}
	return $id;
}

function tkh_demo_upsert_page( array $page, $lang ) {
	$local = $page[ $lang ];
	$id    = wp_insert_post(
		wp_slash(
			array(
				'ID'           => tkh_demo_find( $local['slug'], 'page' ),
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $local['slug'],
				'post_title'   => $local['title'],
				'post_content' => $local['content'],
				'post_author'  => tkh_demo_author(),
			)
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	if ( function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( $id, $lang );
	}
	if ( $page['template'] ) {
		update_post_meta( $id, '_wp_page_template', $page['template'] );
	}
	return $id;
}

/**
 * Removes WordPress' "Hello world!" post and "Sample Page" if they are untouched.
 */
function tkh_demo_remove_wp_samples() {
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
		$id = tkh_demo_find( $sample[0], $sample[1] );
		if ( $id && get_post_field( 'post_date', $id ) === get_post_field( 'post_modified', $id ) ) {
			wp_delete_post( $id, true );
		}
	}
}

add_action(
	'admin_menu',
	function () {
		add_management_page( __( 'Dữ liệu mẫu', 'toplist-khanh-hoa' ), __( 'Dữ liệu mẫu', 'toplist-khanh-hoa' ), 'manage_options', 'tkh-demo', 'tkh_render_demo_page' );
	}
);

function tkh_render_demo_page() {
	$result = null;
	if ( isset( $_POST['tkh_demo_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['tkh_demo_nonce'] ), 'tkh_demo_import' ) && current_user_can( 'manage_options' ) ) {
		$result = tkh_demo_import();
	}
	echo '<div class="wrap"><h1>' . esc_html__( 'Dữ liệu mẫu', 'toplist-khanh-hoa' ) . '</h1>';
	if ( is_wp_error( $result ) ) {
		printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $result->get_error_message() ) );
	} elseif ( $result ) {
		/* translators: 1: posts, 2: pages, 3: terms, 4: languages */
		printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html( sprintf( __( 'Đã nhập %1$d bài viết, %2$d trang, %3$d chuyên mục/khu vực/thẻ (%4$s).', 'toplist-khanh-hoa' ), $result['posts'], $result['pages'], $result['terms'], implode( ', ', $result['languages'] ) ) ) );
	}
	if ( ! function_exists( 'pll_set_post_language' ) ) {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Plugin Polylang chưa được kích hoạt: chỉ phần tiếng Việt được nhập. Cài và kích hoạt Polylang rồi chạy lại để có bản tiếng Anh.', 'toplist-khanh-hoa' ) );
	}
	echo '<p>' . esc_html__( 'Nhập 20 bài review địa điểm, các trang giới thiệu/chính sách, chuyên mục, khu vực và thẻ bằng tiếng Việt và tiếng Anh. Chạy lại an toàn: nội dung trùng đường dẫn sẽ được cập nhật thay vì nhân đôi.', 'toplist-khanh-hoa' ) . '</p>';
	echo '<form method="post">';
	wp_nonce_field( 'tkh_demo_import', 'tkh_demo_nonce' );
	submit_button( __( 'Nhập dữ liệu mẫu', 'toplist-khanh-hoa' ) );
	echo '</form></div>';
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'tkh demo-import',
		function () {
			$result = tkh_demo_import();
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
			WP_CLI::success( sprintf( 'Imported %d posts, %d pages, %d terms (%s).', $result['posts'], $result['pages'], $result['terms'], implode( ', ', $result['languages'] ) ) );
		}
	);
}
