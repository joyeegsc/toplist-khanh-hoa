<?php
/**
 * Compact place card (photography showcase row). Use inside the loop.
 */

defined( 'ABSPATH' ) || exit;

$cat    = tkh_post_category();
$info   = tkh_category_info( $cat );
$badge  = tkh_meta( 'badge' ) ? tkh_meta( 'badge' ) : ( $cat ? $cat->name : '' );
$region = tkh_post_region();
$count  = tkh_meta( 'review_count' );
?>
<article class="relative group bg-surface-container-lowest rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 flex flex-col" data-card="<?php the_ID(); ?>">
	<div class="relative h-48 w-full overflow-hidden bg-surface-container">
		<img alt="<?php echo esc_attr( get_the_title() ); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?php echo esc_url( tkh_image_url() ); ?>" loading="lazy" decoding="async" width="800" height="500">
		<?php if ( $badge ) : ?>
			<span class="absolute top-3 left-3 px-2.5 py-1 rounded-full backdrop-blur-md font-label-sm text-label-sm <?php echo esc_attr( $info['badge'] ); ?>"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<?php echo tkh_fav_button(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="absolute bottom-3 right-3"><?php echo tkh_rating_html( null, false, 'px-2.5 py-0.5 rounded-full bg-surface-container-lowest/90 backdrop-blur-md' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</div>
	<div class="p-4 flex-1 flex flex-col justify-between">
		<div>
			<div class="flex items-center gap-1 text-slate-muted font-body-sm text-body-sm mb-1">
				<span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true"><?php echo esc_html( $info['icon'] ); ?></span>
				<span class="line-clamp-1"><?php echo esc_html( $region ? $region->name : tkh_meta( 'address' ) ); ?></span>
			</div>
			<h3 class="font-headline-sm text-headline-sm text-on-surface line-clamp-2 group-hover:text-primary transition-colors">
				<a class="after:absolute after:inset-0" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h3>
			<p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-2 mt-1"><?php echo esc_html( get_the_excerpt() ); ?></p>
		</div>
		<div class="mt-3 pt-3 border-t border-slate-border/40 flex items-center justify-between gap-2">
			<span class="font-label-sm text-label-sm text-badge-verified font-medium"><?php echo '' !== $count ? esc_html( sprintf( /* translators: %s: number of reviews */ __( '%s đánh giá', 'toplist-khanh-hoa' ), tkh_format_number( $count ) ) ) : ''; ?></span>
			<span class="font-price-tag text-price-tag text-primary-container text-right"><?php echo esc_html( tkh_meta( 'price' ) ); ?></span>
		</div>
	</div>
</article>
