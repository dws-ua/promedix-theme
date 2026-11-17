<?php
/**
 * Title: Для закладів і команд (оптовий блок)
 * Slug: promedix/wholesale-form
 * Categories: promedix, promedix-b2b
 */
?>
<!-- wp:group {"anchor":"wholesale","className":"pmx-section pmx-b2b-soft","layout":{"type":"constrained"}} -->
<div id="wholesale" class="wp-block-group pmx-section pmx-b2b-soft"><!-- wp:columns {"align":"wide","className":"pmx-b2b-soft__box"} -->
<div class="wp-block-columns alignwide pmx-b2b-soft__box"><!-- wp:column {"width":"55%"} -->
<div class="wp-block-column" style="flex-basis:55%"><!-- wp:paragraph {"className":"pmx-eyebrow"} -->
<p class="pmx-eyebrow"><?php esc_html_e( 'Для закладів і команд', 'promedix' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"fontSize":"xl"} -->
<h2 class="wp-block-heading has-xl-font-size"><?php esc_html_e( 'Взуття для всієї команди за оптовими цінами', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"body"} -->
<p class="has-body-color has-text-color"><?php esc_html_e( 'Клініки, аптеки, салони й лабораторії. Підберемо моделі та розміри для кожного працівника, оптові ціни надамо за запитом.', 'promedix' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"pmx-b2b-soft__contacts"} -->
<p class="pmx-b2b-soft__contacts">[promedix_phone type="wholesale"] · [promedix_contact key="email_wholesale"]</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:shortcode -->
[promedix_wholesale_form]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
