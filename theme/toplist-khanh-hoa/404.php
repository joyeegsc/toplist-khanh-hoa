<?php
/**
 * Not found.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="max-w-[880px] mx-auto px-margin-mobile md:px-margin py-20">
	<h1 class="sr-only"><?php esc_html_e( 'Không tìm thấy trang', 'toplist-khanh-hoa' ); ?></h1>
	<?php get_template_part( 'parts/empty', null, array( 'title' => __( 'Không tìm thấy trang', 'toplist-khanh-hoa' ), 'text' => __( 'Đường dẫn có thể đã thay đổi. Hãy thử tìm địa điểm bạn cần.', 'toplist-khanh-hoa' ) ) ); ?>
</section>
<?php
get_footer();
