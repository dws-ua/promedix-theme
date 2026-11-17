<?php
/**
 * Title: Перший екран
 * Slug: promedix/home-hero
 * Categories: promedix
 * Keywords: hero, банер
 *
 * Фото праворуч замінюється в редакторі: клік на зображення → «Замінити».
 */
$hero = promedix_upload( '2025/01/1-5-1024x1024.png' );
$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<!-- wp:group {"className":"pmx-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-hero"><!-- wp:columns {"verticalAlignment":"center","align":"wide","className":"pmx-hero__cols"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center pmx-hero__cols"><!-- wp:column {"verticalAlignment":"center","width":"46%","className":"pmx-hero__text"} -->
<div class="wp-block-column is-vertically-aligned-center pmx-hero__text" style="flex-basis:46%"><!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Комфорт, що працює з вами', 'promedix' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"pmx-hero__lead"} -->
<p class="pmx-hero__lead"><?php esc_html_e( 'Професійне взуття для вашого робочого дня', 'promedix' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Обрати взуття', 'promedix' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"54%","className":"pmx-hero__media"} -->
<div class="wp-block-column is-vertically-aligned-center pmx-hero__media" style="flex-basis:54%"><!-- wp:image {"sizeSlug":"large","className":"pmx-blend pmx-hero__img"} -->
<figure class="wp-block-image size-large pmx-blend pmx-hero__img"><img src="<?php echo esc_url( $hero ); ?>" alt="<?php esc_attr_e( 'Медичні сабо ProMedix', 'promedix' ); ?>" fetchpriority="high"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
