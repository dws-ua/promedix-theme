<?php
/**
 * Title: Для кого (три картки з фото)
 * Slug: promedix/audience
 * Categories: promedix
 *
 * Фото тимчасові (фото товарів). Замініть на фото людей: клік на зображення → «Замінити».
 */
$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$tiles = array(
	array( __( 'Для медиків', 'promedix' ), '2026/03/zhinochi-sabo-promedix-707-likar-1024x1024.jpg' ),
	array( __( 'Для перукарів', 'promedix' ), '2026/03/zhinochi-sabo-promedix-154-tiulpan-1024x1024.jpg' ),
	array( __( 'Для б’юті-майстрів', 'promedix' ), '2026/03/zhinochi-sabo-promedix-714-piony-1024x1024.jpg' ),
);
?>
<!-- wp:group {"className":"pmx-section pmx-aud-wrap","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section pmx-aud-wrap"><!-- wp:group {"align":"wide","className":"pmx-aud-grid","layout":{"type":"grid","minimumColumnWidth":"240px"}} -->
<div class="wp-block-group alignwide pmx-aud-grid"><?php foreach ( $tiles as $t ) : ?><!-- wp:group {"className":"pmx-aud","layout":{"type":"default"}} -->
<div class="wp-block-group pmx-aud"><!-- wp:image {"className":"pmx-blend pmx-aud__img"} -->
<figure class="wp-block-image pmx-blend pmx-aud__img"><img src="<?php echo esc_url( promedix_upload( $t[1] ) ); ?>" alt="" loading="lazy"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"pmx-aud__bar"} -->
<p class="pmx-aud__bar"><a class="pmx-aud__link" href="<?php echo esc_url( $shop ); ?>"><?php echo esc_html( $t[0] ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
