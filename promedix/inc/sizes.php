<?php
/**
 * Size guide: how to measure the foot + insole length tables.
 * Numbers come from the shop's old size guides (woodmart_size_guide 2122/2303,
 * CMS blocks 1196/1208/1219). Override with add_filter( 'promedix_size_tables', ... ).
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

function promedix_size_tables() {
	return apply_filters( 'promedix_size_tables', array(
		'women'           => array( __( 'Жіночі сабо', 'promedix' ), array( '35' => '22,5', '36' => '23', '37' => '23,7', '38' => '24,5', '39' => '25', '40' => '25,5', '41' => '26,5' ) ),
		'men'             => array( __( 'Чоловічі сабо', 'promedix' ), array( '41' => '26,5', '42' => '27', '43' => '27,5', '44' => '28,5', '45' => '29', '46' => '29,5' ) ),
		'kroksy-hey'      => array( __( 'Крокси «Hey!»', 'promedix' ), array( '36/37' => '23', '38/39' => '24', '40/41' => '25' ) ),
		'kroksy-metelyk'  => array( __( 'Крокси з метеликом', 'promedix' ), array( '36' => '23', '37' => '24', '38' => '24,5', '39' => '25', '40' => '26', '41' => '26,5' ) ),
		'kroksy-chorni'   => array( __( 'Крокси чорні', 'promedix' ), array( '36' => '23', '37' => '24', '38' => '24,5', '39' => '25', '40' => '26', '41' => '27' ) ),
	) );
}

/** Which table fits a product: old Woodmart link first, then category / title. */
function promedix_product_size_key( $product_id ) {
	$product_id = (int) apply_filters( 'wpml_object_id', $product_id, 'product', true, apply_filters( 'wpml_default_language', null ) );
	$old        = get_post_meta( $product_id, 'woodmart_sguide_select', true );
	if ( '2122' === (string) $old ) {
		return 'men';
	}
	if ( '2303' === (string) $old ) {
		return 'women';
	}
	$title = mb_strtolower( get_the_title( $product_id ) );
	if ( has_term( array( 'kroksy-anatomichni' ), 'product_cat', $product_id ) || false !== mb_strpos( $title, 'крокс' ) ) {
		if ( false !== mb_strpos( $title, 'hey' ) ) {
			return 'kroksy-hey';
		}
		if ( false !== mb_strpos( $title, 'метелик' ) ) {
			return 'kroksy-metelyk';
		}
		if ( false !== mb_strpos( $title, 'чорн' ) ) {
			return 'kroksy-chorni';
		}
		return 'kroksy-metelyk';
	}
	if ( has_term( array( 'cholovichi-sabo' ), 'product_cat', $product_id ) || false !== mb_strpos( $title, 'чоловіч' ) ) {
		return 'men';
	}
	return 'women';
}

function promedix_size_table_html( $key ) {
	$t = promedix_size_tables();
	if ( empty( $t[ $key ] ) ) {
		return '';
	}
	$h = '<table class="pmx-sizetable"><caption>' . esc_html( $t[ $key ][0] ) . '</caption><thead><tr><th scope="col">' . esc_html__( 'Розмір (UA)', 'promedix' ) . '</th><th scope="col">' . esc_html__( 'Довжина устілки, см', 'promedix' ) . '</th></tr></thead><tbody>';
	foreach ( $t[ $key ][1] as $size => $cm ) {
		$h .= '<tr><th scope="row">' . esc_html( $size ) . '</th><td>' . esc_html( $cm ) . '</td></tr>';
	}
	return $h . '</tbody></table>';
}

function promedix_size_howto_html() {
	$steps = array(
		__( 'Міряйте ввечері: за день стопа трохи збільшується. Одягніть шкарпетки, у яких зазвичай працюєте.', 'promedix' ),
		__( 'Поставте аркуш паперу впритул до стіни і станьте на нього босою ногою, п’ятою до стіни. Вага на обох ногах.', 'promedix' ),
		__( 'Позначте олівцем найдовший палець, тримаючи олівець вертикально.', 'promedix' ),
		__( 'Виміряйте лінійкою відстань від краю аркуша до позначки. Повторіть для другої стопи і беріть більше значення.', 'promedix' ),
		__( 'Знайдіть у таблиці розмір, де довжина устілки дорівнює довжині стопи або більша до 0,5 см. Якщо ви між розмірами, беріть більший.', 'promedix' ),
	);
	$h = '<div class="pmx-howto"><svg class="pmx-howto__pic" viewBox="0 0 200 250" width="150" height="188" role="img" aria-label="' . esc_attr__( 'Стопа на аркуші паперу п’ятою до стіни, довжину міряють від стіни до найдовшого пальця', 'promedix' ) . '">'
		. '<defs><pattern id="pmx-wall" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path d="M0 0v8" stroke="#9fb4bf" stroke-width="2"/></pattern></defs>'
		. '<rect x="8" y="222" width="184" height="20" fill="url(#pmx-wall)"/><path d="M8 222h184" stroke="#47606b" stroke-width="2.5"/>'
		. '<rect x="34" y="16" width="112" height="206" rx="3" fill="#fff" stroke="#c8d6dd" stroke-width="2"/>'
		. '<g fill="#dceaf1" stroke="#286071" stroke-width="2.2" stroke-linejoin="round">'
		. '<path d="M90 220c-17 0-26-12-25-30 1-14 7-24 7-40 0-14-6-26-6-44 0-24 12-46 32-46 18 0 26 18 26 38 0 16-6 30-6 46 0 18 6 30 4 44-2 20-14 32-32 32z"/>'
		. '<ellipse cx="72" cy="52" rx="9" ry="11"/><ellipse cx="88" cy="38" rx="7" ry="9"/><ellipse cx="102" cy="34" rx="6.5" ry="8"/><ellipse cx="115" cy="37" rx="6" ry="7"/><ellipse cx="126" cy="45" rx="5.5" ry="6.5"/>'
		. '</g>'
		. '<path d="M96 26h76" stroke="#286071" stroke-width="1.5" stroke-dasharray="4 4"/>'
		. '<g stroke="#286071" stroke-width="2.5" stroke-linecap="round" fill="none"><path d="M166 30v188"/><path d="m160 38 6-10 6 10M160 210l6 10 6-10"/></g>'
		. '<rect x="150" y="116" width="34" height="22" rx="11" fill="#286071"/><text x="167" y="131.5" text-anchor="middle" font-family="Manrope, sans-serif" font-size="12" font-weight="700" fill="#fff">см</text>'
		. '</svg>'
		. '<ol class="pmx-howto__steps">';
	foreach ( $steps as $s ) {
		$h .= '<li>' . esc_html( $s ) . '</li>';
	}
	return $h . '</ol></div>';
}

/** Full guide for one product: steps, its table, phone and exchange note. */
function promedix_size_guide_html( $product_id, $with_howto = true ) {
	$key   = promedix_product_size_key( $product_id );
	$c     = promedix_contacts();
	$table = promedix_size_table_html( $key );
	$h     = $with_howto
		? '<div class="pmx-sizeguide"><div class="pmx-sizeguide__how"><h3>' . esc_html__( 'Як виміряти стопу', 'promedix' ) . '</h3>' . promedix_size_howto_html() . '</div><div class="pmx-sizeguide__table"><h3>' . esc_html__( 'Таблиця розмірів', 'promedix' ) . '</h3>' . $table . '</div></div>'
		: $table;
	$h  .= '<p class="pmx-sizehint">' . esc_html__( 'Не впевнені щодо розміру? Зателефонуйте, і ми допоможемо підібрати пару:', 'promedix' ) . ' <a href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . esc_html( $c['phone_retail'] ) . '</a>.';
	if ( function_exists( 'promedix_link' ) && promedix_link( 'exchange' ) ) {
		$h .= ' <a href="' . esc_url( promedix_link( 'exchange' ) ) . '">' . esc_html__( 'Умови обміну, якщо розмір не підійшов', 'promedix' ) . '</a>.';
	}
	return $h . '</p>';
}

/** All tables (help page). */
function promedix_size_all_tables_html() {
	$h = '<div class="pmx-sizetables">';
	foreach ( array_keys( promedix_size_tables() ) as $key ) {
		$h .= promedix_size_table_html( $key );
	}
	return $h . '</div>';
}
