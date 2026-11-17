<?php
/**
 * Helpers: contacts and language-aware links to EXISTING URLs.
 * Nothing here creates or changes slugs: it only looks up current ones.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

/**
 * Business contacts. Filterable so they can be changed without editing the theme.
 */
function promedix_contacts() {
	return apply_filters( 'promedix_contacts', array(
		'phone_wholesale'      => '+38 066 008 79 47',
		'phone_wholesale_href' => '+380660087947',
		'phone_retail'         => '+38 063 100 86 86',
		'phone_retail_href'    => '+380631008686',
		'email_wholesale'      => 'zakaz@promedix.com.ua',
		'address'              => __( 'вул. С. Наливайка, 13, офіс 213, м. Біла Церква, 09117', 'promedix' ),
		'instagram'            => 'https://www.instagram.com/promedix.ua/',
		'facebook'             => '',
		'telegram'             => '',
		'viber'                => '',
		// Slug of the "For businesses" page (create it once; set here if different).
		'b2b_page_slug'        => 'dlia-zakladiv',
	) );
}

function promedix_contact( $key ) {
	$c = promedix_contacts();
	return isset( $c[ $key ] ) ? $c[ $key ] : '';
}

/**
 * Translate an object id to the current WPML language (no-op without WPML).
 */
function promedix_wpml_id( $id, $type ) {
	return (int) apply_filters( 'wpml_object_id', $id, $type, true );
}

/**
 * URL of an existing page by its current (default language) slug.
 */
function promedix_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		return $fallback ? $fallback : home_url( '/' );
	}
	return get_permalink( promedix_wpml_id( $page->ID, 'page' ) );
}

/**
 * URL of an existing product category by slug.
 */
function promedix_term_url( $slug, $taxonomy = 'product_cat' ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( ! $term ) {
		return home_url( '/' );
	}
	$link = get_term_link( promedix_wpml_id( $term->term_id, $taxonomy ), $taxonomy );
	return is_wp_error( $link ) ? home_url( '/' ) : $link;
}

/**
 * URL of the "For businesses" page, or the wholesale anchor on the homepage.
 */
function promedix_b2b_url() {
	$page = get_page_by_path( promedix_contact( 'b2b_page_slug' ) );
	if ( $page ) {
		return get_permalink( promedix_wpml_id( $page->ID, 'page' ) );
	}
	return home_url( '/#wholesale' );
}

/**
 * Upload URL helper for images already in the media library.
 */
function promedix_upload( $path ) {
	return content_url( 'uploads/' . ltrim( $path, '/' ) );
}

/**
 * Thin line icons (24px, stroke = currentColor) used in benefit rows and accordions.
 */
function promedix_icon( $name, $size = 32 ) {
	$paths = array(
		'leaf'    => '<path d="M5 19c9 0 14-6 14-14C10 5 5 10 5 19Z"/><path d="M5 19 14 10"/>',
		'layers'  => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 16 9 5 9-5"/>',
		'feather' => '<path d="M20 4c-7 0-13 5-13 12v4"/><path d="M7 16c6 0 10-3 11-8"/><path d="M4 20h6"/>',
		'shield'  => '<path d="M12 3 19 6v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z"/><path d="m9 12 2 2 4-4"/>',
		'waves'   => '<path d="M4 8c2.5-2 5.5-2 8 0s5.5 2 8 0"/><path d="M4 13c2.5-2 5.5-2 8 0s5.5 2 8 0"/><path d="M4 18c2.5-2 5.5-2 8 0s5.5 2 8 0"/>',
		'truck'   => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.6"/><circle cx="17" cy="17.5" r="1.6"/>',
		'ruler'   => '<path d="m4 15 11-11 5 5L9 20Z"/><path d="m8 11 2 2M11 8l2 2M14 5l2 2"/>',
		'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>',
		'text'    => '<path d="M5 6h14M5 10h14M5 14h10M5 18h8"/>',
		'list'    => '<path d="M9 6h11M9 12h11M9 18h11"/><circle cx="5" cy="6" r="1"/><circle cx="5" cy="12" r="1"/><circle cx="5" cy="18" r="1"/>',
		'star'    => '<path d="m12 4 2.5 5 5.5.8-4 3.9.9 5.5L12 16.6 7.1 19.2l.9-5.5-4-3.9L9.5 9Z"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="pmx-icon" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>';
}
