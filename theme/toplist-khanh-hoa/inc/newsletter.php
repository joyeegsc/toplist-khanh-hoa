<?php
/**
 * Newsletter sign-up: stores subscriber emails in an option and lists them under Tools.
 */

defined( 'ABSPATH' ) || exit;

const TKH_SUBSCRIBERS_OPTION = 'tkh_subscribers';

function tkh_handle_subscribe() {
	$back = wp_get_referer() ? wp_get_referer() : tkh_home_url();
	$back = remove_query_arg( 'dang-ky', $back );

	if ( ! isset( $_POST['tkh_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tkh_nonce'] ), 'tkh_subscribe' ) ) {
		wp_safe_redirect( add_query_arg( 'dang-ky', 'loi', $back ) . '#ban-tin' );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'dang-ky', 'loi', $back ) . '#ban-tin' );
		exit;
	}

	$list = get_option( TKH_SUBSCRIBERS_OPTION, array() );
	if ( ! isset( $list[ strtolower( $email ) ] ) ) {
		$list[ strtolower( $email ) ] = current_time( 'mysql' );
		update_option( TKH_SUBSCRIBERS_OPTION, $list, false );
	}

	wp_safe_redirect( add_query_arg( 'dang-ky', 'ok', $back ) . '#ban-tin' );
	exit;
}
add_action( 'admin_post_nopriv_tkh_subscribe', 'tkh_handle_subscribe' );
add_action( 'admin_post_tkh_subscribe', 'tkh_handle_subscribe' );

add_action(
	'admin_menu',
	function () {
		add_management_page( __( 'Đăng ký nhận tin', 'toplist-khanh-hoa' ), __( 'Đăng ký nhận tin', 'toplist-khanh-hoa' ), 'manage_options', 'tkh-subscribers', 'tkh_render_subscribers_page' );
	}
);

function tkh_render_subscribers_page() {
	$list = get_option( TKH_SUBSCRIBERS_OPTION, array() );
	arsort( $list );
	echo '<div class="wrap"><h1>Đăng ký nhận tin</h1>';
	printf( '<p>Tổng cộng: <strong>%d</strong> email.</p>', count( $list ) );
	if ( $list ) {
		echo '<table class="widefat striped"><thead><tr><th>Email</th><th>Thời gian đăng ký</th></tr></thead><tbody>';
		foreach ( $list as $email => $time ) {
			printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $email ), esc_html( $time ) );
		}
		echo '</tbody></table>';
		echo '<p><label for="tkh-export">Sao chép danh sách:</label><br><textarea id="tkh-export" class="large-text" rows="5" readonly>' . esc_textarea( implode( "\n", array_keys( $list ) ) ) . '</textarea></p>';
	}
	echo '</div>';
}
