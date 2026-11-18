<?php
/**
 * Title: Перший екран (слайдер)
 * Slug: promedix/home-hero
 * Categories: promedix
 * Keywords: hero, банер, слайдер
 *
 * Кожен слайд — окрема група «pmx-slide». Текст, кнопки й фото редагуються в редакторі;
 * назва вкладки внизу береться з першого рядка (мітки) слайда.
 * Найкраще пасують фото на блакитному студійному тлі (#D0E5F8), як у карток товарів.
 */
$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$women = promedix_term_url( 'zhinochi-sabo' );
$men   = promedix_term_url( 'cholovichi-sabo' );

$slide = function ( $args ) {
	$heading = $args['h1'] ? 1 : 2;
	ob_start();
	?>
<!-- wp:group {"className":"pmx-slide pmx-slide--<?php echo esc_attr( $args['mod'] ); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group pmx-slide pmx-slide--<?php echo esc_attr( $args['mod'] ); ?>"><!-- wp:columns {"verticalAlignment":"center","className":"pmx-slide__cols"} -->
<div class="wp-block-columns are-vertically-aligned-center pmx-slide__cols"><!-- wp:column {"verticalAlignment":"center","width":"46%","className":"pmx-slide__text"} -->
<div class="wp-block-column is-vertically-aligned-center pmx-slide__text" style="flex-basis:46%"><!-- wp:paragraph {"className":"pmx-slide__tag"} -->
<p class="pmx-slide__tag"><?php echo esc_html( $args['tag'] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":<?php echo (int) $heading; ?>,"className":"pmx-slide__title"} -->
<h<?php echo (int) $heading; ?> class="wp-block-heading pmx-slide__title"><?php echo wp_kses( $args['title'], array( 'span' => array( 'class' => true ), 'br' => array() ) ); ?></h<?php echo (int) $heading; ?>>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"pmx-slide__lead"} -->
<p class="pmx-slide__lead"><?php echo esc_html( $args['lead'] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"pmx-slide__btns"} -->
<div class="wp-block-buttons pmx-slide__btns"><!-- wp:button {"className":"pmx-arrow"} -->
<div class="wp-block-button pmx-arrow"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $args['url'] ); ?>"><?php echo esc_html( $args['btn'] ); ?></a></div>
<!-- /wp:button --><?php if ( ! empty( $args['btn2'] ) ) : ?>

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $args['url2'] ); ?>"><?php echo esc_html( $args['btn2'] ); ?></a></div>
<!-- /wp:button --><?php endif; ?></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"54%","className":"pmx-slide__media"} -->
<div class="wp-block-column is-vertically-aligned-center pmx-slide__media" style="flex-basis:54%"><!-- wp:paragraph {"className":"pmx-slide__num"} -->
<p class="pmx-slide__num" aria-hidden="true"><?php echo esc_html( $args['num'] ); ?></p>
<!-- /wp:paragraph --><?php foreach ( $args['imgs'] as $i => $img ) : ?>

<!-- wp:image {"sizeSlug":"large","className":"pmx-slide__img pmx-slide__img--<?php echo (int) $i + 1; ?>"} -->
<figure class="wp-block-image size-large pmx-slide__img pmx-slide__img--<?php echo (int) $i + 1; ?>"><img src="<?php echo esc_url( promedix_upload( $img ) ); ?>" alt="<?php echo esc_attr( 0 === $i ? $args['alt'] : '' ); ?>"<?php echo $args['h1'] && 0 === $i ? ' fetchpriority="high"' : ' loading="lazy"'; ?>/></figure>
<!-- /wp:image --><?php endforeach; ?><?php if ( ! empty( $args['card'] ) ) : ?>

<!-- wp:image {"className":"pmx-slide__card"} -->
<figure class="wp-block-image pmx-slide__card"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/scenes/' . $args['card'] ) ); ?>" alt=""<?php echo $args['h1'] ? ' fetchpriority="high"' : ' loading="lazy"'; ?>/></figure>
<!-- /wp:image --><?php endif; ?><?php if ( ! empty( $args['person'] ) ) : ?>

<!-- wp:image {"className":"pmx-slide__person"} -->
<figure class="wp-block-image pmx-slide__person"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/people/' . $args['person'] ) ); ?>" alt=""<?php echo $args['h1'] ? ' fetchpriority="high"' : ' loading="lazy"'; ?>/></figure>
<!-- /wp:image --><?php endif; ?>

<!-- wp:paragraph {"className":"pmx-slide__caption"} -->
<p class="pmx-slide__caption"><?php echo esc_html( $args['caption'] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
	<?php
	return ob_get_clean();
};
?>
<!-- wp:group {"className":"pmx-hero pmx-slider","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-hero pmx-slider"><!-- wp:group {"align":"wide","className":"pmx-slider__track","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide pmx-slider__track"><?php
echo $slide( array( // phpcs:ignore WordPress.Security.EscapeOutput
	'mod'     => 'care',
	'h1'      => true,
	'tag'     => __( 'Професійне взуття', 'promedix' ),
	'title'   => __( 'Ви дбаєте про інших. Подбайте <span class="pmx-hl">про себе.</span>', 'promedix' ),
	'lead'    => __( 'Анатомічні сабо з натуральної шкіри для довгих змін. Виготовлено в Італії.', 'promedix' ),
	'btn'     => __( 'Обрати свою пару', 'promedix' ),
	'url'     => $shop,
	'btn2'    => __( 'Допомогти з вибором', 'promedix' ),
	'url2'    => '#pmx-picker',
	'num'     => '707',
	'imgs'    => array( '2025/01/1-5-1024x1024.png' ),
	'card'    => 'card-nurse.webp',
	'alt'     => __( 'Білі медичні сабо ProMedix 707', 'promedix' ),
	'caption' => 'ProMedix 707',
) );
echo $slide( array( // phpcs:ignore WordPress.Security.EscapeOutput
	'mod'     => 'prints',
	'h1'      => false,
	'tag'     => __( 'Колекція принтів', 'promedix' ),
	'title'   => __( 'Квіти на зміні. <span class="pmx-hl">Настрій</span> на весь день.', 'promedix' ),
	'lead'    => __( 'Піони, тюльпани, бджілки й сонечка: обирайте пару під свій характер.', 'promedix' ),
	'btn'     => __( 'Відкрити принти', 'promedix' ),
	'url'     => $women,
	'num'     => '714',
	'imgs'    => array( '2026/03/zhinochi-sabo-promedix-714-piony-1024x1024.jpg', '2026/03/zhinochi-sabo-promedix-154-tiulpan-1024x1024.jpg' ),
	'alt'     => __( 'Сабо ProMedix 714 Піони', 'promedix' ),
	'caption' => 'ProMedix 714 Піони',
) );
echo $slide( array( // phpcs:ignore WordPress.Security.EscapeOutput
	'mod'     => 'team',
	'h1'      => false,
	'tag'     => __( 'Для клінік і салонів', 'promedix' ),
	'title'   => __( 'Одна команда. <span class="pmx-hl">Один стандарт</span> комфорту.', 'promedix' ),
	'lead'    => __( 'Підберемо моделі й розміри для кожного працівника. Оптові ціни надамо за запитом.', 'promedix' ),
	'btn'     => __( 'Отримати пропозицію', 'promedix' ),
	'url'     => promedix_link( 'teams' ) ? promedix_link( 'teams' ) : $shop,
	'btn2'    => __( 'Каталог', 'promedix' ),
	'url2'    => $shop,
	'num'     => 'B2B',
	'imgs'    => array(),
	'card'    => 'card-team.webp',
	'alt'     => __( 'Сабо ProMedix для всієї команди', 'promedix' ),
	'caption' => __( 'Для клінік і салонів', 'promedix' ),
) );
echo $slide( array( // phpcs:ignore WordPress.Security.EscapeOutput
	'mod'     => 'men',
	'h1'      => false,
	'tag'     => __( 'Чоловікам', 'promedix' ),
	'title'   => __( 'Чоловічі сабо. <span class="pmx-hl">Ваш ритм.</span>', 'promedix' ),
	'lead'    => __( 'Чоловічі сабо ProMedix 501 з натуральної шкіри з анатомічною підкладкою.', 'promedix' ),
	'btn'     => __( 'Чоловічі моделі', 'promedix' ),
	'url'     => $men,
	'num'     => '501',
	'imgs'    => array( '2026/03/cholovichi-sabo-promedix-501-syni-1-1-1024x1024.jpg' ),
	'alt'     => __( 'Чоловічі сабо ProMedix 501 Сині', 'promedix' ),
	'caption' => 'ProMedix 501',
) );
?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
