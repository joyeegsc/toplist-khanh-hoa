<?php
/**
 * Site footer, mobile bottom dock and slide-in menu.
 */

defined( 'ABSPATH' ) || exit;

$explore = array();
foreach ( tkh_nav_terms() as $cat ) {
	/* translators: %s: category name, e.g. "Ẩm Thực" */
	$explore[] = array( sprintf( __( '%s Khánh Hòa', 'toplist-khanh-hoa' ), $cat->name ), get_category_link( $cat ) );
}
$support = array_filter(
	array(
		array( __( 'Về Toplist Khánh Hòa', 'toplist-khanh-hoa' ), tkh_page_url( 'gioi-thieu' ) ),
		array( __( 'Hợp Tác Quảng Bá & Truyền Thông', 'toplist-khanh-hoa' ), tkh_page_url( 'hop-tac-quang-ba' ) ),
		array( __( 'Quy Chuẩn Thẩm Định Điểm Đến', 'toplist-khanh-hoa' ), tkh_page_url( 'quy-chuan-tham-dinh' ) ),
		array( __( 'Chính Sách Bảo Mật', 'toplist-khanh-hoa' ), tkh_page_url( 'chinh-sach-bao-mat' ) ),
		array( __( 'Điều Khoản Sử Dụng', 'toplist-khanh-hoa' ), tkh_page_url( 'dieu-khoan-su-dung' ) ),
	),
	fn( $item ) => '' !== $item[1]
);
$favorites_url = tkh_page_url( 'yeu-thich' );
$nav_items     = isset( $GLOBALS['tkh_nav_items'] ) ? $GLOBALS['tkh_nav_items'] : array();
$du_lich       = tkh_term( 'du-lich', 'category' );
?>
</main>
<footer class="w-full bg-surface-container-low shadow-[0_-1px_12px_rgba(2,62,138,0.03)]">
	<div class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin py-space-2xl">
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter gap-y-10 mb-space-2xl">
			<div>
				<div class="mb-space-md"><img alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="h-8 w-auto object-contain" src="<?php echo esc_url( tkh_logo_url() ); ?>" width="116" height="32" loading="lazy"></div>
				<p class="font-body-md text-body-md text-on-surface-variant mb-space-md"><?php esc_html_e( 'Chuyên trang giới thiệu, đánh giá và định hướng trải nghiệm du lịch, ẩm thực, lưu trú tại Khánh Hòa: Nha Trang, Cam Ranh, Ninh Hòa, Vạn Ninh và Phan Rang.', 'toplist-khanh-hoa' ); ?></p>
				<a class="inline-flex items-center gap-2 font-label-lg text-label-lg text-primary hover:text-deep-ocean" href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>"><span class="material-symbols-outlined text-[20px]" aria-hidden="true">rss_feed</span><?php esc_html_e( 'Theo dõi bài viết mới', 'toplist-khanh-hoa' ); ?></a>
			</div>
			<div>
				<h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md"><?php esc_html_e( 'Khám Phá Khánh Hòa', 'toplist-khanh-hoa' ); ?></h2>
				<ul class="space-y-space-sm font-body-md text-body-md text-on-surface-variant">
					<?php foreach ( $explore as $item ) : ?>
						<li><a class="hover:text-primary transition-colors" href="<?php echo esc_url( $item[1] ); ?>"><?php echo esc_html( $item[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md"><?php esc_html_e( 'Hỗ Trợ & Đối Tác', 'toplist-khanh-hoa' ); ?></h2>
				<ul class="space-y-space-sm font-body-md text-body-md text-on-surface-variant">
					<?php foreach ( $support as $item ) : ?>
						<li><a class="hover:text-primary transition-colors" href="<?php echo esc_url( $item[1] ); ?>"><?php echo esc_html( $item[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<h2 class="font-headline-sm text-headline-sm text-on-surface mb-space-md"><?php esc_html_e( 'Thông Tin Liên Hệ', 'toplist-khanh-hoa' ); ?></h2>
				<ul class="space-y-space-sm font-body-md text-body-md text-on-surface-variant">
					<?php if ( tkh_contact( 'address' ) ) : ?>
						<li class="flex items-start gap-space-xs"><span class="material-symbols-outlined text-primary text-[18px] mt-0.5" aria-hidden="true">location_on</span><span><?php echo esc_html( tkh_contact( 'address' ) ); ?></span></li>
					<?php endif; ?>
					<?php if ( tkh_contact( 'phone' ) ) : ?>
						<li class="flex items-center gap-space-xs"><span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">call</span><a class="hover:text-primary" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', tkh_contact( 'phone' ) ) ); ?>"><?php echo esc_html( tkh_contact( 'phone' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( tkh_contact( 'email' ) ) : ?>
						<li class="flex items-center gap-space-xs"><span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">mail</span><a class="hover:text-primary" href="mailto:<?php echo esc_attr( tkh_contact( 'email' ) ); ?>"><?php echo esc_html( tkh_contact( 'email' ) ); ?></a></li>
					<?php endif; ?>
					<?php if ( tkh_contact( 'hours' ) ) : ?>
						<li class="flex items-center gap-space-xs"><span class="material-symbols-outlined text-primary text-[18px]" aria-hidden="true">schedule</span><span><?php echo esc_html( sprintf( /* translators: %s: support hours */ __( 'Hỗ trợ: %s', 'toplist-khanh-hoa' ), tkh_contact( 'hours' ) ) ); ?></span></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="flex flex-col md:flex-row items-center justify-between gap-space-md bg-surface-container-high/40 rounded-xl px-space-md py-space-sm">
			<p class="font-body-sm text-body-sm text-on-surface-variant text-center md:text-left">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Bảo lưu mọi quyền. Nền tảng kết nối du lịch duyên hải miền Trung.', 'toplist-khanh-hoa' ); ?></p>
			<div class="flex items-center gap-space-md font-body-sm text-body-sm text-on-surface-variant">
				<?php if ( tkh_page_url( 'chinh-sach-bao-mat' ) ) : ?><a class="hover:text-primary transition-colors" href="<?php echo esc_url( tkh_page_url( 'chinh-sach-bao-mat' ) ); ?>"><?php esc_html_e( 'Bảo mật', 'toplist-khanh-hoa' ); ?></a><?php endif; ?>
				<?php if ( tkh_page_url( 'dieu-khoan-su-dung' ) ) : ?><a class="hover:text-primary transition-colors" href="<?php echo esc_url( tkh_page_url( 'dieu-khoan-su-dung' ) ); ?>"><?php esc_html_e( 'Điều khoản', 'toplist-khanh-hoa' ); ?></a><?php endif; ?>
				<a class="hover:text-primary transition-colors" href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>"><?php esc_html_e( 'Sơ đồ trang', 'toplist-khanh-hoa' ); ?></a>
			</div>
		</div>
	</div>
</footer>

<nav class="lg:hidden fixed bottom-3 inset-x-3 z-50 h-16 rounded-2xl bg-white/90 backdrop-blur-xl border border-white/60 shadow-[0_20px_48px_-8px_rgba(23,35,58,0.18)] grid grid-cols-5 items-center" aria-label="<?php esc_attr_e( 'Điều hướng nhanh', 'toplist-khanh-hoa' ); ?>">
	<?php
	$dock_link = function ( $href, $icon, $label, $active ) {
		printf(
			'<a href="%s" class="flex flex-col items-center gap-0.5 font-label-sm text-label-sm %s"%s><span class="material-symbols-outlined text-[22px]%s" aria-hidden="true">%s</span>%s</a>',
			esc_url( $href ),
			$active ? 'text-primary-container' : 'text-slate-muted',
			$active ? ' aria-current="page"' : '',
			$active ? ' icon-fill' : '',
			esc_html( $icon ),
			esc_html( $label )
		);
	};
	$dock_link( tkh_home_url(), 'explore', __( 'Khám Phá', 'toplist-khanh-hoa' ), is_front_page() );
	$dock_link( $du_lich ? get_category_link( $du_lich ) : tkh_home_url(), 'location_on', __( 'Địa Điểm', 'toplist-khanh-hoa' ), $du_lich && is_category( $du_lich->term_id ) );
	?>
	<a href="<?php echo esc_url( tkh_search_url() ); ?>" class="-mt-8 mx-auto w-14 h-14 rounded-full bg-primary-container text-on-primary shadow-lg ring-4 ring-surface flex items-center justify-center" aria-label="<?php esc_attr_e( 'Tìm kiếm', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined" aria-hidden="true">search</span></a>
	<?php $dock_link( $favorites_url ? $favorites_url : tkh_home_url(), 'favorite', __( 'Yêu Thích', 'toplist-khanh-hoa' ), is_page_template( 'page-templates/favorites.php' ) ); ?>
	<button type="button" data-menu-open class="flex flex-col items-center gap-0.5 font-label-sm text-label-sm text-slate-muted" aria-controls="tkh-drawer" aria-expanded="false"><span class="material-symbols-outlined text-[22px]" aria-hidden="true">menu</span><?php esc_html_e( 'Menu', 'toplist-khanh-hoa' ); ?></button>
</nav>

<div id="tkh-drawer" class="fixed inset-0 z-[60]" hidden>
	<div class="absolute inset-0 bg-inverse-surface/40 backdrop-blur-sm" data-menu-close></div>
	<div class="absolute right-0 top-0 h-full w-[86%] max-w-sm bg-surface-container-lowest shadow-2xl p-6 flex flex-col gap-6 overflow-y-auto" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'toplist-khanh-hoa' ); ?>">
		<div class="flex items-center justify-between">
			<img alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="h-9 w-auto" src="<?php echo esc_url( tkh_logo_url() ); ?>" width="130" height="36">
			<button type="button" data-menu-close class="w-10 h-10 rounded-full bg-surface-mist flex items-center justify-center" aria-label="<?php esc_attr_e( 'Đóng menu', 'toplist-khanh-hoa' ); ?>"><span class="material-symbols-outlined" aria-hidden="true">close</span></button>
		</div>
		<form class="field" role="search" method="get" action="<?php echo esc_url( tkh_home_url() ); ?>">
			<span class="material-symbols-outlined text-slate-muted text-[20px] mr-2" aria-hidden="true">search</span>
			<label class="sr-only" for="tkh-drawer-search"><?php esc_html_e( 'Tìm kiếm', 'toplist-khanh-hoa' ); ?></label>
			<input id="tkh-drawer-search" name="s" type="search" class="w-full bg-transparent focus:outline-none" placeholder="<?php esc_attr_e( 'Tìm kiếm điểm đến...', 'toplist-khanh-hoa' ); ?>">
		</form>
		<nav class="flex flex-col gap-1" aria-label="<?php esc_attr_e( 'Chuyên mục', 'toplist-khanh-hoa' ); ?>">
			<?php foreach ( $nav_items as $item ) : ?>
				<a class="px-4 py-3 rounded-xl font-headline-sm text-headline-sm <?php echo $item[2] ? 'bg-primary-container text-on-primary' : 'text-on-surface hover:bg-surface-mist'; ?>" href="<?php echo esc_url( $item[1] ); ?>"><?php echo esc_html( $item[0] ); ?></a>
			<?php endforeach; ?>
			<?php if ( $favorites_url ) : ?>
				<a class="px-4 py-3 rounded-xl font-headline-sm text-headline-sm text-on-surface hover:bg-surface-mist" href="<?php echo esc_url( $favorites_url ); ?>"><?php esc_html_e( 'Địa điểm yêu thích', 'toplist-khanh-hoa' ); ?></a>
			<?php endif; ?>
		</nav>
		<?php $drawer_langs = tkh_languages(); if ( $drawer_langs ) : ?>
			<div class="flex gap-2 pt-4 border-t border-slate-border">
				<?php foreach ( $drawer_langs as $lang ) : ?>
					<a class="chip<?php echo $lang['current'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $lang['url'] ); ?>" hreflang="<?php echo esc_attr( $lang['slug'] ); ?>"<?php echo $lang['current'] ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $lang['name'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
