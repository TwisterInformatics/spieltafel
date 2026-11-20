<?php
/**
 * So funktioniert der Spieltreff, mit Hinweis auf Regeln und Datenschutz.
 *
 * @package Spieltafel
 */

?>
<div class="st-how">
	<h2><?php esc_html_e( 'So funktioniert es', 'spieltafel' ); ?></h2>
	<ol class="steps">
		<li><h3><?php esc_html_e( 'Konto anlegen', 'spieltafel' ); ?></h3><p><?php esc_html_e( 'Mit E-Mail und Anzeigenamen. Andere sehen nur den Anzeigenamen.', 'spieltafel' ); ?></p></li>
		<li><h3><?php esc_html_e( 'Anmelden', 'spieltafel' ); ?></h3><p><?php esc_html_e( 'Bei einem Angebot oder Gesuch. Sind alle Plätze weg, kommst du auf die Warteliste.', 'spieltafel' ); ?></p></li>
		<li><h3><?php esc_html_e( 'Bestätigung', 'spieltafel' ); ?></h3><p><?php esc_html_e( 'Der Ersteller gibt deine Anmeldung frei. Erst dann ist sie definitiv und du siehst die Adresse.', 'spieltafel' ); ?></p></li>
		<li><h3><?php esc_html_e( 'Spielen', 'spieltafel' ); ?></h3><p><?php esc_html_e( 'Und die Punkte zählt die Spieltafel-App.', 'spieltafel' ); ?></p></li>
	</ol>
	<p class="st-small">
		<a href="<?php echo esc_url( spieltreff_rules_url() ); ?>"><?php esc_html_e( 'Regeln des Spieltreffs', 'spieltafel' ); ?></a>
		<?php if ( get_privacy_policy_url() ) : ?>
			· <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Datenschutz', 'spieltafel' ); ?></a>
		<?php endif; ?>
	</p>
</div>
