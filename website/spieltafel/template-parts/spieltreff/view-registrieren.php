<?php
/**
 * Registrieren: Anzeigename, E-Mail, Passwort, Region. Mit Bestätigung per E-Mail.
 *
 * @package Spieltafel
 */

$age     = (int) spieltreff_setting( 'min_age' );
$privacy = get_privacy_policy_url();
?>
<div class="st-narrow">
	<span class="eyebrow"><?php esc_html_e( 'Spieltreff', 'spieltafel' ); ?></span>
	<h1><?php esc_html_e( 'Konto erstellen', 'spieltafel' ); ?></h1>
	<?php if ( ! spieltreff_registration_open() ) : ?>
		<p class="hint"><?php esc_html_e( 'Die Registrierung öffnet mit dem Start des Spieltreffs. Schau bald wieder vorbei!', 'spieltafel' ); ?></p>
		<p><a href="<?php echo esc_url( spieltreff_url( 'anmelden' ) ); ?>"><?php esc_html_e( 'Du hast schon ein Konto? Anmelden', 'spieltafel' ); ?></a></p>
		<?php
		return;
	endif;
	?>
	<p><?php esc_html_e( 'Mit einem Konto kannst du Spielabende anbieten, Mitspieler suchen und dich anmelden. Kostenlos und ohne Werbung.', 'spieltafel' ); ?></p>
	<form class="wish-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php spieltreff_notice(); ?>
		<input type="hidden" name="action" value="spieltreff_register">
		<?php wp_nonce_field( 'spieltreff_register', '_st_nonce', false ); ?>
		<div class="field">
			<label for="st-name"><?php esc_html_e( 'Anzeigename', 'spieltafel' ); ?></label>
			<input type="text" id="st-name" name="anzeigename" required minlength="3" maxlength="30" autocomplete="nickname" placeholder="<?php esc_attr_e( 'zum Beispiel Jassfan_Bern', 'spieltafel' ); ?>">
			<small><?php esc_html_e( 'Das sehen die anderen. Es muss nicht dein echter Name sein.', 'spieltafel' ); ?></small>
		</div>
		<div class="field">
			<label for="st-email"><?php esc_html_e( 'E-Mail', 'spieltafel' ); ?></label>
			<input type="email" id="st-email" name="email" required autocomplete="email">
			<small><?php esc_html_e( 'Für die Anmeldung und Benachrichtigungen. Öffentlich sichtbar ist sie nie.', 'spieltafel' ); ?></small>
		</div>
		<div class="field">
			<label for="st-pass"><?php esc_html_e( 'Passwort', 'spieltafel' ); ?></label>
			<input type="password" id="st-pass" name="passwort" required minlength="10" autocomplete="new-password">
			<small><?php esc_html_e( 'Mindestens 10 Zeichen.', 'spieltafel' ); ?></small>
		</div>
		<div class="field">
			<label for="st-kanton"><?php esc_html_e( 'Region', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
			<select id="st-kanton" name="kanton">
				<option value=""><?php esc_html_e( 'Keine Angabe', 'spieltafel' ); ?></option>
				<?php foreach ( spieltreff_regions() as $code => $name ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<small><?php esc_html_e( 'Damit zeigen wir dir zuerst Einträge in deiner Nähe.', 'spieltafel' ); ?></small>
		</div>
		<div class="field field-check">
			<label>
				<input type="checkbox" name="regeln" value="1" required>
				<span>
					<?php esc_html_e( 'Ich akzeptiere die', 'spieltafel' ); ?>
					<a href="<?php echo esc_url( spieltreff_rules_url() ); ?>" target="_blank"><?php esc_html_e( 'Regeln des Spieltreffs', 'spieltafel' ); ?></a>
					<?php if ( $privacy ) : ?>
						<?php esc_html_e( 'und habe die', 'spieltafel' ); ?>
						<a href="<?php echo esc_url( $privacy ); ?>" target="_blank"><?php esc_html_e( 'Datenschutzerklärung', 'spieltafel' ); ?></a>
						<?php esc_html_e( 'gelesen.', 'spieltafel' ); ?>
					<?php endif; ?>
				</span>
			</label>
		</div>
		<div class="field field-check">
			<label>
				<input type="checkbox" name="alter" value="1" required>
				<span>
					<?php
					echo esc_html(
						$age
							/* translators: %d: Mindestalter */
							? sprintf( __( 'Ich bin mindestens %d Jahre alt.', 'spieltafel' ), $age )
							: __( 'Ich bin volljährig oder meine Eltern sind einverstanden.', 'spieltafel' )
					);
					?>
				</span>
			</label>
		</div>
		<?php spieltreff_captcha_fields(); ?>
		<button class="btn" type="submit" data-needs-pow><?php esc_html_e( 'Konto erstellen', 'spieltafel' ); ?></button>
	</form>
	<p class="st-after"><?php esc_html_e( 'Schon registriert?', 'spieltafel' ); ?> <a href="<?php echo esc_url( spieltreff_url( 'anmelden' ) ); ?>"><?php esc_html_e( 'Anmelden', 'spieltafel' ); ?></a></p>
</div>
