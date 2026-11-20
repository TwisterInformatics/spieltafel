<?php
/**
 * E-Mail bestätigen: Fehlermeldung bei ungültigem Link und neuen Link anfordern.
 * Der Link selbst wird in inc/spieltreff/accounts.php verarbeitet.
 *
 * @package Spieltafel
 */

?>
<div class="st-narrow">
	<span class="eyebrow"><?php esc_html_e( 'Spieltreff', 'spieltafel' ); ?></span>
	<h1><?php esc_html_e( 'E-Mail bestätigen', 'spieltafel' ); ?></h1>
	<p><?php esc_html_e( 'Keine E-Mail bekommen oder der Link ist abgelaufen? Schau auch im Spam-Ordner nach oder fordere hier einen neuen Link an.', 'spieltafel' ); ?></p>
	<form class="wish-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php spieltreff_notice(); ?>
		<input type="hidden" name="action" value="spieltreff_resend">
		<?php wp_nonce_field( 'spieltreff_resend', '_st_nonce', false ); ?>
		<div class="field">
			<label for="st-email"><?php esc_html_e( 'E-Mail', 'spieltafel' ); ?></label>
			<input type="email" id="st-email" name="email" required autocomplete="email">
		</div>
		<?php spieltreff_captcha_fields(); ?>
		<button class="btn" type="submit" data-needs-pow><?php esc_html_e( 'Neuen Link senden', 'spieltafel' ); ?></button>
	</form>
</div>
