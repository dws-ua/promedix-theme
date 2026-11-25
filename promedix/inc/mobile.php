<?php
/**
 * Mobile: off-canvas menu drawer (menu + contacts + language) and the bottom shop bar.
 * Both are printed in wp_footer, so the header template part stays untouched;
 * theme.js opens the drawer from the header burger.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

function promedix_svg( $paths, $size = 24 ) {
	return '<svg class="pmx-ico" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

/** Mobile drawer: category menu, contacts, language switch. */
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	$c    = promedix_contacts();
	$menu = has_nav_menu( 'primary' ) ? wp_nav_menu( array(
		'theme_location'  => 'primary',
		'container'       => 'nav',
		'container_class' => 'pmx-drawer__menu',
		'container_aria_label' => __( 'Головне меню', 'promedix' ),
		'menu_id'         => 'pmx-drawer-menu',
		'depth'           => 2,
		'fallback_cb'     => false,
		'echo'            => false,
	) ) : '';

	$links = array();
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$links[] = array( wc_get_page_permalink( 'myaccount' ), __( 'Особистий кабінет', 'promedix' ), 'user' );
	}
	foreach ( array( 'wishlist' => array( __( 'Обране', 'promedix' ), 'heart' ), 'delivery' => array( __( 'Оплата і доставка', 'promedix' ), 'truck' ), 'exchange' => array( __( 'Обмін та повернення', 'promedix' ), 'swap' ), 'teams' => array( __( 'Для команд і закладів', 'promedix' ), 'users' ), 'contacts' => array( __( 'Контакти', 'promedix' ), 'pin' ) ) as $key => $l ) {
		$url = function_exists( 'promedix_link' ) ? promedix_link( $key ) : '';
		if ( $url ) {
			$links[] = array( $url, $l[0], $l[1] );
		}
	}
	$icon = function ( $name, $size = 20 ) {
		return function_exists( 'promedix_ui_icon' ) ? promedix_ui_icon( $name, $size ) : '';
	};

	echo '<div class="pmx-drawer" id="pmx-drawer" hidden>';
	echo '<div class="pmx-drawer__overlay" data-pmx-drawer-close></div>';
	echo '<div class="pmx-drawer__panel" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Меню', 'promedix' ) . '" tabindex="-1">';
	echo '<div class="pmx-drawer__head">' . do_shortcode( '[promedix_logo variant="mono" width="120"]' )
		. '<button type="button" class="pmx-icon-btn pmx-drawer__close" data-pmx-drawer-close aria-label="' . esc_attr__( 'Закрити меню', 'promedix' ) . '">' . promedix_svg( '<path d="M6 6l12 12M18 6 6 18"/>' ) . '</button></div>';
	echo '<div class="pmx-drawer__body">';
	echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( $links ) {
		echo '<ul class="pmx-drawer__links">';
		foreach ( $links as $l ) {
			echo '<li><a href="' . esc_url( $l[0] ) . '">' . $icon( $l[2] ) . '<span>' . esc_html( $l[1] ) . '</span></a></li>'; // phpcs:ignore
		}
		echo '</ul>';
	}
	echo '<div class="pmx-drawer__contacts">';
	echo '<p class="pmx-drawer__label">' . esc_html__( 'Магазин', 'promedix' ) . '</p>';
	echo '<a class="pmx-drawer__phone" href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . $icon( 'phone' ) . esc_html( $c['phone_retail'] ) . '</a>'; // phpcs:ignore
	echo '<p class="pmx-drawer__label">' . esc_html__( 'Опт і заклади', 'promedix' ) . '</p>';
	echo '<a class="pmx-drawer__phone" href="tel:' . esc_attr( $c['phone_wholesale_href'] ) . '">' . $icon( 'phone' ) . esc_html( $c['phone_wholesale'] ) . '</a>'; // phpcs:ignore
	if ( ! empty( $c['email_wholesale'] ) ) {
		echo '<a class="pmx-drawer__line" href="mailto:' . esc_attr( $c['email_wholesale'] ) . '">' . $icon( 'mail' ) . esc_html( $c['email_wholesale'] ) . '</a>'; // phpcs:ignore
	}
	if ( ! empty( $c['address'] ) ) {
		echo '<p class="pmx-drawer__line">' . $icon( 'pin' ) . '<span>' . esc_html( $c['address'] ) . '</span></p>'; // phpcs:ignore
	}
	$social = array();
	foreach ( array( 'instagram' => 'Instagram', 'telegram' => 'Telegram', 'viber' => 'Viber', 'facebook' => 'Facebook' ) as $k => $label ) {
		if ( ! empty( $c[ $k ] ) ) {
			$social[] = '<a href="' . esc_url( $c[ $k ], array( 'https', 'http', 'viber', 'tg' ) ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
		}
	}
	if ( $social ) {
		echo '<p class="pmx-drawer__social">' . implode( '', $social ) . '</p>'; // phpcs:ignore
	}
	echo '</div>';
	echo do_shortcode( '[promedix_lang]' );
	echo '</div></div></div>';
}, 5 );

/** Bottom shop bar (phones only): Головна, Каталог, Пошук, Обране, Кошик. */
function promedix_bnav_cart_count() {
	$n = ( function_exists( 'WC' ) && WC() && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	return '<span class="pmx-bnav__count pmx-bnav__count--cart"' . ( $n ? '' : ' hidden' ) . '>' . $n . '</span>';
}
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['span.pmx-bnav__count--cart'] = promedix_bnav_cart_count();
	return $fragments;
} );

add_action( 'wp_footer', function () {
	if ( is_admin() || ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}
	if ( is_cart() || is_checkout() ) {
		return; // Checkout has its own sticky actions; keep the screen clean.
	}
	$home = apply_filters( 'wpml_home_url', home_url( '/' ) );
	$shop = wc_get_page_permalink( 'shop' );
	$fav  = function_exists( 'promedix_link' ) ? promedix_link( 'wishlist' ) : '';
	$here = function ( $on ) {
		return $on ? ' aria-current="page"' : '';
	};
	echo '<nav class="pmx-bnav" aria-label="' . esc_attr__( 'Меню магазину', 'promedix' ) . '"><ul>';
	echo '<li><a href="' . esc_url( $home ) . '"' . $here( is_front_page() ) . '>' . promedix_svg( '<path d="M4 11 12 4l8 7"/><path d="M6 10v10h12V10"/><path d="M10 20v-5h4v5"/>' ) . '<span>' . esc_html__( 'Головна', 'promedix' ) . '</span></a></li>'; // phpcs:ignore
	echo '<li><a href="' . esc_url( $shop ) . '"' . $here( is_shop() || is_product_taxonomy() || is_product() ) . '>' . promedix_svg( '<rect x="4" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.5"/>' ) . '<span>' . esc_html__( 'Каталог', 'promedix' ) . '</span></a></li>'; // phpcs:ignore
	echo '<li><button type="button" data-pmx-bnav-search aria-controls="pmx-search-panel">' . promedix_svg( '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>' ) . '<span>' . esc_html__( 'Пошук', 'promedix' ) . '</span></button></li>'; // phpcs:ignore
	if ( $fav ) {
		echo '<li><a href="' . esc_url( $fav ) . '"' . $here( is_page( 'wishlist' ) ) . '>' . promedix_svg( '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"/>' ) . '<span>' . esc_html__( 'Обране', 'promedix' ) . '</span><span class="pmx-bnav__count pmx-bnav__count--fav" hidden></span></a></li>'; // phpcs:ignore
	}
	echo '<li><a href="' . esc_url( wc_get_cart_url() ) . '">' . promedix_svg( '<path d="M5 8h14l-1.2 12H6.2z"/><path d="M9 8a3 3 0 0 1 6 0"/>' ) . '<span>' . esc_html__( 'Кошик', 'promedix' ) . '</span>' . promedix_bnav_cart_count() . '</a></li>'; // phpcs:ignore
	echo '</ul></nav>';
}, 6 );

add_filter( 'body_class', function ( $classes ) {
	if ( function_exists( 'is_cart' ) && ! is_cart() && ! is_checkout() ) {
		$classes[] = 'pmx-has-bnav';
	}
	return $classes;
} );
