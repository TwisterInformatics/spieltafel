<?php
/**
 * Anmelden mit E-Mail und Passwort.
 *
 * @package Spieltafel
 */

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$target = spieltreff_safe_target( isset( $_GET['zurueck'] ) ? sanitize_text_field( wp_unslash( $_GET['zurueck'] ) ) : '' );
?>
<div class="st-narrow">
	<span class="eyebrow"><?php esc_html_e( 'Spieltreff', 'spieltafel' ); ?></span>
	<h1><?php esc_html_e( 'Anmelden', 'spieltafel' ); ?></h1>
	<form class="wish-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php spieltreff_notice(); ?>
		<input type="hidden" name="action" value="spieltreff_login">
		<input type="hidden" name="zurueck" value="<?php echo esc_attr( $target ); ?>">
		<?php wp_nonce_field( 'spieltreff_login', '_st_nonce', false ); ?>
		<div class="field">
			<label for="st-email"><?php esc_html_e( 'E-Mail', 'spieltafel' ); ?></label>
			<input type="email" id="st-email" name="email" required autocomplete="email">
		</div>
		<div class="field">
			<label for="st-pass"><?php esc_html_e( 'Passwort', 'spieltafel' ); ?></label>
			<input type="password" id="st-pass" name="passwort" required autocomplete="current-password">
			<small><a href="<?php echo esc_url( wp_lostpassword_url( spieltreff_url( 'anmelden' ) ) ); ?>"><?php esc_html_e( 'Passwort vergessen?', 'spieltafel' ); ?></a></small>
		</div>
		<div class="field field-check">
			<label><input type="checkbox" name="merken" value="1"> <span><?php esc_html_e( 'Angemeldet bleiben', 'spieltafel' ); ?></span></label>
		</div>
		<?php spieltreff_captcha_fields(); ?>
		<button class="btn" type="submit" data-needs-pow><?php esc_html_e( 'Anmelden', 'spieltafel' ); ?></button>
	</form>
	<?php if ( spieltreff_registration_open() ) : ?>
		<p class="st-after"><?php esc_html_e( 'Noch kein Konto?', 'spieltafel' ); ?> <a href="<?php echo esc_url( spieltreff_url( 'registrieren' ) ); ?>"><?php esc_html_e( 'Jetzt registrieren', 'spieltafel' ); ?></a></p>
	<?php endif; ?>
</div>
