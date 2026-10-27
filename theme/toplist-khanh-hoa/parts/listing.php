<?php
/**
 * Listing page body shared by archive.php, search.php and index.php.
 *
 * @var array $args {
 *   @type string $eyebrow     Small label above the title.
 *   @type string $icon        Material Symbols icon name.
 *   @type string $title       Page heading.
 *   @type string $desc        Intro text.
 *   @type array  $crumbs      Breadcrumb items after "Home".
 *   @type bool   $show_search Whether to show the keyword/category fields (search page).
 * }
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
$filters   = tkh_current_filters();
$regions   = get_terms( array( 'taxonomy' => 'khu_vuc', 'parent' => 0, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$regions   = is_wp_error( $regions ) ? array() : $regions;
$nav_terms = tkh_nav_terms();

// Region chips link to the same listing with khu_vuc swapped; region archives filter by category instead.
$base       = tkh_archive_base();
$on_region  = 'khu_vuc' === $base;
$chip_key   = $on_region ? 'category_name' : 'khu_vuc';
$chip_items = array();
foreach ( $on_region ? $nav_terms : $regions as $term ) {
	$chip_items[ $term->slug ] = $term->name;
}
$chip_url = function ( $value ) use ( $chip_key ) {
	$url = remove_query_arg( array( $chip_key, 'paged' ) );
	$url = preg_replace( '#/page/\d+/?#', '/', $url );
	return '' === $value ? $url : add_query_arg( $chip_key, $value, $url );
};
$found = (int) $wp_query->found_posts;
?>
<section class="bg-surface-container-low border-b border-slate-border/60">
	<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-10 md:py-14">
		<?php tkh_breadcrumb( $args['crumbs'] ); ?>
		<div class="flex items-center gap-2 text-primary font-label-md text-label-md uppercase tracking-wider mb-2">
			<span class="material-symbols-outlined text-[18px]" aria-hidden="true"><?php echo esc_html( $args['icon'] ); ?></span>
			<span><?php echo esc_html( $args['eyebrow'] ); ?></span>
		</div>
		<h1 class="font-display-hero-mobile text-display-hero-mobile md:font-display-hero md:text-display-hero text-on-surface"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $args['desc'] ) : ?>
			<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mt-3"><?php echo esc_html( $args['desc'] ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-8 md:py-10">
	<form class="bg-surface-container-lowest rounded-2xl border border-slate-border/70 shadow-[0_4px_20px_-2px_rgba(23,35,58,0.05)] p-3 md:p-4 mb-6" method="get" action="<?php echo esc_url( is_search() ? tkh_home_url() : strtok( $chip_url( '' ), '?' ) ); ?>" data-autosubmit>
		<?php if ( ! empty( $args['show_search'] ) ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 mb-3">
				<div class="md:col-span-6 field">
					<span class="material-symbols-outlined text-slate-muted text-[22px] mr-2 shrink-0" aria-hidden="true">search</span>
					<label class="sr-only" for="list-s"><?php esc_html_e( 'Từ khóa', 'toplist-khanh-hoa' ); ?></label>
					<input id="list-s" name="s" value="<?php echo esc_attr( $filters['s'] ); ?>" class="w-full bg-transparent focus:outline-none placeholder:text-slate-muted" placeholder="<?php esc_attr_e( 'Tên địa điểm, món ăn, khu vực...', 'toplist-khanh-hoa' ); ?>" type="search">
				</div>
				<div class="md:col-span-4 field">
					<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2 shrink-0" aria-hidden="true">category</span>
					<label class="sr-only" for="list-cat"><?php esc_html_e( 'Danh mục', 'toplist-khanh-hoa' ); ?></label>
					<select id="list-cat" name="category_name" class="w-full bg-transparent focus:outline-none appearance-none cursor-pointer pr-5">
						<option value=""><?php esc_html_e( 'Tất Cả Danh Mục', 'toplist-khanh-hoa' ); ?></option>
						<?php foreach ( $nav_terms as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->slug ); ?>"<?php selected( $filters['category_name'], $cat->slug ); ?>><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-3 pointer-events-none" aria-hidden="true">expand_more</span>
				</div>
				<div class="md:col-span-2">
					<button class="w-full h-11 bg-gradient-to-r from-[#FF7A00] to-sunset-gold text-on-primary font-headline-sm text-headline-sm rounded-xl flex items-center justify-center gap-2 shadow-md" type="submit"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">explore</span><?php esc_html_e( 'Tìm', 'toplist-khanh-hoa' ); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<?php
		// Hidden inputs keep filters that aren't the page's own taxonomy or a visible field.
		$own = array( 'category_name' => 'category', 'khu_vuc' => 'khu_vuc', 'tag' => 'post_tag' );
		foreach ( $own as $carry => $taxonomy ) :
			if ( '' !== $filters[ $carry ] && $taxonomy !== $base && ! ( 'category_name' === $carry && ! empty( $args['show_search'] ) ) ) :
				?>
				<input type="hidden" name="<?php echo esc_attr( $carry ); ?>" value="<?php echo esc_attr( $filters[ $carry ] ); ?>">
				<?php
			endif;
		endforeach;
		?>

		<div class="flex flex-col lg:flex-row lg:items-center gap-3">
			<div class="flex gap-2 overflow-x-auto no-scrollbar -mx-1 px-1 lg:flex-1" role="group" aria-label="<?php echo $on_region ? esc_attr__( 'Lọc theo danh mục', 'toplist-khanh-hoa' ) : esc_attr__( 'Lọc theo khu vực', 'toplist-khanh-hoa' ); ?>">
				<a class="chip<?php echo '' === $filters[ $chip_key ] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $chip_url( '' ) ); ?>" rel="nofollow"><?php esc_html_e( 'Tất cả', 'toplist-khanh-hoa' ); ?></a>
				<?php foreach ( $chip_items as $slug => $label ) : ?>
					<a class="chip<?php echo $filters[ $chip_key ] === $slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( $chip_url( $slug ) ); ?>" rel="nofollow"<?php echo $filters[ $chip_key ] === $slug ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>
			<div class="grid grid-cols-3 gap-2 lg:w-[480px] shrink-0">
				<?php
				$selects = array(
					'rating'      => array( __( 'Đánh giá', 'toplist-khanh-hoa' ), tkh_rating_options() ),
					'price_level' => array( __( 'Mức giá', 'toplist-khanh-hoa' ), array( '' => __( 'Mọi mức giá', 'toplist-khanh-hoa' ) ) + tkh_price_levels() ),
					'sort'        => array( __( 'Sắp xếp', 'toplist-khanh-hoa' ), tkh_sort_options() ),
				);
				foreach ( $selects as $name => $select ) :
					?>
					<div class="field !py-2 !px-3">
						<label class="sr-only" for="f-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $select[0] ); ?></label>
						<select id="f-<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" class="w-full bg-transparent font-body-sm text-body-sm md:font-body-md md:text-body-md focus:outline-none appearance-none cursor-pointer pr-5">
							<?php foreach ( $select[1] as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $filters[ $name ], (string) $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="material-symbols-outlined text-slate-muted text-[18px] absolute right-2 pointer-events-none" aria-hidden="true">expand_more</span>
					</div>
				<?php endforeach; ?>
			</div>
			<noscript><button type="submit" class="chip"><?php esc_html_e( 'Áp dụng', 'toplist-khanh-hoa' ); ?></button></noscript>
		</div>
	</form>

	<p class="font-body-md text-body-md text-slate-muted mb-5" role="status">
		<?php if ( '' !== $filters['tag'] && 'post_tag' !== $base ) : $tag = get_term_by( 'slug', $filters['tag'], 'post_tag' ); ?>
			<?php esc_html_e( 'Đang lọc theo:', 'toplist-khanh-hoa' ); ?> <strong class="text-on-surface"><?php echo esc_html( $tag ? $tag->name : $filters['tag'] ); ?></strong> ·
		<?php endif; ?>
		<?php echo wp_kses( sprintf( /* translators: %s: number of places found */ _n( 'Tìm thấy %s địa điểm', 'Tìm thấy %s địa điểm', $found, 'toplist-khanh-hoa' ), '<strong class="text-on-surface">' . esc_html( tkh_format_number( $found ) ) . '</strong>' ), array( 'strong' => array( 'class' => true ) ) ); ?>
	</p>

	<?php if ( have_posts() ) : ?>
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
			<?php while ( have_posts() ) : the_post(); get_template_part( 'parts/card' ); endwhile; ?>
		</div>
		<?php tkh_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'parts/empty', null, array( 'title' => __( 'Chưa có địa điểm phù hợp', 'toplist-khanh-hoa' ), 'text' => __( 'Thử bỏ bớt bộ lọc hoặc tìm với từ khóa khác.', 'toplist-khanh-hoa' ) ) ); ?>
	<?php endif; ?>
</section>
