<?php
/**
 * Compact row card for sidebars (thumbnail, title, rating). Use inside the loop.
 *
 * @var array $args Optional 'rank' => int to show a position number.
 */

defined( 'ABSPATH' ) || exit;

$rank = isset( $args['rank'] ) ? (int) $args['rank'] : 0;
?>
<li class="relative group flex items-center gap-3 p-2 -mx-2 rounded-xl hover:bg-surface-mist transition-colors">
	<div class="relative w-16 h-16 shrink-0 rounded-xl overflow-hidden bg-surface-container">
		<img alt="" class="w-full h-full object-cover" src="<?php echo esc_url( tkh_image_url( 'thumbnail' ) ); ?>" loading="lazy" decoding="async" width="64" height="64">
		<?php if ( $rank ) : ?>
			<span class="absolute top-1 left-1 w-5 h-5 rounded-full bg-deep-ocean text-on-primary font-label-sm text-label-sm flex items-center justify-center" aria-hidden="true"><?php echo esc_html( $rank ); ?></span>
		<?php endif; ?>
	</div>
	<div class="min-w-0">
		<a class="font-label-lg text-label-lg text-on-surface line-clamp-2 group-hover:text-primary after:absolute after:inset-0" href="<?php the_permalink(); ?>"><?php echo esc_html( tkh_place_name() ); ?></a>
		<div class="mt-1 flex items-center gap-2"><?php echo tkh_rating_html( null, false, '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo tkh_price_level_badge(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
</li>
