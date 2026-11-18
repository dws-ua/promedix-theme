<?php
/**
 * Title: Ваша наступна улюблена пара
 * Slug: promedix/products
 * Categories: promedix
 */
$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<!-- wp:group {"className":"pmx-section pmx-products","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section pmx-products"><!-- wp:group {"align":"wide","className":"pmx-section__head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide pmx-section__head"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Ваша наступна улюблена пара', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"pmx-more"} -->
<p class="pmx-more"><a href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Весь каталог', 'promedix' ); ?></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[products limit="4" columns="4" category="zhinochi-sabo,cholovichi-sabo" orderby="popularity" visibility="visible"]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->
