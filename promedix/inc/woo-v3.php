<?php
/**
 * ProMedix 3.0 WooCommerce UI: catalog filter panel (size, colour, print, price, stock),
 * active filter chips, quick profession strip, product page colours/tabs, cart/checkout touches.
 * All filtering uses WooCommerce's own query args (filter_*, query_type_*, min_price, max_price),
 * so results always match the chosen filters. URLs of products and categories do not change.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Colour vs print: both live in pa_color in this shop, the UI splits them.
 * ------------------------------------------------------------------------- */
function promedix_solid_colors() {
	return apply_filters( 'promedix_solid_colors', array(
		'Білий'         => '#FFFFFF',
		'Білий/рожевий' => 'linear-gradient(135deg, #FFFFFF 50%, #F3B9CB 50%)',
		'Рожевий'       => '#F3B9CB',
		'Синій'         => '#2F3F6E',
		'Чорний'        => '#1E2326',
		'Блакитний'     => '#BFDDF2',
		'Сірий'         => '#9AA5AB',
	) );
}
function promedix_is_print( $term ) {
	return ! array_key_exists( $term->name, promedix_solid_colors() );
}

/* Checkbox arrays (filter_size[]=36&filter_size[]=37) → WooCommerce comma lists. */
add_action( 'init', function () {
	foreach ( $_GET as $k => $v ) { // phpcs:ignore WordPress.Security.NonceVerification
		if ( 0 === strpos( $k, 'filter_' ) && is_array( $v ) ) {
			$_GET[ $k ] = implode( ',', array_map( 'sanitize_title', wp_unslash( $v ) ) ); // phpcs:ignore
		}
	}
}, 1 );

/* "В наявності": hide products whose stock status is outofstock. */
add_action( 'woocommerce_product_query', function ( $q ) {
	if ( empty( $_GET['pmx_stock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$term = get_term_by( 'name', 'outofstock', 'product_visibility' );
	if ( ! $term ) {
		return; // Nothing is out of stock.
	}
	$tax   = $q->get( 'tax_query' );
	$tax   = is_array( $tax ) ? $tax : array();
	$tax[] = array( 'taxonomy' => 'product_visibility', 'field' => 'term_taxonomy_id', 'terms' => array( (int) $term->term_taxonomy_id ), 'operator' => 'NOT IN' );
	$q->set( 'tax_query', $tax );

	// With a size chosen, keep only models where that size itself is in stock (variation-level, from WooCommerce's attribute lookup table).
	$sizes = promedix_chosen( 'size' );
	if ( $sizes ) {
		global $wpdb;
		$ids = array();
		foreach ( $sizes as $slug ) {
			$t = get_term_by( 'slug', $slug, 'pa_size' );
			if ( $t ) {
				$ids[] = (int) $t->term_id;
			}
		}
		$table = $wpdb->prefix . 'wc_product_attributes_lookup';
		if ( $ids && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$in      = implode( ',', $ids );
			$parents = $wpdb->get_col( "SELECT DISTINCT product_or_parent_id FROM {$table} WHERE taxonomy = 'pa_size' AND term_id IN ({$in}) AND in_stock = 1" ); // phpcs:ignore WordPress.DB
			if ( $parents ) {
				$prev = (array) $q->get( 'post__in' );
				$q->set( 'post__in', $prev ? array_values( array_intersect( $prev, $parents ) ) : array_map( 'intval', $parents ) );
			}
		}
	}
} );

function promedix_chosen( $attr ) {
	if ( empty( $_GET[ 'filter_' . $attr ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return array();
	}
	return array_values( array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET[ 'filter_' . $attr ] ) ) ) ) ); // phpcs:ignore
}

function promedix_archive_base_url() {
	if ( is_product_taxonomy() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? home_url( '/' ) : $link;
	}
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

/** Price range of the products in the current archive (from the WooCommerce lookup table). */
function promedix_archive_price_range( $ids ) {
	global $wpdb;
	if ( ! $ids ) {
		return array( 0, 0 );
	}
	$ids = implode( ',', array_map( 'intval', $ids ) );
	$row = $wpdb->get_row( "SELECT MIN(min_price) AS lo, MAX(max_price) AS hi FROM {$wpdb->wc_product_meta_lookup} WHERE product_id IN ($ids)" ); // phpcs:ignore
	return $row ? array( (int) floor( $row->lo ), (int) ceil( $row->hi ) ) : array( 0, 0 );
}

/* ---------------------------------------------------------------------------
 * [promedix_filters]: the filter panel (a GET form; "Застосувати" applies, "Скинути" clears).
 * ------------------------------------------------------------------------- */
function promedix_sc_filters_v3() {
	if ( ! function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
		return '';
	}
	$ids = promedix_archive_product_ids();
	if ( empty( $ids ) ) {
		return '';
	}
	$terms_of = function ( $tax ) use ( $ids ) {
		if ( ! taxonomy_exists( $tax ) ) {
			return array();
		}
		$t = wp_get_object_terms( $ids, $tax, array( 'orderby' => 'name' ) );
		if ( is_wp_error( $t ) ) {
			return array();
		}
		$u = array();
		foreach ( $t as $term ) {
			$u[ $term->term_id ] = $term;
		}
		return array_values( $u );
	};
	$groups = '';
	$n_on   = 0;

	// Size tiles.
	$sizes = $terms_of( 'pa_size' );
	usort( $sizes, function ( $a, $b ) { return (float) str_replace( ',', '.', $a->name ) <=> (float) str_replace( ',', '.', $b->name ); } );
	$chosen = promedix_chosen( 'size' );
	$n_on  += count( $chosen );
	if ( count( $sizes ) > 1 ) {
		$h = '';
		foreach ( $sizes as $t ) {
			$h .= '<label class="pmx-chip-opt"><input type="checkbox" name="filter_size[]" value="' . esc_attr( $t->slug ) . '"' . checked( in_array( $t->slug, $chosen, true ), true, false ) . '><span>' . esc_html( $t->name ) . '</span></label>';
		}
		$groups .= '<details class="pmx-fgroup" open><summary>' . esc_html__( 'Розмір', 'promedix' ) . '</summary><fieldset><legend class="pmx-visually-hidden">' . esc_html__( 'Розмір', 'promedix' ) . '</legend><div class="pmx-fgroup__sizes">' . $h . '</div></fieldset></details>';
	}

	// Colour swatches and prints (same attribute, separate groups).
	$colors = $terms_of( 'pa_color' );
	$chosen = promedix_chosen( 'color' );
	$n_on  += count( $chosen );
	$solid  = array_filter( $colors, function ( $t ) { return ! promedix_is_print( $t ); } );
	$prints = array_filter( $colors, 'promedix_is_print' );
	$map    = promedix_solid_colors();
	if ( $solid ) {
		$h = '';
		foreach ( $solid as $t ) {
			$h .= '<label class="pmx-swatch-opt" title="' . esc_attr( $t->name ) . '"><input type="checkbox" name="filter_color[]" value="' . esc_attr( $t->slug ) . '"' . checked( in_array( $t->slug, $chosen, true ), true, false ) . '><span class="pmx-swatch-opt__dot" style="background:' . esc_attr( $map[ $t->name ] ) . '" aria-hidden="true"></span><span class="pmx-swatch-opt__name">' . esc_html( $t->name ) . '</span></label>';
		}
		$groups .= '<details class="pmx-fgroup" open><summary>' . esc_html__( 'Колір', 'promedix' ) . '</summary><fieldset><legend class="pmx-visually-hidden">' . esc_html__( 'Колір', 'promedix' ) . '</legend><div class="pmx-fgroup__swatches">' . $h . '</div></fieldset></details>';
	}
	if ( $prints ) {
		$h = '';
		foreach ( $prints as $t ) {
			$h .= '<label class="pmx-check-opt"><input type="checkbox" name="filter_color[]" value="' . esc_attr( $t->slug ) . '"' . checked( in_array( $t->slug, $chosen, true ), true, false ) . '><span>' . esc_html( $t->name ) . '</span></label>';
		}
		$groups .= '<details class="pmx-fgroup" open><summary>' . esc_html__( 'Принт', 'promedix' ) . '</summary><fieldset><legend class="pmx-visually-hidden">' . esc_html__( 'Принт', 'promedix' ) . '</legend><div class="pmx-fgroup__checks">' . $h . '</div></fieldset></details>';
	}
	if ( $solid || $prints ) {
		$groups .= '<input type="hidden" name="query_type_color" value="or">';
	}
	if ( count( $sizes ) > 1 ) {
		$groups .= '<input type="hidden" name="query_type_size" value="or">';
	}

	// Price.
	list( $lo, $hi ) = promedix_archive_price_range( $ids );
	if ( $hi > $lo ) {
		$min = isset( $_GET['min_price'] ) ? max( $lo, (int) $_GET['min_price'] ) : $lo; // phpcs:ignore
		$max = isset( $_GET['max_price'] ) ? min( $hi, (int) $_GET['max_price'] ) : $hi; // phpcs:ignore
		if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) { // phpcs:ignore
			$n_on++;
		}
		$groups .= '<details class="pmx-fgroup" open><summary>' . esc_html__( 'Ціна, грн', 'promedix' ) . '</summary><fieldset class="pmx-price" data-pmx-price data-lo="' . $lo . '" data-hi="' . $hi . '"><legend class="pmx-visually-hidden">' . esc_html__( 'Ціна', 'promedix' ) . '</legend>'
			. '<div class="pmx-price__range" aria-hidden="true"><input type="range" min="' . $lo . '" max="' . $hi . '" step="1" value="' . $min . '" tabindex="-1" data-pmx-range="min"><input type="range" min="' . $lo . '" max="' . $hi . '" step="1" value="' . $max . '" tabindex="-1" data-pmx-range="max"></div>'
			. '<div class="pmx-price__inputs"><label><span>' . esc_html__( 'від', 'promedix' ) . '</span><input type="number" inputmode="numeric" name="min_price" min="' . $lo . '" max="' . $hi . '" value="' . $min . '"></label>'
			. '<label><span>' . esc_html__( 'до', 'promedix' ) . '</span><input type="number" inputmode="numeric" name="max_price" min="' . $lo . '" max="' . $hi . '" value="' . $max . '"></label></div></fieldset></details>';
	}

	// Stock.
	$stock = ! empty( $_GET['pmx_stock'] ); // phpcs:ignore
	$n_on += $stock ? 1 : 0;
	$groups .= '<div class="pmx-fgroup pmx-fgroup--switch"><label class="pmx-switch"><span>' . esc_html__( 'Лише в наявності', 'promedix' ) . '</span><input type="checkbox" role="switch" name="pmx_stock" value="1"' . checked( $stock, true, false ) . '><span class="pmx-switch__ui" aria-hidden="true"></span></label></div>';

	$keep = '';
	if ( ! empty( $_GET['orderby'] ) ) { // phpcs:ignore
		$keep .= '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ) . '">'; // phpcs:ignore
	}
	if ( is_search() ) {
		$keep .= '<input type="hidden" name="s" value="' . esc_attr( get_search_query() ) . '"><input type="hidden" name="post_type" value="product">';
	}
	$base = promedix_archive_base_url();
	return '<button class="pmx-btn pmx-btn--secondary pmx-filters__open" type="button" aria-controls="pmx-filters" aria-expanded="false" data-pmx-filters-open>' . promedix_ui_icon( 'sparkle', 20 ) . esc_html__( 'Фільтри', 'promedix' ) . ( $n_on ? ' <span class="pmx-badge">' . (int) $n_on . '</span>' : '' ) . '</button>'
		. '<aside class="pmx-filters" id="pmx-filters" aria-labelledby="pmx-filters-t"><form class="pmx-filters__form" method="get" action="' . esc_url( $base ) . '" data-pmx-filters>'
		. '<div class="pmx-filters__head"><h2 id="pmx-filters-t" class="pmx-filters__title">' . esc_html__( 'Фільтри', 'promedix' ) . '</h2><button class="pmx-icon-btn pmx-filters__close" type="button" data-pmx-filters-close aria-label="' . esc_attr__( 'Закрити фільтри', 'promedix' ) . '"><svg class="pmx-ico" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></div>'
		. $groups . $keep
		. '<div class="pmx-filters__actions"><button class="pmx-btn" type="submit">' . esc_html__( 'Застосувати фільтри', 'promedix' ) . '</button>'
		. '<a class="pmx-btn pmx-btn--secondary" href="' . esc_url( $base ) . '">' . esc_html__( 'Скинути фільтри', 'promedix' ) . '</a></div></form></aside>';
}
add_action( 'init', function () {
	remove_shortcode( 'promedix_filters' );
	add_shortcode( 'promedix_filters', 'promedix_sc_filters_v3' );
}, 20 );

/* ---------------------------------------------------------------------------
 * [promedix_active_filters]: chips for each active filter + reset.
 * ------------------------------------------------------------------------- */
add_shortcode( 'promedix_active_filters', function () {
	$chips = array();
	foreach ( array( 'size' => __( 'Розмір', 'promedix' ), 'color' => '' ) as $attr => $label ) {
		$chosen = promedix_chosen( $attr );
		foreach ( $chosen as $slug ) {
			$t = get_term_by( 'slug', $slug, 'pa_' . $attr );
			if ( ! $t ) {
				continue;
			}
			$lab = $label ? $label : ( promedix_is_print( $t ) ? __( 'Принт', 'promedix' ) : __( 'Колір', 'promedix' ) );
			$chips[] = array( $lab . ': ' . $t->name, promedix_filter_link( $attr, array_values( array_diff( $chosen, array( $slug ) ) ) ) );
		}
	}
	if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) { // phpcs:ignore
		$chips[] = array( sprintf( __( 'Ціна: %1$s–%2$s грн', 'promedix' ), isset( $_GET['min_price'] ) ? (int) $_GET['min_price'] : '0', isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : '…' ), remove_query_arg( array( 'min_price', 'max_price', 'paged' ) ) ); // phpcs:ignore
	}
	if ( ! empty( $_GET['pmx_stock'] ) ) { // phpcs:ignore
		$chips[] = array( __( 'В наявності', 'promedix' ), remove_query_arg( array( 'pmx_stock', 'paged' ) ) );
	}
	if ( ! $chips ) {
		return '';
	}
	$h = '<div class="pmx-active" aria-label="' . esc_attr__( 'Активні фільтри', 'promedix' ) . '"><ul>';
	foreach ( $chips as $c ) {
		$h .= '<li><a class="pmx-active__chip" href="' . esc_url( preg_replace( '#/page/\d+/?#', '/', $c[1] ) ) . '" rel="nofollow"><span>' . esc_html( $c[0] ) . '</span><svg class="pmx-ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg><span class="pmx-visually-hidden">' . esc_html__( '(прибрати)', 'promedix' ) . '</span></a></li>';
	}
	return $h . '</ul><a class="pmx-active__reset" href="' . esc_url( promedix_archive_base_url() ) . '">' . esc_html__( 'Скинути все', 'promedix' ) . '</a></div>';
} );

/* Quick profession strip under the catalog hero. */
add_shortcode( 'promedix_quick_professions', function () {
	$h = '<nav class="pmx-quick pmx-container" aria-label="' . esc_attr__( 'Швидкий вибір за професією', 'promedix' ) . '"><span class="pmx-quick__label">' . esc_html__( 'Швидкий вибір за професією:', 'promedix' ) . '</span>';
	foreach ( promedix_professions() as $p ) {
		$h .= '<a href="' . esc_url( $p['url'] ) . '">' . esc_html( $p['label'] ) . promedix_ui_icon( 'arrowup', 18 ) . '</a>';
	}
	return $h . '</nav>';
} );

/* Archive hero lead: first sentence of the term description; full SEO text moves below the products. */
add_filter( 'shortcode_atts_promedix_page_hero', function ( $out ) {
	if ( 'auto' !== $out['lead'] ) {
		return $out;
	}
	$lead = __( 'Комфортне професійне взуття для щоденної роботи.', 'promedix' );
	if ( is_product_taxonomy() ) {
		$d = trim( wp_strip_all_tags( term_description() ) );
		if ( $d && preg_match( '/^(.{20,180}?[.!?])(\s|$)/u', $d, $m ) ) {
			$lead = $m[1];
		}
	} elseif ( is_search() ) {
		$lead = '';
	}
	$out['lead'] = $lead;
	return $out;
} );
add_action( 'woocommerce_after_shop_loop', function () {
	if ( is_shop() && ! is_search() ) {
		echo promedix_seo_block( 'shop' ); // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	if ( ! is_product_taxonomy() ) {
		return;
	}
	$d = term_description();
	if ( $d && strlen( wp_strip_all_tags( $d ) ) > 200 ) {
		echo '<details class="pmx-seo"><summary>' . esc_html( single_term_title( '', false ) ) . '</summary><div class="pmx-prose">' . wp_kses_post( $d ) . '</div></details>';
	} else {
		$o = get_queried_object();
		echo $o instanceof WP_Term ? promedix_seo_block( $o->slug ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}, 30 );

/* Sorting label as in the mock-up ("Сортувати:" + select). */
add_filter( 'woocommerce_catalog_orderby', function ( $o ) {
	$labels = array(
		'menu_order' => __( 'За замовчуванням', 'promedix' ),
		'popularity' => __( 'За популярністю', 'promedix' ),
		'rating'     => __( 'За оцінкою', 'promedix' ),
		'date'       => __( 'Новинки', 'promedix' ),
		'price'      => __( 'Від дешевших', 'promedix' ),
		'price-desc' => __( 'Від дорожчих', 'promedix' ),
	);
	foreach ( $o as $k => $v ) {
		if ( isset( $labels[ $k ] ) ) {
			$o[ $k ] = $labels[ $k ];
		}
	}
	return $o;
}, 20 );

/* Pagination arrows */
add_filter( 'woocommerce_pagination_args', function ( $args ) {
	$args['prev_text'] = '<span class="pmx-visually-hidden">' . esc_html__( 'Попередня сторінка', 'promedix' ) . '</span>←';
	$args['next_text'] = '<span class="pmx-visually-hidden">' . esc_html__( 'Наступна сторінка', 'promedix' ) . '</span>→';
	return $args;
} );

/* Loop images: fixed dimensions + lazy below the fold (WooCommerce already prints width/height). */
add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string' );

/* ---------------------------------------------------------------------------
 * Product page
 * ------------------------------------------------------------------------- */

/* Breadcrumb above the product, with "/" separators. */
add_filter( 'woocommerce_breadcrumb_defaults', function ( $d ) {
	$d['home']        = __( 'Головна', 'promedix' );
	$d['delimiter']   = '<span class="pmx-crumbs__sep" aria-hidden="true">/</span>';
	$d['wrap_before'] = '<nav class="woocommerce-breadcrumb pmx-crumbs" aria-label="' . esc_attr__( 'Хлібні крихти', 'promedix' ) . '">';
	return $d;
} );

/* Colour of the model: links to the sibling products (same model number). */
add_action( 'woocommerce_single_product_summary', function () {
	$sw = promedix_swatches( get_the_ID(), 99 );
	if ( ! $sw ) {
		return;
	}
	$name = '';
	$t    = wc_get_product_terms( get_the_ID(), 'pa_color', array( 'fields' => 'names' ) );
	if ( $t ) {
		$name = implode( ', ', $t );
	}
	echo '<div class="pmx-model-colors" id="pmx-colors"><p class="pmx-label">' . esc_html__( 'Колір', 'promedix' ) . ( $name ? ': <span>' . esc_html( $name ) . '</span>' : '' ) . '</p>' . $sw . '</div>'; // phpcs:ignore
}, 25 );

/* "For teams" line under the buy button. */
add_action( 'woocommerce_single_product_summary', function () {
	$url = promedix_link( 'teams' ) ? promedix_link( 'teams' ) : promedix_link( 'contacts' );
	echo '<p class="pmx-b2b-line">' . promedix_ui_icon( 'users', 20 ) . '<span>' . esc_html__( 'Купуєте для команди або закладу?', 'promedix' ) . ' ' . ( $url ? '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Оптові ціни за запитом', 'promedix' ) . '</a>' : esc_html( promedix_contact( 'phone_wholesale' ) ) ) . '</span></p>'; // phpcs:ignore
}, 45 );

/* Full-width tabs: Опис, Характеристики, Доставка й оплата, Догляд, Відгуки. */
add_action( 'woocommerce_after_single_product_summary', function () {
	$tabs = array();
	foreach ( apply_filters( 'woocommerce_product_tabs', array() ) as $key => $tab ) {
		if ( empty( $tab['callback'] ) ) {
			continue;
		}
		ob_start();
		call_user_func( $tab['callback'], $key, $tab );
		$html = ob_get_clean();
		$html = preg_replace( '#^\s*<h2[^>]*>.*?</h2>#s', '', $html ); // the tab already names the section
		if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
			continue;
		}
		$title        = 'additional_information' === $key ? __( 'Характеристики', 'promedix' ) : ( 'description' === $key ? __( 'Опис', 'promedix' ) : wp_strip_all_tags( $tab['title'] ) );
		$tabs[ $key ] = array( $title, $html );
	}
	$m        = promedix_shop_methods();
	$delivery = '';
	if ( $m['shipping'] ) {
		$delivery .= '<h3>' . esc_html__( 'Доставка', 'promedix' ) . '</h3><ul>';
		foreach ( $m['shipping'] as $s ) {
			$delivery .= '<li>' . esc_html( $s[0] ) . '</li>';
		}
		$delivery .= '</ul>';
	}
	if ( $m['payment'] ) {
		$delivery .= '<h3>' . esc_html__( 'Оплата', 'promedix' ) . '</h3><ul>';
		foreach ( $m['payment'] as $s ) {
			$delivery .= '<li>' . esc_html( $s[0] ) . '</li>';
		}
		$delivery .= '</ul>';
	}
	$more = array_filter( array( promedix_link( 'delivery' ) ? '<a href="' . esc_url( promedix_link( 'delivery' ) ) . '">' . esc_html__( 'Оплата і доставка', 'promedix' ) . '</a>' : '', promedix_link( 'exchange' ) ? '<a href="' . esc_url( promedix_link( 'exchange' ) ) . '">' . esc_html__( 'Обмін та повернення', 'promedix' ) . '</a>' : '' ) );
	if ( $more ) {
		$delivery .= '<p>' . implode( ' · ', $more ) . '</p>';
	}
	if ( $delivery ) {
		$tabs = array_slice( $tabs, 0, 2, true ) + array( 'delivery' => array( __( 'Доставка й оплата', 'promedix' ), $delivery ) ) + array_slice( $tabs, 2, null, true );
	}
	$care = apply_filters( 'promedix_care_text', '<p>' . esc_html__( 'Протирайте взуття вологою м’якою серветкою. Сушіть при кімнатній температурі, подалі від батарей і прямого сонця.', 'promedix' ) . '</p>' );
	if ( $care ) {
		$tabs = array_slice( $tabs, 0, 3, true ) + array( 'care' => array( __( 'Догляд', 'promedix' ), $care ) ) + array_slice( $tabs, 3, null, true );
	}
	$c          = promedix_contacts();
	$tabs_sizes = '<p>' . esc_html__( 'Не впевнені щодо розміру? Зателефонуйте, і ми допоможемо підібрати пару:', 'promedix' ) . ' <a href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . esc_html( $c['phone_retail'] ) . '</a>.</p>';
	if ( promedix_link( 'exchange' ) ) {
		$tabs_sizes .= '<p><a href="' . esc_url( promedix_link( 'exchange' ) ) . '">' . esc_html__( 'Умови обміну, якщо розмір не підійшов', 'promedix' ) . '</a></p>';
	}
	if ( ! $tabs ) {
		return;
	}
	echo '<section class="pmx-tabs" data-pmx-tabs><div class="pmx-tabs__list" role="tablist" aria-label="' . esc_attr__( 'Інформація про товар', 'promedix' ) . '">';
	$i = 0;
	foreach ( $tabs as $key => $t ) {
		echo '<button type="button" role="tab" id="pmx-tab-' . esc_attr( $key ) . '" aria-controls="pmx-panel-' . esc_attr( $key ) . '" aria-selected="' . ( 0 === $i ? 'true' : 'false' ) . '"' . ( 0 === $i ? '' : ' tabindex="-1"' ) . '>' . esc_html( $t[0] ) . '</button>';
		$i++;
	}
	echo '</div>';
	$i = 0;
	foreach ( $tabs as $key => $t ) {
		echo '<div class="pmx-tabs__panel pmx-prose" role="tabpanel" id="pmx-panel-' . esc_attr( $key ) . '" aria-labelledby="pmx-tab-' . esc_attr( $key ) . '" tabindex="0"' . ( 0 === $i ? '' : ' hidden' ) . '>' . $t[1] . '</div>'; // phpcs:ignore
		$i++;
	}
	echo '</section>';
	echo '<dialog class="pmx-dialog" id="pmx-size-dialog" aria-labelledby="pmx-size-dialog-t"><div class="pmx-dialog__in"><h2 id="pmx-size-dialog-t">' . esc_html__( 'Як обрати розмір', 'promedix' ) . '</h2>' . $tabs_sizes . '<form method="dialog"><button class="pmx-btn">' . esc_html__( 'Зрозуміло', 'promedix' ) . '</button></form></div></dialog>'; // phpcs:ignore
}, 10 );

add_filter( 'woocommerce_output_related_products_args', function ( $a ) {
	$a['posts_per_page'] = 4;
	$a['columns']        = 4;
	return $a;
}, 20 );
add_filter( 'woocommerce_product_related_products_heading', function () {
	return __( 'Схожі товари', 'promedix' );
}, 20 );

/* Variation dropdown: clear label instead of "Choose an option". */
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', function ( $args ) {
	$args['show_option_none'] = __( 'Оберіть розмір', 'promedix' );
	return $args;
} );

/* ---------------------------------------------------------------------------
 * Cart & checkout
 * ------------------------------------------------------------------------- */
add_filter( 'woocommerce_order_button_text', function () {
	return __( 'Підтвердити замовлення', 'promedix' );
} );
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $t;
	}
	$map = array(
		'Proceed to checkout' => 'Оформити замовлення',
		'Cart totals'         => 'Ваше замовлення',
		'Your order'          => 'Ваше замовлення',
	);
	return isset( $map[ $text ] ) ? $map[ $text ] : $t;
}, 10, 3 );

/* Empty cart: friendly empty state with a way back. */
remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
add_action( 'woocommerce_cart_is_empty', function () {
	echo '<div class="pmx-empty">' . promedix_ui_icon( 'bag', 48 ) . '<h2>' . esc_html__( 'Ваш кошик порожній', 'promedix' ) . '</h2><p>' . esc_html__( 'Оберіть модель і розмір у каталозі.', 'promedix' ) . '</p></div>'; // phpcs:ignore
}, 10 );
