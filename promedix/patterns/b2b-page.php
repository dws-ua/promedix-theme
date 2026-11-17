<?php
/**
 * Title: Сторінка «Для закладів і партнерів»
 * Slug: promedix/b2b-page
 * Categories: promedix-b2b
 */
$offer = array(
	array( __( 'Підбір моделей', 'promedix' ), __( 'Порадимо моделі під специфіку роботи: відділення, аптека, лабораторія.', 'promedix' ) ),
	array( __( 'Розміри для кожного', 'promedix' ), __( 'Зберемо замовлення за розмірами всіх працівників в одному документі.', 'promedix' ) ),
	array( __( 'Однаковий стиль команди', 'promedix' ), __( 'Однотонні моделі для єдиного вигляду персоналу або принти для дитячих відділень.', 'promedix' ) ),
	array( __( 'Обмін розмірів', 'promedix' ), __( 'Якщо розмір не підійшов, допоможемо обміняти.', 'promedix' ) ),
);
$faq = array(
	array( __( 'Який мінімальний обсяг замовлення?', 'promedix' ), __( '[Вкажіть мінімальне гуртове замовлення]', 'promedix' ) ),
	array( __( 'Чи можна замовити різні розміри та моделі в одному замовленні?', 'promedix' ), __( 'Так, ми збираємо замовлення під розміри кожного працівника.', 'promedix' ) ),
	array( __( 'Що робити, якщо розмір не підійшов?', 'promedix' ), __( 'Обміняємо розмір відповідно до умов обміну та повернення.', 'promedix' ) ),
	array( __( 'Чи працюєте ви з безготівковим розрахунком?', 'promedix' ), __( '[Уточніть умови оплати для юридичних осіб]', 'promedix' ) ),
);
$imgs = array( '2026/03/zhinochi-sabo-promedix-707-likar-1024x1024.jpg', '2026/03/cholovichi-sabo-promedix-501-syni-1-1-1024x1024.jpg', '2025/01/1-5-1024x1024.png', '2025/01/2-10-1024x1024.png' );
?>
<!-- wp:group {"className":"pmx-b2b-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"mint","textColor":"ink","layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-b2b-hero has-ink-color has-mint-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"56%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:56%"><!-- wp:paragraph {"className":"pmx-eyebrow"} -->
<p class="pmx-eyebrow"><?php esc_html_e( 'Для закладів і партнерів', 'promedix' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Взуємо персонал клінік, аптек і лабораторій', 'promedix' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"body","fontSize":"lg"} -->
<p class="has-body-color has-text-color has-lg-font-size"><?php esc_html_e( 'Італійські шкіряні сабо ProMedix з анатомічною устілкою. Допоможемо підібрати моделі й розміри для всієї команди та підготуємо гуртову пропозицію.', 'promedix' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#wholesale"><?php esc_html_e( 'Отримати оптову пропозицію', 'promedix' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( 'tel:' . promedix_contact( 'phone_wholesale_href' ) ); ?>"><?php echo esc_html( promedix_contact( 'phone_wholesale' ) ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"44%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%"><!-- wp:group {"className":"pmx-b2b-hero__grid","layout":{"type":"grid","columnCount":2}} -->
<div class="wp-block-group pmx-b2b-hero__grid"><?php foreach ( $imgs as $img ) : ?><!-- wp:image {"className":"pmx-blend pmx-tile"} -->
<figure class="wp-block-image pmx-blend pmx-tile"><img src="<?php echo esc_url( promedix_upload( $img ) ); ?>" alt="" loading="lazy"/></figure>
<!-- /wp:image --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"promedix/segments"} /-->

<!-- wp:group {"className":"pmx-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"align":"wide"} -->
<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'Що ми робимо для закладів', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:group {"align":"wide","className":"pmx-grid","layout":{"type":"grid","minimumColumnWidth":"260px"}} -->
<div class="wp-block-group alignwide pmx-grid"><?php foreach ( $offer as $o ) : ?><!-- wp:group {"className":"pmx-card pmx-card--line","backgroundColor":"white","layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group pmx-card pmx-card--line has-white-background-color has-background"><!-- wp:heading {"level":3,"fontSize":"lg"} -->
<h3 class="wp-block-heading has-lg-font-size"><?php echo esc_html( $o[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"sm"} -->
<p class="has-muted-color has-text-color has-sm-font-size"><?php echo esc_html( $o[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><?php endforeach; ?></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"promedix/steps"} /-->

<!-- wp:group {"className":"pmx-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:0"><!-- wp:group {"align":"wide","className":"pmx-partners","backgroundColor":"mint","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide pmx-partners has-mint-background-color has-background"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:heading {"fontSize":"xl"} -->
<h2 class="wp-block-heading has-xl-font-size"><?php esc_html_e( 'Дилерам і магазинам', 'promedix' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"body"} -->
<p class="has-body-color has-text-color"><?php esc_html_e( 'Продаєте медичний одяг або взуття? Додайте ProMedix в асортимент. Розкажемо про умови співпраці й надамо фото та описи моделей.', 'promedix' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#wholesale"><?php esc_html_e( 'Стати партнером', 'promedix' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"promedix/wholesale-form"} /-->

<!-- wp:group {"className":"pmx-section pmx-faq","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group pmx-section pmx-faq" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:heading {"fontSize":"2xl"} -->
<h2 class="wp-block-heading has-2-xl-font-size"><?php esc_html_e( 'Часті питання', 'promedix' ); ?></h2>
<!-- /wp:heading --><?php foreach ( $faq as $f ) : ?>

<!-- wp:details {"className":"pmx-faq__item"} -->
<details class="wp-block-details pmx-faq__item"><summary><?php echo esc_html( $f[0] ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $f[1] ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --><?php endforeach; ?></div>
<!-- /wp:group -->
