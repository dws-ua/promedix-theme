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
	$h = '<div class="pmx-howto"><svg class="pmx-howto__pic" viewBox="0 0 120 150" width="96" height="120" aria-hidden="true"><rect x="10" y="6" width="100" height="138" rx="4" fill="#fff" stroke="currentColor" stroke-width="2"/><path d="M60 128c-14 0-22-10-22-26 0-14 4-24 4-38 0-16 6-34 20-34 12 0 18 14 18 30 0 14-2 26-2 40 0 16-6 28-18 28z" fill="var(--product-photo-bg, #e6eef3)" stroke="currentColor" stroke-width="2"/><path d="M100 24v104" stroke="var(--primary, #286071)" stroke-width="2.5"/><path d="M95 24h10M95 128h10" stroke="var(--primary, #286071)" stroke-width="2.5"/></svg><ol class="pmx-howto__steps">';
	foreach ( $steps as $s ) {
		$h .= '<li>' . esc_html( $s ) . '</li>';
	}
	return $h . '</ol></div>';
}

/** Full guide for one product: steps, its table, phone and exchange note. */
function promedix_size_guide_html( $product_id ) {
	$key = promedix_product_size_key( $product_id );
	$c   = promedix_contacts();
	$h   = '<h3>' . esc_html__( 'Як виміряти стопу', 'promedix' ) . '</h3>' . promedix_size_howto_html();
	$h  .= '<h3>' . esc_html__( 'Таблиця розмірів', 'promedix' ) . '</h3>' . promedix_size_table_html( $key );
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
