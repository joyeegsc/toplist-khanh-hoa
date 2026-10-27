<?php
/**
 * SEO: titles, meta description, canonical, robots, Open Graph/Twitter cards, JSON-LD
 * (Organization, WebSite + SearchAction, BreadcrumbList, Article + place, ItemList) and
 * sitemap/head cleanup. hreflang alternates come from Polylang.
 *
 * Steps aside when a dedicated SEO plugin is active so tags are never duplicated.
 */

defined( 'ABSPATH' ) || exit;

function tkh_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

add_action( 'after_setup_theme', 'tkh_seo_init', 20 );

// Search engine verification codes (Appearance → Customize → SEO), printed even alongside SEO plugins.
add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_section( 'tkh_seo', array( 'title' => 'SEO', 'priority' => 31 ) );
		$fields = array(
			'tkh_google_verification' => __( 'Mã xác minh Google Search Console', 'toplist-khanh-hoa' ),
			'tkh_bing_verification'   => __( 'Mã xác minh Bing Webmaster', 'toplist-khanh-hoa' ),
		);
		foreach ( $fields as $id => $label ) {
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'tkh_sanitize_verification' ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'description' => __( 'Chỉ dán phần content="…" của thẻ meta.', 'toplist-khanh-hoa' ), 'section' => 'tkh_seo', 'type' => 'text' ) );
		}
	}
);

function tkh_sanitize_verification( $value ) {
	if ( preg_match( '/content=["\']([^"\']+)["\']/', (string) $value, $m ) ) {
		$value = $m[1];
	}
	return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $value );
}

add_action(
	'wp_head',
	function () {
		foreach ( array( 'google-site-verification' => 'tkh_google_verification', 'msvalidate.01' => 'tkh_bing_verification' ) as $name => $mod ) {
			$code = get_theme_mod( $mod, '' );
			if ( $code ) {
				printf( '<meta name="%s" content="%s">' . "\n", esc_attr( $name ), esc_attr( $code ) );
			}
		}
	},
	1
);

function tkh_seo_init() {
	// Head cleanup and sitemap hygiene apply even alongside an SEO plugin.
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	add_filter( 'wp_sitemaps_add_provider', fn( $provider, $name ) => 'users' === $name ? false : $provider, 10, 2 );
	add_filter( 'wp_sitemaps_posts_query_args', 'tkh_sitemap_exclude_utility_pages', 10, 2 );
	add_action( 'template_redirect', 'tkh_redirect_thin_archives' );

	if ( tkh_seo_plugin_active() ) {
		return;
	}
	add_filter( 'document_title_separator', fn() => '|' );
	add_filter( 'document_title_parts', 'tkh_document_title_parts' );
	add_filter( 'wp_robots', 'tkh_robots' );
	add_action( 'wp_head', 'tkh_seo_head', 1 );
	add_action( 'wp_footer', 'tkh_schema_output', 99 );
}

/**
 * Pages that exist for utility (favorites list) rather than search traffic.
 */
function tkh_is_utility_page( $post_id = null ) {
	return 'page-templates/favorites.php' === get_page_template_slug( $post_id ? $post_id : get_queried_object_id() );
}

function tkh_sitemap_exclude_utility_pages( $args, $post_type ) {
	if ( 'page' === $post_type ) {
		$args['meta_query'] = array(
			'relation' => 'OR',
			array( 'key' => '_wp_page_template', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_wp_page_template', 'value' => 'page-templates/favorites.php', 'compare' => '!=' ),
		);
	}
	return $args;
}

/**
 * Author and date archives only duplicate the category listings.
 */
function tkh_redirect_thin_archives() {
	if ( is_author() || is_date() ) {
		wp_safe_redirect( tkh_home_url(), 301 );
		exit;
	}
}

function tkh_document_title_parts( $parts ) {
	if ( is_front_page() ) {
		$parts['tagline'] = get_bloginfo( 'description' );
	} elseif ( is_archive() && tkh_archive_term() ) {
		$parts['title'] = tkh_archive_heading( tkh_archive_term() );
	} elseif ( is_search() ) {
		$q              = get_search_query( false );
		$parts['title'] = '' !== $q ? sprintf( /* translators: %s: search keywords */ __( 'Kết quả cho “%s”', 'toplist-khanh-hoa' ), $q ) : __( 'Tìm địa điểm ở Khánh Hòa', 'toplist-khanh-hoa' );
	}
	if ( is_paged() ) {
		$parts['page'] = sprintf( /* translators: %d: page number */ __( 'Trang %d', 'toplist-khanh-hoa' ), (int) get_query_var( 'paged' ) );
	}
	return $parts;
}

function tkh_robots( $robots ) {
	$noindex = is_search() || is_404() || is_attachment() || ( ( is_archive() || is_home() ) && tkh_is_filtered_listing() ) || ( is_page() && tkh_is_utility_page() );
	if ( $noindex ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['max-image-preview'] );
	} else {
		$robots['max-snippet']       = '-1';
		$robots['max-video-preview'] = '-1';
	}
	return $robots;
}

function tkh_seo_description() {
	if ( is_singular() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	} elseif ( is_front_page() ) {
		$text = __( 'Cẩm nang du lịch Khánh Hòa: review địa điểm ăn uống, đảo và bãi biển, resort, khu vui chơi và quán cafe ở Nha Trang, Cam Ranh, Ninh Hòa, Vạn Ninh kèm giá tham khảo và kinh nghiệm thực tế.', 'toplist-khanh-hoa' );
	} elseif ( is_archive() && tkh_archive_term() ) {
		$term = tkh_archive_term();
		$text = $term->description ? $term->description : sprintf( /* translators: %s: archive heading */ __( '%s: danh sách địa điểm được tuyển chọn kèm đánh giá, giá tham khảo và kinh nghiệm.', 'toplist-khanh-hoa' ), tkh_archive_heading( $term ) );
	} else {
		$text = get_bloginfo( 'description' );
	}
	$text = trim( preg_replace( '/\s+/', ' ', (string) $text ) );
	if ( '' === $text ) {
		$text = get_bloginfo( 'description' );
	}
	return mb_strlen( $text ) > 160 ? rtrim( mb_substr( $text, 0, 157 ), " ,.;:" ) . '…' : $text;
}

/**
 * Canonical URL for the current view, or '' when the page is noindex / has its own canonical.
 */
function tkh_canonical_url() {
	if ( is_singular() ) {
		return wp_get_canonical_url();
	}
	if ( is_front_page() ) {
		return tkh_home_url();
	}
	$term = is_archive() ? tkh_archive_term() : null;
	if ( $term ) {
		$url   = get_term_link( $term );
		$paged = (int) get_query_var( 'paged' );
		return $paged > 1 ? trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' ) : $url;
	}
	return '';
}

function tkh_seo_image() {
	return is_singular( 'post' ) ? tkh_image_url( 'tkh-hero', get_queried_object_id() ) : tkh_fallback_images()['bay'];
}

function tkh_seo_head() {
	if ( is_404() ) {
		return;
	}
	$title       = wp_get_document_title();
	$description = tkh_seo_description();
	$canonical   = tkh_canonical_url();
	$url         = $canonical ? $canonical : home_url( add_query_arg( array() ) );
	$is_article  = is_singular( 'post' );

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}
	if ( $canonical && ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	$og = array(
		'og:type'        => $is_article ? 'article' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => $title,
		'og:description' => $description,
		'og:url'         => $url,
		'og:image'       => tkh_seo_image(),
		'og:image:alt'   => $is_article ? tkh_place_name( get_queried_object_id() ) : get_bloginfo( 'name' ),
		'og:locale'      => tkh_locale(),
	);
	foreach ( $og as $property => $content ) {
		if ( '' !== (string) $content ) {
			printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), esc_attr( $content ) );
		}
	}
	foreach ( tkh_languages() as $lang ) {
		if ( ! $lang['current'] ) {
			printf( '<meta property="og:locale:alternate" content="%s">' . "\n", esc_attr( 'en' === $lang['slug'] ? 'en_US' : 'vi_VN' ) );
		}
	}
	if ( $is_article ) {
		$cat = tkh_post_category( get_queried_object_id() );
		printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( 'c', get_queried_object_id() ) ) );
		printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( 'c', get_queried_object_id() ) ) );
		if ( $cat ) {
			printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $cat->name ) );
		}
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( tkh_seo_image() ) );
}

/**
 * schema.org PostalAddress from the free-text address field.
 */
function tkh_schema_address( $address ) {
	return array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $address,
		'addressRegion'   => 'Khánh Hòa',
		'addressCountry'  => 'VN',
	);
}

/**
 * JSON-LD graph, printed in the footer so the breadcrumb built while rendering is available.
 */
function tkh_schema_output() {
	if ( is_404() ) {
		return;
	}
	$home     = tkh_home_url();
	$org_id   = home_url( '/#organization' );
	$site_id  = $home . '#website';
	$page_url = tkh_canonical_url() ? tkh_canonical_url() : home_url( add_query_arg( array() ) );
	$lang     = 'en' === tkh_lang() ? 'en' : 'vi';
	$graph    = array();

	$org = array(
		'@type' => 'Organization',
		'@id'   => $org_id,
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
		'logo'  => array( '@type' => 'ImageObject', 'url' => tkh_logo_url() ),
	);
	if ( tkh_contact( 'email' ) ) {
		$org['email'] = tkh_contact( 'email' );
	}
	if ( tkh_contact( 'phone' ) ) {
		$org['telephone'] = tkh_contact( 'phone' );
	}
	$graph[] = $org;

	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => $site_id,
		'url'             => $home,
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'inLanguage'      => $lang,
		'publisher'       => array( '@id' => $org_id ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => add_query_arg( 's', '{search_term_string}', $home ) ),
			'query-input' => 'required name=search_term_string',
		),
	);

	$crumbs = isset( $GLOBALS['tkh_breadcrumb'] ) ? $GLOBALS['tkh_breadcrumb'] : array();
	if ( $crumbs && ! is_front_page() ) {
		$items = array( array( '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Trang chủ', 'toplist-khanh-hoa' ), 'item' => $home ) );
		foreach ( $crumbs as $i => $crumb ) {
			$item = array( '@type' => 'ListItem', 'position' => $i + 2, 'name' => wp_strip_all_tags( $crumb[0] ) );
			$item['item'] = ! empty( $crumb[1] ) ? $crumb[1] : $page_url;
			$items[]      = $item;
		}
		$graph[] = array( '@type' => 'BreadcrumbList', '@id' => $page_url . '#breadcrumb', 'itemListElement' => $items );
	}

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$cat     = tkh_post_category( $post_id );
		$info    = tkh_category_info( $cat );
		$place   = array(
			'@type' => $info['schema'],
			'@id'   => get_permalink( $post_id ) . '#place',
			'name'  => tkh_place_name( $post_id ),
			'image' => tkh_image_url( 'tkh-hero', $post_id ),
			'url'   => get_permalink( $post_id ),
		);
		if ( tkh_meta( 'address', $post_id ) ) {
			$place['address'] = tkh_schema_address( tkh_meta( 'address', $post_id ) );
		}
		if ( tkh_meta( 'price', $post_id ) && 'TouristAttraction' !== $info['schema'] ) {
			$place['priceRange'] = tkh_meta( 'price', $post_id );
		}
		$graph[] = $place;
		$graph[] = array(
			'@type'            => 'Article',
			'@id'              => get_permalink( $post_id ) . '#article',
			'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
			'description'      => tkh_seo_description(),
			'image'            => array( tkh_image_url( 'tkh-hero', $post_id ) ),
			'datePublished'    => get_the_date( 'c', $post_id ),
			'dateModified'     => get_the_modified_date( 'c', $post_id ),
			'inLanguage'       => $lang,
			'articleSection'   => $cat ? $cat->name : '',
			'author'           => array( '@id' => $org_id ),
			'publisher'        => array( '@id' => $org_id ),
			'mainEntityOfPage' => get_permalink( $post_id ),
			'about'            => array( '@id' => get_permalink( $post_id ) . '#place' ),
			'isPartOf'         => array( '@id' => $site_id ),
		);
	} elseif ( is_archive() && tkh_archive_term() && have_posts() ) {
		global $wp_query;
		$items = array();
		foreach ( $wp_query->posts as $i => $post ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'url' => get_permalink( $post ), 'name' => wp_strip_all_tags( get_the_title( $post ) ) );
		}
		$graph[] = array(
			'@type'      => 'CollectionPage',
			'@id'        => $page_url . '#webpage',
			'url'        => $page_url,
			'name'       => wp_get_document_title(),
			'inLanguage' => $lang,
			'isPartOf'   => array( '@id' => $site_id ),
			'mainEntity' => array( '@type' => 'ItemList', 'itemListElement' => $items ),
		);
	}

	printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
}
