<?php
/**
 * Wide "top pick" card shown first on unfiltered listings. Use inside the loop.
 */

defined( 'ABSPATH' ) || exit;

$cat    = tkh_post_category();
$info   = tkh_category_info( $cat );
$region = tkh_post_region();
$badge  = tkh_meta( 'badge' ) ? tkh_meta( 'badge' ) : ( $cat ? $cat->name : '' );
?>
<article class="relative group grid md:grid-cols-5 bg-surface-container-lowest rounded-3xl overflow-hidden border border-slate-border/70 shadow-[0_12px_32px_-4px_rgba(2,62,138,0.08)] hover:shadow-[0_20px_48px_-8px_rgba(2,62,138,0.16)] transition-shadow duration-300 mb-gutter" data-card="<?php the_ID(); ?>">
	<div class="relative md:col-span-2 h-60 md:h-full min-h-[260px] overflow-hidden bg-surface-container">
		<img alt="<?php echo esc_attr( tkh_place_name() ); ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none" src="<?php echo esc_url( tkh_image_url() ); ?>" loading="lazy" decoding="async" width="800" height="500">
		<span class="absolute top-4 left-4 inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-gradient-to-r from-[#FF7A00] to-sunset-gold text-on-primary font-label-md text-label-md shadow-md">
			<span class="material-symbols-outlined icon-fill text-[16px]" aria-hidden="true">trending_up</span><?php esc_html_e( 'Nổi bật nhất', 'toplist-khanh-hoa' ); ?>
		</span>
		<?php echo tkh_fav_button(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<div class="md:col-span-3 p-6 md:p-8 flex flex-col">
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<?php if ( $badge ) : ?><span class="px-3 py-1 rounded-full font-label-sm text-label-sm <?php echo esc_attr( $info['badge'] ); ?>"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
			<?php if ( $region ) : ?><span class="inline-flex items-center gap-1 font-body-sm text-body-sm text-slate-muted"><span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true">location_on</span><?php echo esc_html( $region->name ); ?></span><?php endif; ?>
		</div>
		<h2 class="font-headline-md text-[22px] md:text-[26px] leading-tight font-bold text-on-surface group-hover:text-primary transition-colors">
			<a class="after:absolute after:inset-0 focus-visible:outline-none" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<p class="font-body-md text-body-md md:font-body-lg md:text-body-lg text-on-surface-variant line-clamp-3 mt-3"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="mt-auto pt-5 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-slate-border/60 mt-6">
			<?php echo tkh_rating_html( null, true, 'bg-secondary-fixed/60 px-3 py-1 rounded-lg' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="font-price-tag text-price-tag text-primary-container"><?php echo esc_html( tkh_meta( 'price' ) ); ?></span>
			<?php echo tkh_price_level_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="ml-auto inline-flex items-center gap-1 font-label-lg text-label-lg text-primary-container group-hover:gap-2 transition-all"><?php esc_html_e( 'Đọc review', 'toplist-khanh-hoa' ); ?><span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span></span>
		</div>
	</div>
</article>
