<?php
/**
 * Title: Смуга професій
 * Slug: promedix/professions
 * Categories: promedix
 */
$shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$items = array(
	array( __( 'Медикам', 'promedix' ), promedix_term_url( 'medychne-vzuttia' ) ),
	array( __( 'Перукарям', 'promedix' ), promedix_term_url( 'zhinochi-sabo' ) ),
	array( __( 'Б’юті-майстрам', 'promedix' ), $shop ),
	array( __( 'Для щоденної роботи', 'promedix' ), promedix_term_url( 'kroksy-anatomichni' ) ),
);
?>
<!-- wp:group {"className":"pmx-prof","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-prof"><!-- wp:html -->
<nav class="pmx-prof__row alignwide" aria-label="<?php esc_attr_e( 'За професією', 'promedix' ); ?>"><?php foreach ( $items as $it ) : ?><a class="pmx-prof__link" href="<?php echo esc_url( $it[1] ); ?>"><?php echo esc_html( $it[0] ); ?><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8"/></svg></a><?php endforeach; ?></nav>
<!-- /wp:html --></div>
<!-- /wp:group -->
