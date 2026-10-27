<?php
/**
 * Place details stored as post meta, editable from a meta box on the post screen.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions: meta key => [label, type, help].
 */
function tkh_place_fields() {
	return array(
		'_tkh_place_name'   => array( __( 'Tên địa điểm', 'toplist-khanh-hoa' ), 'string', 'VD: Tháp Bà Ponagar' ),
		'_tkh_address'      => array( __( 'Địa chỉ', 'toplist-khanh-hoa' ), 'string', 'VD: 61 Hai Tháng Tư, Bắc Nha Trang, Khánh Hòa' ),
		'_tkh_rating'       => array( __( 'Điểm đánh giá (0–5)', 'toplist-khanh-hoa' ), 'number', 'VD: 4.6' ),
		'_tkh_review_count' => array( __( 'Số lượt đánh giá', 'toplist-khanh-hoa' ), 'integer', 'VD: 1250' ),
		'_tkh_price'        => array( __( 'Mức giá hiển thị', 'toplist-khanh-hoa' ), 'string', 'VD: 35.000 - 60.000 ₫/người' ),
		'_tkh_price_level'  => array( __( 'Phân khúc giá', 'toplist-khanh-hoa' ), 'string', 'binh-dan | trung-cap | cao-cap' ),
		'_tkh_hours'        => array( __( 'Giờ mở cửa', 'toplist-khanh-hoa' ), 'string', 'VD: 06:00 - 17:30' ),
		'_tkh_badge'        => array( __( 'Nhãn trên ảnh', 'toplist-khanh-hoa' ), 'string', 'VD: Di sản Chăm Pa' ),
		'_tkh_note'         => array( __( 'Ghi chú nổi bật (chân thẻ)', 'toplist-khanh-hoa' ), 'string', 'VD: Bao gồm ăn sáng' ),
	);
}

function tkh_price_levels() {
	return array(
		'binh-dan'  => __( 'Bình dân', 'toplist-khanh-hoa' ),
		'trung-cap' => __( 'Trung cấp', 'toplist-khanh-hoa' ),
		'cao-cap'   => __( 'Cao cấp', 'toplist-khanh-hoa' ),
	);
}

add_action(
	'init',
	function () {
		foreach ( tkh_place_fields() as $key => $field ) {
			register_post_meta(
				'post',
				$key,
				array(
					'type'              => $field[1],
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'tkh_sanitize_meta_' . $field[1],
					'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

function tkh_sanitize_meta_string( $value ) {
	return sanitize_text_field( $value );
}

function tkh_sanitize_meta_number( $value ) {
	return '' === $value ? '' : max( 0, min( 5, round( (float) $value, 1 ) ) );
}

function tkh_sanitize_meta_integer( $value ) {
	return '' === $value ? '' : absint( $value );
}

add_action(
	'add_meta_boxes_post',
	function () {
		add_meta_box( 'tkh-place', __( 'Thông tin địa điểm', 'toplist-khanh-hoa' ), 'tkh_render_place_box', 'post', 'normal', 'high' );
	}
);

function tkh_render_place_box( $post ) {
	wp_nonce_field( 'tkh_place_save', 'tkh_place_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( tkh_place_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf( '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td>', esc_attr( $key ), esc_html( $field[0] ) );
		if ( '_tkh_price_level' === $key ) {
			printf( '<select id="%s" name="%s"><option value="">—</option>', esc_attr( $key ), esc_attr( $key ) );
			foreach ( tkh_price_levels() as $slug => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $slug ), selected( $value, $slug, false ), esc_html( $label ) );
			}
			echo '</select>';
		} else {
			$type = 'string' === $field[1] ? 'text' : 'number';
			$step = 'number' === $field[1] ? ' step="0.1" min="0" max="5"' : ( 'integer' === $field[1] ? ' step="1" min="0"' : '' );
			printf( '<input type="%s" class="regular-text" id="%s" name="%s" value="%s"%s>', esc_attr( $type ), esc_attr( $key ), esc_attr( $key ), esc_attr( $value ), $step ); // phpcs:ignore WordPress.Security.EscapeOutput -- $step is a static string.
		}
		printf( '<p class="description">%s</p></td></tr>', esc_html( $field[2] ) );
	}
	echo '</tbody></table>';
}

add_action(
	'save_post_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['tkh_place_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tkh_place_nonce'] ), 'tkh_place_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( tkh_place_fields() as $key => $field ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$value = call_user_func( 'tkh_sanitize_meta_' . $field[1], wp_unslash( $_POST[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized by the field callback.
			if ( '_tkh_price_level' === $key && ! isset( tkh_price_levels()[ $value ] ) ) {
				$value = '';
			}
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
);
