<?php
/**
 * Listing sidebar: top-rated places (within the archive's term), popular topics and a newsletter box.
 *
 * @var array $args { @type WP_Term|null $term }
 */

defined( 'ABSPATH' ) || exit;

$term      = isset( $args['term'] ) ? $args['term'] : null;
$top_query = array(
	'posts_per_page'      => 5,
	'meta_key'            => '_tkh_rating',
	'orderby'             => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $term ) {
	$top_query['tax_query'] = array( array( 'taxonomy' => $term->taxonomy, 'terms' => $term->term_id ) );
}
$top    = new WP_Query( $top_query );
$topics = get_terms( array( 'taxonomy' => 'post_tag', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hide_empty' => true ) );
$topics = is_wp_error( $topics ) ? array() : $topics;
?>
<div class="space-y-6 lg:sticky lg:top-24">
	<?php if ( $top->have_posts() ) : ?>
		<section class="bg-surface-container-lowest rounded-2xl border border-slate-border/70 p-5" aria-labelledby="top-rated-heading">
			<h2 id="top-rated-heading" class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 mb-3"><span class="material-symbols-outlined icon-fill text-sunset-gold" aria-hidden="true">star</span><?php esc_html_e( 'Đánh giá cao nhất', 'toplist-khanh-hoa' ); ?></h2>
			<ol class="space-y-1">
				<?php
				$rank = 0;
				while ( $top->have_posts() ) :
					$top->the_post();
					get_template_part( 'parts/card-mini', null, array( 'rank' => ++$rank ) );
				endwhile;
				wp_reset_postdata();
				?>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ( $topics ) : ?>
		<section class="bg-surface-container-lowest rounded-2xl border border-slate-border/70 p-5" aria-labelledby="topics-heading">
			<h2 id="topics-heading" class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2 mb-3"><span class="material-symbols-outlined text-primary" aria-hidden="true">sell</span><?php esc_html_e( 'Chủ đề phổ biến', 'toplist-khanh-hoa' ); ?></h2>
			<div class="flex flex-wrap gap-2">
				<?php foreach ( $topics as $topic ) : ?>
					<a class="chip !py-1 !px-3 !text-body-sm" href="<?php echo esc_url( get_term_link( $topic ) ); ?>">#<?php echo esc_html( $topic->name ); ?></a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#005d90] via-[#0077b6] to-[#023e8a] p-5 text-white" aria-labelledby="mini-newsletter">
		<div class="absolute -top-10 -right-10 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
		<h2 id="mini-newsletter" class="relative font-headline-sm text-headline-sm flex items-center gap-2"><span class="material-symbols-outlined text-sunset-gold" aria-hidden="true">mail</span><?php esc_html_e( 'Nhận gợi ý mỗi tuần', 'toplist-khanh-hoa' ); ?></h2>
		<p class="relative font-body-sm text-body-sm text-white/85 mt-1 mb-3"><?php esc_html_e( 'Quán mới, kinh nghiệm đi đảo và ưu đãi lưu trú gửi thẳng vào hộp thư.', 'toplist-khanh-hoa' ); ?></p>
		<form class="relative flex flex-col gap-2" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="tkh_subscribe">
			<?php wp_nonce_field( 'tkh_subscribe', 'tkh_nonce', false ); ?>
			<label class="sr-only" for="side-email"><?php esc_html_e( 'Email', 'toplist-khanh-hoa' ); ?></label>
			<input id="side-email" name="email" type="email" required autocomplete="email" class="w-full px-3.5 py-2.5 rounded-xl bg-white text-on-surface font-body-md text-body-md placeholder:text-slate-muted focus:outline-none focus:ring-2 focus:ring-sunset-gold" placeholder="<?php esc_attr_e( 'Nhập địa chỉ email của bạn...', 'toplist-khanh-hoa' ); ?>">
			<button class="w-full py-2.5 rounded-xl bg-secondary-container hover:bg-secondary text-on-secondary font-label-lg text-label-lg cursor-pointer transition-colors" type="submit"><?php esc_html_e( 'Đăng Ký Ngay', 'toplist-khanh-hoa' ); ?></button>
		</form>
	</section>
</div>
