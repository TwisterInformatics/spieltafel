<?php
/**
 * Knöpfe zu App Store und Google Play. Ohne Link steht «bald».
 *
 * @package Spieltafel
 */

$stores = array(
	array( spieltafel_mod( 'app_store_url' ), __( 'Laden im', 'spieltafel' ), 'App Store', __( 'iPhone und iPad', 'spieltafel' ), __( 'Bald im App Store', 'spieltafel' ) ),
	array( spieltafel_mod( 'play_url' ), __( 'Jetzt bei', 'spieltafel' ), 'Google Play', __( 'Android', 'spieltafel' ), __( 'Bald bei Google Play', 'spieltafel' ) ),
);
$icon   = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zm0 3v13h10V5H7zm5 14.2a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>';
?>
<div class="hero-badges">
	<?php foreach ( $stores as $store ) : ?>
		<?php if ( $store[0] ) : ?>
			<a class="store-badge" href="<?php echo esc_url( $store[0] ); ?>" rel="noopener">
				<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- festes SVG. ?>
				<span><small><?php echo esc_html( $store[1] ); ?></small><strong><?php echo esc_html( $store[2] ); ?></strong></span>
			</a>
		<?php else : ?>
			<span class="store-badge is-soon">
				<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><small><?php echo esc_html( $store[3] ); ?></small><strong><?php echo esc_html( $store[4] ); ?></strong></span>
			</span>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
