<?php
/**
 * Standard place card (culinary / stay / listing grids). Use inside the loop.
 *
 * @var array $args Optional: 'hidden' => true renders the card hidden (favorites page).
 */

defined( 'ABSPATH' ) || exit;

$cat    = tkh_post_category();
$info   = tkh_category_info( $cat );
$badge  = tkh_meta( 'badge' ) ? tkh_meta( 'badge' ) : ( $cat ? $cat->name : '' );
$note   = tkh_meta( 'note' );
$region = tkh_post_region();
$hidden = ! empty( $args['hidden'] );
?>
<article class="relative bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm hover:shadow-[0_16px_40px_-8px_rgba(2,62,138,0.18)] hover:-translate-y-1 motion-reduce:hover:translate-y-0 border border-slate-border/60 transition-all duration-300 flex flex-col group" data-card="<?php the_ID(); ?>"<?php echo $hidden ? ' hidden' : ''; ?>>
	<div class="relative aspect-[16/10] w-full overflow-hidden bg-surface-container">
		<img alt="<?php echo esc_attr( tkh_place_name() ); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 motion-reduce:transition-none" src="<?php echo esc_url( tkh_image_url() ); ?>" loading="lazy" decoding="async" width="800" height="500">
		<div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#07223b]/50 to-transparent pointer-events-none"></div>
		<?php if ( $badge ) : ?>
			<span class="absolute top-3 left-3 px-3 py-1 rounded-full font-label-sm text-label-sm <?php echo esc_attr( $info['badge'] ); ?>"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<?php echo tkh_fav_button(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="absolute bottom-3 left-3"><?php echo tkh_rating_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</div>
	<div class="p-5 flex-1 flex flex-col">
		<div class="flex items-center justify-between gap-2 text-slate-muted font-body-sm text-body-sm mb-1.5">
			<span class="inline-flex items-center gap-1 min-w-0">
				<span class="material-symbols-outlined text-[16px] shrink-0 text-primary" aria-hidden="true"><?php echo esc_html( $info['icon'] ); ?></span>
				<span class="truncate"><?php echo esc_html( $region ? $region->name : tkh_meta( 'address' ) ); ?></span>
			</span>
			<?php echo tkh_price_level_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<h3 class="font-headline-sm text-headline-sm text-on-surface group-hover:text-primary transition-colors line-clamp-2">
			<a class="after:absolute after:inset-0 focus-visible:outline-none" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>
		<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mt-2"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<div class="mt-auto pt-4">
			<div class="pt-4 border-t border-slate-border/50 flex items-center justify-between gap-3">
				<?php if ( $note ) : ?>
					<span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-badge-verified"><span class="material-symbols-outlined icon-fill text-[14px]" aria-hidden="true">verified</span><?php echo esc_html( $note ); ?></span>
				<?php else : ?>
					<span class="font-label-md text-label-md text-slate-muted"><?php esc_html_e( 'Giá tham khảo', 'toplist-khanh-hoa' ); ?></span>
				<?php endif; ?>
				<span class="font-price-tag text-price-tag text-primary-container text-right"><?php echo esc_html( tkh_meta( 'price' ) ); ?></span>
			</div>
		</div>
	</div>
</article>
