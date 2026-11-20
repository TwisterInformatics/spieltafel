<?php
/**
 * Fuss jeder Seite.
 *
 * @package Spieltafel
 */

?>
</main>
<footer class="site-footer">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( spieltafel_img( 'app-icon.png' ) ); ?>" alt="" width="36" height="36">
			<span><?php bloginfo( 'name' ); ?></span>
		</a>
		<nav aria-label="<?php esc_attr_e( 'Fusszeile', 'spieltafel' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'spieltafel_footer_fallback_menu',
				)
			);
			?>
		</nav>
		<p style="margin:0">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> · <?php esc_html_e( 'Kostenlos, ohne Werbung', 'spieltafel' ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
