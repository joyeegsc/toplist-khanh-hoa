<?php
/**
 * Listing page shared by archive.php, search.php and index.php: image hero with stats,
 * section switcher, full search filter (keyword, category, location, rating, price, sort),
 * active-filter chips, a "top pick" card, results grid, sidebar and cross-links.
 *
 * @var array $args {
 *   @type string       $eyebrow Small label above the title.
 *   @type string       $icon    Material Symbols icon name.
 *   @type string       $title   Page heading.
 *   @type string       $desc    Intro text.
 *   @type array        $crumbs  Breadcrumb items after "Home".
 *   @type WP_Term|null $term    The archive's own term, if any.
 * }
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
$filters   = tkh_current_filters();
$base      = tkh_archive_base();
$term      = isset( $args['term'] ) ? $args['term'] : null;
$nav_terms = tkh_nav_terms();
$regions   = get_terms( array( 'taxonomy' => 'khu_vuc', 'parent' => 0, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$regions   = is_wp_error( $regions ) ? array() : $regions;
$found     = (int) $wp_query->found_posts;
$active    = tkh_active_filters();
$stats     = $term ? tkh_term_stats( $term ) : null;
$hero_img  = $stats ? $stats['image'] : tkh_fallback_images()['bay'];
$clear_url = $term ? get_term_link( $term ) : tkh_search_url();

// The page's own term pre-fills its select so the filter reads "where you are".
$selected_cat    = 'category' === $base && $term ? $term->slug : basename( $filters['category_name'] );
$selected_region = 'khu_vuc' === $base && $term ? $term->slug : $filters['khu_vuc'];
$selected_tag    = 'post_tag' === $base && $term ? $term->slug : $filters['tag'];

// Quick chips below the filter: locations on most pages, categories on a location page.
$chip_key   = 'khu_vuc' === $base ? 'category_name' : 'khu_vuc';
$chip_terms = 'khu_vuc' === $base ? $nav_terms : $regions;
$chip_url   = function ( $value ) use ( $chip_key ) {
	$url = preg_replace( '#/page/\d+/?#', '/', remove_query_arg( array( $chip_key, 'paged' ) ) );
	return '' === $value ? $url : add_query_arg( $chip_key, $value, $url );
};

// Section switcher under the hero: sibling locations on a location page, categories elsewhere.
$switch_terms = 'khu_vuc' === $base ? $regions : $nav_terms;

$show_featured = ! is_paged() && ! $active && $found >= 4;
?>
<section class="relative w-full -mt-20 pt-28 pb-16 md:pb-20 px-margin-mobile md:px-margin overflow-hidden">
	<div class="absolute inset-0">
		<img src="<?php echo esc_url( $hero_img ); ?>" alt="" class="w-full h-full object-cover" fetchpriority="high">
		<div class="absolute inset-0 bg-gradient-to-b from-[#07223b]/85 via-[#023e8a]/70 to-[#07223b]/90"></div>
	</div>
	<div class="relative z-10 max-w-[1280px] mx-auto">
		<?php tkh_breadcrumb( $args['crumbs'], true ); ?>
		<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white font-label-md text-label-md uppercase tracking-wider mb-3">
			<span class="material-symbols-outlined text-[18px] text-sunset-gold" aria-hidden="true"><?php echo esc_html( $args['icon'] ); ?></span>
			<span><?php echo esc_html( $args['eyebrow'] ); ?></span>
		</div>
		<h1 class="font-display-hero-mobile text-display-hero-mobile md:font-display-hero md:text-display-hero text-white max-w-4xl"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $args['desc'] ) : ?>
			<p class="font-body-lg text-body-lg text-white/85 max-w-2xl mt-3"><?php echo esc_html( $args['desc'] ); ?></p>
		<?php endif; ?>
		<?php if ( $stats && $stats['count'] ) : ?>
			<dl class="mt-6 flex flex-wrap gap-2.5">
				<?php
				$figures = array(
					array( 'location_on', tkh_format_number( $stats['count'] ), _n( 'địa điểm', 'địa điểm', $stats['count'], 'toplist-khanh-hoa' ) ),
					array( 'star', number_format( $stats['avg'], 1, '.', '' ), __( 'điểm trung bình', 'toplist-khanh-hoa' ) ),
					array( 'reviews', tkh_format_number( $stats['reviews'] ), __( 'lượt đánh giá', 'toplist-khanh-hoa' ) ),
				);
				if ( 'khu_vuc' !== $base ) {
					$figures[] = array( 'map', tkh_format_number( $stats['areas'] ), _n( 'khu vực', 'khu vực', $stats['areas'], 'toplist-khanh-hoa' ) );
				}
				foreach ( $figures as $figure ) :
					?>
					<div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-white">
						<span class="material-symbols-outlined icon-fill text-[18px] text-sunset-gold" aria-hidden="true"><?php echo esc_html( $figure[0] ); ?></span>
						<dt class="sr-only"><?php echo esc_html( $figure[2] ); ?></dt>
						<dd class="font-headline-sm text-headline-sm"><?php echo esc_html( $figure[1] ); ?> <span class="font-body-sm text-body-sm text-white/75"><?php echo esc_html( $figure[2] ); ?></span></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>
</section>

<?php if ( $switch_terms ) : ?>
<nav class="relative z-20 max-w-[1280px] mx-auto px-margin-mobile md:px-margin -mt-7" aria-label="<?php echo 'khu_vuc' === $base ? esc_attr__( 'Khu vực khác', 'toplist-khanh-hoa' ) : esc_attr__( 'Chuyên mục', 'toplist-khanh-hoa' ); ?>">
	<div class="flex gap-2 overflow-x-auto no-scrollbar bg-surface-container-lowest rounded-2xl p-2 shadow-[0_12px_32px_-4px_rgba(2,62,138,0.12)] border border-slate-border/60">
		<?php if ( 'khu_vuc' !== $base ) : ?>
			<a class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-label-lg text-label-lg transition-colors <?php echo is_search() && '' === $filters['category_name'] ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:bg-surface-mist'; ?>" href="<?php echo esc_url( tkh_search_url() ); ?>"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">travel_explore</span><?php esc_html_e( 'Tất cả', 'toplist-khanh-hoa' ); ?></a>
		<?php endif; ?>
		<?php foreach ( $switch_terms as $key => $item ) : ?>
			<?php
			$is_current = ( $term && $term->term_id === $item->term_id ) || ( is_search() && 'category' === $item->taxonomy && basename( $filters['category_name'] ) === $item->slug );
			$icon       = 'khu_vuc' === $base ? 'location_on' : tkh_category_info( $item )['icon'];
			?>
			<a class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-label-lg text-label-lg transition-colors <?php echo $is_current ? 'bg-primary-container text-on-primary' : 'text-on-surface-variant hover:bg-surface-mist'; ?>" href="<?php echo esc_url( get_term_link( $item ) ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
				<span class="material-symbols-outlined text-[20px]" aria-hidden="true"><?php echo esc_html( $icon ); ?></span><?php echo esc_html( $item->name ); ?>
				<span class="px-1.5 rounded-md font-label-sm text-label-sm <?php echo $is_current ? 'bg-white/20' : 'bg-surface-mist'; ?>"><?php echo esc_html( $item->count ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</nav>
<?php endif; ?>

<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-8 md:py-10 grid grid-cols-1 lg:grid-cols-12 gap-8">
	<div class="lg:col-span-8 xl:col-span-9 min-w-0">

		<form id="bo-loc" class="bg-surface-container-lowest rounded-2xl border border-slate-border/70 shadow-[0_4px_20px_-2px_rgba(23,35,58,0.05)] p-4 md:p-5 mb-5 scroll-mt-24" role="search" method="get" action="<?php echo esc_url( tkh_home_url() ); ?>" data-autosubmit>
			<div class="flex items-center gap-2 mb-3">
				<span class="material-symbols-outlined text-primary" aria-hidden="true">tune</span>
				<h2 class="font-headline-sm text-headline-sm text-on-surface"><?php esc_html_e( 'Bộ lọc tìm kiếm', 'toplist-khanh-hoa' ); ?></h2>
			</div>
			<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-2.5">
				<div class="sm:col-span-2 xl:col-span-4">
					<label class="block font-label-md text-label-md text-slate-muted mb-1" for="list-s"><?php esc_html_e( 'Từ khóa', 'toplist-khanh-hoa' ); ?></label>
					<div class="field">
						<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true">search</span>
						<input id="list-s" name="s" value="<?php echo esc_attr( $filters['s'] ); ?>" class="w-full bg-transparent focus:outline-none placeholder:text-slate-muted" placeholder="<?php esc_attr_e( 'Tên địa điểm, món ăn...', 'toplist-khanh-hoa' ); ?>" type="search">
					</div>
				</div>
				<div class="xl:col-span-3">
					<label class="block font-label-md text-label-md text-slate-muted mb-1" for="list-cat"><?php esc_html_e( 'Chuyên mục', 'toplist-khanh-hoa' ); ?></label>
					<div class="field">
						<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true">category</span>
						<select id="list-cat" name="category_name" class="w-full bg-transparent focus:outline-none appearance-none cursor-pointer pr-6">
							<option value=""><?php esc_html_e( 'Tất Cả Danh Mục', 'toplist-khanh-hoa' ); ?></option>
							<?php foreach ( $nav_terms as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->slug ); ?>"<?php selected( $selected_cat, $cat->slug ); ?>><?php echo esc_html( $cat->name . ' (' . $cat->count . ')' ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-3 pointer-events-none" aria-hidden="true">expand_more</span>
					</div>
				</div>
				<div class="xl:col-span-3">
					<label class="block font-label-md text-label-md text-slate-muted mb-1" for="list-region"><?php esc_html_e( 'Địa điểm / Khu vực', 'toplist-khanh-hoa' ); ?></label>
					<div class="field">
						<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true">location_on</span>
						<select id="list-region" name="khu_vuc" class="w-full bg-transparent focus:outline-none appearance-none cursor-pointer pr-6">
							<option value=""><?php esc_html_e( 'Toàn Tỉnh', 'toplist-khanh-hoa' ); ?></option>
							<?php foreach ( $regions as $region ) : ?>
								<option value="<?php echo esc_attr( $region->slug ); ?>"<?php selected( $selected_region, $region->slug ); ?>><?php echo esc_html( $region->name . ' (' . $region->count . ')' ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-3 pointer-events-none" aria-hidden="true">expand_more</span>
					</div>
				</div>
				<div class="sm:col-span-2 xl:col-span-2 flex items-end">
					<button class="w-full h-11 cursor-pointer bg-gradient-to-r from-[#FF7A00] to-sunset-gold hover:brightness-105 text-on-primary font-headline-sm text-headline-sm rounded-xl flex items-center justify-center gap-2 shadow-md transition-all focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-sunset-gold" type="submit"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span><?php esc_html_e( 'Tìm', 'toplist-khanh-hoa' ); ?></button>
				</div>
				<?php
				$selects = array(
					'rating'      => array( __( 'Đánh giá', 'toplist-khanh-hoa' ), 'star', tkh_rating_options() ),
					'price_level' => array( __( 'Mức giá', 'toplist-khanh-hoa' ), 'payments', array( '' => __( 'Mọi mức giá', 'toplist-khanh-hoa' ) ) + tkh_price_levels() ),
					'sort'        => array( __( 'Sắp xếp', 'toplist-khanh-hoa' ), 'sort', tkh_sort_options() ),
				);
				foreach ( $selects as $name => $select ) :
					?>
					<div class="<?php echo 'sort' === $name ? 'sm:col-span-2 xl:col-span-4' : 'xl:col-span-4'; ?>">
						<label class="block font-label-md text-label-md text-slate-muted mb-1" for="f-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $select[0] ); ?></label>
						<div class="field">
							<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true"><?php echo esc_html( $select[1] ); ?></span>
							<select id="f-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" class="w-full bg-transparent focus:outline-none appearance-none cursor-pointer pr-6">
								<?php foreach ( $select[2] as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $filters[ $name ], (string) $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-3 pointer-events-none" aria-hidden="true">expand_more</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( '' !== $selected_tag ) : ?>
				<input type="hidden" name="tag" value="<?php echo esc_attr( $selected_tag ); ?>">
			<?php endif; ?>

			<?php if ( $chip_terms ) : ?>
				<div class="mt-4 pt-4 border-t border-slate-border/60 flex items-center gap-2">
					<span class="shrink-0 font-label-sm text-label-sm text-slate-muted uppercase tracking-wider"><?php echo 'khu_vuc' === $chip_key ? esc_html__( 'Lọc nhanh khu vực:', 'toplist-khanh-hoa' ) : esc_html__( 'Lọc nhanh chuyên mục:', 'toplist-khanh-hoa' ); ?></span>
					<div class="flex gap-2 overflow-x-auto no-scrollbar -mr-1 pr-1" role="group">
						<?php $current_chip = 'khu_vuc' === $chip_key ? $filters['khu_vuc'] : basename( $filters['category_name'] ); ?>
						<a class="chip !py-1 !px-3 !text-body-sm<?php echo '' === $current_chip ? ' is-active' : ''; ?>" href="<?php echo esc_url( $chip_url( '' ) ); ?>" rel="nofollow"><?php esc_html_e( 'Tất cả', 'toplist-khanh-hoa' ); ?></a>
						<?php foreach ( $chip_terms as $chip ) : ?>
							<a class="chip !py-1 !px-3 !text-body-sm<?php echo $current_chip === $chip->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( $chip_url( $chip->slug ) ); ?>" rel="nofollow"<?php echo $current_chip === $chip->slug ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $chip->name ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
			<noscript><button type="submit" class="chip mt-3"><?php esc_html_e( 'Áp dụng', 'toplist-khanh-hoa' ); ?></button></noscript>
		</form>

		<div class="flex flex-wrap items-center gap-2 mb-6" role="status">
			<p class="font-body-md text-body-md text-slate-muted mr-2"><?php echo wp_kses( sprintf( /* translators: %s: number of places found */ _n( 'Tìm thấy %s địa điểm', 'Tìm thấy %s địa điểm', $found, 'toplist-khanh-hoa' ), '<strong class="text-on-surface">' . esc_html( tkh_format_number( $found ) ) . '</strong>' ), array( 'strong' => array( 'class' => true ) ) ); ?></p>
			<?php foreach ( $active as $chip ) : ?>
				<a class="inline-flex items-center gap-1 pl-3 pr-2 py-1 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-md text-label-md hover:bg-primary-fixed-dim transition-colors" href="<?php echo esc_url( $chip[1] ); ?>" rel="nofollow" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: filter name */ __( 'Bỏ lọc %s', 'toplist-khanh-hoa' ), $chip[0] ) ); ?>">
					<?php echo esc_html( $chip[0] ); ?><span class="material-symbols-outlined text-[16px]" aria-hidden="true">close</span>
				</a>
			<?php endforeach; ?>
			<?php if ( count( $active ) > 1 ) : ?>
				<a class="inline-flex items-center gap-1 font-label-md text-label-md text-secondary hover:underline" href="<?php echo esc_url( $clear_url ); ?>" rel="nofollow"><span class="material-symbols-outlined text-[16px]" aria-hidden="true">filter_alt_off</span><?php esc_html_e( 'Xóa tất cả bộ lọc', 'toplist-khanh-hoa' ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( have_posts() ) : ?>
			<?php
			if ( $show_featured ) {
				the_post();
				get_template_part( 'parts/card-horizontal' );
			}
			?>
			<h2 class="sr-only"><?php esc_html_e( 'Danh sách địa điểm', 'toplist-khanh-hoa' ); ?></h2>
			<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-gutter">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'parts/card' ); endwhile; ?>
			</div>
			<?php tkh_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'parts/empty', null, array( 'title' => __( 'Chưa có địa điểm phù hợp', 'toplist-khanh-hoa' ), 'text' => __( 'Thử bỏ bớt bộ lọc hoặc tìm với từ khóa khác.', 'toplist-khanh-hoa' ) ) ); ?>
		<?php endif; ?>
	</div>

	<aside class="lg:col-span-4 xl:col-span-3">
		<?php get_template_part( 'parts/listing-sidebar', null, array( 'term' => $term ) ); ?>
	</aside>
</div>

<?php
// Cross-links to the other categories (or other locations on a location page).
$more = array_filter( $switch_terms, fn( $item ) => ! $term || $item->term_id !== $term->term_id );
if ( $more ) :
	?>
	<section class="bg-surface-container-low py-12 md:py-16">
		<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin">
			<?php
			tkh_section_header(
				array(
					'icon'    => 'khu_vuc' === $base ? 'map' : 'explore',
					'eyebrow' => __( 'Khám phá thêm', 'toplist-khanh-hoa' ),
					'title'   => 'khu_vuc' === $base ? __( 'Các khu vực khác ở Khánh Hòa', 'toplist-khanh-hoa' ) : __( 'Các chuyên mục khác', 'toplist-khanh-hoa' ),
				)
			);
			?>
			<?php $more_cols = array( 1 => 'lg:grid-cols-2', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5' ); ?>
			<div class="grid grid-cols-2 md:grid-cols-3 <?php echo esc_attr( $more_cols[ min( 5, count( $more ) ) ] ); ?> gap-gutter">
				<?php foreach ( array_slice( $more, 0, 5 ) as $item ) : $item_stats = tkh_term_stats( $item ); ?>
					<a class="group relative h-40 md:h-48 rounded-2xl overflow-hidden flex items-end p-4 shadow-sm hover:shadow-lg transition-shadow" href="<?php echo esc_url( get_term_link( $item ) ); ?>">
						<img src="<?php echo esc_url( $item_stats['image'] ); ?>" alt="" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none" loading="lazy" decoding="async" width="400" height="300">
						<span class="absolute inset-0 bg-gradient-to-t from-[#07223b]/90 via-[#07223b]/30 to-transparent"></span>
						<span class="relative text-white">
							<span class="block font-headline-sm text-headline-sm"><?php echo esc_html( $item->name ); ?></span>
							<span class="block font-body-sm text-body-sm text-white/80"><?php echo esc_html( sprintf( /* translators: %s: number of places */ _n( '%s địa điểm', '%s địa điểm', $item->count, 'toplist-khanh-hoa' ), $item->count ) ); ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
