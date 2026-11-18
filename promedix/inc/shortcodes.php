<?php
/**
 * Shortcodes used inside block templates (HTML templates cannot run PHP).
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

/**
 * [promedix_menu location="primary" class=""]: a classic menu, translated by WPML.
 */
function promedix_sc_menu( $atts ) {
	$atts = shortcode_atts( array( 'location' => 'primary', 'class' => '' ), $atts );
	if ( ! has_nav_menu( $atts['location'] ) ) {
		return '';
	}
	return wp_nav_menu( array(
		'theme_location' => $atts['location'],
		'container'      => 'nav',
		'container_id'   => 'pmx-menu-' . sanitize_html_class( $atts['location'] ),
		'container_class'=> 'pmx-menu pmx-menu--' . sanitize_html_class( $atts['location'] ) . ' ' . sanitize_html_class( $atts['class'] ),
		'container_aria_label' => 'primary' === $atts['location'] ? __( 'Головне меню', 'promedix' ) : '',
		'depth'          => 2,
		'fallback_cb'    => false,
		'echo'           => false,
	) );
}
add_shortcode( 'promedix_menu', 'promedix_sc_menu' );

/**
 * [promedix_lang]: compact УКР / РУС switcher from WPML active languages.
 */
function promedix_sc_lang() {
	$langs = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
	if ( empty( $langs ) ) {
		return '';
	}
	$labels = array( 'uk' => 'УКР', 'ru' => 'РУС' );
	$out    = array();
	foreach ( $langs as $code => $l ) {
		$label = isset( $labels[ $code ] ) ? $labels[ $code ] : strtoupper( $code );
		if ( ! empty( $l['active'] ) ) {
			$out[] = '<span class="pmx-lang__item is-active" aria-current="true">' . esc_html( $label ) . '</span>';
		} else {
			$out[] = '<a class="pmx-lang__item" hreflang="' . esc_attr( $code ) . '" href="' . esc_url( $l['url'] ) . '">' . esc_html( $label ) . '</a>';
		}
	}
	return '<div class="pmx-lang">' . implode( '<span aria-hidden="true">/</span>', $out ) . '</div>';
}
add_shortcode( 'promedix_lang', 'promedix_sc_lang' );

/**
 * [promedix_phone type="wholesale|retail" label="..."]
 */
function promedix_sc_phone( $atts ) {
	$atts  = shortcode_atts( array( 'type' => 'wholesale', 'label' => '' ), $atts );
	$type  = 'retail' === $atts['type'] ? 'retail' : 'wholesale';
	$phone = promedix_contact( 'phone_' . $type );
	$href  = promedix_contact( 'phone_' . $type . '_href' );
	$label = $atts['label'] ? '<span class="pmx-phone__label">' . esc_html( $atts['label'] ) . '</span> ' : '';
	return '<a class="pmx-phone pmx-phone--' . $type . '" href="tel:' . esc_attr( $href ) . '">' . $label . esc_html( $phone ) . '</a>';
}
add_shortcode( 'promedix_phone', 'promedix_sc_phone' );

/**
 * [promedix_contact key="email_wholesale|address"]
 */
function promedix_sc_contact( $atts ) {
	$atts = shortcode_atts( array( 'key' => '' ), $atts );
	$val  = promedix_contact( $atts['key'] );
	if ( '' === $val ) {
		return '';
	}
	if ( is_email( $val ) ) {
		return '<a href="mailto:' . esc_attr( $val ) . '">' . esc_html( $val ) . '</a>';
	}
	return esc_html( $val );
}
add_shortcode( 'promedix_contact', 'promedix_sc_contact' );

/**
 * [promedix_b2b_button label="" style="outline|solid|light"]
 */
function promedix_sc_b2b_button( $atts ) {
	$atts = shortcode_atts( array( 'label' => __( 'Прайс за запитом', 'promedix' ), 'style' => 'outline' ), $atts );
	return '<a class="pmx-btn pmx-btn--' . sanitize_html_class( $atts['style'] ) . '" href="' . esc_url( promedix_b2b_url() ) . '">' . esc_html( $atts['label'] ) . '</a>';
}
add_shortcode( 'promedix_b2b_button', 'promedix_sc_b2b_button' );

/**
 * [promedix_b2b_url]: bare URL, for use inside button blocks.
 */
add_shortcode( 'promedix_b2b_url', function () {
	return esc_url( promedix_b2b_url() );
} );

/**
 * [promedix_cart]: cart button with live count (updated via WooCommerce fragments).
 */
function promedix_cart_markup() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	$count = WC()->cart->get_cart_contents_count();
	return '<a class="pmx-cart" href="' . esc_url( wc_get_cart_url() ) . '" aria-label="' . esc_attr( sprintf( __( 'Кошик, товарів: %d', 'promedix' ), $count ) ) . '">'
		. '<svg class="pmx-ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1.2 12H6.2z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>'
		. ( $count ? '<span class="pmx-cart__count">' . (int) $count . '</span>' : '' ) . '</a>';
}
add_shortcode( 'promedix_cart', 'promedix_cart_markup' );

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['a.pmx-cart'] = promedix_cart_markup();
	return $fragments;
} );

/**
 * [promedix_account]: account icon link.
 */
add_shortcode( 'promedix_account', function () {
	if ( ! function_exists( 'wc_get_page_permalink' ) ) {
		return '';
	}
	return '<a class="pmx-icon-btn pmx-account" href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" aria-label="' . esc_attr__( 'Особистий кабінет', 'promedix' ) . '">'
		. '<svg class="pmx-ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg></a>';
} );

/**
 * [promedix_year]
 */
add_shortcode( 'promedix_year', function () {
	return esc_html( wp_date( 'Y' ) );
} );

/**
 * [promedix_wholesale_form]: renders the Contact Form 7 form titled
 * "Гуртова заявка ProMedix" (filterable), or a phone/e-mail fallback.
 */
function promedix_sc_wholesale_form() {
	$title = apply_filters( 'promedix_wholesale_form_title', 'Гуртова заявка ProMedix' );
	if ( function_exists( 'wpcf7_get_contact_form_by_title' ) ) {
		$form = wpcf7_get_contact_form_by_title( $title );
		if ( $form ) {
			$id = promedix_wpml_id( $form->id(), 'wpcf7_contact_form' );
			return '<div class="pmx-form">' . do_shortcode( '[contact-form-7 id="' . (int) $id . '"]' ) . '</div>';
		}
	}
	return '<div class="pmx-form pmx-form--fallback"><p>' . esc_html__( 'Зателефонуйте або напишіть нам:', 'promedix' ) . '</p><p>'
		. promedix_sc_phone( array( 'type' => 'wholesale' ) ) . '<br>'
		. promedix_sc_contact( array( 'key' => 'email_wholesale' ) ) . '</p></div>';
}
add_shortcode( 'promedix_wholesale_form', 'promedix_sc_wholesale_form' );

/**
 * [promedix_logo variant="dark|white" height="46"]: approved ProMedix logo linking home.
 * Replace assets/img/promedix-logo*.png with a larger PNG or SVG when available.
 */
function promedix_sc_logo( $atts ) {
	$atts = shortcode_atts( array( 'variant' => 'dark', 'height' => 46 ), $atts );
	$file = 'white' === $atts['variant'] ? 'promedix-logo-white.png' : 'promedix-logo.png';
	$home = apply_filters( 'wpml_home_url', home_url( '/' ) );
	return '<a class="pmx-logo-link" href="' . esc_url( $home ) . '" rel="home" aria-label="' . esc_attr__( 'ProMedix, на головну', 'promedix' ) . '">'
		. '<img src="' . esc_url( get_theme_file_uri( 'assets/img/' . $file ) ) . '" alt="ProMedix" height="' . (int) $atts['height'] . '" style="height:' . (int) $atts['height'] . 'px;width:auto;display:block"></a>';
}
add_shortcode( 'promedix_logo', 'promedix_sc_logo' );

/**
 * [promedix_picker]: "Знайдемо вашу пару?" — who it is for + size → catalog with the size filter.
 * Without JS the form opens the shop filtered by size.
 */
add_shortcode( 'promedix_picker', function () {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$who  = apply_filters( 'promedix_picker_groups', array(
		'medychne-vzuttia'   => __( 'Медикам', 'promedix' ),
		'zhinochi-sabo'      => __( 'Жінкам', 'promedix' ),
		'cholovichi-sabo'    => __( 'Чоловікам', 'promedix' ),
		'kroksy-anatomichni' => __( 'Для щоденної роботи', 'promedix' ),
	) );
	$sizes = taxonomy_exists( 'pa_size' ) ? get_terms( array( 'taxonomy' => 'pa_size', 'hide_empty' => true ) ) : array();
	if ( is_wp_error( $sizes ) ) {
		$sizes = array();
	}
	usort( $sizes, function ( $a, $b ) {
		return (float) $a->name <=> (float) $b->name;
	} );
	ob_start();
	?>
	<form class="pmx-picker alignwide" action="<?php echo esc_url( $shop ); ?>" method="get" data-pmx-picker>
		<h2 class="pmx-picker__title"><?php esc_html_e( 'Знайдемо вашу пару?', 'promedix' ); ?></h2>
		<label class="pmx-picker__field"><span class="pmx-visually-hidden"><?php esc_html_e( 'Для кого', 'promedix' ); ?></span>
			<select data-pmx-picker-cat>
				<option value="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Ваша професія', 'promedix' ); ?></option>
				<?php foreach ( $who as $slug => $label ) : ?>
					<option value="<?php echo esc_url( promedix_term_url( $slug ) ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="pmx-picker__field"><span class="pmx-visually-hidden"><?php esc_html_e( 'Розмір', 'promedix' ); ?></span>
			<select name="filter_size">
				<option value=""><?php esc_html_e( 'Ваш розмір', 'promedix' ); ?></option>
				<?php foreach ( $sizes as $t ) : ?>
					<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<input type="hidden" name="query_type_size" value="or">
		<button class="pmx-btn pmx-arrow-btn" type="submit"><?php esc_html_e( 'Підібрати', 'promedix' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
} );
