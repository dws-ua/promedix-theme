<?php
/**
 * Title: Категорії каталогу
 * Slug: promedix/categories
 * Categories: promedix
 */
$cats = array(
	array( 'zhinochi-sabo', __( 'Жіночі сабо', 'promedix' ), __( 'Однотонні та з принтами · 36–41', 'promedix' ), '2026/04/zhinochi-sabo-promedix-714-nartsys-1-1024x1024.jpg', 'beige' ),
	array( 'cholovichi-sabo', __( 'Чоловічі сабо', 'promedix' ), __( 'Класичні кольори для зміни', 'promedix' ), '2026/03/cholovichi-sabo-promedix-501-syni-1-1-1024x1024.jpg', 'sand' ),
	array( 'kroksy-anatomichni', __( 'Крокси анатомічні', 'promedix' ), __( 'Анатомічна модель для зміни', 'promedix' ), '2025/01/2-10-1024x1024.png', 'sage' ),
);
?>
<!-- wp:group {"className":"pmx-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Каталог', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"pmx-grid","layout":{"type":"grid","minimumColumnWidth":"260px"}} -->
<div class="wp-block-group alignwide pmx-grid"><?php foreach ( $cats as $c ) : ?><!-- wp:group {"className":"pmx-cat pmx-cat--<?php echo esc_attr( $c[4] ); ?>","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group pmx-cat pmx-cat--<?php echo esc_attr( $c[4] ); ?>"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a class="pmx-cat__link" href="<?php echo esc_url( promedix_term_url( $c[0] ) ); ?>"><?php echo esc_html( $c[1] ); ?></a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"body","fontSize":"sm"} -->
<p class="has-body-color has-text-color has-sm-font-size"><?php echo esc_html( $c[2] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:image {"className":"pmx-blend pmx-cat__img"} -->
<figure class="wp-block-image pmx-blend pmx-cat__img"><img src="<?php echo esc_url( promedix_upload( $c[3] ) ); ?>" alt="" loading="lazy"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
