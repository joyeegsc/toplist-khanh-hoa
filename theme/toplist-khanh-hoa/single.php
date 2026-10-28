<?php
/**
 * Single place review.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	$cat     = tkh_post_category();
	$info    = tkh_category_info( $cat );
	$region  = tkh_post_region();
	$address = tkh_meta( 'address' );
	$badge   = tkh_meta( 'badge' ) ? tkh_meta( 'badge' ) : ( $cat ? $cat->name : '' );
	$place   = tkh_place_name();
	$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( $place . ' ' . $address ) );
	$minutes = tkh_reading_minutes();
	$crumbs  = array();
	if ( $cat ) {
		$crumbs[] = array( $cat->name, get_category_link( $cat ) );
	}
	$crumbs[] = array( $place, null );

	$facts = array_filter(
		array(
			array( 'payments', __( 'Mức giá', 'toplist-khanh-hoa' ), tkh_meta( 'price' ), '' ),
			array( 'schedule', __( 'Giờ mở cửa', 'toplist-khanh-hoa' ), tkh_meta( 'hours' ), '' ),
			array( 'map', __( 'Khu vực', 'toplist-khanh-hoa' ), $region ? $region->name : '', $region ? get_term_link( $region ) : '' ),
			array( 'savings', __( 'Phân khúc', 'toplist-khanh-hoa' ), tkh_price_level_label( tkh_meta( 'price_level' ) ), '' ),
		),
		fn( $fact ) => '' !== (string) $fact[2]
	);
	list( $content, $headings ) = tkh_toc( apply_filters( 'the_content', get_the_content() ) );
	?>
	<div class="fixed top-0 left-0 right-0 z-[70] h-1 pointer-events-none" aria-hidden="true">
		<div class="h-full origin-left scale-x-0 bg-gradient-to-r from-primary-container via-tertiary-fixed-dim to-sunset-gold" data-reading-progress></div>
	</div>

	<article <?php post_class(); ?> data-article>
		<header class="relative w-full min-h-[480px] md:min-h-[560px] -mt-20 pt-28 pb-24 md:pb-28 px-margin-mobile md:px-margin overflow-hidden flex items-end">
			<div class="absolute inset-0">
				<img src="<?php echo esc_url( tkh_image_url( 'tkh-hero' ) ); ?>" alt="<?php echo esc_attr( $place ); ?>" class="w-full h-full object-cover" fetchpriority="high">
				<div class="absolute inset-0 bg-gradient-to-b from-[#07223b]/75 via-[#023e8a]/35 to-[#07223b]/95"></div>
			</div>
			<div class="relative z-10 max-w-[1280px] w-full mx-auto">
				<?php tkh_breadcrumb( $crumbs, true ); ?>
				<div class="flex flex-wrap items-center gap-2 mb-4">
					<?php if ( $cat ) : ?>
						<a class="inline-flex items-center gap-1 px-3 py-1 rounded-full font-label-sm text-label-sm <?php echo esc_attr( $info['badge'] ); ?>" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><span class="material-symbols-outlined text-[14px]" aria-hidden="true"><?php echo esc_html( $info['icon'] ); ?></span><?php echo esc_html( $badge ); ?></a>
					<?php endif; ?>
					<?php if ( $region ) : ?>
						<a class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/15 hover:bg-white/25 backdrop-blur-md text-white font-label-sm text-label-sm transition-colors" href="<?php echo esc_url( get_term_link( $region ) ); ?>"><span class="material-symbols-outlined text-[14px]" aria-hidden="true">location_on</span><?php echo esc_html( $region->name ); ?></a>
					<?php endif; ?>
					<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white font-label-sm text-label-sm"><span class="material-symbols-outlined icon-fill text-[14px] text-badge-verified" aria-hidden="true">verified</span><?php esc_html_e( 'Đã kiểm duyệt', 'toplist-khanh-hoa' ); ?></span>
				</div>
				<h1 class="font-display-hero-mobile text-display-hero-mobile md:font-display-hero md:text-display-hero text-white max-w-4xl"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="mt-4 font-body-lg text-body-lg md:text-[18px] md:leading-8 text-white/85 max-w-3xl"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-white/85 font-body-md text-body-md">
					<?php echo tkh_rating_html( null, true, 'bg-surface-container-lowest/95 px-3 py-1 rounded-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">timer</span><?php echo esc_html( sprintf( /* translators: %d: minutes */ _n( '%d phút đọc', '%d phút đọc', $minutes, 'toplist-khanh-hoa' ), $minutes ) ); ?></span>
					<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">update</span><time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s: date */ __( 'Cập nhật %s', 'toplist-khanh-hoa' ), get_the_modified_date() ) ); ?></time></span>
				</div>
			</div>
		</header>

		<?php if ( $facts ) : ?>
			<div class="relative z-20 max-w-[1280px] mx-auto px-margin-mobile md:px-margin -mt-14">
				<dl class="grid grid-cols-2 lg:grid-cols-4 bg-surface-container-lowest rounded-2xl border border-slate-border/70 shadow-[0_20px_48px_-8px_rgba(23,35,58,0.14)] divide-y lg:divide-y-0 lg:divide-x divide-slate-border/60 overflow-hidden">
					<?php foreach ( $facts as $fact ) : ?>
						<div class="flex items-start gap-3 p-4 md:p-5">
							<span class="w-10 h-10 shrink-0 rounded-xl bg-primary-fixed text-primary flex items-center justify-center"><span class="material-symbols-outlined text-[20px]" aria-hidden="true"><?php echo esc_html( $fact[0] ); ?></span></span>
							<div class="min-w-0">
								<dt class="font-label-sm text-label-sm text-slate-muted uppercase tracking-wider"><?php echo esc_html( $fact[1] ); ?></dt>
								<dd class="font-label-lg text-label-lg text-on-surface mt-0.5">
									<?php if ( $fact[3] ) : ?>
										<a class="text-primary-container hover:underline" href="<?php echo esc_url( $fact[3] ); ?>"><?php echo esc_html( $fact[2] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $fact[2] ); ?>
									<?php endif; ?>
								</dd>
							</div>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
		<?php endif; ?>

		<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-10 md:py-12 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
			<div class="lg:col-span-8 min-w-0 space-y-6">
				<div class="bg-surface-container-lowest rounded-2xl border border-slate-border shadow-[0_4px_20px_-2px_rgba(23,35,58,0.05)] p-5 md:p-10">
					<div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-border/60">
						<img src="<?php echo esc_url( TKH_URI . '/assets/favicon.svg' ); ?>" alt="" class="w-10 h-10 rounded-full" width="40" height="40">
						<div class="font-body-sm text-body-sm">
							<div class="font-label-lg text-label-lg text-on-surface"><?php echo esc_html( sprintf( /* translators: %s: author name */ __( 'Biên tập: %s', 'toplist-khanh-hoa' ), get_the_author() ) ); ?></div>
							<div class="text-slate-muted"><?php echo esc_html( sprintf( /* translators: %s: date */ __( 'Đăng ngày %s', 'toplist-khanh-hoa' ), get_the_date() ) ); ?></div>
						</div>
					</div>

					<?php if ( count( $headings ) >= 3 ) : ?>
						<details class="lg:hidden mb-8 rounded-xl bg-surface-mist border border-slate-border/70 p-4">
							<summary class="cursor-pointer font-headline-sm text-headline-sm text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">toc</span><?php esc_html_e( 'Mục lục bài viết', 'toplist-khanh-hoa' ); ?></summary>
							<ol class="mt-3 space-y-1.5 list-decimal pl-6 font-body-md text-body-md marker:text-primary-container">
								<?php foreach ( $headings as $heading ) : ?>
									<li><a class="text-on-surface-variant hover:text-primary-container" href="#<?php echo esc_attr( $heading[0] ); ?>"><?php echo esc_html( $heading[1] ); ?></a></li>
								<?php endforeach; ?>
							</ol>
						</details>
					<?php endif; ?>

					<div class="entry-content">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output. ?>
					</div>

					<div class="mt-10 pt-6 border-t border-slate-border flex flex-col md:flex-row md:items-center md:justify-between gap-4">
						<?php $tags = get_the_tags(); ?>
						<div class="flex flex-wrap gap-2">
							<?php foreach ( $tags ? $tags : array() as $tag ) : ?>
								<a class="chip !py-1 !px-3 !text-body-sm" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" rel="tag">#<?php echo esc_html( $tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
						<div class="flex items-center gap-2 shrink-0">
							<span class="font-label-md text-label-md text-slate-muted inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">share</span><?php esc_html_e( 'Chia sẻ', 'toplist-khanh-hoa' ); ?></span>
							<?php foreach ( tkh_share_links() as $share ) : ?>
								<a class="px-3 py-1.5 rounded-full bg-surface-mist hover:bg-primary-fixed font-label-md text-label-md text-on-surface transition-colors" href="<?php echo esc_url( $share[1] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $share[0] ); ?></a>
							<?php endforeach; ?>
							<button type="button" data-copy-link="<?php echo esc_url( get_permalink() ); ?>" class="w-9 h-9 cursor-pointer rounded-full bg-surface-mist hover:bg-primary-fixed flex items-center justify-center transition-colors" aria-label="<?php esc_attr_e( 'Sao chép liên kết', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">link</span></button>
						</div>
					</div>
				</div>

				<section class="flex flex-col sm:flex-row gap-4 items-start bg-surface-container-lowest rounded-2xl border border-slate-border p-5 md:p-6" aria-labelledby="ve-tac-gia">
					<img src="<?php echo esc_url( tkh_logo_url() ); ?>" alt="" class="h-12 w-auto shrink-0" height="48" loading="lazy">
					<div>
						<h2 id="ve-tac-gia" class="font-headline-sm text-headline-sm text-on-surface"><?php esc_html_e( 'Về ban biên tập', 'toplist-khanh-hoa' ); ?></h2>
						<p class="font-body-md text-body-md text-on-surface-variant mt-1"><?php esc_html_e( 'Bài viết do ban biên tập Toplist Khánh Hòa thực hiện từ trải nghiệm và thông tin công khai, được rà soát định kỳ. Giá và giờ mở cửa có thể thay đổi theo mùa.', 'toplist-khanh-hoa' ); ?></p>
						<?php if ( tkh_page_url( 'quy-chuan-tham-dinh' ) ) : ?>
							<a class="inline-flex items-center gap-1 mt-2 font-label-lg text-label-lg text-primary-container hover:underline" href="<?php echo esc_url( tkh_page_url( 'quy-chuan-tham-dinh' ) ); ?>"><?php esc_html_e( 'Quy chuẩn thẩm định của chúng tôi', 'toplist-khanh-hoa' ); ?><span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span></a>
						<?php endif; ?>
					</div>
				</section>

				<?php if ( $address ) : ?>
					<section class="bg-surface-container-lowest rounded-2xl border border-slate-border shadow-[0_4px_20px_-2px_rgba(23,35,58,0.05)] overflow-hidden" aria-labelledby="ban-do">
						<div class="px-5 md:px-8 pt-5 md:pt-6 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
							<div>
								<h2 id="ban-do" class="font-headline-md text-headline-md text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-primary" aria-hidden="true">map</span><?php esc_html_e( 'Bản đồ & đường đi', 'toplist-khanh-hoa' ); ?></h2>
								<p class="font-body-md text-body-md text-on-surface-variant mt-1 flex items-start gap-1"><span class="material-symbols-outlined text-[18px] text-slate-muted mt-0.5" aria-hidden="true">pin_drop</span><?php echo esc_html( $address ); ?></p>
							</div>
							<a class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-container hover:bg-primary text-on-primary font-label-lg text-label-lg transition-colors" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener nofollow"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">near_me</span><?php esc_html_e( 'Chỉ đường', 'toplist-khanh-hoa' ); ?></a>
						</div>
						<iframe class="w-full h-72 md:h-96 border-0" src="<?php echo esc_url( tkh_map_embed_url( $place . ', ' . $address ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr( sprintf( /* translators: %s: place name */ __( 'Bản đồ %s', 'toplist-khanh-hoa' ), $place ) ); ?>"></iframe>
					</section>
				<?php endif; ?>

				<?php
				$prev = get_previous_post( true );
				$next = get_next_post( true );
				if ( $prev || $next ) :
					?>
					<nav class="grid grid-cols-1 sm:grid-cols-2 gap-4" aria-label="<?php esc_attr_e( 'Bài viết khác', 'toplist-khanh-hoa' ); ?>">
						<?php foreach ( array( array( $prev, 'arrow_back', __( 'Bài trước', 'toplist-khanh-hoa' ), '' ), array( $next, 'arrow_forward', __( 'Bài tiếp theo', 'toplist-khanh-hoa' ), 'sm:text-right sm:col-start-2' ) ) as $nav ) : ?>
							<?php if ( $nav[0] ) : ?>
								<a class="group flex flex-col gap-1 p-5 rounded-2xl bg-surface-container-lowest border border-slate-border hover:border-primary-container hover:shadow-md transition-all <?php echo esc_attr( $nav[3] ); ?>" href="<?php echo esc_url( get_permalink( $nav[0] ) ); ?>">
									<span class="inline-flex items-center gap-1 font-label-md text-label-md text-slate-muted uppercase tracking-wider <?php echo $nav[3] ? 'sm:justify-end' : ''; ?>"><span class="material-symbols-outlined text-[16px]" aria-hidden="true"><?php echo esc_html( $nav[1] ); ?></span><?php echo esc_html( $nav[2] ); ?></span>
									<span class="font-headline-sm text-headline-sm text-on-surface group-hover:text-primary line-clamp-2"><?php echo esc_html( get_the_title( $nav[0] ) ); ?></span>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>
			</div>

			<aside class="lg:col-span-4">
				<div class="lg:sticky lg:top-24 space-y-5">
					<div class="bg-card-glass-bg backdrop-blur-xl rounded-2xl border border-slate-border shadow-[0_12px_32px_-4px_rgba(2,62,138,0.08)] p-6">
						<div class="flex items-center justify-between gap-3 mb-4">
							<h2 class="font-headline-md text-headline-md text-on-surface"><?php esc_html_e( 'Thông tin nhanh', 'toplist-khanh-hoa' ); ?></h2>
							<?php if ( '' !== tkh_meta( 'rating' ) ) : ?>
								<div class="text-right">
									<div class="font-display-hero text-[28px] leading-none font-bold text-deep-ocean"><?php echo esc_html( number_format( (float) tkh_meta( 'rating' ), 1, '.', '' ) ); ?><span class="font-body-sm text-body-sm text-slate-muted">/5</span></div>
									<div class="font-body-sm text-body-sm text-slate-muted"><?php echo esc_html( sprintf( /* translators: %s: number of reviews */ __( '%s đánh giá', 'toplist-khanh-hoa' ), tkh_format_number( tkh_meta( 'review_count' ) ) ) ); ?></div>
								</div>
							<?php endif; ?>
						</div>
						<?php if ( tkh_meta( 'note' ) ) : ?>
							<p class="mb-4 inline-flex items-center gap-1 px-3 py-1 rounded-full bg-badge-verified/10 text-[#047857] font-label-md text-label-md"><span class="material-symbols-outlined icon-fill text-[16px]" aria-hidden="true">verified</span><?php echo esc_html( tkh_meta( 'note' ) ); ?></p>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<p class="flex items-start gap-2 font-body-md text-body-md text-on-surface-variant mb-5"><span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">location_on</span><?php echo esc_html( $address ); ?></p>
						<?php endif; ?>
						<div class="flex flex-col gap-2.5">
							<a class="w-full h-12 rounded-xl bg-gradient-to-r from-[#FF7A00] to-sunset-gold text-on-primary font-headline-sm text-headline-sm flex items-center justify-center gap-2 shadow-md hover:brightness-105 transition-all" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener nofollow"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">near_me</span><?php esc_html_e( 'Chỉ đường Google Maps', 'toplist-khanh-hoa' ); ?></a>
							<?php echo tkh_fav_button( null, 'full' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<p class="mt-4 font-body-sm text-body-sm text-slate-muted"><?php esc_html_e( 'Giá và giờ mở cửa mang tính tham khảo, có thể thay đổi theo mùa. Nên liên hệ trước khi ghé.', 'toplist-khanh-hoa' ); ?></p>
					</div>

					<?php if ( count( $headings ) >= 3 ) : ?>
						<nav class="hidden lg:block bg-surface-container-lowest rounded-2xl border border-slate-border p-5" aria-label="<?php esc_attr_e( 'Mục lục bài viết', 'toplist-khanh-hoa' ); ?>" data-toc>
							<p class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 mb-3"><span class="material-symbols-outlined text-[20px] text-primary" aria-hidden="true">toc</span><?php esc_html_e( 'Mục lục bài viết', 'toplist-khanh-hoa' ); ?></p>
							<ol class="space-y-0.5 max-h-[40vh] overflow-y-auto pr-1 font-body-sm text-body-sm">
								<?php foreach ( $headings as $heading ) : ?>
									<li><a class="toc-link block pl-3 py-1.5 border-l-2 border-slate-border text-on-surface-variant hover:text-primary-container hover:border-primary-container transition-colors" href="#<?php echo esc_attr( $heading[0] ); ?>"><?php echo esc_html( $heading[1] ); ?></a></li>
								<?php endforeach; ?>
							</ol>
						</nav>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</article>

	<?php
	$related_sets = array();
	if ( $cat ) {
		$related_sets[] = array(
			'query'      => array( 'category__in' => array( $cat->term_id ), 'post__not_in' => array( $post_id ), 'posts_per_page' => 3, 'meta_key' => '_tkh_review_count', 'orderby' => array( 'meta_value_num' => 'DESC' ), 'ignore_sticky_posts' => true ),
			'icon'       => $info['icon'],
			'eyebrow'    => __( 'Cùng chuyên mục', 'toplist-khanh-hoa' ),
			/* translators: %s: category name */
			'title'      => sprintf( __( 'Thêm gợi ý %s', 'toplist-khanh-hoa' ), mb_strtolower( $cat->name ) ),
			'link'       => get_category_link( $cat ),
			'link_label' => __( 'Xem tất cả', 'toplist-khanh-hoa' ),
		);
	}
	if ( $region ) {
		$related_sets[] = array(
			'query'      => array( 'tax_query' => array( array( 'taxonomy' => 'khu_vuc', 'terms' => $region->term_id ) ), 'category__not_in' => $cat ? array( $cat->term_id ) : array(), 'post__not_in' => array( $post_id ), 'posts_per_page' => 3, 'meta_key' => '_tkh_review_count', 'orderby' => array( 'meta_value_num' => 'DESC' ), 'ignore_sticky_posts' => true ),
			'icon'       => 'location_on',
			'eyebrow'    => __( 'Gần đó', 'toplist-khanh-hoa' ),
			/* translators: %s: region name */
			'title'      => sprintf( __( 'Ăn gì, chơi đâu gần %s', 'toplist-khanh-hoa' ), $region->name ),
			'link'       => get_term_link( $region ),
			'link_label' => __( 'Xem khu vực', 'toplist-khanh-hoa' ),
		);
	}
	foreach ( $related_sets as $i => $set ) :
		$related = new WP_Query( $set['query'] );
		if ( ! $related->have_posts() ) {
			continue;
		}
		?>
		<section class="<?php echo $i ? 'bg-surface-container-low' : ''; ?> py-12 md:py-14">
			<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin">
				<?php tkh_section_header( $set ); ?>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
					<?php while ( $related->have_posts() ) : $related->the_post(); get_template_part( 'parts/card' ); endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>

	<button type="button" class="fixed left-4 bottom-24 lg:left-6 lg:bottom-6 z-40 w-11 h-11 rounded-full bg-deep-ocean text-on-primary shadow-lg flex items-center justify-center cursor-pointer opacity-0 translate-y-3 pointer-events-none transition-all duration-200 motion-reduce:transition-none data-[show=true]:opacity-100 data-[show=true]:translate-y-0 data-[show=true]:pointer-events-auto" data-back-to-top aria-label="<?php esc_attr_e( 'Lên đầu trang', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined" aria-hidden="true">arrow_upward</span></button>
	<?php
endwhile;

get_footer();
