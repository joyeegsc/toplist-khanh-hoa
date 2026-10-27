<?php
/**
 * Template Name: Yêu thích
 *
 * Favorites page: renders every place in the current language hidden; main.js reveals the
 * ones saved in this browser's localStorage.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$places = new WP_Query( array( 'posts_per_page' => 500, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
?>
<section class="bg-surface-container-low border-b border-slate-border/60">
	<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-10 md:py-14">
		<?php tkh_breadcrumb( array( array( get_the_title(), null ) ) ); ?>
		<div class="flex items-center gap-2 text-primary font-label-md text-label-md uppercase tracking-wider mb-2">
			<span class="material-symbols-outlined icon-fill text-[18px] text-sunset-gold" aria-hidden="true">favorite</span><span><?php esc_html_e( 'Danh sách của bạn', 'toplist-khanh-hoa' ); ?></span>
		</div>
		<h1 class="font-display-hero-mobile text-display-hero-mobile md:font-display-hero md:text-display-hero text-on-surface"><?php the_title(); ?></h1>
		<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mt-3"><?php esc_html_e( 'Bấm biểu tượng trái tim trên mỗi địa điểm để lưu lại. Danh sách được giữ trên trình duyệt này.', 'toplist-khanh-hoa' ); ?></p>
	</div>
</section>
<section class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-8 md:py-10" data-favorites>
	<p class="font-body-md text-body-md text-slate-muted mb-5" role="status"><?php echo wp_kses( sprintf( /* translators: %s: number placeholder element */ __( 'Đã lưu %s địa điểm', 'toplist-khanh-hoa' ), '<strong class="text-on-surface" data-fav-count>0</strong>' ), array( 'strong' => array( 'class' => true, 'data-fav-count' => true ) ) ); ?></p>
	<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-gutter" data-fav-list>
		<?php while ( $places->have_posts() ) : $places->the_post(); get_template_part( 'parts/card', null, array( 'hidden' => true ) ); endwhile; wp_reset_postdata(); ?>
	</div>
	<div data-fav-empty>
		<?php get_template_part( 'parts/empty', null, array( 'title' => __( 'Bạn chưa lưu địa điểm nào', 'toplist-khanh-hoa' ), 'text' => __( 'Khám phá các điểm đến, quán ăn và nơi lưu trú rồi bấm ♥ để lưu.', 'toplist-khanh-hoa' ) ) ); ?>
	</div>
</section>
<?php
get_footer();
