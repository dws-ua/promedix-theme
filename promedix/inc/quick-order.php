<?php
/**
 * "Купити в один клік": name + phone in a dialog, creates a WooCommerce order
 * (status on-hold, so the shop gets the usual "New order" email) with the chosen size.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

/* Button under "Додати в кошик". */
add_action( 'woocommerce_after_add_to_cart_button', function () {
	if ( ! is_product() ) {
		return;
	}
	echo '<button type="button" class="pmx-btn pmx-btn--secondary pmx-oneclick" data-pmx-oneclick>'
		. '<svg class="pmx-ico" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg>'
		. esc_html__( 'Купити в один клік', 'promedix' ) . '</button>'
		. '<p class="pmx-oneclick__need" role="alert" hidden>' . esc_html__( 'Спершу оберіть розмір.', 'promedix' ) . '</p>';
} );

/* The dialog. */
add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$c = promedix_contacts();
	echo '<dialog class="pmx-dialog pmx-dialog--oneclick" id="pmx-oneclick-dialog" aria-labelledby="pmx-oneclick-t">'
		. '<div class="pmx-dialog__head"><h2 id="pmx-oneclick-t">' . esc_html__( 'Купити в один клік', 'promedix' ) . '</h2><form method="dialog"><button class="pmx-icon-btn" aria-label="' . esc_attr__( 'Закрити', 'promedix' ) . '"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></form></div>'
		. '<div class="pmx-dialog__in">'
		. '<form class="pmx-oneclick-form" data-pmx-oneclick-form action="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" method="post" novalidate>'
		. '<p class="pmx-oneclick-form__what" data-pmx-oneclick-what></p>'
		. '<p class="pmx-muted">' . esc_html__( 'Залиште ім’я і телефон: менеджер зателефонує, уточнить доставку й оплату.', 'promedix' ) . '</p>'
		. '<p class="pmx-field"><label for="pmx-oc-name">' . esc_html__( 'Ім’я', 'promedix' ) . '</label><input id="pmx-oc-name" name="name" type="text" autocomplete="name" maxlength="80"></p>'
		. '<p class="pmx-field"><label for="pmx-oc-phone">' . esc_html__( 'Телефон', 'promedix' ) . ' <span aria-hidden="true">*</span></label><input id="pmx-oc-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="+380 __ ___ __ __" maxlength="20"></p>'
		. '<p class="pmx-oc-hp" aria-hidden="true"><label>Website<input name="website" type="text" tabindex="-1" autocomplete="off"></label></p>'
		. '<input type="hidden" name="action" value="pmx_oneclick"><input type="hidden" name="nonce" value="' . esc_attr( wp_create_nonce( 'pmx_oneclick' ) ) . '">'
		. '<input type="hidden" name="product_id" value=""><input type="hidden" name="variation_id" value=""><input type="hidden" name="quantity" value="1">'
		. '<p class="pmx-oneclick-form__err" role="alert" hidden></p>'
		. '<button type="submit" class="pmx-btn pmx-oneclick-form__go">' . esc_html__( 'Замовити', 'promedix' ) . '</button>'
		. '<p class="pmx-oneclick-form__or">' . esc_html__( 'Або зателефонуйте:', 'promedix' ) . ' <a href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . esc_html( $c['phone_retail'] ) . '</a></p>'
		. '</form>'
		. '<div class="pmx-oneclick-done" data-pmx-oneclick-done hidden role="status"></div>'
		. '</div></dialog>';
}, 20 );

/* AJAX: create the order. */
function promedix_oneclick_handle() {
	$fail = function ( $msg, $code = 400 ) {
		wp_send_json_error( array( 'message' => $msg ), $code );
	};
	if ( ! function_exists( 'wc_create_order' ) ) {
		$fail( __( 'Магазин тимчасово недоступний.', 'promedix' ), 500 );
	}
	if ( ! check_ajax_referer( 'pmx_oneclick', 'nonce', false ) ) {
		$fail( __( 'Сторінка застаріла. Оновіть її і спробуйте ще раз або зателефонуйте нам.', 'promedix' ), 403 );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot.
		$fail( __( 'Не вдалося надіслати.', 'promedix' ) );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'pmx_oc_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		$fail( __( 'Забагато спроб. Зателефонуйте нам, будь ласка.', 'promedix' ), 429 );
	}
	set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );

	$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone  = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$digits = preg_replace( '/\D+/', '', $phone );
	if ( strlen( $digits ) < 10 || strlen( $digits ) > 13 ) {
		$fail( __( 'Вкажіть номер телефону, наприклад +380 63 100 86 86.', 'promedix' ) );
	}
	$pid = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$vid = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$qty = isset( $_POST['quantity'] ) ? max( 1, min( 50, absint( $_POST['quantity'] ) ) ) : 1;

	$product = wc_get_product( $vid ? $vid : $pid );
	if ( ! $product || ( $vid && $product->get_parent_id() !== $pid ) ) {
		$fail( __( 'Товар не знайдено.', 'promedix' ) );
	}
	if ( $product->is_type( 'variable' ) ) {
		$fail( __( 'Спершу оберіть розмір.', 'promedix' ) );
	}
	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		$fail( __( 'Цього розміру зараз немає в наявності.', 'promedix' ) );
	}

	try {
		$order = wc_create_order( array( 'created_via' => 'promedix-oneclick' ) );
		$order->add_product( $product, $qty );
		$order->set_billing_first_name( $name );
		$order->set_billing_phone( $phone );
		$order->set_billing_country( 'UA' );
		$order->set_customer_ip_address( $ip );
		$order->calculate_totals();
		$order->add_order_note( __( 'Замовлення «Купити в один клік»: зателефонувати клієнту, уточнити доставку й оплату.', 'promedix' ) );
		$order->set_status( 'on-hold' );
		$order->save();
	} catch ( Exception $e ) {
		$fail( __( 'Не вдалося оформити. Зателефонуйте нам, будь ласка.', 'promedix' ), 500 );
	}

	wp_send_json_success( array(
		'message' => sprintf( __( 'Дякуємо! Замовлення №%s прийнято. Ми зателефонуємо вам найближчим часом.', 'promedix' ), $order->get_order_number() ),
	) );
}
add_action( 'wp_ajax_pmx_oneclick', 'promedix_oneclick_handle' );
add_action( 'wp_ajax_nopriv_pmx_oneclick', 'promedix_oneclick_handle' );
