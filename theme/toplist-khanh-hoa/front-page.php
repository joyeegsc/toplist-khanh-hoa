<?php
/**
 * Home page, following the template's section order.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$place_total = ( new WP_Query( array( 'posts_per_page' => 1, 'fields' => 'ids' ) ) )->found_posts;
$regions     = get_terms( array( 'taxonomy' => 'khu_vuc', 'parent' => 0, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$regions     = is_wp_error( $regions ) ? array() : $regions;
$region_icon = array( 'nha-trang' => 'beach_access', 'bac-nha-trang' => 'explore', 'cam-ranh' => 'flight_takeoff', 'cam-lam' => 'villa', 'ninh-hoa' => 'restaurant', 'van-ninh' => 'sailing', 'phan-rang-vinh-hy' => 'temple_buddhist' );
$nav_terms   = tkh_nav_terms();

/**
 * Posts in a navigation category, most-reviewed first.
 */
$by_category = function ( $slug, $count ) use ( $nav_terms ) {
	return new WP_Query(
		array(
			'cat'                 => isset( $nav_terms[ $slug ] ) ? $nav_terms[ $slug ]->term_id : -1,
			'posts_per_page'      => $count,
			'meta_key'            => '_tkh_review_count',
			'orderby'             => array( 'meta_value_num' => 'DESC' ),
			'ignore_sticky_posts' => true,
		)
	);
};
$tag_slug = function ( $slug ) {
	$term = tkh_term( $slug, 'post_tag' );
	return $term ? $term->slug : $slug;
};

$featured_tag = tkh_term( 'noi-bat', 'post_tag' );
$featured     = new WP_Query( array( 'tag_id' => $featured_tag ? $featured_tag->term_id : -1, 'posts_per_page' => 4, 'ignore_sticky_posts' => true ) );
if ( ! $featured->have_posts() ) {
	$featured = new WP_Query( array( 'posts_per_page' => 4, 'ignore_sticky_posts' => true ) );
}
$culinary = $by_category( 'am-thuc', 3 );
$stays    = $by_category( 'luu-tru', 3 );

$quick_filters = array(
	array( '<span class="text-sunset-gold font-bold">★</span>', __( 'Đánh Giá 4.5+', 'toplist-khanh-hoa' ), array( 'rating' => '4.5' ) ),
	array( '🔥', __( 'Phổ Biến Nhất', 'toplist-khanh-hoa' ), array( 'sort' => 'reviews' ) ),
	array( '🏖️', __( 'Ven Biển', 'toplist-khanh-hoa' ), array( 'tag' => $tag_slug( 'ven-bien' ) ) ),
	array( '💰', __( 'Giá Bình Dân', 'toplist-khanh-hoa' ), array( 'price_level' => 'binh-dan' ) ),
	array( '🌅', __( 'View Hoàng Hôn Lãng Mạn', 'toplist-khanh-hoa' ), array( 'tag' => $tag_slug( 'hoang-hon' ) ) ),
);
$area_keywords    = 'en' === tkh_lang() ? array( 'Tran Phu', 'Hon Tre', 'Long Beach', 'Ba Ngoi', 'Van Phong', 'Vinh Hy' ) : array( 'Trần Phú', 'Hòn Tre', 'Bãi Dài', 'Ba Ngòi', 'Vân Phong', 'Vĩnh Hy' );
$newsletter_state = isset( $_GET['dang-ky'] ) ? sanitize_key( wp_unslash( $_GET['dang-ky'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- display-only flag.
?>
<div class="flex flex-col w-full">

	<!-- Hero banner -->
	<section class="relative w-full min-h-[580px] -mt-20 pt-28 pb-16 px-margin-mobile md:px-margin overflow-hidden flex items-center justify-center">
		<div class="absolute inset-0 w-full h-full">
			<img src="<?php echo esc_url( tkh_fallback_images()['bay'] ); ?>" alt="<?php esc_attr_e( 'Vịnh biển bờ cát vàng Nha Trang, Khánh Hòa', 'toplist-khanh-hoa' ); ?>" class="w-full h-full object-cover object-center" fetchpriority="high">
			<div class="absolute inset-0 bg-gradient-to-b from-[#07223b]/90 via-[#023e8a]/70 to-[#07223b]/95"></div>
			<div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[900px] h-[500px] bg-gradient-to-b from-[#0077b6]/30 to-transparent rounded-full blur-3xl pointer-events-none"></div>
		</div>
		<div class="max-w-[1280px] w-full mx-auto relative z-10 flex flex-col items-center text-center">
			<div class="inline-flex items-center gap-space-xs px-4 py-1.5 rounded-full bg-surface-container-lowest/15 backdrop-blur-md font-label-md text-label-md mb-5 shadow-sm border border-white/10">
				<span class="material-symbols-outlined icon-fill text-[16px] text-sunset-gold" aria-hidden="true">verified</span>
				<span class="tracking-wider uppercase font-semibold text-white"><?php esc_html_e( 'Cẩm Nang Số Du Lịch Vịnh Ngọc', 'toplist-khanh-hoa' ); ?></span>
			</div>
			<h1 class="font-display-hero text-[34px] md:text-[50px] leading-[1.2] font-extrabold text-on-primary tracking-tight max-w-5xl mb-3"><?php bloginfo( 'name' ); ?></h1>
			<p class="font-headline-md text-[18px] md:text-[22px] font-medium max-w-3xl mb-4 bg-clip-text text-transparent bg-gradient-to-r from-[#94ccff] via-[#b9eaff] to-[#59d5ff]"><?php esc_html_e( 'Khám Phá Điểm Đến, Ẩm Thực & Trải Nghiệm Khánh Hòa', 'toplist-khanh-hoa' ); ?></p>
			<p class="font-body-md text-body-md text-[#cedaf9] max-w-2xl mb-8 leading-relaxed"><?php echo esc_html( sprintf( /* translators: 1: number of places, 2: number of regions */ __( 'Khám phá %1$s địa điểm ẩm thực, du lịch và lưu trú được tuyển chọn tại %2$s khu vực của tỉnh.', 'toplist-khanh-hoa' ), tkh_format_number( $place_total ), count( $regions ) ) ); ?></p>

			<div class="w-full max-w-5xl bg-surface-container-lowest/95 backdrop-blur-xl rounded-2xl p-3 md:p-4 shadow-[0_20px_48px_-8px_rgba(2,62,138,0.3)]">
				<form class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center" role="search" method="get" action="<?php echo esc_url( tkh_home_url() ); ?>">
					<div class="md:col-span-5 field">
						<span class="material-symbols-outlined text-slate-muted text-[22px] mr-2 shrink-0" aria-hidden="true">search</span>
						<label class="sr-only" for="hero-s"><?php esc_html_e( 'Từ khóa', 'toplist-khanh-hoa' ); ?></label>
						<input id="hero-s" name="s" class="w-full bg-transparent font-body-md text-body-md text-on-surface placeholder:text-slate-muted focus:outline-none" placeholder="<?php esc_attr_e( 'Nhà hàng hải sản, đảo Bình Ba, resort...', 'toplist-khanh-hoa' ); ?>" type="search">
					</div>
					<div class="md:col-span-3 field">
						<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true">category</span>
						<label class="sr-only" for="hero-cat"><?php esc_html_e( 'Danh mục', 'toplist-khanh-hoa' ); ?></label>
						<select id="hero-cat" name="category_name" class="w-full bg-transparent font-body-md text-body-md text-on-surface focus:outline-none appearance-none cursor-pointer pr-5">
							<option value=""><?php esc_html_e( 'Tất Cả Danh Mục', 'toplist-khanh-hoa' ); ?></option>
							<?php foreach ( $nav_terms as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-3 pointer-events-none" aria-hidden="true">expand_more</span>
					</div>
					<div class="md:col-span-2 field">
						<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true">location_on</span>
						<label class="sr-only" for="hero-region"><?php esc_html_e( 'Khu vực', 'toplist-khanh-hoa' ); ?></label>
						<select id="hero-region" name="khu_vuc" class="w-full bg-transparent font-body-md text-body-md text-on-surface focus:outline-none appearance-none cursor-pointer pr-5">
							<option value=""><?php esc_html_e( 'Toàn Tỉnh', 'toplist-khanh-hoa' ); ?></option>
							<?php foreach ( $regions as $region ) : ?>
								<option value="<?php echo esc_attr( $region->slug ); ?>"><?php echo esc_html( $region->name ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-3 pointer-events-none" aria-hidden="true">expand_more</span>
					</div>
					<div class="md:col-span-2">
						<button class="w-full h-11 bg-gradient-to-r from-[#FF7A00] to-sunset-gold hover:brightness-105 text-on-primary font-headline-sm text-headline-sm rounded-xl flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-all active:scale-[0.99]" type="submit">
							<span class="material-symbols-outlined text-[20px]" aria-hidden="true">explore</span><span><?php esc_html_e( 'Tìm Kiếm', 'toplist-khanh-hoa' ); ?></span>
						</button>
					</div>
				</form>
				<div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-slate-border/50 text-left">
					<span class="font-label-sm text-label-sm text-slate-muted uppercase tracking-wider pl-1 shrink-0"><?php esc_html_e( 'Gợi Ý Nhanh:', 'toplist-khanh-hoa' ); ?></span>
					<?php foreach ( $quick_filters as $qf ) : ?>
						<a class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-surface-mist hover:bg-primary-fixed text-on-surface font-body-sm text-body-sm transition-colors" href="<?php echo esc_url( tkh_search_url( $qf[2] ) ); ?>"><?php echo wp_kses_post( $qf[0] ); ?> <?php echo esc_html( $qf[1] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- Photography showcase -->
	<?php if ( $featured->have_posts() ) : ?>
	<section class="w-full max-w-[1280px] mx-auto px-margin-mobile md:px-margin -mt-6 relative z-20 mb-space-xl md:mb-space-2xl" aria-labelledby="noi-bat-heading">
		<h2 id="noi-bat-heading" class="sr-only"><?php esc_html_e( 'Điểm đến nổi bật', 'toplist-khanh-hoa' ); ?></h2>
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
			<?php while ( $featured->have_posts() ) : $featured->the_post(); get_template_part( 'parts/card-compact' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Quality & stats strip -->
	<section class="w-full bg-surface-container-low py-10 px-margin-mobile md:px-margin mb-space-xl md:mb-space-2xl">
		<div class="max-w-[1280px] mx-auto grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
			<div>
				<span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary font-label-sm text-label-sm mb-2"><?php esc_html_e( 'TIÊU CHUẨN THẨM ĐỊNH', 'toplist-khanh-hoa' ); ?></span>
				<h2 class="font-headline-md text-headline-md text-on-surface"><?php esc_html_e( 'Minh Bạch & Thực Tế', 'toplist-khanh-hoa' ); ?></h2>
				<p class="font-body-sm text-body-sm text-on-surface-variant mt-1"><?php esc_html_e( 'Giá tham khảo công khai, nhận xét viết từ trải nghiệm thực tế tại điểm đến.', 'toplist-khanh-hoa' ); ?></p>
			</div>
			<?php
			$stats = array(
				array( 'verified_user', 'bg-primary-fixed text-primary', tkh_format_number( $place_total ), __( 'Địa điểm được tuyển chọn', 'toplist-khanh-hoa' ) ),
				array( 'map', 'bg-tertiary-fixed text-tertiary', count( $regions ), __( 'Khu vực trọng điểm', 'toplist-khanh-hoa' ) ),
				array( 'category', 'bg-secondary-fixed text-secondary', count( $nav_terms ), __( 'Chuyên mục trải nghiệm', 'toplist-khanh-hoa' ) ),
			);
			foreach ( $stats as $stat ) :
				?>
				<div class="flex items-center gap-4 bg-surface-container-lowest p-4 rounded-xl shadow-sm">
					<div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 <?php echo esc_attr( $stat[1] ); ?>"><span class="material-symbols-outlined text-[28px]" aria-hidden="true"><?php echo esc_html( $stat[0] ); ?></span></div>
					<div>
						<div class="font-display-hero text-[28px] font-bold text-deep-ocean leading-tight"><?php echo esc_html( $stat[2] ); ?></div>
						<div class="font-body-sm text-body-sm text-slate-muted"><?php echo esc_html( $stat[3] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Culinary -->
	<?php if ( $culinary->have_posts() ) : ?>
	<section class="w-full max-w-[1280px] mx-auto px-margin-mobile md:px-margin mb-space-xl md:mb-space-2xl">
		<?php
		tkh_section_header(
			array(
				'icon'       => 'restaurant_menu',
				'eyebrow'    => __( 'Tinh Hoa Mỹ Vị Miền Biển', 'toplist-khanh-hoa' ),
				'title'      => __( 'Ẩm Thực Khánh Hòa Nổi Tiếng', 'toplist-khanh-hoa' ),
				'desc'       => __( 'Từ tô bún cá sáng sớm, cuốn nem nướng đến mâm hải sản tươi đậm vị miền biển.', 'toplist-khanh-hoa' ),
				'link'       => isset( $nav_terms['am-thuc'] ) ? get_category_link( $nav_terms['am-thuc'] ) : '',
				'link_label' => __( 'Xem tất cả quán ăn', 'toplist-khanh-hoa' ),
			)
		);
		?>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
			<?php while ( $culinary->have_posts() ) : $culinary->the_post(); get_template_part( 'parts/card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Regional explorer -->
	<?php if ( $regions ) : ?>
	<section class="w-full max-w-[1280px] mx-auto px-margin-mobile md:px-margin mb-space-xl md:mb-space-2xl">
		<div class="bg-surface-container rounded-3xl p-6 md:p-10">
			<div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
				<div>
					<span class="font-label-md text-label-md text-primary uppercase tracking-wider font-semibold"><?php esc_html_e( 'Bản Đồ Điểm Đến', 'toplist-khanh-hoa' ); ?></span>
					<h2 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-on-surface mt-1"><?php esc_html_e( 'Khám Phá Theo Khu Vực', 'toplist-khanh-hoa' ); ?></h2>
					<p class="font-body-md text-body-md text-on-surface-variant mt-1"><?php esc_html_e( 'Tìm điểm ăn chơi, nghỉ ngơi phù hợp theo từng khu vực của Khánh Hòa.', 'toplist-khanh-hoa' ); ?></p>
				</div>
				<span class="self-start md:self-auto px-3.5 py-1.5 rounded-full bg-surface-container-lowest text-deep-ocean font-headline-sm text-headline-sm shadow-sm"><?php echo esc_html( sprintf( /* translators: %s: number of regions */ __( '%s Khu Vực Trọng Điểm', 'toplist-khanh-hoa' ), count( $regions ) ) ); ?></span>
			</div>
			<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
				<?php foreach ( array_slice( $regions, 0, 6 ) as $region ) : $key = tkh_term_key( $region ); ?>
					<a class="p-4 rounded-xl bg-surface-container-lowest hover:bg-primary hover:text-on-primary transition-all duration-200 group text-center shadow-sm" href="<?php echo esc_url( get_term_link( $region ) ); ?>">
						<div class="w-10 h-10 mx-auto mb-2 rounded-full bg-primary-fixed group-hover:bg-surface-container-lowest/20 flex items-center justify-center text-primary group-hover:text-on-primary">
							<span class="material-symbols-outlined text-[20px]" aria-hidden="true"><?php echo esc_html( isset( $region_icon[ $key ] ) ? $region_icon[ $key ] : 'location_on' ); ?></span>
						</div>
						<div class="font-headline-sm text-headline-sm mb-0.5 truncate"><?php echo esc_html( $region->name ); ?></div>
						<div class="font-body-sm text-body-sm text-slate-muted group-hover:text-tertiary-fixed"><?php echo esc_html( sprintf( /* translators: %s: number of places */ _n( '%s địa điểm', '%s địa điểm', $region->count, 'toplist-khanh-hoa' ), $region->count ) ); ?></div>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="flex flex-wrap items-center justify-center gap-2 pt-4 border-t border-slate-border/50 text-center">
				<?php foreach ( array_slice( $regions, 6 ) as $region ) : ?>
					<a class="px-3 py-1 rounded-lg bg-surface-container-lowest/70 hover:bg-surface-container-lowest text-on-surface-variant font-body-sm text-body-sm transition-colors" href="<?php echo esc_url( get_term_link( $region ) ); ?>"><?php echo esc_html( $region->name ); ?> (<?php echo esc_html( $region->count ); ?>)</a>
				<?php endforeach; ?>
				<?php foreach ( $area_keywords as $place ) : ?>
					<a class="px-3 py-1 rounded-lg bg-surface-container-lowest/70 hover:bg-surface-container-lowest text-on-surface-variant font-body-sm text-body-sm transition-colors" href="<?php echo esc_url( tkh_search_url( array( 's' => $place ) ) ); ?>"><?php echo esc_html( $place ); ?></a>
				<?php endforeach; ?>
				<a class="px-3 py-1 rounded-lg bg-surface-container-lowest/70 hover:bg-surface-container-lowest text-primary font-body-sm text-body-sm font-semibold transition-colors" href="<?php echo esc_url( tkh_search_url() ); ?>"><?php esc_html_e( '+ Xem tất cả địa điểm', 'toplist-khanh-hoa' ); ?></a>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Stays -->
	<?php if ( $stays->have_posts() ) : ?>
	<section class="w-full max-w-[1280px] mx-auto px-margin-mobile md:px-margin mb-space-xl md:mb-space-2xl">
		<?php
		tkh_section_header(
			array(
				'icon'       => 'hotel',
				'eyebrow'    => __( 'Nghỉ Dưỡng & Khách Sạn Tiện Nghi', 'toplist-khanh-hoa' ),
				'title'      => __( 'Cơ Sở Lưu Trú Đáng Trải Nghiệm', 'toplist-khanh-hoa' ),
				'desc'       => __( 'Từ resort ven vịnh Bắc Nha Trang đến những khu nghỉ dưỡng biệt lập bên Bãi Dài Cam Ranh.', 'toplist-khanh-hoa' ),
				'link'       => isset( $nav_terms['luu-tru'] ) ? get_category_link( $nav_terms['luu-tru'] ) : '',
				'link_label' => __( 'Xem toàn bộ nơi lưu trú', 'toplist-khanh-hoa' ),
			)
		);
		?>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
			<?php while ( $stays->have_posts() ) : $stays->the_post(); get_template_part( 'parts/card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Newsletter -->
	<section id="ban-tin" class="w-full max-w-[1280px] mx-auto px-margin-mobile md:px-margin mb-space-xl md:mb-space-2xl scroll-mt-24">
		<div class="relative rounded-3xl bg-gradient-to-r from-[#005d90] via-[#0077b6] to-[#023e8a] p-6 md:p-12 text-on-primary overflow-hidden shadow-xl">
			<div class="absolute top-0 right-0 -mr-16 -mt-16 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
			<div class="absolute bottom-0 left-1/3 -mb-20 w-60 h-60 rounded-full bg-tertiary-fixed-dim/20 blur-xl pointer-events-none"></div>
			<div class="relative z-10 max-w-2xl">
				<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white font-label-sm text-label-sm mb-3">
					<span class="material-symbols-outlined text-[16px] text-sunset-gold" aria-hidden="true">mail</span><?php esc_html_e( 'BẢN TIN DU LỊCH ĐỊNH KỲ', 'toplist-khanh-hoa' ); ?>
				</span>
				<h2 class="font-display-hero text-[28px] md:text-[38px] font-bold leading-tight mb-3"><?php esc_html_e( 'Nhận Cẩm Nang Ẩm Thực & Ưu Đãi Nghỉ Dưỡng Khánh Hòa', 'toplist-khanh-hoa' ); ?></h2>
				<p class="font-body-lg text-body-lg text-surface-container-high/90 mb-6"><?php esc_html_e( 'Nhận danh sách quán mới, kinh nghiệm đi đảo và các gợi ý lưu trú được cập nhật mỗi tuần.', 'toplist-khanh-hoa' ); ?></p>
				<form class="flex flex-col sm:flex-row items-center gap-3 max-w-lg" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="tkh_subscribe">
					<?php wp_nonce_field( 'tkh_subscribe', 'tkh_nonce' ); ?>
					<label class="sr-only" for="tkh-email"><?php esc_html_e( 'Email', 'toplist-khanh-hoa' ); ?></label>
					<input id="tkh-email" name="email" class="w-full px-4 py-3 rounded-xl bg-surface-container-lowest text-on-surface font-body-md text-body-md placeholder:text-slate-muted focus:outline-none focus:ring-2 focus:ring-sunset-gold shadow-md" placeholder="<?php esc_attr_e( 'Nhập địa chỉ email của bạn...', 'toplist-khanh-hoa' ); ?>" required type="email" autocomplete="email">
					<button class="w-full sm:w-auto px-6 py-3 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary font-headline-sm text-headline-sm transition-all shadow-md shrink-0 active:scale-95" type="submit"><?php esc_html_e( 'Đăng Ký Ngay', 'toplist-khanh-hoa' ); ?></button>
				</form>
				<?php if ( 'ok' === $newsletter_state ) : ?>
					<p class="mt-3 font-label-lg text-label-lg text-tertiary-fixed" role="status"><?php esc_html_e( 'Cảm ơn bạn! Email đã được ghi nhận.', 'toplist-khanh-hoa' ); ?></p>
				<?php elseif ( 'loi' === $newsletter_state ) : ?>
					<p class="mt-3 font-label-lg text-label-lg text-secondary-fixed" role="alert"><?php esc_html_e( 'Email chưa hợp lệ, vui lòng thử lại.', 'toplist-khanh-hoa' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</div>
<?php
get_footer();
