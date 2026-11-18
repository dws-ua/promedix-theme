<?php
/**
 * ProMedix 3.0: shared layout pieces (logo, page hero, team band, footer),
 * service pages (help, contacts, favourites, profession, about, teams) and the request form.
 *
 * Content rules: only data that exists in the shop (pages, WooCommerce methods, attributes, contacts).
 *
 * @package promedix
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Links
 * ------------------------------------------------------------------------- */

/**
 * Slugs of pages the theme links to. Pages that do not exist yet are simply not linked.
 */
function promedix_page_slugs() {
	return apply_filters( 'promedix_page_slugs', array(
		'contacts'   => 'contacts',
		'delivery'   => 'oplata-i-dostavka',
		'exchange'   => 'obmin-ta-povernennya',
		'privacy'    => 'privacy-policy',
		'blog'       => 'blog',
		'wishlist'   => 'wishlist',
		'about'      => 'pro-brend',
		'teams'      => 'dlia-komand',
		'medics'     => 'dlia-medykiv',
		'hair'       => 'dlia-perukariv',
		'beauty'     => 'dlia-biuti-maistriv',
	) );
}

/** URL of an existing page by key, or '' when the page does not exist. */
function promedix_link( $key ) {
	$slugs = promedix_page_slugs();
	if ( empty( $slugs[ $key ] ) ) {
		return '';
	}
	$page = get_page_by_path( $slugs[ $key ] );
	if ( ! $page || 'publish' !== $page->post_status ) {
		return '';
	}
	return get_permalink( promedix_wpml_id( $page->ID, 'page' ) );
}

/** Profession entry points: the profession page if it exists, else the closest catalog. */
function promedix_professions() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$list = array(
		'medics' => array( __( 'Медикам', 'promedix' ), __( 'Взуття для медиків', 'promedix' ), 'medychne-vzuttia' ),
		'hair'   => array( __( 'Перукарям', 'promedix' ), __( 'Взуття для перукарів', 'promedix' ), '' ),
		'beauty' => array( __( 'Б’юті-майстрам', 'promedix' ), __( 'Взуття для б’юті-майстрів', 'promedix' ), '' ),
	);
	$out = array();
	foreach ( $list as $key => $p ) {
		$url = promedix_link( $key );
		if ( ! $url ) {
			$url = $p[2] ? promedix_term_url( $p[2] ) : $shop;
		}
		$out[ $key ] = array( 'label' => $p[0], 'title' => $p[1], 'cat' => $p[2], 'url' => $url );
	}
	return apply_filters( 'promedix_professions', $out );
}

/* ---------------------------------------------------------------------------
 * Logo (original SVG files, never redrawn)
 * ------------------------------------------------------------------------- */

/**
 * [promedix_logo variant="mono|color|plate" width="160"]
 * "plate" = mono logo on a light plate, for dark sections (no white logo file was supplied).
 */
function promedix_sc_logo_v3( $atts ) {
	$atts  = shortcode_atts( array( 'variant' => 'mono', 'width' => 160, 'height' => 0, 'link' => '1' ), $atts );
	$w     = max( 60, (int) $atts['width'] );
	$file  = 'color' === $atts['variant'] ? 'logo-promedix-color.svg' : 'logo-promedix-mono.svg';
	$img   = '<img class="pmx-logo" src="' . esc_url( get_theme_file_uri( 'assets/img/' . $file ) ) . '" alt="ProMedix" width="' . $w . '" height="' . (int) round( $w * 301 / 1061 ) . '" style="width:' . $w . 'px;height:auto">';
	$class = 'pmx-logo-link' . ( 'plate' === $atts['variant'] ? ' pmx-logo-link--plate' : '' );
	if ( '0' === (string) $atts['link'] ) {
		return '<span class="' . esc_attr( $class ) . '">' . $img . '</span>';
	}
	$home = apply_filters( 'wpml_home_url', home_url( '/' ) );
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $home ) . '" rel="home" aria-label="' . esc_attr__( 'ProMedix, на головну', 'promedix' ) . '">' . $img . '</a>';
}
add_action( 'init', function () {
	remove_shortcode( 'promedix_logo' );
	add_shortcode( 'promedix_logo', 'promedix_sc_logo_v3' );
}, 20 );

/* ---------------------------------------------------------------------------
 * Icons: one outline set, 24px grid, stroke = currentColor
 * ------------------------------------------------------------------------- */
function promedix_ui_icon( $name, $size = 22 ) {
	$p = array(
		'search'  => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
		'bag'     => '<path d="M5 8h14l-1.2 12H6.2z"/><path d="M9 8a3 3 0 0 1 6 0"/>',
		'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
		'heart'   => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
		'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrowup' => '<path d="M7 17 17 7M9 7h8v8"/>',
		'phone'   => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z"/>',
		'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'pin'     => '<path d="M12 21s-6-5.4-6-11a6 6 0 0 1 12 0c0 5.6-6 11-6 11z"/><circle cx="12" cy="10" r="2.2"/>',
		'truck'   => '<path d="M3 6h11v10H3zM14 9h4l3 3v4h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
		'card'    => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M7 15h4"/>',
		'swap'    => '<path d="M4 8h13l-3-3M20 16H7l3 3"/>',
		'ruler'   => '<path d="M3 15 15 3l6 6L9 21z"/><path d="m7 11 2 2M10 8l2 2M13 5l2 2"/>',
		'help'    => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.3-1 .9-1 1.7M12 17h.01"/>',
		'box'     => '<path d="m3 7 9-4 9 4v10l-9 4-9-4z"/><path d="m3 7 9 4 9-4M12 11v10"/>',
		'logout'  => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>',
		'users'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.5 3.5-5 6.5-5s5.5 1.5 6.5 5"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14c2.2 0 3.8 1.2 4.5 3.5"/>',
		'cross'   => '<path d="M12 4v16M4 12h16"/>',
		'scissors'=> '<circle cx="6" cy="7" r="2.5"/><circle cx="6" cy="17" r="2.5"/><path d="M8 8.5 20 18M8 15.5 20 6"/>',
		'sparkle' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
		'trash'   => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
		'check'   => '<path d="m5 12 4.5 4.5L19 7"/>',
		'info'    => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5h.01"/>',
	);
	if ( ! isset( $p[ $name ] ) ) {
		return '';
	}
	return '<svg class="pmx-ico" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}

/* ---------------------------------------------------------------------------
 * Page hero band: [promedix_page_hero lead="" image=""]
 * Uses the current page/term title as the single H1.
 * ------------------------------------------------------------------------- */
function promedix_current_title() {
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return __( 'Каталог взуття', 'promedix' );
	}
	if ( is_tax() || is_category() || is_tag() ) {
		return single_term_title( '', false );
	}
	if ( is_search() ) {
		/* translators: %s: search query */
		return sprintf( __( 'Пошук: «%s»', 'promedix' ), get_search_query() );
	}
	if ( is_404() ) {
		return __( 'Сторінку не знайдено', 'promedix' );
	}
	return get_the_title();
}

add_shortcode( 'promedix_page_hero', function ( $atts ) {
	$atts  = shortcode_atts( array( 'title' => '', 'lead' => '', 'image' => '', 'crumbs' => '1', 'size' => '' ), $atts, 'promedix_page_hero' );
	$title = $atts['title'] ? $atts['title'] : promedix_current_title();
	$words = preg_split( '/\s+/u', trim( $title ) );
	if ( count( $words ) > 1 ) {
		$last  = array_pop( $words );
		$title = esc_html( implode( ' ', $words ) ) . ' <span class="pmx-hl">' . esc_html( $last ) . '</span>';
	} else {
		$title = '<span class="pmx-hl">' . esc_html( $title ) . '</span>';
	}
	$crumbs = '';
	if ( '1' === $atts['crumbs'] && ! is_front_page() ) {
		$crumbs = '<nav class="pmx-crumbs" aria-label="' . esc_attr__( 'Хлібні крихти', 'promedix' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Головна', 'promedix' ) . '</a><span aria-hidden="true">/</span><span aria-current="page">' . esc_html( wp_strip_all_tags( promedix_current_title() ) ) . '</span></nav>';
	}
	$img = '';
	if ( $atts['image'] ) {
		$img = '<figure class="pmx-page-hero__img"><img src="' . esc_url( promedix_upload( $atts['image'] ) ) . '" alt="" width="520" height="520"></figure>';
	}
	return '<header class="pmx-page-hero' . ( $img ? ' has-image' : '' ) . ( $atts['size'] ? ' pmx-page-hero--' . sanitize_html_class( $atts['size'] ) : '' ) . '"><div class="pmx-container pmx-page-hero__in"><div class="pmx-page-hero__text">' . $crumbs
		. '<h1 class="pmx-page-hero__title">' . $title . '</h1>'
		. ( $atts['lead'] ? '<p class="pmx-page-hero__lead">' . esc_html( $atts['lead'] ) . '</p>' : '' )
		. '</div>' . $img . '</div></header>';
} );

/* ---------------------------------------------------------------------------
 * Dark "for teams" band and footer columns
 * ------------------------------------------------------------------------- */
add_shortcode( 'promedix_team_band', function () {
	$url = promedix_link( 'teams' );
	if ( ! $url ) {
		$url = promedix_link( 'contacts' );
	}
	if ( ! $url ) {
		$url = 'tel:' . promedix_contact( 'phone_wholesale_href' );
	}
	return '<section class="pmx-teamband" aria-labelledby="pmx-teamband-t"><div class="pmx-container pmx-teamband__in">'
		. promedix_sc_logo_v3( array( 'variant' => 'plate', 'width' => 132, 'link' => '0' ) )
		. '<div class="pmx-teamband__text"><h2 id="pmx-teamband-t" class="pmx-teamband__title">' . esc_html__( 'Комфорт для всієї команди', 'promedix' ) . '</h2>'
		. '<p class="pmx-teamband__lead">' . esc_html__( 'Для клінік, салонів і професійних команд. Оптові ціни за запитом.', 'promedix' ) . '</p></div>'
		. '<a class="pmx-btn pmx-btn--light" href="' . esc_url( $url ) . '">' . esc_html__( 'Дізнатися більше', 'promedix' ) . promedix_ui_icon( 'arrow', 20 ) . '</a>'
		. '</div></section>';
} );

add_shortcode( 'promedix_footer_cols', function () {
	$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$col   = function ( $title, $links ) {
		$links = array_filter( $links, function ( $l ) { return ! empty( $l[1] ); } );
		if ( ! $links ) {
			return '';
		}
		$h = '<div class="pmx-fcol"><h2 class="pmx-fcol__title">' . esc_html( $title ) . '</h2><ul>';
		foreach ( $links as $l ) {
			$h .= '<li><a href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a></li>';
		}
		return $h . '</ul></div>';
	};
	$cat = array(
		array( __( 'Жіночі сабо', 'promedix' ), promedix_term_url( 'zhinochi-sabo' ) ),
		array( __( 'Чоловічі сабо', 'promedix' ), promedix_term_url( 'cholovichi-sabo' ) ),
		array( __( 'Крокси анатомічні', 'promedix' ), promedix_term_url( 'kroksy-anatomichni' ) ),
		array( __( 'Медичне взуття', 'promedix' ), promedix_term_url( 'medychne-vzuttia' ) ),
		array( __( 'Весь каталог', 'promedix' ), $shop ),
	);
	$brand = array(
		array( __( 'Про ProMedix', 'promedix' ), promedix_link( 'about' ) ),
		array( __( 'Для команд', 'promedix' ), promedix_link( 'teams' ) ),
		array( __( 'Блог', 'promedix' ), promedix_link( 'blog' ) ),
		array( __( 'Контакти', 'promedix' ), promedix_link( 'contacts' ) ),
	);
	$help = array(
		array( __( 'Оплата і доставка', 'promedix' ), promedix_link( 'delivery' ) ),
		array( __( 'Обмін та повернення', 'promedix' ), promedix_link( 'exchange' ) ),
		array( __( 'Особистий кабінет', 'promedix' ), function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '' ),
		array( __( 'Політика конфіденційності', 'promedix' ), promedix_link( 'privacy' ) ),
	);
	$c = promedix_contacts();
	$contacts = '<div class="pmx-fcol pmx-fcol--contacts"><h2 class="pmx-fcol__title">' . esc_html__( 'Контакти', 'promedix' ) . '</h2><ul>'
		. '<li>' . promedix_ui_icon( 'phone', 20 ) . '<span><small>' . esc_html__( 'Для покупців', 'promedix' ) . '</small><a href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . esc_html( $c['phone_retail'] ) . '</a></span></li>'
		. '<li>' . promedix_ui_icon( 'users', 20 ) . '<span><small>' . esc_html__( 'Для команд і партнерів', 'promedix' ) . '</small><a href="tel:' . esc_attr( $c['phone_wholesale_href'] ) . '">' . esc_html( $c['phone_wholesale'] ) . '</a></span></li>'
		. '<li>' . promedix_ui_icon( 'mail', 20 ) . '<a href="mailto:' . esc_attr( $c['email_wholesale'] ) . '">' . esc_html( $c['email_wholesale'] ) . '</a></li>'
		. '<li>' . promedix_ui_icon( 'pin', 20 ) . '<span>' . esc_html( $c['address'] ) . '</span></li>'
		. '</ul></div>';
	return $col( __( 'Каталог', 'promedix' ), $cat ) . $col( __( 'Про бренд', 'promedix' ), $brand ) . $col( __( 'Допомога', 'promedix' ), $help ) . $contacts;
} );

/* ---------------------------------------------------------------------------
 * Request forms (contacts, teams). Real delivery via wp_mail; success shown only if wp_mail returned true.
 * If a CF7 form with the given title exists, it is used instead.
 * ------------------------------------------------------------------------- */
function promedix_form_fields( $type ) {
	if ( 'team' === $type ) {
		return array(
			'org'     => array( __( 'Організація', 'promedix' ), 'text', true, 'organization' ),
			'name'    => array( __( 'Контактна особа', 'promedix' ), 'text', true, 'name' ),
			'phone'   => array( __( 'Телефон', 'promedix' ), 'tel', true, 'tel' ),
			'qty'     => array( __( 'Орієнтовна кількість пар', 'promedix' ), 'number', false, 'off' ),
			'message' => array( __( 'Коментар', 'promedix' ), 'textarea', false, 'off' ),
		);
	}
	return array(
		'name'    => array( __( 'Ім’я', 'promedix' ), 'text', true, 'name' ),
		'contact' => array( __( 'Телефон або email', 'promedix' ), 'text', true, 'email' ),
		'topic'   => array( __( 'Тема', 'promedix' ), 'select', true, 'off', array( __( 'Вибір моделі або розміру', 'promedix' ), __( 'Замовлення', 'promedix' ), __( 'Обмін або повернення', 'promedix' ), __( 'Співпраця для команди', 'promedix' ), __( 'Інше', 'promedix' ) ) ),
		'message' => array( __( 'Повідомлення', 'promedix' ), 'textarea', true, 'off' ),
	);
}

add_shortcode( 'promedix_form', function ( $atts ) {
	$atts = shortcode_atts( array( 'type' => 'contact', 'cf7' => '' ), $atts );
	if ( $atts['cf7'] && function_exists( 'wpcf7_get_contact_form_by_title' ) ) {
		$form = wpcf7_get_contact_form_by_title( $atts['cf7'] );
		if ( $form ) {
			return '<div class="pmx-form">' . do_shortcode( '[contact-form-7 id="' . (int) $form->id() . '"]' ) . '</div>';
		}
	}
	$type   = 'team' === $atts['type'] ? 'team' : 'contact';
	$status = isset( $_GET['pmx_form'] ) ? sanitize_key( wp_unslash( $_GET['pmx_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$errors = get_transient( 'pmx_form_err_' . ( isset( $_GET['pmx_key'] ) ? sanitize_key( wp_unslash( $_GET['pmx_key'] ) ) : 'x' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$errors = is_array( $errors ) ? $errors : array( 'fields' => array(), 'values' => array() );
	$out    = '<form class="pmx-form pmx-form--' . $type . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate data-pmx-form>';
	if ( 'sent' === $status ) {
		$out .= '<div class="pmx-notice pmx-notice--success" role="status">' . promedix_ui_icon( 'check', 20 ) . esc_html__( 'Дякуємо! Запит надіслано, ми зв’яжемося з вами найближчим часом.', 'promedix' ) . '</div>';
	} elseif ( 'fail' === $status ) {
		$c    = promedix_contacts();
		$out .= '<div class="pmx-notice pmx-notice--error" role="alert">' . esc_html__( 'Не вдалося надіслати запит. Будь ласка, зателефонуйте нам:', 'promedix' ) . ' <a href="tel:' . esc_attr( $c['phone_wholesale_href'] ) . '">' . esc_html( $c['phone_wholesale'] ) . '</a></div>';
	} elseif ( 'invalid' === $status ) {
		$out .= '<div class="pmx-notice pmx-notice--error" role="alert">' . esc_html__( 'Перевірте, будь ласка, позначені поля.', 'promedix' ) . '</div>';
	}
	$out .= '<input type="hidden" name="action" value="promedix_form"><input type="hidden" name="pmx_type" value="' . esc_attr( $type ) . '">'
		. '<input type="hidden" name="pmx_back" value="' . esc_url( get_permalink() ) . '">'
		. wp_nonce_field( 'promedix_form', 'pmx_nonce', true, false )
		. '<p class="pmx-hp" aria-hidden="true"><label>Website<input type="text" name="pmx_website" tabindex="-1" autocomplete="off"></label></p><div class="pmx-form__grid">';
	foreach ( promedix_form_fields( $type ) as $key => $f ) {
		$id    = 'pmx-' . $type . '-' . $key;
		$err   = isset( $errors['fields'][ $key ] ) ? $errors['fields'][ $key ] : '';
		$val   = isset( $errors['values'][ $key ] ) ? $errors['values'][ $key ] : '';
		$req   = $f[2] ? ' required aria-required="true"' : '';
		$descr = $err ? ' aria-invalid="true" aria-describedby="' . $id . '-err"' : '';
		$wide  = in_array( $f[1], array( 'textarea' ), true ) ? ' pmx-field--wide' : '';
		$out  .= '<div class="pmx-field' . $wide . ( $err ? ' has-error' : '' ) . '"><label for="' . $id . '">' . esc_html( $f[0] ) . ( $f[2] ? ' <span class="pmx-req" aria-hidden="true">*</span>' : '' ) . '</label>';
		if ( 'textarea' === $f[1] ) {
			$out .= '<textarea id="' . $id . '" name="' . $key . '" rows="4"' . $req . $descr . '>' . esc_textarea( $val ) . '</textarea>';
		} elseif ( 'select' === $f[1] ) {
			$out .= '<select id="' . $id . '" name="' . $key . '"' . $req . $descr . '><option value="">' . esc_html__( 'Оберіть тему', 'promedix' ) . '</option>';
			foreach ( $f[4] as $opt ) {
				$out .= '<option' . selected( $val, $opt, false ) . '>' . esc_html( $opt ) . '</option>';
			}
			$out .= '</select>';
		} else {
			$out .= '<input id="' . $id . '" name="' . $key . '" type="' . $f[1] . '" autocomplete="' . esc_attr( $f[3] ) . '" value="' . esc_attr( $val ) . '"' . ( 'number' === $f[1] ? ' min="1" inputmode="numeric"' : '' ) . $req . $descr . '>';
		}
		$out .= '<p class="pmx-field__err" id="' . $id . '-err"' . ( $err ? '' : ' hidden' ) . '>' . esc_html( $err ) . '</p></div>';
	}
	$privacy = promedix_link( 'privacy' );
	$out    .= '</div><div class="pmx-field pmx-field--check' . ( isset( $errors['fields']['consent'] ) ? ' has-error' : '' ) . '"><label class="pmx-check-label"><input type="checkbox" name="consent" value="1" required aria-required="true"> <span>' . esc_html__( 'Я погоджуюся на обробку персональних даних', 'promedix' )
		. ( $privacy ? ' (<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'політика конфіденційності', 'promedix' ) . '</a>)' : '' ) . '</span></label>'
		. '<p class="pmx-field__err" id="pmx-' . $type . '-consent-err"' . ( isset( $errors['fields']['consent'] ) ? '' : ' hidden' ) . '>' . esc_html__( 'Потрібна ваша згода.', 'promedix' ) . '</p></div>'
		. '<button class="pmx-btn pmx-form__submit" type="submit"><span>' . ( 'team' === $type ? esc_html__( 'Надіслати запит', 'promedix' ) : esc_html__( 'Надіслати', 'promedix' ) ) . '</span>' . promedix_ui_icon( 'arrow', 20 ) . '</button></form>';
	return $out;
} );

function promedix_form_handle() {
	$back = isset( $_POST['pmx_back'] ) ? esc_url_raw( wp_unslash( $_POST['pmx_back'] ) ) : home_url( '/' );
	$back = wp_validate_redirect( $back, home_url( '/' ) );
	if ( ! isset( $_POST['pmx_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pmx_nonce'] ) ), 'promedix_form' ) ) {
		wp_safe_redirect( add_query_arg( 'pmx_form', 'fail', $back ) . '#pmx-form' );
		exit;
	}
	if ( ! empty( $_POST['pmx_website'] ) ) { // honeypot: pretend nothing happened, show no success
		wp_safe_redirect( $back );
		exit;
	}
	$type   = ( isset( $_POST['pmx_type'] ) && 'team' === $_POST['pmx_type'] ) ? 'team' : 'contact';
	$fields = promedix_form_fields( $type );
	$values = array();
	$errors = array();
	foreach ( $fields as $key => $f ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$val = 'textarea' === $f[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		$values[ $key ] = $val;
		if ( $f[2] && '' === trim( $val ) ) {
			$errors[ $key ] = __( 'Заповніть це поле.', 'promedix' );
		} elseif ( 'tel' === $f[1] && '' !== $val && ! preg_match( '/^[\d\s()+-]{10,20}$/', $val ) ) {
			$errors[ $key ] = __( 'Вкажіть телефон у форматі +380 XX XXX XX XX.', 'promedix' );
		} elseif ( 'contact' === $key && '' !== $val && ! is_email( $val ) && ! preg_match( '/^[\d\s()+-]{10,20}$/', $val ) ) {
			$errors[ $key ] = __( 'Вкажіть телефон або коректну адресу email.', 'promedix' );
		}
	}
	if ( empty( $_POST['consent'] ) ) {
		$errors['consent'] = __( 'Потрібна ваша згода.', 'promedix' );
	}
	if ( $errors ) {
		$k = wp_generate_password( 12, false );
		set_transient( 'pmx_form_err_' . strtolower( $k ), array( 'fields' => $errors, 'values' => $values ), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( array( 'pmx_form' => 'invalid', 'pmx_key' => strtolower( $k ) ), $back ) . '#pmx-form' );
		exit;
	}
	$lines = array();
	foreach ( $fields as $key => $f ) {
		$lines[] = $f[0] . ': ' . $values[ $key ];
	}
	$subject = 'team' === $type ? __( 'Запит для команди з сайту ProMedix', 'promedix' ) : __( 'Звернення з сайту ProMedix', 'promedix' );
	$to      = apply_filters( 'promedix_form_recipient', promedix_contact( 'email_wholesale' ), $type );
	$sent    = wp_mail( $to, $subject, implode( "\n", $lines ) . "\n\n" . home_url( '/' ) );
	wp_safe_redirect( add_query_arg( 'pmx_form', $sent ? 'sent' : 'fail', $back ) . '#pmx-form' );
	exit;
}
add_action( 'admin_post_nopriv_promedix_form', 'promedix_form_handle' );
add_action( 'admin_post_promedix_form', 'promedix_form_handle' );

/* ---------------------------------------------------------------------------
 * Help page: real shipping and payment methods from WooCommerce + existing page content
 * ------------------------------------------------------------------------- */
function promedix_shop_methods() {
	$out = array( 'shipping' => array(), 'payment' => array() );
	if ( ! function_exists( 'WC' ) ) {
		return $out;
	}
	if ( class_exists( 'WC_Shipping_Zones' ) ) {
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			foreach ( $zone['shipping_methods'] as $m ) {
				if ( 'yes' === $m->enabled ) {
					$out['shipping'][ $m->id ] = array( $m->get_title(), $m->id );
				}
			}
		}
	}
	foreach ( WC()->payment_gateways() ? WC()->payment_gateways()->get_available_payment_gateways() : array() as $g ) {
		$out['payment'][ $g->id ] = array( $g->get_title(), $g->id );
	}
	return $out;
}

function promedix_page_content( $key ) {
	$slugs = promedix_page_slugs();
	$page  = isset( $slugs[ $key ] ) ? get_page_by_path( $slugs[ $key ] ) : null;
	if ( ! $page ) {
		return '';
	}
	$page = get_post( promedix_wpml_id( $page->ID, 'page' ) );
	return promedix_demote_h1( apply_filters( 'the_content', $page->post_content ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
}

/** The page hero already holds the only H1: demote headings inside old page text. */
function promedix_demote_h1( $html ) {
	return preg_replace( array( '#<h1(\s|>)#i', '#</h1>#i' ), array( '<h2$1', '</h2>' ), (string) $html );
}

add_shortcode( 'promedix_help', function () {
	$m       = promedix_shop_methods();
	$c       = promedix_contacts();
	$current = get_post_field( 'post_name', get_the_ID() );
	$slugs   = promedix_page_slugs();
	$sections = array(
		'delivery' => array( __( 'Доставка', 'promedix' ), 'truck' ),
		'payment'  => array( __( 'Оплата', 'promedix' ), 'card' ),
		'exchange' => array( __( 'Обмін і повернення', 'promedix' ), 'swap' ),
		'size'     => array( __( 'Як обрати розмір', 'promedix' ), 'ruler' ),
		'faq'      => array( __( 'Поширені запитання', 'promedix' ), 'help' ),
	);
	$nav = '<nav class="pmx-sidenav" aria-label="' . esc_attr__( 'Розділи допомоги', 'promedix' ) . '"><ul>';
	foreach ( $sections as $id => $s ) {
		$nav .= '<li><a href="#pmx-help-' . $id . '">' . promedix_ui_icon( $s[1] ) . '<span>' . esc_html( $s[0] ) . '</span></a></li>';
	}
	$nav .= '</ul></nav>';

	$tiles = function ( $items, $icons ) {
		if ( ! $items ) {
			return '<p class="pmx-muted">' . esc_html__( 'Способи уточнюються під час оформлення замовлення.', 'promedix' ) . '</p>';
		}
		$h = '<ul class="pmx-tiles-list">';
		foreach ( $items as $id => $it ) {
			$icon = isset( $icons[ $id ] ) ? $icons[ $id ] : 'box';
			$h   .= '<li class="pmx-tile-card">' . promedix_ui_icon( $icon, 24 ) . '<span>' . esc_html( $it[0] ) . '</span></li>';
		}
		return $h . '</ul>';
	};
	$body  = '<section id="pmx-help-delivery" class="pmx-help__sec"><h2>' . esc_html__( 'Доставка', 'promedix' ) . '</h2>' . $tiles( $m['shipping'], array( 'nova_poshta_shipping' => 'truck', 'free_shipping' => 'box', 'local_pickup' => 'pin' ) )
		. '<p class="pmx-muted">' . esc_html__( 'Спосіб і вартість доставки уточнюються під час оформлення.', 'promedix' ) . '</p></section>';
	$body .= '<section id="pmx-help-payment" class="pmx-help__sec"><h2>' . esc_html__( 'Оплата', 'promedix' ) . '</h2>' . $tiles( $m['payment'], array( 'cheque' => 'card', 'cod' => 'box' ) )
		. '<p class="pmx-muted">' . esc_html__( 'Доступні способи оплати показані в замовленні.', 'promedix' ) . '</p></section>';
	// The shop's own page texts (Оплата і доставка / Обмін та повернення) are shown as they are.
	$page_text = '';
	if ( $current === $slugs['delivery'] || $current === $slugs['exchange'] ) {
		$page_text = promedix_demote_h1( apply_filters( 'the_content', get_post_field( 'post_content', get_the_ID() ) ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}
	if ( $page_text && $current === $slugs['delivery'] ) {
		$body .= '<section class="pmx-help__sec pmx-prose">' . $page_text . '</section>';
	}
	$exchange = $current === $slugs['exchange'] ? $page_text : promedix_page_content( 'exchange' );
	$body    .= '<section id="pmx-help-exchange" class="pmx-help__sec"><h2>' . esc_html__( 'Обмін і повернення', 'promedix' ) . '</h2>'
		. ( $exchange ? '<details class="pmx-acc__item pmx-acc--single"' . ( $current === $slugs['exchange'] ? ' open' : '' ) . '><summary><span>' . esc_html__( 'Умови обміну та повернення', 'promedix' ) . '</span></summary><div class="pmx-acc__body pmx-prose">' . $exchange . '</div></details>' : '' )
		. '</section>';
	$body .= '<section id="pmx-help-size" class="pmx-help__sec"><div class="pmx-sizehelp"><div><h2>' . esc_html__( 'Допомога з розміром', 'promedix' ) . '</h2><p>' . esc_html__( 'Не впевнені щодо розміру? Зателефонуйте нам, і ми підкажемо, яку пару обрати.', 'promedix' ) . '</p>'
		. '<a class="pmx-btn pmx-btn--secondary" href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . promedix_ui_icon( 'phone', 20 ) . esc_html( $c['phone_retail'] ) . '</a></div></div></section>';
	$faq = array(
		array( __( 'Як оформити замовлення?', 'promedix' ), __( 'Оберіть модель і розмір, додайте пару в кошик і заповніть форму оформлення. Також можна замовити телефоном або електронною поштою.', 'promedix' ) ),
		array( __( 'Як обрати розмір?', 'promedix' ), sprintf( __( 'Зателефонуйте нам за номером %s, і ми допоможемо з вибором.', 'promedix' ), $c['phone_retail'] ) ),
		array( __( 'Як оформити обмін?', 'promedix' ), __( 'Умови описані в розділі «Обмін і повернення» вище. Збережіть, будь ласка, чек.', 'promedix' ) ),
		array( __( 'Чи можна замовити взуття для всієї команди?', 'promedix' ), sprintf( __( 'Так. Залиште запит на сторінці для команд або зателефонуйте за номером %s.', 'promedix' ), $c['phone_wholesale'] ) ),
	);
	$body .= '<section id="pmx-help-faq" class="pmx-help__sec"><h2>' . esc_html__( 'Поширені запитання', 'promedix' ) . '</h2><div class="pmx-acc">';
	foreach ( $faq as $q ) {
		$body .= '<details class="pmx-acc__item"><summary><span>' . esc_html( $q[0] ) . '</span></summary><div class="pmx-acc__body"><p>' . esc_html( $q[1] ) . '</p></div></details>';
	}
	$body .= '</div></section>';
	return '<div class="pmx-help pmx-container">' . $nav . '<div class="pmx-help__body">' . $body . '</div></div>';
} );

/* ---------------------------------------------------------------------------
 * Contacts page
 * ------------------------------------------------------------------------- */
add_shortcode( 'promedix_contacts_page', function () {
	$c = promedix_contacts();
	return '<div class="pmx-contacts pmx-container"><div class="pmx-contacts__cards">'
		. '<section class="pmx-card-soft">' . promedix_ui_icon( 'users', 28 ) . '<h2>' . esc_html__( 'Для покупців', 'promedix' ) . '</h2><p><a class="pmx-bigline" href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . promedix_ui_icon( 'phone', 20 ) . esc_html( $c['phone_retail'] ) . '</a></p></section>'
		. '<section class="pmx-card-soft">' . promedix_ui_icon( 'box', 28 ) . '<h2>' . esc_html__( 'Для команд і партнерів', 'promedix' ) . '</h2><p><a class="pmx-bigline" href="tel:' . esc_attr( $c['phone_wholesale_href'] ) . '">' . promedix_ui_icon( 'phone', 20 ) . esc_html( $c['phone_wholesale'] ) . '</a></p><p><a class="pmx-bigline" href="mailto:' . esc_attr( $c['email_wholesale'] ) . '">' . promedix_ui_icon( 'mail', 20 ) . esc_html( $c['email_wholesale'] ) . '</a></p></section>'
		. '<section class="pmx-card-soft">' . promedix_ui_icon( 'pin', 28 ) . '<h2>' . esc_html__( 'Адреса', 'promedix' ) . '</h2><p>' . esc_html( $c['address'] ) . '</p></section>'
		. '</div><section class="pmx-contacts__form" id="pmx-form" aria-labelledby="pmx-cf-t"><h2 id="pmx-cf-t">' . esc_html__( 'Напишіть нам', 'promedix' ) . '</h2>' . do_shortcode( '[promedix_form type="contact"]' ) . '</section></div>';
} );

/* ---------------------------------------------------------------------------
 * Favourites (stored in the browser, rendered through the WooCommerce Store API)
 * ------------------------------------------------------------------------- */
add_shortcode( 'promedix_favorites', function () {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	return '<div class="pmx-favs" data-pmx-favs data-api="' . esc_url( rest_url( 'wc/store/v1/products' ) ) . '">'
		. '<ul class="products columns-3 pmx-favs__list" hidden></ul>'
		. '<div class="pmx-empty" data-pmx-favs-empty>' . promedix_ui_icon( 'heart', 48 ) . '<h2>' . esc_html__( 'Тут з’являться обрані моделі', 'promedix' ) . '</h2><p>' . esc_html__( 'Натисніть на сердечко на картці товару, щоб зберегти його. Список зберігається в цьому браузері.', 'promedix' ) . '</p><a class="pmx-btn" href="' . esc_url( $shop ) . '">' . esc_html__( 'Перейти до каталогу', 'promedix' ) . promedix_ui_icon( 'arrow', 20 ) . '</a></div>'
		. '<p class="pmx-favs__loading" hidden role="status">' . esc_html__( 'Завантажуємо обране…', 'promedix' ) . '</p>'
		. '<p class="pmx-favs__error pmx-notice pmx-notice--error" hidden role="alert">' . esc_html__( 'Не вдалося завантажити обране. Оновіть сторінку.', 'promedix' ) . '</p></div>';
} );

/* My account: "Обране" tab (endpoint /my-account/obrane/), icons for menu items. */
add_action( 'init', function () {
	add_rewrite_endpoint( 'obrane', EP_ROOT | EP_PAGES );
	if ( get_option( 'promedix_rewrite_v3' ) !== '1' ) {
		flush_rewrite_rules( false );
		update_option( 'promedix_rewrite_v3', '1' );
	}
} );
add_filter( 'woocommerce_get_query_vars', function ( $vars ) {
	$vars['obrane'] = 'obrane';
	return $vars;
} );
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	$new = array();
	foreach ( $items as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'orders' === $k ) {
			$new['obrane'] = __( 'Обране', 'promedix' );
		}
	}
	if ( ! isset( $new['obrane'] ) ) {
		$new = array( 'obrane' => __( 'Обране', 'promedix' ) ) + $new;
	}
	unset( $new['downloads'] );
	return $new;
} );
add_action( 'woocommerce_account_obrane_endpoint', function () {
	echo do_shortcode( '[promedix_favorites]' ); // phpcs:ignore WordPress.Security.EscapeOutput
} );
add_filter( 'woocommerce_endpoint_obrane_title', function () {
	return __( 'Обране', 'promedix' );
} );

/* Empty states in the account */
add_action( 'woocommerce_before_account_orders', function ( $has_orders ) {
	if ( $has_orders ) {
		return;
	}
	$shop = wc_get_page_permalink( 'shop' );
	echo '<div class="pmx-empty">' . promedix_ui_icon( 'bag', 48 ) . '<h2>' . esc_html__( 'Тут з’являться ваші замовлення', 'promedix' ) . '</h2><a class="pmx-btn" href="' . esc_url( $shop ) . '">' . esc_html__( 'Перейти до каталогу', 'promedix' ) . promedix_ui_icon( 'arrow', 20 ) . '</a></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
} );

/* ---------------------------------------------------------------------------
 * Profession page: [promedix_profession key="medics|hair|beauty"] (key guessed from the page slug)
 * ------------------------------------------------------------------------- */
function promedix_profession_key() {
	$slug  = get_post_field( 'post_name', get_the_ID() );
	$slugs = promedix_page_slugs();
	foreach ( array( 'medics', 'hair', 'beauty' ) as $k ) {
		if ( $slugs[ $k ] === $slug ) {
			return $k;
		}
	}
	return 'medics';
}

add_shortcode( 'promedix_profession', function ( $atts ) {
	$atts = shortcode_atts( array( 'key' => '' ), $atts );
	$key  = $atts['key'] ? $atts['key'] : promedix_profession_key();
	$all  = promedix_professions();
	$p    = isset( $all[ $key ] ) ? $all[ $key ] : reset( $all );
	$tabs = '<nav class="pmx-segment" aria-label="' . esc_attr__( 'Професії', 'promedix' ) . '">';
	foreach ( $all as $k => $it ) {
		$tabs .= '<a href="' . esc_url( $it['url'] ) . '"' . ( $k === $key ? ' aria-current="page" class="is-on"' : '' ) . '>' . esc_html( $it['label'] ) . '</a>';
	}
	$tabs .= '</nav>';
	$products = $p['cat'] ? '[products limit="4" columns="4" category="' . esc_attr( $p['cat'] ) . '" orderby="popularity" visibility="visible"]' : '[products limit="4" columns="4" orderby="popularity" visibility="visible"]';
	$c        = promedix_contacts();
	return '<div class="pmx-container pmx-prof-page">' . $tabs
		. '<section class="pmx-block"><h2>' . esc_html__( 'Оберіть свою пару', 'promedix' ) . '</h2>' . do_shortcode( $products ) . '</section>'
		. '<section class="pmx-block pmx-sizehelp"><div><h2>' . esc_html__( 'Потрібна допомога з розміром?', 'promedix' ) . '</h2><p>' . esc_html__( 'Підкажемо, як обрати розмір, щоб було комфортно весь робочий день.', 'promedix' ) . '</p>'
		. '<a class="pmx-btn pmx-btn--secondary" href="tel:' . esc_attr( $c['phone_retail_href'] ) . '">' . promedix_ui_icon( 'phone', 20 ) . esc_html( $c['phone_retail'] ) . '</a></div></section></div>';
} );

/* ---------------------------------------------------------------------------
 * Teams page body: [promedix_teams]
 * ------------------------------------------------------------------------- */
add_shortcode( 'promedix_teams', function () {
	$cards = array(
		array( 'cross', __( 'Клініки', 'promedix' ), __( 'Лікарні, медичні центри, лабораторії й аптеки.', 'promedix' ) ),
		array( 'scissors', __( 'Салони краси', 'promedix' ), __( 'Перукарні, барбершопи, манікюрні й косметологічні студії.', 'promedix' ) ),
		array( 'users', __( 'Інші команди', 'promedix' ), __( 'Усі, хто працює на ногах: кухні, готелі, сервіс.', 'promedix' ) ),
	);
	$steps = array(
		array( __( 'Заявка', 'promedix' ), __( 'Залиште запит для вашої команди.', 'promedix' ) ),
		array( __( 'Підбір моделей', 'promedix' ), __( 'Запропонуємо відповідні моделі та розміри.', 'promedix' ) ),
		array( __( 'Узгодження замовлення', 'promedix' ), __( 'Уточнимо деталі й ціни та сформуємо замовлення.', 'promedix' ) ),
	);
	$h = '<div class="pmx-container pmx-teams"><ul class="pmx-teams__cards">';
	foreach ( $cards as $cd ) {
		$h .= '<li class="pmx-card-soft">' . promedix_ui_icon( $cd[0], 28 ) . '<h2 class="pmx-h3">' . esc_html( $cd[1] ) . '</h2><p>' . esc_html( $cd[2] ) . '</p></li>';
	}
	$h .= '</ul><section class="pmx-block"><h2>' . esc_html__( 'Як ми працюємо', 'promedix' ) . '</h2><ol class="pmx-steps">';
	foreach ( $steps as $i => $s ) {
		$h .= '<li><b class="pmx-steps__n" aria-hidden="true">' . ( $i + 1 ) . '</b><div><h3>' . esc_html( $s[0] ) . '</h3><p>' . esc_html( $s[1] ) . '</p></div></li>';
	}
	$h .= '</ol></section><section class="pmx-block pmx-card-form" id="pmx-form" aria-labelledby="pmx-tf-t"><h2 id="pmx-tf-t">' . esc_html__( 'Залиште запит для вашої команди', 'promedix' ) . '</h2>'
		. do_shortcode( '[promedix_form type="team" cf7="Гуртова заявка ProMedix"]' ) . '</section></div>';
	return $h;
} );

/* ---------------------------------------------------------------------------
 * About page: [promedix_about] (only facts present in the shop data)
 * ------------------------------------------------------------------------- */
add_shortcode( 'promedix_about', function () {
	$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$cards = array(
		array( '2026/03/zhinochi-sabo-promedix-714-piony-1024x1024.jpg', __( 'Натуральна шкіра', 'promedix' ), __( 'Верх і підкладка моделей ProMedix виготовлені з натуральної шкіри.', 'promedix' ) ),
		array( '2025/01/1-5-1024x1024.png', __( 'Анатомічна підкладка', 'promedix' ), __( 'Анатомічна підкладка та протиударна підошва для довгих змін на ногах.', 'promedix' ) ),
		array( '2026/03/zhinochi-sabo-promedix-707-likar-1024x1024.jpg', __( 'Особистий стиль', 'promedix' ), __( 'Класичні кольори та принти, щоб пара пасувала саме вам.', 'promedix' ) ),
	);
	$h = '<div class="pmx-container pmx-about"><div class="pmx-about__intro">' . promedix_sc_logo_v3( array( 'width' => 220, 'link' => '0' ) )
		. '<h2 class="pmx-about__title">' . esc_html__( 'Для людей, які дбають', 'promedix' ) . ' <span class="pmx-hl">' . esc_html__( 'про інших.', 'promedix' ) . '</span></h2>'
		. '<p class="pmx-lead">' . esc_html__( 'ProMedix — професійне взуття для медиків, перукарів, б’юті-майстрів і всіх, хто проводить робочий день на ногах. Моделі виготовлені в Італії.', 'promedix' ) . '</p></div><ul class="pmx-about__cards">';
	foreach ( $cards as $cd ) {
		$h .= '<li class="pmx-card-soft pmx-card-soft--img"><img src="' . esc_url( promedix_upload( $cd[0] ) ) . '" alt="" width="600" height="450" loading="lazy"><h3>' . esc_html( $cd[1] ) . '</h3><p>' . esc_html( $cd[2] ) . '</p></li>';
	}
	return $h . '</ul><p class="pmx-center"><a class="pmx-btn" href="' . esc_url( $shop ) . '">' . esc_html__( 'Знайти свою пару', 'promedix' ) . promedix_ui_icon( 'arrow', 20 ) . '</a></p></div>';
} );

/**
 * Shortcode blocks run wpautop, which wraps inline output (logo, icons, cart, buttons) in <p> and breaks layouts.
 * Render them without autop: the theme's shortcodes return complete markup.
 */
add_filter(
	'pre_render_block',
	function ( $pre, $block ) {
		if ( null === $pre && isset( $block['blockName'] ) && 'core/shortcode' === $block['blockName'] ) {
			return do_shortcode( trim( (string) $block['innerHTML'] ) );
		}
		return $pre;
	},
	10,
	2
);
