<?php
/**
 * Empty-state block.
 *
 * @var array $args { title, text }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="text-center py-16 px-6 bg-surface-container-lowest rounded-2xl border border-dashed border-slate-border">
	<div class="w-14 h-14 mx-auto mb-4 rounded-full bg-primary-fixed text-primary flex items-center justify-center"><span class="material-symbols-outlined" aria-hidden="true">travel_explore</span></div>
	<h2 class="font-headline-md text-headline-md text-on-surface"><?php echo esc_html( $args['title'] ); ?></h2>
	<p class="font-body-md text-body-md text-on-surface-variant mt-1 mb-5"><?php echo esc_html( $args['text'] ); ?></p>
	<a class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-primary-container text-on-primary font-headline-sm text-headline-sm hover:bg-primary transition-colors" href="<?php echo esc_url( tkh_search_url() ); ?>"><?php esc_html_e( 'Xem tất cả địa điểm', 'toplist-khanh-hoa' ); ?></a>
</div>
