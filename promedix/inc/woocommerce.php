<?php
/**
 * WooCommerce integration: B2B prompts and checkout fields for organisations.
 * Does not touch product, category or page permalinks.
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Cart: wholesale hint.
 */
add_action( 'woocommerce_before_cart_totals', function () {
	echo '<div class="pmx-strip pmx-strip--small"><span>' . esc_html__( 'Замовляєте для колективу? Для закладів діють гуртові умови.', 'promedix' ) . '</span> <a href="' . esc_url( promedix_b2b_url() ) . '">' . esc_html__( 'Запитати гуртову ціну', 'promedix' ) . '</a></div>';
} );

/**
 * Classic checkout: show the company field and add an optional EDRPOU code.
 */
add_filter( 'woocommerce_billing_fields', function ( $fields ) {
	if ( isset( $fields['billing_company'] ) ) {
		$fields['billing_company']['label']    = __( 'Назва закладу або компанії (необов’язково)', 'promedix' );
		$fields['billing_company']['required'] = false;
		$fields['billing_company']['priority'] = 25;
	}
	$fields['billing_edrpou'] = array(
		'label'    => __( 'Код ЄДРПОУ для рахунку (необов’язково)', 'promedix' ),
		'required' => false,
		'class'    => array( 'form-row-wide' ),
		'priority' => 26,
		'type'     => 'text',
	);
	return $fields;
} );

/**
 * Block checkout: same optional EDRPOU field (WooCommerce 8.9+).
 */
add_action( 'woocommerce_init', function () {
	if ( function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		woocommerce_register_additional_checkout_field( array(
			'id'       => 'promedix/edrpou',
			'label'    => __( 'Код ЄДРПОУ для рахунку (для закладів)', 'promedix' ),
			'location' => 'order',
			'type'     => 'text',
			'required' => false,
		) );
	}
} );

/**
 * Show EDRPOU in the admin order screen (classic field).
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	$code = $order->get_meta( '_billing_edrpou' );
	if ( $code ) {
		echo '<p><strong>' . esc_html__( 'ЄДРПОУ', 'promedix' ) . ':</strong> ' . esc_html( $code ) . '</p>';
	}
} );

/**
 * Products per page on archives.
 */
add_filter( 'loop_shop_per_page', function () {
	return 24;
}, 20 );

/* =====================================================================
 * 1.2.0: catalog and product page layout from the approved reference.
 * Nothing below changes URLs: filters use WooCommerce's own ?filter_* query args.
 * ===================================================================== */

/**
 * Archive: the theme prints breadcrumb, title and description itself ([promedix_archive_head]).
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );
add_action( 'wp', function () {
	if ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
	}
} );

/**
 * [promedix_archive_head]: breadcrumb, serif title and short description.
 */
add_shortcode( 'promedix_archive_head', function () {
	ob_start();
	echo '<div class="pmx-archive-head">';
	woocommerce_breadcrumb( array( 'delimiter' => '<span class="pmx-bc-sep" aria-hidden="true">›</span>' ) );
	if ( is_search() ) {
		$title = sprintf( __( 'Пошук: «%s»', 'promedix' ), get_search_query() );
		$desc  = '';
	} elseif ( is_product_taxonomy() ) {
		$term  = get_queried_object();
		$title = $term->name;
		$desc  = term_description( $term );
	} else {
		$title = __( 'Каталог взуття', 'promedix' );
		$desc  = '<p>' . esc_html__( 'Професійне взуття для медиків, перукарів, б’юті-майстрів та інших професій, де важливі комфорт і легкість протягом дня.', 'promedix' ) . '</p>';
	}
	echo '<h1 class="pmx-archive-head__title">' . esc_html( $title ) . '</h1>';
	if ( $desc ) {
		// Long SEO descriptions stay on the page but collapse after the first paragraph.
		echo '<div class="pmx-archive-head__desc">' . wp_kses_post( $desc ) . '</div>';
	}
	echo '</div>';
	return ob_get_clean();
} );

/**
 * Product ids in the current archive (category/tag/shop), unfiltered, for filter counts.
 */
function promedix_archive_product_ids() {
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'posts_per_page' => 500,
		'no_found_rows'  => true,
	);
	if ( is_product_taxonomy() ) {
		$term              = get_queried_object();
		$args['tax_query'] = array( array( 'taxonomy' => $term->taxonomy, 'field' => 'term_id', 'terms' => $term->term_id ) );
	}
	return get_posts( $args );
}

/**
 * Current archive URL without paging, keeping other query args.
 */
function promedix_filter_link( $attr, $slugs ) {
	$base = remove_query_arg( array( 'paged', 'product-page' ) );
	$base = preg_replace( '#/page/\d+/?#', '/', $base );
	$key  = 'filter_' . $attr;
	if ( empty( $slugs ) ) {
		return remove_query_arg( array( $key, 'query_type_' . $attr ), $base );
	}
	return add_query_arg( array( $key => implode( ',', $slugs ), 'query_type_' . $attr => 'or' ), $base );
}

/**
 * [promedix_filters]: sidebar filters (size tiles, colour and material checkboxes).
 * Groups: filterable with 'promedix_filter_groups' => [ attribute => [label, style] ].
 */
add_shortcode( 'promedix_filters', function () {
	if ( ! function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
		return '';
	}
	$groups = apply_filters( 'promedix_filter_groups', array(
		'size'           => array( __( 'Розмір', 'promedix' ), 'tiles' ),
		'color'          => array( __( 'Колір', 'promedix' ), 'checks' ),
		'material-verkhu'=> array( __( 'Матеріал', 'promedix' ), 'checks' ),
		'material-pidoshvy' => array( __( 'Підошва', 'promedix' ), 'checks' ),
	) );
	$ids = promedix_archive_product_ids();
	if ( empty( $ids ) ) {
		return '';
	}
	$out    = '';
	$active = false;
	foreach ( $groups as $attr => $g ) {
		$tax = 'pa_' . $attr;
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$terms = wp_get_object_terms( $ids, $tax, array( 'orderby' => 'name' ) );
		if ( is_wp_error( $terms ) || count( $terms ) < 2 ) {
			continue; // A filter with one option filters nothing.
		}
		$terms = array_values( array_unique( $terms, SORT_REGULAR ) );
		if ( 'tiles' === $g[1] ) {
			usort( $terms, function ( $a, $b ) { return (float) $a->name <=> (float) $b->name; } );
		}
		$chosen = isset( $_GET[ 'filter_' . $attr ] ) ? array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET[ 'filter_' . $attr ] ) ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		$active = $active || ! empty( $chosen );
		$items  = '';
		foreach ( $terms as $t ) {
			$on    = in_array( $t->slug, $chosen, true );
			$next  = $on ? array_values( array_diff( $chosen, array( $t->slug ) ) ) : array_merge( $chosen, array( $t->slug ) );
			$cls   = 'pmx-filter__opt' . ( $on ? ' is-on' : '' );
			$items .= '<a class="' . $cls . '" href="' . esc_url( promedix_filter_link( $attr, $next ) ) . '" rel="nofollow" aria-pressed="' . ( $on ? 'true' : 'false' ) . '">'
				. ( 'checks' === $g[1] ? '<span class="pmx-check" aria-hidden="true"></span>' : '' )
				. esc_html( $t->name ) . '</a>';
		}
		$out .= '<details class="pmx-filter pmx-filter--' . esc_attr( $g[1] ) . '" open><summary>' . esc_html( $g[0] ) . '</summary><div class="pmx-filter__opts">' . $items . '</div></details>';
	}
	if ( ! $out ) {
		return '';
	}
	$reset = $active ? '<a class="pmx-filter-reset" href="' . esc_url( strtok( remove_query_arg( array( 'paged' ) ), '?' ) ) . '">' . esc_html__( 'Скинути фільтри', 'promedix' ) . '</a>' : '';
	return '<aside class="pmx-filters" aria-label="' . esc_attr__( 'Фільтри', 'promedix' ) . '"><details class="pmx-filters__toggle"><summary>' . esc_html__( 'Фільтри', 'promedix' ) . '</summary></details><div class="pmx-filters__body">' . $out . $reset . '</div></aside>';
} );

/**
 * Loop: clean cards (photo, name, price). Add-to-cart lives on the product page.
 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

/**
 * "Знайдено N моделей" instead of the default result count.
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
add_action( 'woocommerce_before_shop_loop', function () {
	$n   = (int) wc_get_loop_prop( 'total' );
	$m10 = $n % 10;
	$m100 = $n % 100;
	$word = ( 1 === $m10 && 11 !== $m100 ) ? __( 'модель', 'promedix' ) : ( ( $m10 >= 2 && $m10 <= 4 && ( $m100 < 12 || $m100 > 14 ) ) ? __( 'моделі', 'promedix' ) : __( 'моделей', 'promedix' ) );
	echo '<p class="woocommerce-result-count pmx-count">' . esc_html( sprintf( __( 'Знайдено %1$d %2$s', 'promedix' ), $n, $word ) ) . '</p>';
}, 20 );
add_filter( 'woocommerce_catalog_orderby', function ( $o ) {
	$labels = array(
		'menu_order' => __( 'Сортувати: за замовчуванням', 'promedix' ),
		'popularity' => __( 'Сортувати: популярні', 'promedix' ),
		'rating'     => __( 'Сортувати: за оцінкою', 'promedix' ),
		'date'       => __( 'Сортувати: новинки', 'promedix' ),
		'price'      => __( 'Сортувати: дешевші', 'promedix' ),
		'price-desc' => __( 'Сортувати: дорожчі', 'promedix' ),
	);
	foreach ( $o as $k => $v ) {
		if ( isset( $labels[ $k ] ) ) {
			$o[ $k ] = $labels[ $k ];
		}
	}
	return $o;
} );

/**
 * Single product: summary order like the reference.
 * title → price → short description → size tiles + add to cart → accordions → small B2B line.
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 6 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 8 );

/**
 * Accordion content. Texts are filterable: add_filter( 'promedix_product_accordions', ... ).
 */
function promedix_product_accordions() {
	$delivery_url = promedix_page_url( 'oplata-i-dostavka' );
	$exchange_url = promedix_page_url( 'obmin-ta-povernennya' );
	$items = array(
		'delivery' => array(
			'icon'  => 'truck',
			'title' => __( 'Доставка', 'promedix' ),
			'html'  => '<p>' . esc_html__( 'Відправляємо Новою поштою по всій Україні.', 'promedix' ) . '</p><p><a href="' . esc_url( $delivery_url ) . '">' . esc_html__( 'Усе про оплату і доставку', 'promedix' ) . '</a></p>',
		),
		'sizes' => array(
			'icon'  => 'ruler',
			'title' => __( 'Розмірна сітка', 'promedix' ),
			'html'  => '<p>' . esc_html__( 'Модель відповідає стандартній розмірній сітці. Не впевнені в розмірі? Зателефонуйте, допоможемо підібрати пару.', 'promedix' ) . '</p><p>' . promedix_sc_phone( array( 'type' => 'retail' ) ) . ' · <a href="' . esc_url( $exchange_url ) . '">' . esc_html__( 'Обмін та повернення', 'promedix' ) . '</a></p>',
		),
		'care' => array(
			'icon'  => 'info',
			'title' => __( 'Догляд за взуттям', 'promedix' ),
			'html'  => '<p>' . esc_html__( 'Протирайте взуття вологою м’якою серветкою, сушіть при кімнатній температурі подалі від батарей і прямого сонця.', 'promedix' ) . '</p>',
		),
	);
	return apply_filters( 'promedix_product_accordions', $items );
}

/* 3.0: the old summary accordions were replaced by full-width tabs (see inc/woo-v3.php). */

/**
 * "Переваги моделі" row under the product.
 */
/* 3.0: no generic "benefits" row (product facts come from the attributes tab). */

add_filter( 'woocommerce_product_related_products_heading', function () {
	return __( 'Схожі моделі', 'promedix' );
} );
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );
add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
	return __( 'Додати в кошик', 'promedix' );
} );
add_filter( 'woocommerce_single_product_carousel_options', function ( $o ) {
	$o['directionNav'] = false;
	return $o;
} );
add_filter( 'woocommerce_reset_variations_link', function () {
	return '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Скинути вибір', 'promedix' ) . '">' . esc_html__( 'Скинути', 'promedix' ) . '</a>';
} );

/* ---------------------------------------------------------------------------
 * 2.0: cards from the new reference (short title, colour swatches, "грн").
 * ------------------------------------------------------------------------- */

/** Prices read "1 259 грн" instead of "1 259 ₴". */
add_filter( 'woocommerce_currency_symbol', function ( $symbol, $currency ) {
	return 'UAH' === $currency ? 'грн' : $symbol;
}, 10, 2 );

/** Short card title: "Жіночі сабо ProMedix 708 Білі" → "ProMedix 708 Білі". */
function promedix_short_title( $title ) {
	$pos = mb_stripos( $title, 'ProMedix' );
	return ( false !== $pos && $pos > 0 ) ? mb_substr( $title, $pos ) : $title;
}
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', function () {
	echo '<h2 class="' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '">' . esc_html( promedix_short_title( get_the_title() ) ) . '</h2>';
}, 10 );

/**
 * Products of the same model ("promedix-707-bili", "promedix-707-rozhevi") grouped by model number.
 * Cached for 12 hours; cleared when a product is saved.
 */
function promedix_model_map() {
	$map = get_transient( 'promedix_model_map' );
	if ( is_array( $map ) ) {
		return $map;
	}
	$map = array();
	$ids = get_posts( array(
		'post_type'        => 'product',
		'post_status'      => 'publish',
		'posts_per_page'   => 300,
		'fields'           => 'ids',
		'orderby'          => 'menu_order title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
	foreach ( $ids as $id ) {
		$slug = get_post_field( 'post_name', $id );
		if ( preg_match( '/promedix-(\d{3})/', $slug, $m ) ) {
			$map[ $m[1] ][] = (int) $id;
		}
	}
	set_transient( 'promedix_model_map', $map, 12 * HOUR_IN_SECONDS );
	return $map;
}
add_action( 'save_post_product', function () {
	delete_transient( 'promedix_model_map' );
} );

function promedix_swatches( $product_id, $max = 3 ) {
	$slug = get_post_field( 'post_name', $product_id );
	if ( ! preg_match( '/promedix-(\d{3})/', $slug, $m ) ) {
		return '';
	}
	$map  = promedix_model_map();
	$ids  = isset( $map[ $m[1] ] ) ? $map[ $m[1] ] : array();
	if ( count( $ids ) < 2 ) {
		return '';
	}
	$ids  = array_values( array_unique( array_merge( array( (int) $product_id ), $ids ) ) );
	$more = count( $ids ) - $max;
	$out  = '<span class="pmx-swatches">';
	foreach ( array_slice( $ids, 0, $max ) as $id ) {
		$img = get_the_post_thumbnail_url( $id, 'woocommerce_gallery_thumbnail' );
		$cls = 'pmx-swatch' . ( $id === (int) $product_id ? ' is-current' : '' );
		$out .= '<a class="' . $cls . '" href="' . esc_url( get_permalink( $id ) ) . '" title="' . esc_attr( promedix_short_title( get_the_title( $id ) ) ) . '"' . ( $img ? ' style="background-image:url(' . esc_url( $img ) . ')"' : '' ) . '><span class="pmx-visually-hidden">' . esc_html( get_the_title( $id ) ) . '</span></a>';
	}
	if ( $more > 0 ) {
		$out .= '<a class="pmx-swatch-more" href="' . esc_url( get_permalink( $product_id ) ) . '#pmx-colors" title="' . esc_attr__( 'Усі кольори моделі', 'promedix' ) . '">+' . (int) $more . '</a>';
	}
	return $out . '</span>';
}

/* Card footer: title + price on the left, swatches on the right. */
add_action( 'woocommerce_after_shop_loop_item', function () {
	echo promedix_swatches( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
}, 7 );
