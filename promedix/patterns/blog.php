<?php
/**
 * Title: Корисне з блогу
 * Slug: promedix/blog
 * Categories: promedix
 */
$blog = get_option( 'page_for_posts' ) ? get_permalink( promedix_wpml_id( (int) get_option( 'page_for_posts' ), 'page' ) ) : promedix_page_url( 'blog' );
?>
<!-- wp:group {"className":"pmx-section","style":{"spacing":{"padding":{"top":"0","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section" style="padding-top:0;padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Корисне з блогу', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a href="<?php echo esc_url( $blog ); ?>"><strong><?php esc_html_e( 'Усі статті', 'promedix' ); ?></strong></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":7,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"className":"pmx-post-grid","layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"pmx-post-card","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group pmx-post-card"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/10","style":{"border":{"radius":"16px"}}} /-->

<!-- wp:post-title {"isLink":true,"level":3,"fontSize":"lg"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:group -->
