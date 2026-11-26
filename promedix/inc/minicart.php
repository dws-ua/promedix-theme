<?php
/**
 * Mini-cart side panel: "Додати в кошик" on the product page adds by AJAX and opens
 * a panel on the right (full width on phones) with the cart, subtotal and next steps,
 * instead of reloading the page with the green WooCommerce notice on top.
 * Without JS the form still posts the usual way.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

/** Smallest "free shipping from" amount among enabled free_shipping methods (0 if none). */
function promedix_free_shipping_min() {
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return 0;
	}
	$min = 0;
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		foreach ( $zone['shipping_methods'] as $m ) {
			if ( 'free_shipping' !== $m->id || 'yes' !== $m->enabled ) {
				continue;
			}
			$req = (string) $m->get_option( 'requires' );
			$amt = (float) wc_format_decimal( $m->get_option( 'min_amount' ) );
			if ( in_array( $req, array( 'min_amount', 'either' ), true ) && $amt > 0 ) {
				$min = $min ? min( $min, $amt ) : $amt;
			}
		}
	}
	return $min;
}

/** Inner HTML of the panel (also returned by AJAX). */
function promedix_minicart_body( $added_key = '' ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	$cart  = WC()->cart;
	$items = array_reverse( $cart->get_cart(), true );
	$h     = '<div class="pmx-minicart__body" data-pmx-minicart-body>';
	if ( ! $items ) {
		return $h . '<p class="pmx-minicart__empty">' . esc_html__( 'Кошик порожній.', 'promedix' ) . '</p></div>';
	}
	$h .= '<ul class="pmx-minicart__list">';
	foreach ( $items as $key => $item ) {
		$p = $item['data'];
		if ( ! $p || ! $p->exists() ) {
			continue;
		}
		$link = $p->is_visible() ? $p->get_permalink( $item ) : '';
		$name = $p->get_name();
		$h   .= '<li class="pmx-minicart__item' . ( $key === $added_key ? ' is-added' : '' ) . '">'
			. '<span class="pmx-minicart__pic">' . $p->get_image( 'woocommerce_thumbnail' ) . '</span>'
			. '<span class="pmx-minicart__info">'
			. ( $link ? '<a class="pmx-minicart__name" href="' . esc_url( $link ) . '">' . esc_html( $name ) . '</a>' : '<span class="pmx-minicart__name">' . esc_html( $name ) . '</span>' )
			. '<span class="pmx-minicart__qty">' . (int) $item['quantity'] . ' × ' . $cart->get_product_price( $p ) . '</span>'
			. '</span></li>';
	}
	$h .= '</ul>';

	$h  .= '<div class="pmx-minicart__sum"><span>' . esc_html__( 'Сума', 'promedix' ) . '</span><strong>' . $cart->get_cart_subtotal() . '</strong></div>';
	$min = promedix_free_shipping_min();
	if ( $min > 0 ) {
		$total = (float) $cart->get_displayed_subtotal();
		if ( $total >= $min ) {
			$h .= '<p class="pmx-minicart__ship is-free">' . esc_html__( 'Доставка для цього замовлення безкоштовна.', 'promedix' ) . '</p>';
		} else {
			/* translators: %s: amount left for free shipping */
			$h .= '<p class="pmx-minicart__ship">' . sprintf( esc_html__( 'До безкоштовної доставки бракує %s.', 'promedix' ), wc_price( $min - $total ) ) . '</p>';
		}
	}
	return $h . '</div>';
}

add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$x = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
	echo '<dialog class="pmx-minicart" id="pmx-minicart" aria-labelledby="pmx-minicart-t" data-ajax="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '">'
		. '<div class="pmx-minicart__head">'
		. '<h2 id="pmx-minicart-t"><svg class="pmx-ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>' . esc_html__( 'Додано до кошика', 'promedix' ) . '</h2>'
		. '<form method="dialog"><button class="pmx-icon-btn" aria-label="' . esc_attr__( 'Закрити', 'promedix' ) . '">' . $x . '</button></form></div>'
		. promedix_minicart_body()
		. '<div class="pmx-minicart__foot">'
		. '<a class="pmx-btn pmx-minicart__go" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Оформити замовлення', 'promedix' ) . '</a>'
		. '<form method="dialog"><button class="pmx-btn pmx-btn--secondary pmx-minicart__more">' . esc_html__( 'Продовжити покупки', 'promedix' ) . '</button></form>'
		. '<a class="pmx-minicart__cart" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'Переглянути кошик', 'promedix' ) . '</a>'
		. '</div></dialog>'; // phpcs:ignore WordPress.Security.EscapeOutput
}, 21 );

/* AJAX: add the product page form to the cart. */
function promedix_ajax_add_to_cart() {
	$err = function ( $msg ) {
		wp_send_json_error( array( 'message' => $msg ) );
	};
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		$err( __( 'Кошик тимчасово недоступний.', 'promedix' ) );
	}
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- same as WooCommerce's own add-to-cart, no nonce (pages may be cached).
	$product_id   = absint( wp_unslash( $_POST['product_id'] ?? 0 ) );
	$variation_id = absint( wp_unslash( $_POST['variation_id'] ?? 0 ) );
	$qty          = wc_stock_amount( wp_unslash( $_POST['quantity'] ?? 1 ) );
	$qty          = $qty > 0 ? $qty : 1;
	$product      = $product_id ? wc_get_product( $product_id ) : null;
	if ( ! $product ) {
		$err( __( 'Товар не знайдено.', 'promedix' ) );
	}
	$variation = array();
	if ( $product->is_type( 'variable' ) ) {
		if ( ! $variation_id ) {
			$err( __( 'Спершу оберіть розмір.', 'promedix' ) );
		}
		foreach ( $_POST as $k => $v ) {
			if ( 0 === strpos( (string) $k, 'attribute_' ) && is_scalar( $v ) ) {
				$variation[ sanitize_title( wp_unslash( $k ) ) ] = wc_clean( wp_unslash( $v ) );
			}
		}
	}
	// phpcs:enable

	$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $qty, $variation_id, $variation );
	$key    = $passed ? WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation ) : false;
	if ( ! $key ) {
		$msgs = wc_get_notices( 'error' );
		wc_clear_notices();
		$msg = $msgs ? wp_strip_all_tags( is_array( $msgs[0] ) ? $msgs[0]['notice'] : $msgs[0] ) : __( 'Не вдалося додати товар. Спробуйте ще раз.', 'promedix' );
		$err( $msg );
	}
	wc_clear_notices();
	do_action( 'woocommerce_ajax_added_to_cart', $product_id );

	wp_send_json_success( array(
		'body'      => promedix_minicart_body( $key ),
		'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		'cart_hash' => WC()->cart->get_cart_hash(),
	) );
}
add_action( 'wp_ajax_pmx_add_to_cart', 'promedix_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_pmx_add_to_cart', 'promedix_ajax_add_to_cart' );
