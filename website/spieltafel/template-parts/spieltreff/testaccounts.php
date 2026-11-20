<?php
/**
 * Testkonten verwalten (nur für Admins), auf der Seite «Mein Konto».
 *
 * @package Spieltafel
 */

if ( ! spieltreff_can_manage_testers() ) {
	return;
}
$created  = get_transient( 'st_test_' . get_current_user_id() );
$accounts = spieltreff_test_accounts();
$action   = admin_url( 'admin-post.php' );
if ( $created ) {
	delete_transient( 'st_test_' . get_current_user_id() );
}
?>
<section class="st-box st-testkonten" id="testkonten">
	<h2><?php esc_html_e( 'Testkonten', 'spieltafel' ); ?> <span class="st-badge"><?php esc_html_e( 'nur für Admins', 'spieltafel' ); ?></span></h2>
	<p class="st-small"><?php esc_html_e( 'Konten mit der Rolle «Spieler» zum Testen. Sie sind sofort bestätigt und funktionieren auch vor der Freischaltung.', 'spieltafel' ); ?></p>

	<?php if ( $created ) : ?>
		<div class="st-created" role="status">
			<strong><?php esc_html_e( 'Zugangsdaten (werden nur jetzt angezeigt):', 'spieltafel' ); ?></strong>
			<dl>
				<dt><?php esc_html_e( 'Anzeigename', 'spieltafel' ); ?></dt><dd><?php echo esc_html( $created['name'] ); ?></dd>
				<dt><?php esc_html_e( 'E-Mail', 'spieltafel' ); ?></dt><dd><code><?php echo esc_html( $created['email'] ); ?></code></dd>
				<dt><?php esc_html_e( 'Passwort', 'spieltafel' ); ?></dt><dd><code><?php echo esc_html( $created['pass'] ); ?></code></dd>
			</dl>
			<p class="st-small"><?php $created['mail'] ? esc_html_e( 'Die Zugangsdaten wurden auch per E-Mail verschickt.', 'spieltafel' ) : esc_html_e( 'Es wurde keine E-Mail verschickt. Gib die Daten selbst weiter.', 'spieltafel' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( $action ); ?>">
		<input type="hidden" name="action" value="spieltreff_testkonto">
		<input type="hidden" name="do" value="erstellen">
		<?php wp_nonce_field( 'spieltreff_testkonto', '_st_nonce', false ); ?>
		<div class="field-row">
			<div class="field">
				<label for="st-t-name"><?php esc_html_e( 'Anzeigename', 'spieltafel' ); ?></label>
				<input type="text" id="st-t-name" name="anzeigename" required minlength="3" maxlength="30" autocomplete="off" placeholder="<?php esc_attr_e( 'zum Beispiel Test Anna', 'spieltafel' ); ?>">
			</div>
			<div class="field">
				<label for="st-t-kanton"><?php esc_html_e( 'Region', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
				<select id="st-t-kanton" name="kanton">
					<option value=""><?php esc_html_e( 'Keine Angabe', 'spieltafel' ); ?></option>
					<?php foreach ( spieltreff_regions() as $code => $name ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="field-row">
			<div class="field">
				<label for="st-t-email"><?php esc_html_e( 'E-Mail', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
				<input type="email" id="st-t-email" name="email" autocomplete="off">
				<small><?php esc_html_e( 'Leer lassen für ein Konto ohne E-Mail (bekommt dann keine Benachrichtigungen).', 'spieltafel' ); ?></small>
			</div>
			<div class="field">
				<label for="st-t-pass"><?php esc_html_e( 'Passwort', 'spieltafel' ); ?> <span class="opt"><?php esc_html_e( '(freiwillig)', 'spieltafel' ); ?></span></label>
				<input type="text" id="st-t-pass" name="passwort" minlength="10" autocomplete="off">
				<small><?php esc_html_e( 'Leer lassen, dann wird eines erzeugt.', 'spieltafel' ); ?></small>
			</div>
		</div>
		<div class="field field-check">
			<label><input type="checkbox" name="senden" value="1" checked> <span><?php esc_html_e( 'Zugangsdaten per E-Mail an die Person schicken', 'spieltafel' ); ?></span></label>
		</div>
		<button class="btn" type="submit"><?php esc_html_e( 'Testkonto anlegen', 'spieltafel' ); ?></button>
	</form>

	<?php if ( $accounts ) : ?>
		<h3><?php esc_html_e( 'Bestehende Testkonten', 'spieltafel' ); ?> <span class="st-count"><?php echo count( $accounts ); ?></span></h3>
		<ul class="st-mini">
			<?php foreach ( $accounts as $account ) : ?>
				<li class="st-test-row">
					<span>
						<strong><?php echo esc_html( $account->display_name ); ?></strong>
						<span class="st-meta"><?php echo esc_html( $account->user_email ); ?></span>
					</span>
					<form method="post" action="<?php echo esc_url( $action ); ?>" data-confirm="<?php esc_attr_e( 'Testkonto mit allen Einträgen und Anmeldungen löschen?', 'spieltafel' ); ?>">
						<input type="hidden" name="action" value="spieltreff_testkonto">
						<input type="hidden" name="do" value="loeschen">
						<input type="hidden" name="user" value="<?php echo (int) $account->ID; ?>">
						<?php wp_nonce_field( 'spieltreff_testkonto', '_st_nonce', false ); ?>
						<button class="link-button st-danger" type="submit"><?php esc_html_e( 'Löschen', 'spieltafel' ); ?></button>
					</form>
				</li>
			<?php endforeach; ?>
		</ul>
		<form method="post" action="<?php echo esc_url( $action ); ?>" data-confirm="<?php esc_attr_e( 'Alle Testkonten mit ihren Einträgen und Anmeldungen löschen?', 'spieltafel' ); ?>">
			<input type="hidden" name="action" value="spieltreff_testkonto">
			<input type="hidden" name="do" value="alle_loeschen">
			<?php wp_nonce_field( 'spieltreff_testkonto', '_st_nonce', false ); ?>
			<button class="btn btn-secondary btn-small" type="submit"><?php esc_html_e( 'Alle Testkonten löschen', 'spieltafel' ); ?></button>
		</form>
	<?php endif; ?>
</section>
