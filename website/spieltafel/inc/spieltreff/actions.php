<?php
/**
 * Spieltreff: Formulare für Einträge, Anmeldungen, Nachrichten und Meldungen.
 * Alle Formulare senden an admin-post.php mit eigenem Nonce.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/** Nur für angemeldete, bestätigte und nicht gesperrte Benutzer. */
function spieltreff_require_user( $back ) {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( spieltreff_url( 'anmelden', array( 'zurueck' => rawurlencode( $back ) ) ) );
		exit;
	}
	$uid = get_current_user_id();
	if ( get_user_meta( $uid, '_st_gesperrt', true ) ) {
		spieltreff_redirect( $back, 'gesperrt' );
	}
	if ( get_user_meta( $uid, '_st_verify', true ) ) {
		spieltreff_redirect( $back, 'unbestaetigt' );
	}
	return $uid;
}

function spieltreff_post_field( $key, $type = 'text', $max = 200 ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce wird im Handler geprüft.
	if ( ! isset( $_POST[ $key ] ) ) {
		return 'array' === $type ? array() : '';
	}
	switch ( $type ) {
		case 'textarea':
			return mb_substr( trim( sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) ), 0, $max );
		case 'int':
			return (int) $_POST[ $key ];
		case 'bool':
			return ! empty( $_POST[ $key ] );
		case 'array':
			return is_array( $_POST[ $key ] ) ? array_map( 'sanitize_key', wp_unslash( $_POST[ $key ] ) ) : array();
		default:
			return mb_substr( trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ), 0, $max );
	}
	// phpcs:enable
}

/* Eintrag erstellen oder bearbeiten */

add_action( 'admin_post_spieltreff_save', 'spieltreff_handle_save' );
add_action( 'admin_post_nopriv_spieltreff_save', 'spieltreff_handle_save' );

function spieltreff_handle_save() {
	$post_id = spieltreff_post_field( 'id', 'int' );
	$back    = $post_id ? spieltreff_url( 'neu', array( 'bearbeiten' => $post_id ) ) : spieltreff_back( spieltreff_url( 'neu' ) );
	$uid     = spieltreff_require_user( $back );
	spieltreff_check_nonce( 'spieltreff_save', $back );
	if ( ! spieltreff_can_view() ) {
		spieltreff_redirect( spieltreff_url(), 'nicht_moeglich' );
	}

	$old = null;
	if ( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || SPIELTREFF_TYPE !== $post->post_type || ( (int) $post->post_author !== $uid && ! current_user_can( 'edit_others_posts' ) ) ) {
			spieltreff_redirect( spieltreff_url(), 'nicht_moeglich' );
		}
		$old = spieltreff_entry( $post );
	} else {
		// Höchstens 5 neue Einträge pro Tag und Person.
		$today = get_posts(
			array(
				'post_type'      => SPIELTREFF_TYPE,
				'author'         => $uid,
				'post_status'    => 'any',
				'date_query'     => array( array( 'after' => '24 hours ago' ) ),
				'fields'         => 'ids',
				'posts_per_page' => 10,
			)
		);
		if ( count( $today ) >= 5 && ! current_user_can( 'edit_others_posts' ) ) {
			spieltreff_redirect( $back, 'limit' );
		}
	}

	$typ   = $old ? $old['typ'] : ( 'gesuch' === spieltreff_post_field( 'typ' ) ? 'gesuch' : 'angebot' );
	$d     = array(
		'titel'       => spieltreff_post_field( 'titel', 'text', 80 ),
		'text'        => spieltreff_post_field( 'text', 'textarea', 2000 ),
		'datum'       => spieltreff_post_field( 'datum', 'text', 10 ),
		'zeit'        => spieltreff_post_field( 'zeit', 'text', 5 ),
		'ende'        => spieltreff_post_field( 'ende', 'text', 5 ),
		'wann'        => spieltreff_post_field( 'wann', 'text', 80 ),
		'ort'         => spieltreff_post_field( 'ort', 'text', 60 ),
		'plz'         => spieltreff_post_field( 'plz', 'text', 4 ),
		'kanton'      => strtoupper( spieltreff_post_field( 'kanton', 'text', 2 ) ),
		'ortsart'     => sanitize_key( spieltreff_post_field( 'ortsart', 'text', 20 ) ),
		'adresse'     => spieltreff_post_field( 'adresse', 'textarea', 300 ),
		'kontakt'     => spieltreff_post_field( 'kontakt', 'textarea', 300 ),
		'spiele'      => array_values( array_intersect( spieltreff_post_field( 'spiele', 'array' ), wp_list_pluck( spieltafel_games(), 'id' ) ) ),
		'spiele_frei' => spieltreff_post_field( 'spiele_frei', 'text', 200 ),
		'max'         => max( 0, min( 99, spieltreff_post_field( 'max', 'int' ) ) ),
		'min'         => max( 0, min( 99, spieltreff_post_field( 'min', 'int' ) ) ),
		'nur_bei_min' => spieltreff_post_field( 'nur_bei_min', 'bool' ),
		'freigabe'    => spieltreff_post_field( 'freigabe', 'bool' ),
		'schluss'     => spieltreff_post_field( 'schluss', 'text', 10 ),
		'anfaenger'   => spieltreff_post_field( 'anfaenger', 'bool' ),
	);

	// Prüfen.
	$valid = mb_strlen( $d['titel'] ) >= 5 && '' !== $d['ort'] && isset( spieltreff_regions()[ $d['kanton'] ] )
		&& ( $d['spiele'] || '' !== $d['spiele_frei'] );
	if ( $d['plz'] && ! preg_match( '/^\d{4}$/', $d['plz'] ) ) {
		$d['plz'] = '';
	}
	if ( ! isset( spieltreff_place_kinds()[ $d['ortsart'] ] ) ) {
		$d['ortsart'] = 'anderes';
	}
	$start = 0;
	if ( 'angebot' === $typ ) {
		$valid = $valid && $d['max'] >= 1 && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d['datum'] ) && preg_match( '/^\d{2}:\d{2}$/', $d['zeit'] );
		if ( $d['ende'] && ! preg_match( '/^\d{2}:\d{2}$/', $d['ende'] ) ) {
			$d['ende'] = '';
		}
		if ( $valid ) {
			$start = spieltreff_timestamp( $d['datum'], $d['zeit'] );
			// Beim Bearbeiten darf ein unverändertes Datum bleiben.
			$unchanged = $old && $old['start'] === $start;
			if ( ! $start || ( ! $unchanged && $start < time() ) || $start > time() + YEAR_IN_SECONDS ) {
				spieltreff_redirect( $back, 'datum' );
			}
		}
		if ( $d['schluss'] && ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d['schluss'] ) || $d['schluss'] > $d['datum'] ) ) {
			$d['schluss'] = '';
		}
		if ( $d['min'] > $d['max'] + 1 ) {
			$d['min'] = $d['max'] + 1;
		}
		if ( $d['nur_bei_min'] && $d['min'] < 2 ) {
			$d['nur_bei_min'] = false;
		}
	} else {
		$valid            = $valid && '' !== $d['wann'];
		$d['datum']       = '';
		$d['zeit']        = '';
		$d['ende']        = '';
		$d['schluss']     = '';
		$d['nur_bei_min'] = false;
		$d['min']         = 0;
	}
	if ( ! $valid ) {
		spieltreff_redirect( $back, 'fehlt' );
	}

	// Freigabe durch die Moderation?
	$mode   = spieltreff_setting( 'moderation' );
	$status = 'publish';
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		if ( 'alle' === $mode ) {
			$status = 'pending';
		} elseif ( 'erster' === $mode && ! $old ) {
			$published = get_posts(
				array(
					'post_type'      => SPIELTREFF_TYPE,
					'author'         => $uid,
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'posts_per_page' => 1,
				)
			);
			$status = $published || get_user_meta( $uid, '_st_vertraut', true ) ? 'publish' : 'pending';
		} elseif ( $old ) {
			$status = $old['status'];
		}
	}

	$postarr = array(
		'post_type'    => SPIELTREFF_TYPE,
		'post_title'   => $d['titel'],
		'post_content' => $d['text'],
		'post_status'  => $status,
	);
	if ( $old ) {
		$postarr['ID'] = $post_id;
	} else {
		$postarr['post_author'] = $uid;
	}
	// Reiner Text: kein HTML, kein Filter für Beiträge.
	kses_remove_filters();
	$post_id = $old ? wp_update_post( $postarr, true ) : wp_insert_post( $postarr, true );
	kses_init_filters();
	if ( is_wp_error( $post_id ) ) {
		spieltreff_redirect( $back, 'fehler' );
	}

	$visible = 'angebot' === $typ
		? ( $start + ( $d['ende'] ? 6 : 3 ) * HOUR_IN_SECONDS )
		: ( $old && $old['sichtbar'] > time() ? $old['sichtbar'] : time() + DAY_IN_SECONDS * (int) spieltreff_setting( 'gesuch_days' ) );
	$meta    = array_merge(
		$d,
		array(
			'typ'          => $typ,
			'start'        => $start,
			'sichtbar_bis' => $visible,
		)
	);
	unset( $meta['titel'], $meta['text'] );
	foreach ( $meta as $key => $value ) {
		if ( is_bool( $value ) ) {
			if ( $value ) {
				update_post_meta( $post_id, '_st_' . $key, '1' );
			} else {
				delete_post_meta( $post_id, '_st_' . $key );
			}
		} else {
			update_post_meta( $post_id, '_st_' . $key, $value );
		}
	}
	// Ein Eintrag pro Spiel für den Filter.
	delete_post_meta( $post_id, '_st_spiel' );
	foreach ( $d['spiele'] as $game ) {
		add_post_meta( $post_id, '_st_spiel', $game );
	}

	$new = spieltreff_entry( $post_id );
	if ( $old ) {
		// Teilnehmende informieren, wenn sich Zeit oder Ort ändern.
		$changed = array();
		if ( spieltreff_when( $old ) !== spieltreff_when( $new ) ) {
			$changed[] = __( 'Neu wann:', 'spieltafel' ) . ' ' . spieltreff_when( $new );
		}
		if ( spieltreff_where( $old ) !== spieltreff_where( $new ) || $old['adresse'] !== $new['adresse'] ) {
			$changed[] = __( 'Neu wo:', 'spieltafel' ) . ' ' . spieltreff_where( $new );
		}
		if ( $changed ) {
			spieltreff_notify_all(
				$post_id,
				/* translators: %s: Titel */
				sprintf( __( 'Geändert: %s', 'spieltafel' ), $new['titel'] ),
				array_merge( array( __( 'Beim Eintrag, für den du angemeldet bist, hat sich etwas geändert.', 'spieltafel' ), '' ), $changed )
			);
		}
		spieltreff_fill_spots( $post_id );
		wp_safe_redirect( add_query_arg( 'st', 'gespeichert', get_permalink( $post_id ) ) );
		exit;
	}

	if ( 'pending' === $status ) {
		spieltreff_mail_admin(
			/* translators: %s: Titel */
			sprintf( __( 'Neuer Eintrag zum Prüfen: %s', 'spieltafel' ), $new['titel'] ),
			array(
				__( 'Von:', 'spieltafel' ) . ' ' . spieltreff_display_name( $uid ),
				__( 'Ansehen:', 'spieltafel' ) . ' ' . get_permalink( $post_id ),
				__( 'Freigeben:', 'spieltafel' ) . ' ' . admin_url( 'edit.php?post_status=pending&post_type=' . SPIELTREFF_TYPE ),
			)
		);
	} elseif ( spieltreff_setting( 'notify_new' ) ) {
		spieltreff_mail_admin(
			/* translators: %s: Titel */
			sprintf( __( 'Neuer Eintrag: %s', 'spieltafel' ), $new['titel'] ),
			array( get_permalink( $post_id ) )
		);
	}
	wp_safe_redirect( add_query_arg( 'st', 'pending' === $status ? 'pruefung' : 'erstellt', get_permalink( $post_id ) ) );
	exit;
}

/* Aktionen auf einem Eintrag: Anmelden, Abmelden, Verwalten, Absagen, Löschen, Verlängern, Nachricht, Melden */

add_action( 'admin_post_spieltreff_action', 'spieltreff_handle_action' );
add_action( 'admin_post_nopriv_spieltreff_action', 'spieltreff_handle_action' );

function spieltreff_handle_action() {
	$post_id = spieltreff_post_field( 'id', 'int' );
	$post    = $post_id ? get_post( $post_id ) : null;
	if ( ! $post || SPIELTREFF_TYPE !== $post->post_type ) {
		spieltreff_redirect( spieltreff_url(), 'nicht_moeglich' );
	}
	$back = get_permalink( $post );
	$uid  = spieltreff_require_user( $back );
	spieltreff_check_nonce( 'spieltreff_action_' . $post_id, $back );

	$e       = spieltreff_entry( $post );
	$is_host = $e['autor'] === $uid || current_user_can( 'edit_others_posts' );
	$do      = spieltreff_post_field( 'do', 'text', 20 );

	switch ( $do ) {
		case 'anmelden':
			if ( ! spieltreff_rate_limit( 'join_' . $uid, 30, DAY_IN_SECONDS ) ) {
				spieltreff_redirect( $back, 'zuviel' );
			}
			$result = spieltreff_join( $post_id, $uid, spieltreff_post_field( 'nachricht', 'textarea', 1000 ) );
			spieltreff_redirect( $back, is_wp_error( $result ) ? $result->get_error_code() : $result, 'mitmachen' );
			break;

		case 'abmelden':
			spieltreff_leave( $post_id, $uid );
			spieltreff_redirect( $back, 'abgemeldet', 'mitmachen' );
			break;

		case 'entscheiden':
			if ( ! $is_host ) {
				spieltreff_redirect( $back, 'nicht_moeglich' );
			}
			$result = spieltreff_decide( spieltreff_post_field( 'row', 'int' ), spieltreff_post_field( 'status', 'text', 20 ), $uid );
			spieltreff_redirect( $back, is_wp_error( $result ) ? $result->get_error_code() : 'aktualisiert', 'verwalten' );
			break;

		case 'absagen':
			if ( ! $is_host || $e['abgesagt'] ) {
				spieltreff_redirect( $back, 'nicht_moeglich' );
			}
			update_post_meta( $post_id, '_st_abgesagt', '1' );
			$reason = spieltreff_post_field( 'grund', 'textarea', 500 );
			spieltreff_notify_all(
				$post,
				/* translators: %s: Titel */
				sprintf( __( 'Abgesagt: %s', 'spieltafel' ), $e['titel'] ),
				array_filter(
					array(
						__( 'Leider findet dieser Spieltreff nicht statt.', 'spieltafel' ),
						$reason ? "\n" . $reason : '',
					)
				)
			);
			spieltreff_redirect( $back, 'abgesagt', 'verwalten' );
			break;

		case 'loeschen':
			if ( ! $is_host ) {
				spieltreff_redirect( $back, 'nicht_moeglich' );
			}
			if ( ! $e['abgesagt'] && ! spieltreff_is_past( $e ) ) {
				spieltreff_notify_all(
					$post,
					/* translators: %s: Titel */
					sprintf( __( 'Abgesagt: %s', 'spieltafel' ), $e['titel'] ),
					array( __( 'Der Eintrag wurde gelöscht, der Spieltreff findet nicht statt.', 'spieltafel' ) )
				);
			}
			wp_delete_post( $post_id, true );
			spieltreff_redirect( spieltreff_url( 'konto' ), 'geloescht' );
			break;

		case 'verlaengern':
			if ( ! $is_host || 'gesuch' !== $e['typ'] ) {
				spieltreff_redirect( $back, 'nicht_moeglich' );
			}
			update_post_meta( $post_id, '_st_sichtbar_bis', time() + DAY_IN_SECONDS * (int) spieltreff_setting( 'gesuch_days' ) );
			spieltreff_redirect( spieltreff_back( $back ), 'verlaengert' );
			break;

		case 'nachricht':
			if ( ! $is_host ) {
				spieltreff_redirect( $back, 'nicht_moeglich' );
			}
			$text = spieltreff_post_field( 'text', 'textarea', 2000 );
			if ( '' === $text ) {
				spieltreff_redirect( $back, 'fehlt', 'verwalten' );
			}
			if ( ! spieltreff_rate_limit( 'msg_' . $post_id, 5, DAY_IN_SECONDS ) ) {
				spieltreff_redirect( $back, 'zuviel', 'verwalten' );
			}
			$to       = spieltreff_post_field( 'an', 'text', 20 );
			$statuses = 'alle' === $to ? array( 'bestaetigt', 'angefragt', 'warteliste' ) : array( 'bestaetigt' );
			$host     = get_userdata( $e['autor'] );
			spieltreff_notify_all(
				$post,
				/* translators: 1: Name, 2: Titel */
				sprintf( __( 'Nachricht von %1$s: %2$s', 'spieltafel' ), $host->display_name, $e['titel'] ),
				array( $text, '', __( 'Mit «Antworten» schreibst du direkt an den Ersteller.', 'spieltafel' ) ),
				$statuses,
				$host->user_email
			);
			spieltreff_redirect( $back, 'gesendet', 'verwalten' );
			break;

		case 'melden':
			$reason = spieltreff_post_field( 'grund', 'text', 60 );
			if ( ! in_array( $reason, spieltreff_report_reasons(), true ) ) {
				$reason = __( 'Anderes', 'spieltafel' );
			}
			$note   = spieltreff_post_field( 'text', 'textarea', 500 );
			$all    = get_post_meta( $post_id, '_st_meldung' );
			foreach ( $all as $report ) {
				if ( (int) ( $report['user'] ?? 0 ) === $uid ) {
					spieltreff_redirect( $back, 'gemeldet' );
				}
			}
			add_post_meta(
				$post_id,
				'_st_meldung',
				array(
					'user'  => $uid,
					'grund' => $reason,
					'text'  => $note,
					'zeit'  => time(),
				)
			);
			$count  = count( $all ) + 1;
			$hidden = false;
			if ( $count >= (int) spieltreff_setting( 'report_limit' ) && 'publish' === $post->post_status ) {
				wp_update_post( array( 'ID' => $post_id, 'post_status' => 'pending' ) );
				$hidden = true;
			}
			spieltreff_mail_admin(
				/* translators: %s: Titel */
				sprintf( __( 'Eintrag gemeldet: %s', 'spieltafel' ), $e['titel'] ),
				array_filter(
					array(
						__( 'Grund:', 'spieltafel' ) . ' ' . $reason,
						$note,
						/* translators: %d: Anzahl Meldungen */
						sprintf( __( 'Meldungen insgesamt: %d', 'spieltafel' ), $count ),
						$hidden ? __( 'Der Eintrag ist jetzt ausgeblendet, bis du ihn prüfst.', 'spieltafel' ) : '',
						get_edit_post_link( $post_id, 'raw' ),
					)
				)
			);
			spieltreff_redirect( $hidden ? spieltreff_url() : $back, 'gemeldet' );
			break;
	}
	spieltreff_redirect( $back, 'nicht_moeglich' );
}

/* Kalender-Datei (.ics) für Bestätigte und den Ersteller */

add_action( 'admin_post_spieltreff_ics', 'spieltreff_handle_ics' );

function spieltreff_handle_ics() {
	$post_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'spieltreff_ics_' . $post_id ) ) {
		wp_die( esc_html__( 'Der Link ist abgelaufen.', 'spieltafel' ) );
	}
	$e   = spieltreff_entry( $post_id );
	$uid = get_current_user_id();
	$row = spieltreff_row( $post_id, $uid );
	if ( 'angebot' !== $e['typ'] || ( $e['autor'] !== $uid && ( ! $row || 'bestaetigt' !== $row->status ) ) ) {
		wp_die( esc_html__( 'Nur für bestätigte Teilnehmende.', 'spieltafel' ) );
	}
	$end   = $e['ende'] ? spieltreff_timestamp( $e['datum'], $e['ende'] ) : $e['start'] + 3 * HOUR_IN_SECONDS;
	$end   = $end > $e['start'] ? $end : $end + DAY_IN_SECONDS;
	$esc   = function ( $s ) {
		return str_replace( array( '\\', ';', ',', "\r\n", "\n" ), array( '\\\\', '\;', '\,', '\n', '\n' ), $s );
	};
	$where = trim( $e['adresse'] ? $e['adresse'] . ', ' . spieltreff_where( $e ) : spieltreff_where( $e ) );
	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Spieltafel//Spieltreff//DE',
		'BEGIN:VEVENT',
		'UID:spieltreff-' . $post_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
		'DTSTART:' . gmdate( 'Ymd\THis\Z', $e['start'] ),
		'DTEND:' . gmdate( 'Ymd\THis\Z', $end ),
		'SUMMARY:' . $esc( $e['titel'] ),
		'LOCATION:' . $esc( str_replace( "\n", ', ', $where ) ),
		'DESCRIPTION:' . $esc( implode( ', ', spieltreff_game_names( $e ) ) . "\n" . get_permalink( $post_id ) ),
		'URL:' . get_permalink( $post_id ),
		'END:VEVENT',
		'END:VCALENDAR',
	);
	nocache_headers();
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="spieltreff-' . $post_id . '.ics"' );
	echo implode( "\r\n", $lines ) . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Kalenderformat.
	exit;
}

function spieltreff_ics_url( $post_id ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=spieltreff_ics&id=' . $post_id ), 'spieltreff_ics_' . $post_id );
}

/** Gründe für eine Meldung. */
function spieltreff_report_reasons() {
	return array(
		__( 'Spam oder Werbung', 'spieltafel' ),
		__( 'Beleidigend oder unangemessen', 'spieltafel' ),
		__( 'Glücksspiel um Geld', 'spieltafel' ),
		__( 'Falsche oder irreführende Angaben', 'spieltafel' ),
		__( 'Anderes', 'spieltafel' ),
	);
}
