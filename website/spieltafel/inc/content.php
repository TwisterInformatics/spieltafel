<?php
/**
 * Texte zur App: Funktionen und Ablauf. Stand der App-Version 1.0.
 * Bei neuen Funktionen der App hier ergänzen.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/** Funktionen der App, je mit Symbol, Titel und Text. */
function spieltafel_features() {
	$features = array(
		array( '🧮', __( 'Die App rechnet', 'spieltafel' ), __( 'Nach jeder Runde nur die Punkte eintragen. Spieltafel zählt zusammen, zeigt den Spielstand und ermittelt den Sieger.', 'spieltafel' ) ),
		array( '🃏', __( 'Jass mit allem', 'spieltafel' ), __( 'Schieber, Differenzler und Coiffeur mit Trumpf-Faktor, Weis, Stöck und Match. Die Gegenpunkte (157 minus eigene) rechnet die App selbst.', 'spieltafel' ) ),
		array( '📖', __( 'Kurzanleitung zu jedem Spiel', 'spieltafel' ), __( 'Die wichtigsten Regeln in fünf Sätzen, direkt in der App. Dazu ein Link zur ausführlichen Anleitung.', 'spieltafel' ) ),
		array( '🔎', __( 'Spiel schnell finden', 'spieltafel' ), __( 'Spiele durchsuchen, auch nach anderen Namen wie Mäxchen oder 31, oder nach Kategorie filtern.', 'spieltafel' ) ),
		array( '⏯️', __( 'Fortsetzen und Verlauf', 'spieltafel' ), __( 'Laufende Partien jederzeit fortsetzen. Beendete Partien bleiben im Verlauf, nach Tag sortiert.', 'spieltafel' ) ),
		array( '✏️', __( 'Freie Zählung', 'spieltafel' ), __( 'Für jedes andere Spiel: beliebig viele Spieler, Minuspunkte erlaubt, höchste oder tiefste Summe gewinnt.', 'spieltafel' ) ),
		array( '❤️', __( 'Punkte, Leben oder Plätze', 'spieltafel' ), __( 'Je nach Spiel zählt die App Punkte, Strafpunkte, Leben wie bei Mäxle und Schwimmen, oder Plätze wie beim Arschlöchle.', 'spieltafel' ) ),
		array( '📱', __( 'Für Handy und Tablet', 'spieltafel' ), __( 'Läuft auf iPhone und Android. Auf dem iPad und auf Tablets grosse Schrift, im Querformat mit zwei Spalten.', 'spieltafel' ) ),
		array( '💡', __( 'Bildschirm bleibt an', 'spieltafel' ), __( 'Auf Wunsch geht der Bildschirm während einer Partie nicht aus.', 'spieltafel' ) ),
		array( '🔒', __( 'Kein Konto, keine Werbung', 'spieltafel' ), __( 'Alle Partien bleiben auf deinem Gerät. Kein Server, kein Tracking, keine Anmeldung.', 'spieltafel' ) ),
		array( '🆓', __( 'Kostenlos', 'spieltafel' ), __( 'Spieltafel kostet nichts, ohne Käufe in der App.', 'spieltafel' ) ),
		array( '🇨🇭', __( 'Aus der Schweiz', 'spieltafel' ), __( 'Mit Schweizer Klassikern wie Jass, Tschau Sepp und Gämsch.', 'spieltafel' ) ),
	);
	return apply_filters( 'spieltafel_features', $features );
}

/** Ablauf in vier Schritten. */
function spieltafel_steps() {
	return apply_filters(
		'spieltafel_steps',
		array(
			array( __( 'Spiel wählen', 'spieltafel' ), __( 'Suchen oder nach Kategorie filtern.', 'spieltafel' ) ),
			array( __( 'Spieler eintragen', 'spieltafel' ), __( 'Namen oder Teams eingeben und starten.', 'spieltafel' ) ),
			array( __( 'Runden erfassen', 'spieltafel' ), __( 'Punkte eintippen, die Wertung übernimmt die App.', 'spieltafel' ) ),
			array( __( 'Sieger sehen', 'spieltafel' ), __( 'Spielstand jederzeit im Blick, am Schluss steht der Sieger fest.', 'spieltafel' ) ),
		)
	);
}

/** Screenshots im Theme-Ordner. */
function spieltafel_screenshots() {
	return array(
		array( 'iphone-1-spiele.webp', __( 'Spiel suchen', 'spieltafel' ) ),
		array( 'iphone-2-spielstand.webp', __( 'Spielstand beim Schieber', 'spieltafel' ) ),
		array( 'iphone-3-runde.webp', __( 'Runde erfassen', 'spieltafel' ) ),
	);
}
