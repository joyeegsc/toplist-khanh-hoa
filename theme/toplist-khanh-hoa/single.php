<?php
/**
 * Single place review.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cat       = tkh_post_category();
	$info      = tkh_category_info( $cat );
	$region    = tkh_post_region();
	$address   = tkh_meta( 'address' );
	$badge     = tkh_meta( 'badge' ) ? tkh_meta( 'badge' ) : ( $cat ? $cat->name : '' );
	$place     = tkh_place_name();
	$map_url   = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( trim( $place . ' ' . $address ) );
	$level     = tkh_price_level_label( tkh_meta( 'price_level' ) );
	$crumbs    = array();
	if ( $cat ) {
		$crumbs[] = array( $cat->name, get_category_link( $cat ) );
	}
	$crumbs[] = array( get_the_title(), null );
	$facts    = array_filter(
		array(
			array( 'payments', __( 'Mức giá', 'toplist-khanh-hoa' ), trim( tkh_meta( 'price' ) . ( $level ? ' · ' . $level : '' ), ' ·' ) ),
			array( 'schedule', __( 'Giờ mở cửa', 'toplist-khanh-hoa' ), tkh_meta( 'hours' ) ),
			array( 'location_on', __( 'Địa chỉ', 'toplist-khanh-hoa' ), $address ),
		),
		fn( $fact ) => '' !== $fact[2]
	);
	list( $content, $headings ) = tkh_toc( apply_filters( 'the_content', get_the_content() ) );
	?>
	<article <?php post_class(); ?>>
		<header class="relative w-full min-h-[440px] md:min-h-[520px] -mt-20 pt-28 pb-10 px-margin-mobile md:px-margin overflow-hidden flex items-end">
			<div class="absolute inset-0">
				<img src="<?php echo esc_url( tkh_image_url( 'tkh-hero' ) ); ?>" alt="<?php echo esc_attr( $place ); ?>" class="w-full h-full object-cover" fetchpriority="high">
				<div class="absolute inset-0 bg-gradient-to-b from-[#07223b]/70 via-[#023e8a]/40 to-[#07223b]/95"></div>
			</div>
			<div class="relative z-10 max-w-[1280px] w-full mx-auto">
				<?php tkh_breadcrumb( $crumbs, true ); ?>
				<div class="flex flex-wrap items-center gap-2 mb-3">
					<?php if ( $badge ) : ?><span class="px-3 py-1 rounded-full font-label-sm text-label-sm <?php echo esc_attr( $info['badge'] ); ?>"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
					<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white font-label-sm text-label-sm"><span class="material-symbols-outlined icon-fill text-[14px] text-badge-verified" aria-hidden="true">verified</span><?php esc_html_e( 'Đã kiểm duyệt', 'toplist-khanh-hoa' ); ?></span>
				</div>
				<h1 class="font-display-hero-mobile text-display-hero-mobile md:font-display-hero md:text-display-hero text-white max-w-4xl"><?php the_title(); ?></h1>
				<div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-white/90 font-body-md text-body-md">
					<?php echo tkh_rating_html( null, true, 'bg-surface-container-lowest/95 px-3 py-1 rounded-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $address ) : ?><span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">location_on</span><?php echo esc_html( $address ); ?></span><?php endif; ?>
				</div>
			</div>
		</header>

		<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-10 md:py-14 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
			<div class="lg:col-span-8 min-w-0 space-y-6">
				<div class="bg-surface-container-lowest rounded-2xl border border-slate-border shadow-[0_4px_20px_-2px_rgba(23,35,58,0.05)] p-5 md:p-10">
					<p class="font-body-sm text-body-sm text-slate-muted mb-6 flex flex-wrap gap-x-4 gap-y-1">
						<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">edit_note</span><?php echo esc_html( sprintf( /* translators: %s: author name */ __( 'Biên tập: %s', 'toplist-khanh-hoa' ), get_the_author() ) ); ?></span>
						<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">update</span><time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s: date */ __( 'Cập nhật %s', 'toplist-khanh-hoa' ), get_the_modified_date() ) ); ?></time></span>
						<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">timer</span><?php echo esc_html( sprintf( /* translators: %d: minutes */ _n( '%d phút đọc', '%d phút đọc', tkh_reading_minutes(), 'toplist-khanh-hoa' ), tkh_reading_minutes() ) ); ?></span>
					</p>

					<?php if ( count( $headings ) >= 3 ) : ?>
						<details class="mb-8 rounded-xl bg-surface-mist border border-slate-border/70 p-4 md:p-5" open>
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
								<a class="chip" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" rel="tag">#<?php echo esc_html( $tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
						<div class="flex items-center gap-2 shrink-0">
							<span class="font-label-md text-label-md text-slate-muted inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">share</span><?php esc_html_e( 'Chia sẻ', 'toplist-khanh-hoa' ); ?></span>
							<?php foreach ( tkh_share_links() as $share ) : ?>
								<a class="px-3 py-1.5 rounded-full bg-surface-mist hover:bg-primary-fixed font-label-md text-label-md text-on-surface" href="<?php echo esc_url( $share[1] ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $share[0] ); ?></a>
							<?php endforeach; ?>
							<button type="button" data-copy-link="<?php echo esc_url( get_permalink() ); ?>" class="w-9 h-9 rounded-full bg-surface-mist hover:bg-primary-fixed flex items-center justify-center" aria-label="<?php esc_attr_e( 'Sao chép liên kết', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">link</span></button>
						</div>
					</div>
				</div>

				<?php if ( $address ) : ?>
					<section class="bg-surface-container-lowest rounded-2xl border border-slate-border shadow-[0_4px_20px_-2px_rgba(23,35,58,0.05)] overflow-hidden" aria-labelledby="ban-do">
						<h2 id="ban-do" class="font-headline-md text-headline-md text-on-surface px-5 md:px-8 pt-5 md:pt-6 pb-3 flex items-center gap-2"><span class="material-symbols-outlined text-primary" aria-hidden="true">map</span><?php esc_html_e( 'Bản đồ & đường đi', 'toplist-khanh-hoa' ); ?></h2>
						<iframe class="w-full h-72 md:h-96 border-0" src="<?php echo esc_url( tkh_map_embed_url( $place . ', ' . $address ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr( sprintf( /* translators: %s: place name */ __( 'Bản đồ %s', 'toplist-khanh-hoa' ), $place ) ); ?>"></iframe>
					</section>
				<?php endif; ?>
			</div>

			<aside class="lg:col-span-4">
				<div class="lg:sticky lg:top-24 bg-card-glass-bg backdrop-blur-xl rounded-2xl border border-slate-border shadow-[0_12px_32px_-4px_rgba(2,62,138,0.08)] p-6">
					<h2 class="font-headline-md text-headline-md text-on-surface mb-4"><?php esc_html_e( 'Thông tin nhanh', 'toplist-khanh-hoa' ); ?></h2>
					<dl class="space-y-4">
						<?php foreach ( $facts as $fact ) : ?>
							<div class="flex gap-3">
								<span class="w-10 h-10 shrink-0 rounded-xl bg-primary-fixed text-primary flex items-center justify-center"><span class="material-symbols-outlined text-[20px]" aria-hidden="true"><?php echo esc_html( $fact[0] ); ?></span></span>
								<div>
									<dt class="font-label-md text-label-md text-slate-muted uppercase"><?php echo esc_html( $fact[1] ); ?></dt>
									<dd class="font-body-md text-body-md text-on-surface"><?php echo esc_html( $fact[2] ); ?></dd>
								</div>
							</div>
						<?php endforeach; ?>
						<?php if ( $region ) : ?>
							<div class="flex gap-3">
								<span class="w-10 h-10 shrink-0 rounded-xl bg-tertiary-fixed text-tertiary flex items-center justify-center"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">map</span></span>
								<div>
									<dt class="font-label-md text-label-md text-slate-muted uppercase"><?php esc_html_e( 'Khu vực', 'toplist-khanh-hoa' ); ?></dt>
									<dd class="font-body-md text-body-md"><a class="text-primary-container hover:underline" href="<?php echo esc_url( get_term_link( $region ) ); ?>"><?php echo esc_html( $region->name ); ?></a></dd>
								</div>
							</div>
						<?php endif; ?>
					</dl>
					<div class="mt-6 flex flex-col gap-2.5">
						<a class="w-full h-12 rounded-xl bg-gradient-to-r from-[#FF7A00] to-sunset-gold text-on-primary font-headline-sm text-headline-sm flex items-center justify-center gap-2 shadow-md hover:brightness-105 transition-all" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener nofollow"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">near_me</span><?php esc_html_e( 'Chỉ đường Google Maps', 'toplist-khanh-hoa' ); ?></a>
						<?php echo tkh_fav_button( null, 'full' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<p class="mt-4 font-body-sm text-body-sm text-slate-muted"><?php esc_html_e( 'Giá và giờ mở cửa mang tính tham khảo, có thể thay đổi theo mùa. Nên liên hệ trước khi ghé.', 'toplist-khanh-hoa' ); ?></p>
				</div>
			</aside>
		</div>
	</article>

	<?php
	$related = new WP_Query(
		array(
			'category__in'        => $cat ? array( $cat->term_id ) : array(),
			'post__not_in'        => array( get_the_ID() ),
			'posts_per_page'      => 3,
			'orderby'             => 'rand',
			'ignore_sticky_posts' => true,
		)
	);
	if ( $related->have_posts() ) :
		?>
		<section class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin pb-space-2xl">
			<?php
			tkh_section_header(
				array(
					'icon'       => $info['icon'],
					'eyebrow'    => __( 'Có thể bạn quan tâm', 'toplist-khanh-hoa' ),
					/* translators: %s: category name */
					'title'      => $cat ? sprintf( __( 'Thêm gợi ý %s', 'toplist-khanh-hoa' ), mb_strtolower( $cat->name ) ) : __( 'Thêm gợi ý địa điểm', 'toplist-khanh-hoa' ),
					'link'       => $cat ? get_category_link( $cat ) : '',
					'link_label' => __( 'Xem tất cả', 'toplist-khanh-hoa' ),
				)
			);
			?>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
				<?php while ( $related->have_posts() ) : $related->the_post(); get_template_part( 'parts/card' ); endwhile; wp_reset_postdata(); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
