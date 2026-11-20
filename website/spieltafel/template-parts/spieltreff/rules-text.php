<?php
/**
 * Vorschlag für die Regeln und Nutzungsbedingungen des Spieltreffs.
 * Wird beim ersten Start als Seite «spieltreff-regeln» angelegt und kann dort angepasst werden.
 *
 * @package Spieltafel
 */

$age  = (int) spieltreff_setting( 'min_age' );
$days = (int) spieltreff_setting( 'keep_days' );
?>
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Der Spieltreff bringt Menschen zusammen, die gerne spielen. Damit das für alle angenehm bleibt, gelten diese Regeln. Mit der Registrierung stimmst du ihnen zu.', 'spieltafel' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Wer mitmachen kann', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
	<li><?php echo esc_html( $age ? sprintf( /* translators: %d: Alter */ __( 'Du bist mindestens %d Jahre alt.', 'spieltafel' ), $age ) : __( 'Minderjährige brauchen das Einverständnis ihrer Eltern.', 'spieltafel' ) ); ?></li>
	<li><?php esc_html_e( 'Pro Person ein Konto, mit einer echten E-Mail-Adresse.', 'spieltafel' ); ?></li>
</ul>
<!-- /wp:list -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Was erlaubt ist', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
	<li><?php esc_html_e( 'Private Spielabende, Spielrunden in Restaurants, Ludotheken, Vereinen und online.', 'spieltafel' ); ?></li>
	<li><?php esc_html_e( 'Gesuche für einzelne Abende oder feste Gruppen.', 'spieltafel' ); ?></li>
	<li><?php esc_html_e( 'Ein kleiner Unkostenbeitrag (zum Beispiel für Getränke) ist in Ordnung, wenn er im Eintrag steht.', 'spieltafel' ); ?></li>
</ul>
<!-- /wp:list -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Was nicht erlaubt ist', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
	<li><?php esc_html_e( 'Glücksspiel um Geld (zum Beispiel Poker mit Einsatz). In der Schweiz regelt das Geldspielgesetz solche Spiele streng.', 'spieltafel' ); ?></li>
	<li><?php esc_html_e( 'Werbung, kommerzielle Angebote ohne Bezug zum gemeinsamen Spielen, Spam.', 'spieltafel' ); ?></li>
	<li><?php esc_html_e( 'Beleidigungen, Belästigung, Diskriminierung und Inhalte, die gegen Schweizer Recht verstossen.', 'spieltafel' ); ?></li>
	<li><?php esc_html_e( 'Daten anderer weitergeben, ohne zu fragen, zum Beispiel E-Mail-Adressen oder die Adresse eines Spielabends.', 'spieltafel' ); ?></li>
</ul>
<!-- /wp:list -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Anmeldungen', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Eine Anmeldung ist erst definitiv, wenn der Ersteller sie bestätigt hat. Kannst du nicht kommen, melde dich bitte rechtzeitig ab, damit jemand von der Warteliste nachrücken kann. Wer einen Eintrag erstellt, sagt ab, wenn der Abend nicht stattfindet.', 'spieltafel' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Sicherheit', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Triff dich beim ersten Mal lieber an einem öffentlichen Ort. Gib deine Adresse erst bestätigten Teilnehmenden preis; der Spieltreff zeigt sie ohnehin nur ihnen. Die Treffen finden auf eigene Verantwortung statt. Wir vermitteln nur den Kontakt und haften nicht für das, was bei einem Treffen passiert.', 'spieltafel' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Moderation', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Jeder Eintrag kann gemeldet werden. Wir prüfen Meldungen und können Einträge ohne Angabe von Gründen ausblenden oder löschen und Konten sperren. Wer einen Eintrag zum ersten Mal erstellt, wartet eventuell kurz auf unsere Freigabe.', 'spieltafel' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2><?php esc_html_e( 'Deine Daten', 'spieltafel' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo esc_html( sprintf( /* translators: %d: Tage */ __( 'Andere sehen nur deinen Anzeigenamen. Vergangene Einträge löschen wir %d Tage nach dem Termin. Unter «Mein Konto» kannst du deine Daten jederzeit herunterladen und dein Konto mit allen Daten löschen. Mehr dazu in der Datenschutzerklärung.', 'spieltafel' ), $days ) ); ?></p>
<!-- /wp:paragraph -->
