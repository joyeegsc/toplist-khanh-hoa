<?php
/**
 * Rendering helpers shared by templates.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Photos from the design template, used until a post has its own featured image.
 */
function tkh_fallback_images() {
	$base = 'https://lh3.googleusercontent.com/aida/';
	return array(
		'bay'     => $base . 'AEtjO1XoE0xSPhoKtR9HXQ5Su2_Fam3m0fMI4b45C2mNTuNVQ-SFb4ZrVMj-rnFdSAlySFIeVWLoyoNLsnWqj0RPWbhPckyVFqARFuVU72FbV9QZI9e7-1MU0OAZ0vfHynssh22K_2w9WGVgglTQdHrWCQlv2QzuNoPmsjK2W5ptxkBxEt0d0s_WfQ-r05_1auTm6VTb9QA-g8E5R_HEHqheOKAgOubPejXhmmi6Rl1u1ivBbKzfXcW7UVk5VOTA',
		'seafood' => $base . 'AEtjO1V4retIrLHkGC6wjBxbQRJOSa9HDgzQBecvqxF4XmHWoOeX3ZwREZDq6EzEKor_ABZDtrq_V9nSHeI_fKM-sG6SMRNvMjdbvm1IT0PLNZikgfH1KoeG1mgng4QlNGRGE34KtwnDuEQ09p7hY3RzJMI-Q1z2Y1KHlle4RhdHJht0-Nbg4GzkyoLFfgrvEyTUYcZRIxHgBFiPnSUY7wutwvCGt5tyJVeLwaguhCHen23IyA43Z3grKOb4cyM',
		'nature'  => $base . 'AEtjO1Xlb0rgvRHAvY5z7vmMzvEg8_a8FgUHzQGL_r4zjaCfaesAgRbPX64mIf9qYZDf5KwS_5RQjonxASZY6-bTEyEUsLZ9s8e7oSUfgJFCj-u3FarKNyOLCl-kJ1jCTqJC-dbV71mzw1dtTu1y4msJJNmm6EeF-vhshQmIJlSaVlz4bYShzENeOihXuhhQX9-sN90E6jXgFejxscBBHFplca7uhbA2fNUOSgVHCRVv9pgqcXFwSdxXSZuvy7A',
		'cafe'    => $base . 'AEtjO1VVMn35S1DKl3HbpvH4FxFTf6OeSMJd2WXNID2eHtn0So2LZIWWwOWfJ54H3lmFmeQjNctNeoTZGaw4lsVp_DEvrsBJ4ybz6KYneCpl8RZsv0n-MQTXgSlQfJ6IEpHURko5B8sKtTsBEZ3OjPlR9VlHxLPNboTrybnoBGSQpWLVskF3EP5dNjBncZAz460GEejDNFBj_UtdJCol4jWen1ZHbYjqGMeNPxOwODnMHE1Tbq5hBFmp9vDJZwVD',
		'grill'   => $base . 'AEtjO1V261-59VsvmpfsGJbVVOd4bQdJ7ttC_ZnUby3U2KRRn-PGA_RqpfGic7KJYrwJRqO38Ad6LkSfODKjvck5EqXoeRE7fh2b65FBIBgaebnp9-5-UeR2MLrQZPCV_DUM8JWTgwIRA49bkcmveiLzauVFyjwiKOQhkMYcUzsFfXr2PR1prFX_htd8fTU1TFNq_SsV30awTk1VnNKDmPLF_0cq14lWac9M32Afxhm7Rtqd2WuheWWK2K_FLv-e',
		'noodle'  => $base . 'AEtjO1VHI-DsfZfCEwfa0axwcdChF4l89KT7Cw3tKD-F9iNACPjP44yZuFBD8Fl4yILTiOV7sOSfD7AukgZHWXdMgqUsNnoJg7iC9Ux8-DOKMFHxMWKrEft1rmKhCQR5ZfZ0BIFHX5M8XiN0imwelkG8m-53kJ14AGLtukrz_MvTl7hvG24HSl6C8qMfoDF3QfKE-To3fxQ3p-cjZO4TDlqBDAY6i9kB-HGldbU24kdomCno_T6DIDTqC_Jhmc3I',
		'logo'    => $base . 'AEtjO1X7BeoyXlvP0NEn7V2z8lun4p-OsKTHBM4PAhLfTrwaJHF9AqCIZH4KknEevgc-VqAyubQHZuIuSPet07hbw1dmB-WJ_H6aSE0GwP0rQHNmPgzu6JAXlsxKJ7XGqHej6nWZ4PZte-Rts2aOgHcMSidE4DU9oltTiHNmUTHg8SEn-SxMDfZv3DxhLvluHxtIxlu_jKCLJOaE-3btQtGzojVXAyP-GNXe4vWrozhS_8uBW5fmzougcRaAOUSS',
	);
}

function tkh_meta( $key, $post_id = null ) {
	return get_post_meta( $post_id ? $post_id : get_the_ID(), '_tkh_' . $key, true );
}

/**
 * First category of a post that belongs to the main navigation, else the first category.
 */
function tkh_post_category( $post_id = null ) {
	$cats = get_the_category( $post_id ? $post_id : get_the_ID() );
	foreach ( $cats as $cat ) {
		if ( isset( tkh_nav_categories()[ tkh_term_key( $cat ) ] ) ) {
			return $cat;
		}
	}
	return $cats ? $cats[0] : null;
}

function tkh_post_region( $post_id = null ) {
	$terms = get_the_terms( $post_id ? $post_id : get_the_ID(), 'khu_vuc' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Icon, badge classes and schema type for a category term (any language) or default-language slug.
 */
function tkh_category_info( $term_or_slug ) {
	$slug = $term_or_slug instanceof WP_Term ? tkh_term_key( $term_or_slug ) : (string) $term_or_slug;
	$all  = tkh_nav_categories();
	return isset( $all[ $slug ] ) ? $all[ $slug ] : array( 'icon' => 'location_on', 'badge' => 'bg-primary text-on-primary', 'schema' => 'TouristAttraction' );
}

/**
 * Navigation categories in the current language: default slug => WP_Term.
 */
function tkh_nav_terms() {
	$terms = array();
	foreach ( array_keys( tkh_nav_categories() ) as $slug ) {
		$term = tkh_term( $slug, 'category' );
		if ( $term ) {
			$terms[ $slug ] = $term;
		}
	}
	return $terms;
}

/**
 * Card/hero image URL: featured image, else a template photo chosen by the "_tkh_image" meta or category.
 */
function tkh_image_url( $size = 'tkh-card', $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}
	$images = tkh_fallback_images();
	$key    = get_post_meta( $post_id, '_tkh_image', true );
	if ( ! isset( $images[ $key ] ) ) {
		$by_cat = array( 'am-thuc' => 'seafood', 'cafe' => 'cafe', 'luu-tru' => 'bay', 'giai-tri' => 'bay', 'du-lich' => 'nature' );
		$cat    = tkh_term_key( tkh_post_category( $post_id ) );
		$key    = isset( $by_cat[ $cat ] ) ? $by_cat[ $cat ] : 'bay';
	}
	return $images[ $key ];
}

function tkh_logo_url() {
	$logo_id = get_theme_mod( 'custom_logo' );
	return $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : tkh_fallback_images()['logo'];
}

function tkh_format_number( $n ) {
	return 'en' === tkh_lang() ? number_format( (int) $n ) : number_format( (int) $n, 0, ',', '.' );
}

/**
 * Rating pill: star, score and (optionally) review count.
 */
function tkh_rating_html( $post_id = null, $with_count = true, $class = 'bg-surface-container-lowest/90 backdrop-blur-md px-3 py-1 rounded-lg' ) {
	$rating = tkh_meta( 'rating', $post_id );
	if ( '' === $rating ) {
		return '';
	}
	$count = tkh_meta( 'review_count', $post_id );
	$html  = '<span class="' . esc_attr( $class ) . ' text-on-surface font-label-md text-label-md inline-flex items-center gap-1.5">';
	$html .= '<span class="material-symbols-outlined icon-fill text-sunset-gold text-[16px]" aria-hidden="true">star</span>';
	/* translators: %s: rating out of 5 */
	$html .= '<span class="font-bold"><span class="sr-only">' . esc_html__( 'Điểm đánh giá', 'toplist-khanh-hoa' ) . ' </span>' . esc_html( number_format( (float) $rating, 1, '.', '' ) ) . '</span>';
	if ( $with_count && '' !== $count ) {
		/* translators: %s: number of reviews */
		$html .= '<span class="text-slate-muted font-normal">(' . esc_html( sprintf( __( '%s đánh giá', 'toplist-khanh-hoa' ), tkh_format_number( $count ) ) ) . ')</span>';
	}
	return $html . '</span>';
}

function tkh_fav_button( $post_id = null, $variant = 'icon' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( 'icon' === $variant ) {
		return sprintf(
			'<button type="button" data-fav="%d" aria-pressed="false" aria-label="%s" class="fav-btn absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-surface-container-lowest/90 backdrop-blur-md flex items-center justify-center text-slate-muted hover:text-sunset-gold shadow-sm transition-colors"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">favorite</span></button>',
			$post_id,
			esc_attr__( 'Lưu vào yêu thích', 'toplist-khanh-hoa' )
		);
	}
	return sprintf(
		'<button type="button" data-fav="%d" aria-pressed="false" class="fav-btn w-full h-12 rounded-xl border border-slate-border bg-surface-container-lowest hover:bg-surface-mist text-on-surface font-headline-sm text-headline-sm flex items-center justify-center gap-2 transition-colors"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">favorite</span><span class="fav-off">%s</span><span class="fav-on">%s</span></button>',
		$post_id,
		esc_html__( 'Lưu vào yêu thích', 'toplist-khanh-hoa' ),
		esc_html__( 'Đã lưu yêu thích', 'toplist-khanh-hoa' )
	);
}

/**
 * Section heading with eyebrow, title, description and an optional "see all" link.
 */
function tkh_section_header( $args ) {
	$args = wp_parse_args( $args, array( 'icon' => '', 'eyebrow' => '', 'title' => '', 'desc' => '', 'link' => '', 'link_label' => '', 'tag' => 'h2' ) );
	?>
	<div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
		<div>
			<?php if ( $args['eyebrow'] ) : ?>
				<div class="flex items-center gap-2 text-primary font-label-md text-label-md uppercase tracking-wider mb-2">
					<?php if ( $args['icon'] ) : ?><span class="material-symbols-outlined text-[18px]" aria-hidden="true"><?php echo esc_html( $args['icon'] ); ?></span><?php endif; ?>
					<span><?php echo esc_html( $args['eyebrow'] ); ?></span>
				</div>
			<?php endif; ?>
			<<?php echo tag_escape( $args['tag'] ); ?> class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-on-surface"><?php echo esc_html( $args['title'] ); ?></<?php echo tag_escape( $args['tag'] ); ?>>
			<?php if ( $args['desc'] ) : ?>
				<p class="font-body-md text-body-md text-on-surface-variant max-w-xl mt-1"><?php echo esc_html( $args['desc'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $args['link'] ) : ?>
			<a class="self-start md:self-auto inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container-high hover:bg-primary-container hover:text-on-primary font-headline-sm text-headline-sm text-on-surface transition-all" href="<?php echo esc_url( $args['link'] ); ?>">
				<span><?php echo esc_html( $args['link_label'] ); ?></span>
				<span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Pagination styled for listings.
 */
function tkh_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'prev_text' => '<span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_left</span><span class="sr-only">' . esc_html__( 'Trang trước', 'toplist-khanh-hoa' ) . '</span>',
			'next_text' => '<span class="sr-only">' . esc_html__( 'Trang sau', 'toplist-khanh-hoa' ) . '</span><span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>',
		)
	);
	if ( ! $links ) {
		return;
	}
	echo '<nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="' . esc_attr__( 'Phân trang', 'toplist-khanh-hoa' ) . '">';
	foreach ( $links as $link ) {
		$is_current = false !== strpos( $link, 'current' );
		$classes    = $is_current ? 'bg-primary-container text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-mist border border-slate-border';
		echo str_replace( 'class="page-numbers', 'class="min-w-10 h-10 px-3 rounded-xl inline-flex items-center justify-center font-label-lg text-label-lg transition-colors ' . esc_attr( $classes ) . ' page-numbers', $link ); // phpcs:ignore WordPress.Security.EscapeOutput -- core-generated markup.
	}
	echo '</nav>';
}

/**
 * Breadcrumb trail items after "Home": array of [label, url|null]. Also feeds the BreadcrumbList schema.
 */
function tkh_breadcrumb( array $items, $light = false ) {
	$GLOBALS['tkh_breadcrumb'] = $items;
	$text  = $light ? 'text-white/80' : 'text-slate-muted';
	$link  = $light ? 'hover:text-white' : 'hover:text-primary';
	$items = array_merge( array( array( __( 'Trang chủ', 'toplist-khanh-hoa' ), tkh_home_url() ) ), $items );
	echo '<nav aria-label="' . esc_attr__( 'Đường dẫn', 'toplist-khanh-hoa' ) . '" class="font-body-sm text-body-sm ' . esc_attr( $text ) . ' mb-3"><ol class="flex flex-wrap items-center gap-1">';
	foreach ( $items as $i => $item ) {
		if ( $i ) {
			echo '<li aria-hidden="true">/</li>';
		}
		if ( ! empty( $item[1] ) ) {
			printf( '<li><a class="%s transition-colors" href="%s">%s</a></li>', esc_attr( $link ), esc_url( $item[1] ), esc_html( $item[0] ) );
		} else {
			printf( '<li aria-current="page" class="line-clamp-1">%s</li>', esc_html( $item[0] ) );
		}
	}
	echo '</ol></nav>';
}

/**
 * Place name: the "Tên địa điểm" field, else the title up to its first colon.
 */
function tkh_place_name( $post_id = null ) {
	$name = tkh_meta( 'place_name', $post_id );
	return '' !== $name ? $name : trim( (string) preg_replace( '/\s*[:|–—].*$/u', '', get_the_title( $post_id ) ) );
}

function tkh_price_level_label( $level ) {
	$levels = tkh_price_levels();
	return isset( $levels[ $level ] ) ? $levels[ $level ] : '';
}
