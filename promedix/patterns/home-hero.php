<?php
/**
 * Title: Перший екран
 * Slug: promedix/home-hero
 * Categories: promedix
 * Keywords: hero, банер
 *
 * Фото праворуч замінюється в редакторі: клік на зображення → «Замінити».
 * Найкраще пасують фото на блакитному студійному тлі (#D0E5F8), як у карток товарів.
 */
$hero = promedix_upload( '2025/01/1-5-1024x1024.png' );
$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<!-- wp:group {"className":"pmx-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-hero"><!-- wp:columns {"verticalAlignment":"center","align":"wide","className":"pmx-hero__cols"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center pmx-hero__cols"><!-- wp:column {"verticalAlignment":"center","width":"44%","className":"pmx-hero__text"} -->
<div class="wp-block-column is-vertically-aligned-center pmx-hero__text" style="flex-basis:44%"><!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Ви дбаєте про інших.', 'promedix' ); ?> <?php esc_html_e( 'Подбайте', 'promedix' ); ?> <span class="pmx-hl"><?php esc_html_e( 'про себе.', 'promedix' ); ?></span></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"pmx-hero__lead"} -->
<p class="pmx-hero__lead"><?php esc_html_e( 'Професійне взуття для вашого робочого дня.', 'promedix' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"pmx-hero__btns"} -->
<div class="wp-block-buttons pmx-hero__btns"><!-- wp:button {"className":"pmx-arrow"} -->
<div class="wp-block-button pmx-arrow"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Обрати свою пару', 'promedix' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#pmx-picker"><?php esc_html_e( 'Допомогти з вибором', 'promedix' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"56%","className":"pmx-hero__media"} -->
<div class="wp-block-column is-vertically-aligned-center pmx-hero__media" style="flex-basis:56%"><!-- wp:image {"sizeSlug":"large","className":"pmx-hero__img"} -->
<figure class="wp-block-image size-large pmx-hero__img"><img src="<?php echo esc_url( $hero ); ?>" alt="<?php esc_attr_e( 'Медичні сабо ProMedix', 'promedix' ); ?>" fetchpriority="high"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"pmx-hero__caption"} -->
<p class="pmx-hero__caption">ProMedix 707</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
