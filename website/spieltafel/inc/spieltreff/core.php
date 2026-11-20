<?php
/**
 * Spieltreff: Einträge lesen, Anmeldungen, Warteliste, E-Mails.
 *
 * Status einer Anmeldung:
 * - angefragt:  wartet auf die Freigabe durch den Ersteller
 * - bestaetigt: definitiv dabei
 * - warteliste: alle Plätze belegt, rückt nach, sobald einer frei wird
 * - abgelehnt:  vom Ersteller abgelehnt
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

function spieltreff_status_labels() {
	return array(
		'angefragt'  => __( 'Angefragt', 'spieltafel' ),
		'bestaetigt' => __( 'Bestätigt', 'spieltafel' ),
		'warteliste' => __( 'Warteliste', 'spieltafel' ),
		'abgelehnt'  => __( 'Abgelehnt', 'spieltafel' ),
	);
}

/* E-Mail */

/**
 * E-Mail an einen Benutzer (ID) oder eine Adresse. Absender wie bei den Spielwünschen (Customizer).
 *
 * @param int|string $to       Benutzer-ID oder E-Mail.
 * @param string     $subject  Betreff.
 * @param string[]   $lines    Zeilen.
 * @param string     $reply_to Antwortadresse.
 */
function spieltreff_mail( $to, $subject, $lines, $reply_to = '' ) {
	$name = '';
	if ( is_int( $to ) ) {
		$user = get_userdata( $to );
		if ( ! $user ) {
			return false;
		}
		$name = $user->display_name;
		$to   = $user->user_email;
	}
	if ( ! is_email( $to ) ) {
		return false;
	}
	$body = array();
	if ( $name ) {
		/* translators: %s: Anzeigename */
		$body[] = sprintf( __( 'Hallo %s', 'spieltafel' ), $name );
		$body[] = '';
	}
	$body   = array_merge( $body, $lines );
	$body[] = '';
	$body[] = '-- ';
	/* translators: %s: Name der Website */
	$body[]  = sprintf( __( 'Spieltreff von %s', 'spieltafel' ), get_bloginfo( 'name' ) );
	$body[]  = spieltreff_url();
	$headers = array();
	$from    = function_exists( 'spieltafel_mod' ) ? spieltafel_mod( 'wish_from' ) : '';
	if ( $from && is_email( $from ) ) {
		$from_name = spieltafel_mod( 'wish_from_name' );
		$from_name = $from_name ? $from_name : get_bloginfo( 'name' );
		$headers[] = 'From: ' . str_replace( array( '"', "\r", "\n", '<', '>' ), '', $from_name ) . ' <' . $from . '>';
	}
	if ( $reply_to && is_email( $reply_to ) ) {
		$headers[] = 'Reply-To: ' . $reply_to;
	}
	$subject = '[' . __( 'Spieltreff', 'spieltafel' ) . '] ' . str_replace( array( "\r", "\n" ), ' ', $subject );
	return wp_mail( $to, $subject, implode( "\n", $body ), $headers );
}

/** E-Mail an die Moderation. */
function spieltreff_mail_admin( $subject, $lines ) {
	$to = spieltreff_setting( 'notify_email' );
	$to = $to && is_email( $to ) ? $to : get_option( 'admin_email' );
	return spieltreff_mail( $to, $subject, $lines );
}

/* Meldungen nach einer Aktion: ?st=code */

function spieltreff_notice_texts() {
	return array(
		'gespeichert'    => array( 'ok', __( 'Gespeichert.', 'spieltafel' ) ),
		'erstellt'       => array( 'ok', __( 'Dein Eintrag ist online.', 'spieltafel' ) ),
		'pruefung'       => array( 'ok', __( 'Danke! Dein Eintrag wird kurz geprüft und erscheint dann für alle. Wir melden uns per E-Mail.', 'spieltafel' ) ),
		'angefragt'      => array( 'ok', __( 'Deine Anmeldung ist angekommen. Sie wird definitiv, sobald der Ersteller sie bestätigt. Du bekommst eine E-Mail.', 'spieltafel' ) ),
		'bestaetigt'     => array( 'ok', __( 'Du bist dabei! Die Details findest du unten.', 'spieltafel' ) ),
		'warteliste'     => array( 'ok', __( 'Alle Plätze sind belegt. Du stehst auf der Warteliste und rückst automatisch nach, sobald ein Platz frei wird.', 'spieltafel' ) ),
		'abgemeldet'     => array( 'ok', __( 'Du bist abgemeldet.', 'spieltafel' ) ),
		'aktualisiert'   => array( 'ok', __( 'Die Anmeldung ist aktualisiert. Die Person bekommt eine E-Mail.', 'spieltafel' ) ),
		'abgesagt'       => array( 'ok', __( 'Abgesagt. Alle Angemeldeten bekommen eine E-Mail.', 'spieltafel' ) ),
		'geloescht'      => array( 'ok', __( 'Der Eintrag ist gelöscht.', 'spieltafel' ) ),
		'verlaengert'    => array( 'ok', __( 'Das Gesuch ist verlängert.', 'spieltafel' ) ),
		'gesendet'       => array( 'ok', __( 'Die Nachricht ist verschickt.', 'spieltafel' ) ),
		'gemeldet'       => array( 'ok', __( 'Danke für die Meldung. Wir schauen uns den Eintrag an.', 'spieltafel' ) ),
		'registriert'    => array( 'ok', __( 'Fast geschafft! Wir haben dir eine E-Mail geschickt. Bitte klicke auf den Link darin, um dein Konto zu bestätigen.', 'spieltafel' ) ),
		'verifiziert'    => array( 'ok', __( 'Deine E-Mail ist bestätigt. Willkommen im Spieltreff!', 'spieltafel' ) ),
		'willkommen'     => array( 'ok', __( 'Du bist angemeldet.', 'spieltafel' ) ),
		'ausgeloggt'     => array( 'ok', __( 'Du bist abgemeldet. Bis bald!', 'spieltafel' ) ),
		'konto_weg'      => array( 'ok', __( 'Dein Konto und alle deine Daten sind gelöscht.', 'spieltafel' ) ),
		'passwort'       => array( 'ok', __( 'Dein Passwort ist geändert.', 'spieltafel' ) ),
		'link_gesendet'  => array( 'ok', __( 'Falls es ein unbestätigtes Konto mit dieser E-Mail gibt, ist ein neuer Link unterwegs.', 'spieltafel' ) ),
		'test_erstellt'  => array( 'ok', __( 'Das Testkonto ist angelegt.', 'spieltafel' ) ),
		'test_geloescht' => array( 'ok', __( 'Gelöscht.', 'spieltafel' ) ),
		'email_vergeben' => array( 'error', __( 'Diese E-Mail ist ungültig oder schon vergeben.', 'spieltafel' ) ),
		'fehlt'          => array( 'error', __( 'Bitte fülle alle Pflichtfelder richtig aus.', 'spieltafel' ) ),
		'abgelaufen'     => array( 'error', __( 'Das Formular war zu lange offen. Bitte versuche es noch einmal.', 'spieltafel' ) ),
		'captcha'        => array( 'error', __( 'Der Spamschutz hat nicht geklappt. Bitte warte, bis «Kein Roboter» erscheint, und sende das Formular noch einmal.', 'spieltafel' ) ),
		'zuviel'         => array( 'error', __( 'Das waren zu viele Versuche. Bitte versuche es später wieder.', 'spieltafel' ) ),
		'login'          => array( 'error', __( 'E-Mail oder Passwort stimmen nicht.', 'spieltafel' ) ),
		'unbestaetigt'   => array( 'error', __( 'Bitte bestätige zuerst deine E-Mail-Adresse. Den Link findest du in unserer E-Mail.', 'spieltafel' ) ),
		'gesperrt'       => array( 'error', __( 'Dieses Konto ist gesperrt. Bitte melde dich bei uns, falls das ein Irrtum ist.', 'spieltafel' ) ),
		'link_ungueltig' => array( 'error', __( 'Der Link ist ungültig oder abgelaufen. Fordere unten einen neuen an.', 'spieltafel' ) ),
		'name_vergeben'  => array( 'error', __( 'Diesen Anzeigenamen gibt es schon. Bitte wähle einen anderen.', 'spieltafel' ) ),
		'passwort_kurz'  => array( 'error', __( 'Das Passwort braucht mindestens 10 Zeichen.', 'spieltafel' ) ),
		'passwort_falsch' => array( 'error', __( 'Das Passwort stimmt nicht.', 'spieltafel' ) ),
		'geschlossen'    => array( 'error', __( 'Die Registrierung öffnet mit dem Start des Spieltreffs.', 'spieltafel' ) ),
		'voll'           => array( 'error', __( 'Alle Plätze sind schon vergeben.', 'spieltafel' ) ),
		'nicht_moeglich' => array( 'error', __( 'Das ist bei diesem Eintrag nicht möglich.', 'spieltafel' ) ),
		'datum'          => array( 'error', __( 'Bitte wähle ein Datum in der Zukunft (höchstens ein Jahr im Voraus).', 'spieltafel' ) ),
		'limit'          => array( 'error', __( 'Du hast heute schon viele Einträge erstellt. Bitte versuche es morgen wieder.', 'spieltafel' ) ),
		'fehler'         => array( 'error', __( 'Das hat leider nicht geklappt. Bitte versuche es später noch einmal.', 'spieltafel' ) ),
	);
}

function spieltreff_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nur Anzeige.
	$code  = isset( $_GET['st'] ) ? sanitize_key( $_GET['st'] ) : '';
	$texts = spieltreff_notice_texts();
	if ( isset( $texts[ $code ] ) ) {
		printf( '<div class="notice notice-%s" role="status">%s</div>', esc_attr( $texts[ $code ][0] ), esc_html( $texts[ $code ][1] ) );
	}
}

/** Zurück zu einer Adresse, mit Meldung. */
function spieltreff_redirect( $url, $code, $anchor = '' ) {
	$url = remove_query_arg( array( 'st' ), $url );
	wp_safe_redirect( add_query_arg( 'st', $code, $url ) . ( $anchor ? '#' . $anchor : '' ) );
	exit;
}

/** Adresse, von der das Formular kam (nur eigene Website). */
function spieltreff_back( $fallback = '' ) {
	$back = wp_get_referer();
	if ( ! $back || wp_parse_url( $back, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		$back = $fallback ? $fallback : spieltreff_url();
	}
	return strtok( $back, '#' );
}

/** Nonce prüfen, sonst zurück mit Meldung. */
function spieltreff_check_nonce( $action, $back ) {
	if ( ! isset( $_POST['_st_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_st_nonce'] ) ), $action ) ) {
		spieltreff_redirect( $back, 'abgelaufen' );
	}
}

/* Benutzer */

/** Darf der Benutzer mitmachen (bestätigte E-Mail, nicht gesperrt)? */
function spieltreff_user_ok( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	return ! get_user_meta( $user_id, '_st_verify', true ) && ! get_user_meta( $user_id, '_st_gesperrt', true );
}

function spieltreff_display_name( $user_id ) {
	$user = get_userdata( $user_id );
	return $user ? $user->display_name : __( 'Gelöschtes Konto', 'spieltafel' );
}

/* Einträge */

/** Alle Angaben eines Eintrags. */
function spieltreff_entry( $post ) {
	$post = get_post( $post );
	$m    = function ( $key ) use ( $post ) {
		return get_post_meta( $post->ID, '_st_' . $key, true );
	};
	$games = $m( 'spiele' );
	return array(
		'id'          => $post->ID,
		'typ'         => 'gesuch' === $m( 'typ' ) ? 'gesuch' : 'angebot',
		'titel'       => $post->post_title,
		'text'        => $post->post_content,
		'autor'       => (int) $post->post_author,
		'datum'       => (string) $m( 'datum' ),
		'zeit'        => (string) $m( 'zeit' ),
		'ende'        => (string) $m( 'ende' ),
		'wann'        => (string) $m( 'wann' ),
		'ort'         => (string) $m( 'ort' ),
		'plz'         => (string) $m( 'plz' ),
		'kanton'      => (string) $m( 'kanton' ),
		'ortsart'     => (string) $m( 'ortsart' ),
		'adresse'     => (string) $m( 'adresse' ),
		'kontakt'     => (string) $m( 'kontakt' ),
		'spiele'      => is_array( $games ) ? $games : array(),
		'spiele_frei' => (string) $m( 'spiele_frei' ),
		'max'         => (int) $m( 'max' ),
		'min'         => (int) $m( 'min' ),
		'nur_bei_min' => (bool) $m( 'nur_bei_min' ),
		'freigabe'    => (bool) $m( 'freigabe' ),
		'schluss'     => (string) $m( 'schluss' ),
		'anfaenger'   => (bool) $m( 'anfaenger' ),
		'abgesagt'    => (bool) $m( 'abgesagt' ),
		'start'       => (int) $m( 'start' ),
		'sichtbar'    => (int) $m( 'sichtbar_bis' ),
		'status'      => $post->post_status,
	);
}

/** Namen der Spiele: aus der Liste und frei eingegeben. */
function spieltreff_game_names( $e, $with_links = false ) {
	$names = array();
	foreach ( $e['spiele'] as $id ) {
		$game = spieltafel_game( $id );
		if ( $game ) {
			$names[] = $with_links ? array( $game['name'], spieltafel_game_url( $game ) ) : $game['name'];
		}
	}
	foreach ( array_filter( array_map( 'trim', explode( ',', $e['spiele_frei'] ) ) ) as $free ) {
		$names[] = $with_links ? array( $free, '' ) : $free;
	}
	return $names;
}

function spieltreff_weekday( $ts ) {
	$days = array( 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' );
	return $days[ (int) wp_date( 'w', $ts ) ];
}

/** «Montag, 15. Dezember 2026» */
function spieltreff_format_day( $ts, $short = false ) {
	$months = array( '', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' );
	$day    = spieltreff_weekday( $ts );
	if ( $short ) {
		return mb_substr( $day, 0, 2 ) . ', ' . wp_date( 'j.n.', $ts );
	}
	return $day . ', ' . wp_date( 'j. ', $ts ) . $months[ (int) wp_date( 'n', $ts ) ] . wp_date( ' Y', $ts );
}

/** Wann: Datum und Zeit beim Angebot, Text beim Gesuch. */
function spieltreff_when( $e, $short = false ) {
	if ( 'gesuch' === $e['typ'] ) {
		return $e['wann'];
	}
	if ( ! $e['start'] ) {
		return '';
	}
	$text = spieltreff_format_day( $e['start'], $short ) . ', ' . $e['zeit'];
	if ( $e['ende'] ) {
		$text .= '–' . $e['ende'];
	}
	return $text . ' ' . __( 'Uhr', 'spieltafel' );
}

/** Wo, öffentlich: Ort und Kanton, nie die genaue Adresse. */
function spieltreff_where( $e ) {
	$regions = spieltreff_regions();
	if ( 'ON' === $e['kanton'] || 'online' === $e['ortsart'] ) {
		return __( 'Online', 'spieltafel' );
	}
	$parts = array_filter( array( trim( $e['plz'] . ' ' . $e['ort'] ), $e['kanton'] && 'XX' !== $e['kanton'] ? $e['kanton'] : ( $regions[ $e['kanton'] ] ?? '' ) ) );
	return implode( ', ', $parts );
}

/** Zeitpunkt aus Datum und Zeit in Schweizer Zeit. */
function spieltreff_timestamp( $date, $time ) {
	$dt = date_create_immutable( $date . ' ' . ( $time ? $time : '00:00' ), wp_timezone() );
	return $dt ? $dt->getTimestamp() : 0;
}

function spieltreff_is_past( $post ) {
	$e = is_array( $post ) ? $post : spieltreff_entry( $post );
	if ( 'gesuch' === $e['typ'] ) {
		return $e['sichtbar'] && $e['sichtbar'] < time();
	}
	return $e['start'] && $e['start'] + 3 * HOUR_IN_SECONDS < time();
}

/** Anmeldeschluss vorbei? */
function spieltreff_closed( $e ) {
	if ( $e['abgesagt'] || spieltreff_is_past( $e ) || 'publish' !== $e['status'] ) {
		return true;
	}
	if ( 'angebot' === $e['typ'] && $e['start'] && $e['start'] < time() ) {
		return true;
	}
	if ( $e['schluss'] ) {
		$end = spieltreff_timestamp( $e['schluss'], '23:59' );
		if ( $end && $end < time() ) {
			return true;
		}
	}
	return false;
}

/* Anmeldungen */

function spieltreff_rows( $post_id, $status = null ) {
	global $wpdb;
	$table = spieltreff_table();
	if ( $status ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE post_id = %d AND status = %s ORDER BY id ASC", $post_id, $status ) ); // phpcs:ignore WordPress.DB
	}
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE post_id = %d ORDER BY id ASC", $post_id ) ); // phpcs:ignore WordPress.DB
}

function spieltreff_row( $post_id, $user_id ) {
	global $wpdb;
	$table = spieltreff_table();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE post_id = %d AND user_id = %d", $post_id, $user_id ) ); // phpcs:ignore WordPress.DB
}

function spieltreff_row_by_id( $id ) {
	global $wpdb;
	$table = spieltreff_table();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB
}

function spieltreff_set_status( $row_id, $status ) {
	global $wpdb;
	return $wpdb->update( spieltreff_table(), array( 'status' => $status, 'updated' => current_time( 'mysql', true ) ), array( 'id' => $row_id ), array( '%s', '%s' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/** Zahlen: bestätigt, angefragt, Warteliste. */
function spieltreff_counts( $post_id ) {
	$c = array(
		'bestaetigt' => 0,
		'angefragt'  => 0,
		'warteliste' => 0,
		'abgelehnt'  => 0,
	);
	foreach ( spieltreff_rows( $post_id ) as $row ) {
		if ( isset( $c[ $row->status ] ) ) {
			++$c[ $row->status ];
		}
	}
	return $c;
}

/** Freie Plätze (PHP_INT_MAX, wenn unbegrenzt). */
function spieltreff_free_spots( $e ) {
	if ( $e['max'] <= 0 ) {
		return PHP_INT_MAX;
	}
	return max( 0, $e['max'] - spieltreff_counts( $e['id'] )['bestaetigt'] );
}

/** Anmelden. Gibt den neuen Status zurück oder einen Fehlercode. */
function spieltreff_join( $post_id, $user_id, $message ) {
	$post = get_post( $post_id );
	if ( ! $post || SPIELTREFF_TYPE !== $post->post_type ) {
		return new WP_Error( 'nicht_moeglich' );
	}
	$e = spieltreff_entry( $post );
	if ( $e['autor'] === $user_id || spieltreff_closed( $e ) || spieltreff_row( $post_id, $user_id ) ) {
		return new WP_Error( 'nicht_moeglich' );
	}
	if ( spieltreff_free_spots( $e ) > 0 ) {
		$status = $e['freigabe'] ? 'angefragt' : 'bestaetigt';
	} else {
		$status = 'warteliste';
	}
	global $wpdb;
	$now = current_time( 'mysql', true );
	$ok  = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		spieltreff_table(),
		array(
			'post_id'   => $post_id,
			'user_id'   => $user_id,
			'status'    => $status,
			'nachricht' => mb_substr( $message, 0, 1000 ),
			'created'   => $now,
			'updated'   => $now,
		),
		array( '%d', '%d', '%s', '%s', '%s', '%s' )
	);
	if ( ! $ok ) {
		return new WP_Error( 'fehler' );
	}

	$name   = spieltreff_display_name( $user_id );
	$labels = array(
		'angefragt'  => __( 'möchte mitmachen und wartet auf deine Bestätigung', 'spieltafel' ),
		'bestaetigt' => __( 'hat sich angemeldet', 'spieltafel' ),
		'warteliste' => __( 'steht auf der Warteliste', 'spieltafel' ),
	);
	spieltreff_mail(
		$e['autor'],
		/* translators: 1: Name, 2: Titel */
		sprintf( __( '%1$s: %2$s', 'spieltafel' ), $name, $e['titel'] ),
		array_filter(
			array(
				$name . ' ' . $labels[ $status ] . ': «' . $e['titel'] . '».',
				$message ? "\n" . __( 'Nachricht:', 'spieltafel' ) . "\n" . $message . "\n" : '',
				'angefragt' === $status ? __( 'Bestätigen oder ablehnen:', 'spieltafel' ) : __( 'Alle Anmeldungen:', 'spieltafel' ),
				get_permalink( $post ) . '#verwalten',
			)
		)
	);
	return $status;
}

/** Abmelden. Ein frei gewordener Platz geht an die Warteliste. */
function spieltreff_leave( $post_id, $user_id ) {
	$row = spieltreff_row( $post_id, $user_id );
	if ( ! $row || 'abgelehnt' === $row->status ) {
		return false;
	}
	global $wpdb;
	$wpdb->delete( spieltreff_table(), array( 'id' => $row->id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$e = spieltreff_entry( $post_id );
	if ( 'bestaetigt' === $row->status || 'angefragt' === $row->status ) {
		spieltreff_mail(
			$e['autor'],
			/* translators: 1: Name, 2: Titel */
			sprintf( __( '%1$s hat sich abgemeldet: %2$s', 'spieltafel' ), spieltreff_display_name( $user_id ), $e['titel'] ),
			array( get_permalink( $post_id ) . '#verwalten' )
		);
	}
	if ( 'bestaetigt' === $row->status ) {
		spieltreff_fill_spots( $post_id );
	}
	return true;
}

/** Freie Plätze an die Warteliste vergeben (in der Reihenfolge der Anmeldung). */
function spieltreff_fill_spots( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return;
	}
	$e = spieltreff_entry( $post );
	if ( spieltreff_closed( $e ) ) {
		return;
	}
	$free = spieltreff_free_spots( $e );
	if ( $e['freigabe'] ) {
		// Bei Freigabe: so viele nachrücken lassen, wie Plätze frei sind, abzüglich offener Anfragen.
		$free -= spieltreff_counts( $post_id )['angefragt'];
	}
	foreach ( spieltreff_rows( $post_id, 'warteliste' ) as $row ) {
		if ( $free <= 0 ) {
			break;
		}
		--$free;
		if ( $e['freigabe'] ) {
			spieltreff_set_status( $row->id, 'angefragt' );
			spieltreff_mail(
				(int) $row->user_id,
				/* translators: %s: Titel */
				sprintf( __( 'Ein Platz ist frei: %s', 'spieltafel' ), $e['titel'] ),
				array(
					__( 'Bei diesem Eintrag ist ein Platz frei geworden. Du bist von der Warteliste nachgerückt, der Ersteller muss deine Anmeldung noch bestätigen.', 'spieltafel' ),
					get_permalink( $post_id ),
				)
			);
			spieltreff_mail(
				$e['autor'],
				/* translators: 1: Name, 2: Titel */
				sprintf( __( '%1$s ist nachgerückt: %2$s', 'spieltafel' ), spieltreff_display_name( (int) $row->user_id ), $e['titel'] ),
				array( __( 'Bitte bestätige die Anmeldung:', 'spieltafel' ), get_permalink( $post_id ) . '#verwalten' )
			);
		} else {
			spieltreff_set_status( $row->id, 'bestaetigt' );
			spieltreff_mail_status( (int) $row->user_id, $e, 'nachgerueckt' );
		}
	}
}

/** Der Ersteller ändert eine Anmeldung: bestaetigt, abgelehnt oder warteliste. */
function spieltreff_decide( $row_id, $status, $host_id ) {
	$row = spieltreff_row_by_id( $row_id );
	if ( ! $row || ! in_array( $status, array( 'bestaetigt', 'abgelehnt', 'warteliste' ), true ) ) {
		return new WP_Error( 'nicht_moeglich' );
	}
	$e = spieltreff_entry( (int) $row->post_id );
	if ( $e['autor'] !== $host_id && ! user_can( $host_id, 'edit_others_posts' ) ) {
		return new WP_Error( 'nicht_moeglich' );
	}
	if ( $row->status === $status ) {
		return $status;
	}
	if ( 'bestaetigt' === $status && spieltreff_free_spots( $e ) <= 0 ) {
		return new WP_Error( 'voll' );
	}
	$was = $row->status;
	spieltreff_set_status( $row->id, $status );
	spieltreff_mail_status( (int) $row->user_id, $e, $status );

	if ( 'bestaetigt' === $status && spieltreff_free_spots( $e ) <= 0 ) {
		// Voll: offene Anfragen kommen auf die Warteliste.
		foreach ( spieltreff_rows( $e['id'], 'angefragt' ) as $open ) {
			spieltreff_set_status( $open->id, 'warteliste' );
			spieltreff_mail_status( (int) $open->user_id, $e, 'warteliste' );
		}
	}
	if ( 'bestaetigt' === $was ) {
		spieltreff_fill_spots( $e['id'] );
	}
	return $status;
}

/** E-Mail an Teilnehmende bei einer Änderung ihrer Anmeldung. */
function spieltreff_mail_status( $user_id, $e, $status ) {
	$url   = get_permalink( $e['id'] );
	$texts = array(
		'bestaetigt'   => array( __( 'Du bist dabei: %s', 'spieltafel' ), __( 'Deine Anmeldung ist bestätigt und damit definitiv. Ort und Kontakt findest du jetzt auf der Seite des Eintrags.', 'spieltafel' ) ),
		'nachgerueckt' => array( __( 'Nachgerückt, du bist dabei: %s', 'spieltafel' ), __( 'Ein Platz ist frei geworden und du bist von der Warteliste nachgerückt. Deine Anmeldung ist definitiv.', 'spieltafel' ) ),
		'abgelehnt'    => array( __( 'Leider kein Platz: %s', 'spieltafel' ), __( 'Der Ersteller konnte deine Anmeldung diesmal leider nicht berücksichtigen. Schau doch bei den anderen Einträgen vorbei.', 'spieltafel' ) ),
		'warteliste'   => array( __( 'Warteliste: %s', 'spieltafel' ), __( 'Alle Plätze sind vergeben, du stehst auf der Warteliste. Wird ein Platz frei, bekommst du eine E-Mail.', 'spieltafel' ) ),
	);
	if ( ! isset( $texts[ $status ] ) ) {
		return;
	}
	$lines = array( $texts[ $status ][1] );
	if ( 'angebot' === $e['typ'] && in_array( $status, array( 'bestaetigt', 'nachgerueckt' ), true ) ) {
		$lines[] = '';
		$lines[] = __( 'Wann:', 'spieltafel' ) . ' ' . spieltreff_when( $e );
		$lines[] = __( 'Wo:', 'spieltafel' ) . ' ' . spieltreff_where( $e );
		if ( $e['nur_bei_min'] && $e['min'] > spieltreff_counts( $e['id'] )['bestaetigt'] + 1 ) {
			/* translators: %d: Mindestanzahl */
			$lines[] = sprintf( __( 'Findet nur statt, wenn mindestens %d Personen bestätigt sind. Wir melden uns, falls es nicht klappt.', 'spieltafel' ), $e['min'] );
		}
	}
	$lines[] = '';
	$lines[] = $url;
	spieltreff_mail( $user_id, sprintf( $texts[ $status ][0], $e['titel'] ), $lines );
}

/** E-Mail an alle Angemeldeten (ohne Abgelehnte). */
function spieltreff_notify_all( $post, $subject, $lines, $statuses = array( 'bestaetigt', 'angefragt', 'warteliste' ), $reply_to = '' ) {
	$post = get_post( $post );
	foreach ( spieltreff_rows( $post->ID ) as $row ) {
		if ( in_array( $row->status, $statuses, true ) ) {
			spieltreff_mail( (int) $row->user_id, $subject, array_merge( $lines, array( '', get_permalink( $post ) ) ), $reply_to );
		}
	}
}

/** Findet das Angebot statt? «ja», «offen» (Mindestanzahl noch nicht erreicht) oder «abgesagt». */
function spieltreff_state( $e ) {
	if ( $e['abgesagt'] ) {
		return 'abgesagt';
	}
	if ( 'angebot' === $e['typ'] && $e['nur_bei_min'] && $e['min'] > 0 ) {
		// Der Ersteller zählt mit.
		return spieltreff_counts( $e['id'] )['bestaetigt'] + 1 >= $e['min'] ? 'ja' : 'offen';
	}
	return 'ja';
}

/* Abfragen */

/**
 * Sichtbare Einträge mit Filtern.
 *
 * @param array $f typ, kanton, spiel, suche, anfaenger, seite, anzahl.
 */
function spieltreff_query( $f ) {
	$typ  = 'gesuch' === ( $f['typ'] ?? '' ) ? 'gesuch' : 'angebot';
	$meta = array(
		'relation' => 'AND',
		array( 'key' => '_st_typ', 'value' => $typ ),
		array(
			'key'     => '_st_sichtbar_bis',
			'value'   => time(),
			'compare' => '>=',
			'type'    => 'NUMERIC',
		),
		array(
			'key'     => '_st_abgesagt',
			'compare' => 'NOT EXISTS',
		),
	);
	if ( ! empty( $f['kanton'] ) ) {
		$meta[] = array( 'key' => '_st_kanton', 'value' => $f['kanton'] );
	}
	if ( ! empty( $f['spiel'] ) ) {
		$meta[] = array( 'key' => '_st_spiel', 'value' => $f['spiel'] );
	}
	if ( ! empty( $f['anfaenger'] ) ) {
		$meta[] = array( 'key' => '_st_anfaenger', 'value' => '1' );
	}
	$args = array(
		'post_type'      => SPIELTREFF_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => $f['anzahl'] ?? 12,
		'paged'          => max( 1, (int) ( $f['seite'] ?? 1 ) ),
		'meta_query'     => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => false,
	);
	if ( ! empty( $f['suche'] ) ) {
		$args['s'] = $f['suche'];
	}
	if ( 'angebot' === $typ ) {
		$args['meta_key'] = '_st_start'; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'ASC';
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}
	return new WP_Query( $args );
}
