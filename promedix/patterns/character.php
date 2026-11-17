<?php
/**
 * Title: Банер «Трохи характеру»
 * Slug: promedix/character
 * Categories: promedix
 *
 * Чотири фото праворуч можна замінити фото людей у роботі: клік на зображення → «Замінити».
 */
$link  = promedix_term_url( 'zhinochi-sabo' );
$tiles = array(
	'2026/03/zhinochi-sabo-promedix-714-piony-1024x1024.jpg',
	'2026/03/zhinochi-sabo-promedix-154-tiulpan-1024x1024.jpg',
	'2026/03/zhinochi-sabo-promedix-707-likar-1024x1024.jpg',
	'2026/03/zhinochi-sabo-promedix-708-bdzhilka-1024x1024.jpg',
);
?>
<!-- wp:group {"className":"pmx-char","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-char"><!-- wp:group {"align":"wide","className":"pmx-char__box","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide pmx-char__box"><!-- wp:group {"className":"pmx-char__text","layout":{"type":"default"}} -->
<div class="wp-block-group pmx-char__text"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Трохи характеру. У кожному кроці.', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"pmx-arrow"} -->
<div class="wp-block-button pmx-arrow"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Відкрити принти', 'promedix' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"pmx-char__tiles","layout":{"type":"default"}} -->
<div class="wp-block-group pmx-char__tiles"><?php foreach ( $tiles as $t ) : ?><!-- wp:image {"className":"pmx-char__tile"} -->
<figure class="wp-block-image pmx-char__tile"><img src="<?php echo esc_url( promedix_upload( $t ) ); ?>" alt="" loading="lazy"/></figure>
<!-- /wp:image --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
