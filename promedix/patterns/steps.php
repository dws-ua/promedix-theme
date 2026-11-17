<?php
/**
 * Title: Як працює гуртове замовлення
 * Slug: promedix/steps
 * Categories: promedix, promedix-b2b
 */
$steps = array(
	array( __( 'Заявка', 'promedix' ), __( 'Залиште заявку або зателефонуйте у гуртовий відділ.', 'promedix' ) ),
	array( __( 'Підбір моделей і розмірів', 'promedix' ), __( 'Допоможемо обрати моделі та зібрати розміри персоналу.', 'promedix' ) ),
	array( __( 'Пропозиція', 'promedix' ), __( 'Надішлемо гуртові ціни на обрані моделі.', 'promedix' ) ),
	array( __( 'Доставка', 'promedix' ), __( 'Відправимо замовлення та обміняємо розмір, якщо не підійде.', 'promedix' ) ),
);
?>
<!-- wp:group {"className":"pmx-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Як працює гуртове замовлення', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"pmx-grid","layout":{"type":"grid","minimumColumnWidth":"230px"}} -->
<div class="wp-block-group alignwide pmx-grid"><?php foreach ( $steps as $i => $st ) : ?><!-- wp:group {"className":"pmx-card pmx-card--line","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group pmx-card pmx-card--line"><!-- wp:paragraph {"className":"pmx-num"} -->
<p class="pmx-num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"fontSize":"lg"} -->
<h3 class="wp-block-heading has-lg-font-size"><?php echo esc_html( $st[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"sm"} -->
<p class="has-muted-color has-text-color has-sm-font-size"><?php echo esc_html( $st[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
