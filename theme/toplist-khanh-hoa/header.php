<?php
/**
 * Site header: glass top bar with logo, category navigation, search and language switch.
 */

defined( 'ABSPATH' ) || exit;

$current_cat = is_single() ? tkh_term_key( tkh_post_category() ) : '';
$nav_items   = array( array( __( 'Trang Chủ', 'toplist-khanh-hoa' ), tkh_home_url(), is_front_page() ) );
foreach ( tkh_nav_terms() as $slug => $cat ) {
	$current     = ( is_category() && 'category' === tkh_archive_base() && get_queried_object_id() === $cat->term_id ) || $current_cat === $slug;
	$nav_items[] = array( $cat->name, get_category_link( $cat ), $current );
}
$GLOBALS['tkh_nav_items'] = $nav_items;
$languages                = tkh_languages();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-surface font-body-md text-body-md text-on-surface pb-24 lg:pb-0' ); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg" href="#noi-dung"><?php esc_html_e( 'Bỏ qua đến nội dung', 'toplist-khanh-hoa' ); ?></a>
<header class="site-header fixed top-0 left-0 w-full z-50 bg-card-glass-bg backdrop-blur-xl shadow-[0_1px_8px_rgba(2,62,138,0.06)]">
	<div class="h-20 max-w-[1280px] mx-auto px-margin-mobile md:px-margin flex items-center justify-between gap-gutter">
		<a class="flex items-center shrink-0 pr-2" href="<?php echo esc_url( tkh_home_url() ); ?>" rel="home">
			<img alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="h-10 md:h-11 w-auto object-contain" src="<?php echo esc_url( tkh_logo_url() ); ?>" width="160" height="44">
		</a>
		<nav class="hidden lg:flex items-center gap-1 xl:gap-3 shrink-0" aria-label="<?php esc_attr_e( 'Điều hướng chính', 'toplist-khanh-hoa' ); ?>">
			<?php foreach ( $nav_items as $item ) : ?>
				<a class="px-3 py-1.5 rounded-lg font-headline-sm text-headline-sm transition-all whitespace-nowrap <?php echo $item[2] ? 'bg-primary-container text-on-primary-container' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'; ?>" href="<?php echo esc_url( $item[1] ); ?>"<?php echo $item[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item[0] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="flex items-center gap-3 shrink-0">
			<form class="hidden xl:flex relative items-center w-48" role="search" method="get" action="<?php echo esc_url( tkh_home_url() ); ?>">
				<span class="material-symbols-outlined absolute left-space-sm text-slate-muted text-[18px] pointer-events-none" aria-hidden="true">search</span>
				<label class="sr-only" for="tkh-header-search"><?php esc_html_e( 'Tìm kiếm', 'toplist-khanh-hoa' ); ?></label>
				<input id="tkh-header-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" class="w-full pl-8 pr-space-sm py-1.5 rounded-full bg-surface-porcelain text-on-surface font-body-sm text-body-sm placeholder:text-slate-muted focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary-container/30 shadow-[0_1px_4px_rgba(23,35,58,0.04)] transition-all" placeholder="<?php esc_attr_e( 'Tìm kiếm điểm đến...', 'toplist-khanh-hoa' ); ?>" type="search">
			</form>
			<a class="hidden lg:flex xl:hidden w-9 h-9 rounded-full bg-surface-porcelain items-center justify-center text-slate-muted hover:text-primary" href="<?php echo esc_url( tkh_search_url() ); ?>" aria-label="<?php esc_attr_e( 'Tìm kiếm', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">search</span></a>
			<?php if ( $languages ) : ?>
				<nav class="flex items-center p-0.5 rounded-full bg-surface-mist shrink-0" aria-label="<?php esc_attr_e( 'Ngôn ngữ', 'toplist-khanh-hoa' ); ?>">
					<?php foreach ( $languages as $i => $lang ) : ?>
						<?php if ( $i ) : ?><span class="text-outline-variant font-label-sm text-label-sm px-0.5" aria-hidden="true">|</span><?php endif; ?>
						<?php if ( $lang['current'] ) : ?>
							<span class="px-2.5 py-1 rounded-full bg-surface-container-lowest shadow-[0_1px_3px_rgba(23,35,58,0.08)] font-label-sm text-label-sm text-deep-ocean" aria-current="true" title="<?php echo esc_attr( $lang['name'] ); ?>"><?php echo esc_html( $lang['label'] ); ?></span>
						<?php else : ?>
							<a class="px-2.5 py-1 rounded-full font-label-sm text-label-sm text-slate-muted hover:text-on-surface transition-all" href="<?php echo esc_url( $lang['url'] ); ?>" hreflang="<?php echo esc_attr( $lang['slug'] ); ?>" lang="<?php echo esc_attr( $lang['slug'] ); ?>" title="<?php echo esc_attr( $lang['name'] ); ?>"><?php echo esc_html( $lang['label'] ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</header>
<main id="noi-dung" class="w-full pt-20 bg-surface">
