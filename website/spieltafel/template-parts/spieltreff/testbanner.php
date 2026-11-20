<?php
/**
 * Hinweis im Testbetrieb, nur für angemeldete Benutzer vor der Freischaltung.
 *
 * @package Spieltafel
 */

if ( spieltreff_is_live() ) {
	return;
}
?>
<div class="hint st-testbanner" role="note">
	<strong><?php esc_html_e( 'Testbetrieb:', 'spieltafel' ); ?></strong>
	<?php esc_html_e( 'Der Spieltreff ist noch nicht freigeschaltet. Nur angemeldete Benutzer sehen diese Seiten, Besucher sehen einen Hinweis.', 'spieltafel' ); ?>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SPIELTREFF_TYPE . '&page=spieltreff-einstellungen' ) ); ?>"><?php esc_html_e( 'Freischalten', 'spieltafel' ); ?></a>
	<?php endif; ?>
</div>
