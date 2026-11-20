<?php
/**
 * Kopf jeder Seite.
 *
 * @package Spieltafel
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#inhalt"><?php esc_html_e( 'Zum Inhalt springen', 'spieltafel' ); ?></a>
<header class="site-header">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( spieltafel_img( 'app-icon.png' ) ); ?>" alt="" width="36" height="36">
			<span><?php bloginfo( 'name' ); ?></span>
		</a>
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="hauptmenue"><?php esc_html_e( 'Menü', 'spieltafel' ); ?></button>
		<nav class="main-nav" id="hauptmenue" aria-label="<?php esc_attr_e( 'Hauptmenü', 'spieltafel' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'spieltafel_fallback_menu',
				)
			);
			?>
		</nav>
	</div>
</header>
<main id="inhalt">
