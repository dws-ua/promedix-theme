<?php
/**
 * Title: Смуга переваг з іконками
 * Slug: promedix/benefits
 * Categories: promedix
 */
$items = array(
	array( 'leaf', __( 'Комфорт', 'promedix' ), __( 'на весь день', 'promedix' ) ),
	array( 'layers', __( 'Дихаючі', 'promedix' ), __( 'матеріали', 'promedix' ) ),
	array( 'feather', __( 'Легка', 'promedix' ), __( 'вага', 'promedix' ) ),
	array( 'shield', __( 'Надійність', 'promedix' ), __( 'у щоденній роботі', 'promedix' ) ),
);
?>
<!-- wp:group {"className":"pmx-benefits-band","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-benefits-band"><!-- wp:group {"align":"wide","className":"pmx-benefits","layout":{"type":"grid","minimumColumnWidth":"200px"}} -->
<div class="wp-block-group alignwide pmx-benefits"><?php foreach ( $items as $it ) : ?><!-- wp:paragraph {"className":"pmx-benefit"} -->
<p class="pmx-benefit"><?php echo promedix_icon( $it[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $it[1] ); ?><br><?php echo esc_html( $it[2] ); ?></span></p>
<!-- /wp:paragraph --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
