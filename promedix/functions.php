<?php
/**
 * ProMedix block theme.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

define( 'PROMEDIX_VERSION', '3.4.4' );

require_once get_theme_file_path( 'inc/helpers.php' );
require_once get_theme_file_path( 'inc/shortcodes.php' );
require_once get_theme_file_path( 'inc/woocommerce.php' );
require_once get_theme_file_path( 'inc/v3.php' );
require_once get_theme_file_path( 'inc/seo-texts.php' );
require_once get_theme_file_path( 'inc/landings.php' );
require_once get_theme_file_path( 'inc/woo-v3.php' );

/**
 * Theme setup.
 */
function promedix_setup() {
	load_theme_textdomain( 'promedix', get_theme_file_path( 'languages' ) );

	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1024,
		'product_grid'          => array( 'default_columns' => 3, 'default_rows' => 4 ),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_editor_style( array( promedix_fonts_url(), 'assets/css/theme.css' ) );

	// Classic menu locations: WPML translates and syncs these menus per language.
	register_nav_menus( array(
		'primary'          => __( 'Головне меню', 'promedix' ),
		'topbar'           => __( 'Верхня смужка', 'promedix' ),
		'footer-catalog'   => __( 'Футер: каталог', 'promedix' ),
		'footer-customers' => __( 'Футер: покупцям', 'promedix' ),
	) );
}
add_action( 'after_setup_theme', 'promedix_setup' );

/**
 * Google Fonts URL (Lora + Inter, with Cyrillic).
 * TODO: self-host the fonts in assets/fonts for performance and GDPR.
 */
function promedix_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap';
}

/**
 * Front-end assets.
 */
function promedix_enqueue() {
	wp_enqueue_style( 'promedix-fonts', promedix_fonts_url(), array(), null );
	wp_enqueue_style( 'promedix-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), PROMEDIX_VERSION );
	wp_enqueue_script( 'promedix-theme', get_theme_file_uri( 'assets/js/theme.js' ), array(), PROMEDIX_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}
add_action( 'wp_enqueue_scripts', 'promedix_enqueue' );

/**
 * Preconnect for fonts.
 */
function promedix_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'promedix_resource_hints', 10, 2 );

/**
 * Pattern categories.
 */
function promedix_pattern_categories() {
	register_block_pattern_category( 'promedix', array( 'label' => __( 'ProMedix', 'promedix' ) ) );
	register_block_pattern_category( 'promedix-b2b', array( 'label' => __( 'ProMedix: гурт', 'promedix' ) ) );
}
add_action( 'init', 'promedix_pattern_categories' );

/**
 * Shortcodes inside theme patterns ([products], [promedix_phone], …):
 * the pattern block is rendered after the template's shortcode pass, so run it here.
 */
add_filter( 'render_block_core/pattern', function ( $content ) {
	return ( false !== strpos( $content, '[' ) ) ? do_shortcode( $content ) : $content;
} );
