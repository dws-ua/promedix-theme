<?php
/**
 * Title: Для вашої команди (оптова смужка)
 * Slug: promedix/wholesale-cta
 * Categories: promedix, promedix-b2b
 */
?>
<!-- wp:group {"align":"wide","className":"pmx-strip","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide pmx-strip" style="margin-top:var(--wp--preset--spacing--60)"><!-- wp:group {"className":"pmx-strip__text","layout":{"type":"flex","orientation":"vertical","gap":"4px"}} -->
<div class="wp-block-group pmx-strip__text"><!-- wp:heading {"fontSize":"xl"} -->
<h2 class="wp-block-heading has-xl-font-size"><?php esc_html_e( 'Для вашої команди', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Оптові умови для медичних закладів, аптек, салонів і корпоративних клієнтів. Підберемо моделі й розміри для всього персоналу.', 'promedix' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"white","textColor":"primary"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-primary-color has-white-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( promedix_b2b_url() ); ?>"><?php esc_html_e( 'Оптові умови', 'promedix' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
