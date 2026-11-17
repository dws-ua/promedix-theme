<?php
/**
 * Title: Кому ми постачаємо
 * Slug: promedix/segments
 * Categories: promedix, promedix-b2b
 */
$items = array(
	array( __( 'Клініки та лікарні', 'promedix' ), __( 'Однакове взуття для відділень, підбір розмірів для всього персоналу.', 'promedix' ) ),
	array( __( 'Аптеки й аптечні мережі', 'promedix' ), __( 'Зручні сабо для роботи за першим столом цілий день.', 'promedix' ) ),
	array( __( 'Лабораторії', 'promedix' ), __( 'Моделі для персоналу медичних і діагностичних лабораторій.', 'promedix' ) ),
	array( __( 'Стоматології та салони', 'promedix' ), __( 'Для тих, хто працює стоячи: стоматологи, перукарі, майстри.', 'promedix' ) ),
	array( __( 'Дилери та партнери', 'promedix' ), __( 'Співпраця з магазинами медичного одягу та взуття.', 'promedix' ) ),
);
?>
<!-- wp:group {"className":"pmx-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Кому ми постачаємо', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"pmx-grid pmx-grid--5","layout":{"type":"grid","minimumColumnWidth":"220px"}} -->
<div class="wp-block-group alignwide pmx-grid pmx-grid--5"><?php foreach ( $items as $it ) : ?><!-- wp:group {"className":"pmx-card pmx-card--line","backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group pmx-card pmx-card--line has-white-background-color has-background"><!-- wp:heading {"level":3,"fontSize":"lg"} -->
<h3 class="wp-block-heading has-lg-font-size"><a href="<?php echo esc_url( promedix_b2b_url() ); ?>"><?php echo esc_html( $it[0] ); ?></a></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"sm"} -->
<p class="has-muted-color has-text-color has-sm-font-size"><?php echo esc_html( $it[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
