<?php
/**
 * URL snapshot for ProMedix. NOT part of the theme.
 * Run it with WP loaded (e.g. Novamira execute-php, or `wp eval-file url-snapshot.php`)
 * BEFORE and AFTER switching the theme, save both outputs and compare: they must be identical.
 * Returns: array( url => "type:id:lang" ), sorted.
 */
$out   = array();
$langs = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
$codes = $langs ? array_keys( $langs ) : array( null );

foreach ( $codes as $code ) {
	if ( $code ) {
		do_action( 'wpml_switch_language', $code );
	}
	$types = array( 'post', 'page', 'product' );
	foreach ( $types as $type ) {
		$ids = get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'suppress_filters' => false ) );
		foreach ( $ids as $id ) {
			$out[ get_permalink( $id ) ] = $type . ':' . $id . ':' . $code;
		}
	}
	foreach ( array( 'product_cat', 'product_tag', 'category', 'post_tag' ) as $tax ) {
		$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) ) {
			continue;
		}
		foreach ( $terms as $t ) {
			$link = get_term_link( $t );
			if ( ! is_wp_error( $link ) ) {
				$out[ $link ] = $tax . ':' . $t->term_id . ':' . $code;
			}
		}
	}
	$out[ home_url( '/' ) ] = 'home::' . $code;
}
if ( $codes[0] ) {
	do_action( 'wpml_switch_language', apply_filters( 'wpml_default_language', null ) );
}
ksort( $out );
return array( 'count' => count( $out ), 'md5' => md5( implode( "\n", array_keys( $out ) ) ), 'urls' => $out );
