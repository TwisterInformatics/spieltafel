<?php
/**
 * Spieltreff: Registrieren, E-Mail bestätigen, Anmelden, Konto, Daten herunterladen und löschen.
 *
 * Konten sind normale WordPress-Benutzer mit der Rolle «Spieler». Sie sehen das
 * Backend nicht, nur die Seiten auf der Webseite. Angemeldet wird mit E-Mail und
 * Passwort; der Anzeigename ist das Einzige, was andere sehen.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/** Nur eigene Adressen als Ziel nach dem Anmelden. */
function spieltreff_safe_target( $url ) {
	$url = $url ? esc_url_raw( rawurldecode( $url ) ) : '';
	return $url && wp_validate_redirect( $url, false ) ? $url : spieltreff_url();
}

/* Registrieren */

add_action( 'admin_post_nopriv_spieltreff_register', 'spieltreff_handle_register' );
add_action( 'admin_post_spieltreff_register', 'spieltreff_handle_register' );

function spieltreff_handle_register() {
	$back = spieltreff_url( 'registrieren' );
	if ( is_user_logged_in() ) {
		wp_safe_redirect( spieltreff_url( 'konto' ) );
		exit;
	}
	if ( ! spieltreff_registration_open() ) {
		spieltreff_redirect( $back, 'geschlossen' );
	}
	spieltreff_check_nonce( 'spieltreff_register', $back );
	if ( ! spieltreff_bot_check( 4 ) ) {
		spieltreff_redirect( $back, 'registriert' ); // Bots merken nichts.
	}
	if ( ! spieltreff_pow_verify() ) {
		spieltreff_redirect( $back, 'captcha' );
	}
	if ( ! spieltreff_rate_limit( 'register', 3, HOUR_IN_SECONDS ) ) {
		spieltreff_redirect( $back, 'zuviel' );
	}

	$name  = spieltreff_post_field( 'anzeigename', 'text', 30 );
	$email = sanitize_email( spieltreff_post_field( 'email', 'text', 100 ) );
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwort wird nicht verändert.
	$pass   = isset( $_POST['passwort'] ) ? (string) wp_unslash( $_POST['passwort'] ) : '';
	$kanton = strtoupper( spieltreff_post_field( 'kanton', 'text', 2 ) );
	$ok     = spieltreff_post_field( 'regeln', 'bool' ) && spieltreff_post_field( 'alter', 'bool' );

	$name = preg_replace( '/\s+/u', ' ', $name );
	if ( mb_strlen( $name ) < 3 || ! is_email( $email ) || ! $ok || preg_match( '/[<>@]|https?:/i', $name ) ) {
		spieltreff_redirect( $back, 'fehlt' );
	}
	if ( mb_strlen( $pass ) < 10 ) {
		spieltreff_redirect( $back, 'passwort_kurz' );
	}
	if ( spieltreff_name_taken( $name ) ) {
		spieltreff_redirect( $back, 'name_vergeben' );
	}

	$existing = get_user_by( 'email', $email );
	if ( $existing ) {
		// Nicht verraten, dass es die Adresse schon gibt. Stattdessen Hinweis an die Adresse.
		spieltreff_mail(
			(int) $existing->ID,
			__( 'Registrierung mit deiner E-Mail', 'spieltafel' ),
			array(
				__( 'Jemand wollte sich mit deiner E-Mail-Adresse im Spieltreff registrieren. Du hast schon ein Konto.', 'spieltafel' ),
				__( 'Warst du das und hast dein Passwort vergessen? Hier kannst du ein neues setzen:', 'spieltafel' ),
				wp_lostpassword_url( spieltreff_url( 'anmelden' ) ),
				'',
				__( 'Warst du es nicht, kannst du diese E-Mail ignorieren.', 'spieltafel' ),
			)
		);
		spieltreff_redirect( $back, 'registriert' );
	}

	$login   = 'spieler_' . strtolower( wp_generate_password( 10, false ) );
	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_pass'    => $pass,
			'user_email'   => $email,
			'display_name' => $name,
			'nickname'     => $name,
			'role'         => SPIELTREFF_ROLE,
		)
	);
	if ( is_wp_error( $user_id ) ) {
		spieltreff_redirect( $back, 'fehler' );
	}
	update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
	if ( isset( spieltreff_regions()[ $kanton ] ) ) {
		update_user_meta( $user_id, '_st_kanton', $kanton );
	}
	update_user_meta( $user_id, '_st_zustimmung', time() );
	spieltreff_send_verification( $user_id );
	spieltreff_redirect( $back, 'registriert' );
}

/** Gibt es den Anzeigenamen schon (Gross-/Kleinschreibung egal)? */
function spieltreff_name_taken( $name, $except = 0 ) {
	global $wpdb;
	$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE LOWER(display_name) = LOWER(%s) AND ID <> %d LIMIT 1", $name, $except ) ); // phpcs:ignore WordPress.DB
	return $id > 0;
}

/** Link zum Bestätigen der E-Mail, 7 Tage gültig. Gespeichert wird nur ein Hash. */
function spieltreff_send_verification( $user_id ) {
	$token = wp_generate_password( 32, false );
	update_user_meta( $user_id, '_st_verify', wp_hash_password( $token ) );
	update_user_meta( $user_id, '_st_verify_until', time() + 7 * DAY_IN_SECONDS );
	spieltreff_mail(
		(int) $user_id,
		__( 'Bitte bestätige deine E-Mail', 'spieltafel' ),
		array(
			__( 'Willkommen im Spieltreff! Bitte bestätige deine E-Mail-Adresse mit einem Klick auf diesen Link:', 'spieltafel' ),
			spieltreff_url(
				'bestaetigen',
				array(
					'u' => $user_id,
					'k' => $token,
				)
			),
			'',
			__( 'Der Link gilt 7 Tage. Ohne Bestätigung löschen wir das Konto danach wieder.', 'spieltafel' ),
			__( 'Hast du dich nicht registriert? Dann ignoriere diese E-Mail einfach.', 'spieltafel' ),
		)
	);
}

/** Klick auf den Link in der E-Mail. */
add_action(
	'template_redirect',
	function () {
		if ( 'bestaetigen' !== get_query_var( 'spieltreff_view' ) || empty( $_GET['u'] ) || empty( $_GET['k'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$uid   = (int) $_GET['u']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = sanitize_text_field( wp_unslash( $_GET['k'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$hash  = get_user_meta( $uid, '_st_verify', true );
		$until = (int) get_user_meta( $uid, '_st_verify_until', true );
		if ( ! $hash || $until < time() || ! wp_check_password( $token, $hash ) ) {
			spieltreff_redirect( spieltreff_url( 'bestaetigen' ), 'link_ungueltig' );
		}
		delete_user_meta( $uid, '_st_verify' );
		delete_user_meta( $uid, '_st_verify_until' );
		wp_set_current_user( $uid );
		wp_set_auth_cookie( $uid, false );
		spieltreff_redirect( spieltreff_url(), 'verifiziert' );
	},
	5
);

/** Neuen Bestätigungslink anfordern. */
add_action( 'admin_post_nopriv_spieltreff_resend', 'spieltreff_handle_resend' );
add_action( 'admin_post_spieltreff_resend', 'spieltreff_handle_resend' );
function spieltreff_handle_resend() {
	$back = spieltreff_url( 'bestaetigen' );
	spieltreff_check_nonce( 'spieltreff_resend', $back );
	if ( ! spieltreff_bot_check() || ! spieltreff_pow_verify() ) {
		spieltreff_redirect( $back, 'captcha' );
	}
	if ( ! spieltreff_rate_limit( 'resend', 3, HOUR_IN_SECONDS ) ) {
		spieltreff_redirect( $back, 'zuviel' );
	}
	$user = get_user_by( 'email', sanitize_email( spieltreff_post_field( 'email', 'text', 100 ) ) );
	if ( $user && get_user_meta( $user->ID, '_st_verify', true ) ) {
		spieltreff_send_verification( $user->ID );
	}
	spieltreff_redirect( $back, 'link_gesendet' );
}

/** Wer ein neues Passwort über die E-Mail setzt, hat die Adresse damit auch bestätigt. */
add_action(
	'after_password_reset',
	function ( $user ) {
		delete_user_meta( $user->ID, '_st_verify' );
		delete_user_meta( $user->ID, '_st_verify_until' );
	}
);

/** Unbestätigte und gesperrte Konten können sich nirgends anmelden, auch nicht über wp-login.php. */
add_filter(
	'wp_authenticate_user',
	function ( $user ) {
		if ( $user instanceof WP_User ) {
			if ( get_user_meta( $user->ID, '_st_gesperrt', true ) ) {
				return new WP_Error( 'spieltreff_gesperrt', __( 'Dieses Konto ist gesperrt.', 'spieltafel' ) );
			}
			if ( get_user_meta( $user->ID, '_st_verify', true ) ) {
				return new WP_Error( 'spieltreff_unbestaetigt', __( 'Bitte bestätige zuerst deine E-Mail-Adresse.', 'spieltafel' ) );
			}
		}
		return $user;
	}
);

/* Anmelden und Abmelden */

add_action( 'admin_post_nopriv_spieltreff_login', 'spieltreff_handle_login' );
add_action( 'admin_post_spieltreff_login', 'spieltreff_handle_login' );

function spieltreff_handle_login() {
	$target = spieltreff_safe_target( spieltreff_post_field( 'zurueck', 'text', 500 ) );
	$back   = spieltreff_url( 'anmelden', array( 'zurueck' => rawurlencode( $target ) ) );
	spieltreff_check_nonce( 'spieltreff_login', $back );
	if ( ! spieltreff_bot_check( 1 ) || ! spieltreff_pow_verify() ) {
		spieltreff_redirect( $back, 'captcha' );
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$pass = isset( $_POST['passwort'] ) ? (string) wp_unslash( $_POST['passwort'] ) : '';
	$user = wp_signon(
		array(
			'user_login'    => spieltreff_post_field( 'email', 'text', 100 ),
			'user_password' => $pass,
			'remember'      => spieltreff_post_field( 'merken', 'bool' ),
		),
		is_ssl()
	);
	if ( is_wp_error( $user ) ) {
		$codes = array(
			'spieltreff_gesperrt'     => 'gesperrt',
			'spieltreff_unbestaetigt' => 'unbestaetigt',
			'spieltreff_zuviel'       => 'zuviel',
		);
		spieltreff_redirect( $back, $codes[ $user->get_error_code() ] ?? 'login' );
	}
	spieltreff_redirect( $target, 'willkommen' );
}

function spieltreff_logout_url() {
	return wp_nonce_url( admin_url( 'admin-post.php?action=spieltreff_logout' ), 'spieltreff_logout' );
}

add_action(
	'admin_post_spieltreff_logout',
	function () {
		check_admin_referer( 'spieltreff_logout' );
		wp_logout();
		spieltreff_redirect( spieltreff_url(), 'ausgeloggt' );
	}
);

/* Spieler sehen das Backend und die Admin-Leiste nicht */

add_action(
	'admin_init',
	function () {
		if ( wp_doing_ajax() || ! is_user_logged_in() || current_user_can( 'edit_posts' ) ) {
			return;
		}
		global $pagenow;
		if ( 'admin-post.php' === $pagenow ) {
			return;
		}
		wp_safe_redirect( spieltreff_url( 'konto' ) );
		exit;
	}
);

add_filter(
	'show_admin_bar',
	function ( $show ) {
		return current_user_can( 'edit_posts' ) ? $show : false;
	}
);

/** Nach dem Passwort-Zurücksetzen auf wp-login.php: Spieler zum Spieltreff schicken. */
add_filter(
	'login_redirect',
	function ( $redirect, $requested, $user ) {
		if ( $user instanceof WP_User && ! user_can( $user, 'edit_posts' ) ) {
			return spieltreff_url();
		}
		return $redirect;
	},
	10,
	3
);

/* Konto */

add_action( 'admin_post_spieltreff_account', 'spieltreff_handle_account' );

function spieltreff_handle_account() {
	$back = spieltreff_url( 'konto' );
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( spieltreff_url( 'anmelden' ) );
		exit;
	}
	spieltreff_check_nonce( 'spieltreff_account', $back );
	$uid  = get_current_user_id();
	$user = wp_get_current_user();
	$do   = spieltreff_post_field( 'do', 'text', 20 );
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$pass = isset( $_POST['passwort'] ) ? (string) wp_unslash( $_POST['passwort'] ) : '';

	switch ( $do ) {
		case 'profil':
			$name   = preg_replace( '/\s+/u', ' ', spieltreff_post_field( 'anzeigename', 'text', 30 ) );
			$kanton = strtoupper( spieltreff_post_field( 'kanton', 'text', 2 ) );
			if ( mb_strlen( $name ) < 3 || preg_match( '/[<>@]|https?:/i', $name ) ) {
				spieltreff_redirect( $back, 'fehlt' );
			}
			if ( spieltreff_name_taken( $name, $uid ) ) {
				spieltreff_redirect( $back, 'name_vergeben' );
			}
			wp_update_user(
				array(
					'ID'           => $uid,
					'display_name' => $name,
					'nickname'     => $name,
				)
			);
			if ( isset( spieltreff_regions()[ $kanton ] ) ) {
				update_user_meta( $uid, '_st_kanton', $kanton );
			} else {
				delete_user_meta( $uid, '_st_kanton' );
			}
			spieltreff_redirect( $back, 'gespeichert' );
			break;

		case 'passwort':
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$new = isset( $_POST['neu'] ) ? (string) wp_unslash( $_POST['neu'] ) : '';
			if ( ! wp_check_password( $pass, $user->user_pass, $uid ) ) {
				spieltreff_redirect( $back, 'passwort_falsch', 'passwort' );
			}
			if ( mb_strlen( $new ) < 10 ) {
				spieltreff_redirect( $back, 'passwort_kurz', 'passwort' );
			}
			wp_set_password( $new, $uid );
			wp_set_auth_cookie( $uid, false );
			spieltreff_redirect( $back, 'passwort' );
			break;

		case 'export':
			nocache_headers();
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="spieltreff-daten.json"' );
			echo wp_json_encode( spieltreff_user_export( $uid ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			exit;

		case 'loeschen':
			if ( ! wp_check_password( $pass, $user->user_pass, $uid ) ) {
				spieltreff_redirect( $back, 'passwort_falsch', 'loeschen' );
			}
			if ( user_can( $uid, 'edit_posts' ) ) {
				// Redaktions- und Admin-Konten nur im Backend löschen.
				spieltreff_redirect( $back, 'nicht_moeglich', 'loeschen' );
			}
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_logout();
			wp_delete_user( $uid );
			spieltreff_redirect( spieltreff_url(), 'konto_weg' );
			break;
	}
	spieltreff_redirect( $back, 'nicht_moeglich' );
}

/** Alle Daten einer Person (Auskunftsrecht nach revDSG). */
function spieltreff_user_export( $uid ) {
	global $wpdb;
	$user    = get_userdata( $uid );
	$regions = spieltreff_regions();
	$agreed  = (int) get_user_meta( $uid, '_st_zustimmung', true );
	$data    = array(
		'konto'       => array(
			'anzeigename'  => $user->display_name,
			'email'        => $user->user_email,
			'region'       => $regions[ get_user_meta( $uid, '_st_kanton', true ) ] ?? '',
			'registriert'  => $user->user_registered,
			'zustimmung'   => $agreed ? gmdate( 'c', $agreed ) : '',
		),
		'eintraege'   => array(),
		'anmeldungen' => array(),
	);
	$posts   = get_posts(
		array(
			'post_type'      => SPIELTREFF_TYPE,
			'author'         => $uid,
			'post_status'    => 'any',
			'posts_per_page' => -1,
		)
	);
	foreach ( $posts as $post ) {
		$e = spieltreff_entry( $post );
		unset( $e['id'], $e['autor'], $e['start'], $e['sichtbar'] );
		$e['spiele']         = spieltreff_game_names( $e );
		$e['adresse_url']    = get_permalink( $post );
		$data['eintraege'][] = $e;
	}
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . spieltreff_table() . ' WHERE user_id = %d', $uid ) ); // phpcs:ignore WordPress.DB
	foreach ( $rows as $row ) {
		$data['anmeldungen'][] = array(
			'eintrag'   => get_the_title( (int) $row->post_id ),
			'status'    => $row->status,
			'nachricht' => $row->nachricht,
			'seit'      => $row->created,
		);
	}
	return $data;
}

/* Datenschutz-Werkzeuge von WordPress (Werkzeuge → Personenbezogene Daten exportieren/löschen) */

add_filter(
	'wp_privacy_personal_data_exporters',
	function ( $exporters ) {
		$exporters['spieltreff'] = array(
			'exporter_friendly_name' => __( 'Spieltreff', 'spieltafel' ),
			'callback'               => function ( $email ) {
				$user = get_user_by( 'email', $email );
				$out  = array();
				if ( $user ) {
					$data = spieltreff_user_export( $user->ID );
					foreach ( $data['eintraege'] as $i => $e ) {
						$out[] = array(
							'group_id'    => 'spieltreff-eintraege',
							'group_label' => __( 'Spieltreff-Einträge', 'spieltafel' ),
							'item_id'     => 'eintrag-' . $i,
							'data'        => array(
								array( 'name' => __( 'Titel', 'spieltafel' ), 'value' => $e['titel'] ),
								array( 'name' => __( 'Typ', 'spieltafel' ), 'value' => $e['typ'] ),
								array( 'name' => __( 'Text', 'spieltafel' ), 'value' => $e['text'] ),
								array( 'name' => __( 'Ort', 'spieltafel' ), 'value' => $e['ort'] . ' ' . $e['adresse'] ),
							),
						);
					}
					foreach ( $data['anmeldungen'] as $i => $a ) {
						$out[] = array(
							'group_id'    => 'spieltreff-anmeldungen',
							'group_label' => __( 'Spieltreff-Anmeldungen', 'spieltafel' ),
							'item_id'     => 'anmeldung-' . $i,
							'data'        => array(
								array( 'name' => __( 'Eintrag', 'spieltafel' ), 'value' => $a['eintrag'] ),
								array( 'name' => __( 'Status', 'spieltafel' ), 'value' => $a['status'] ),
								array( 'name' => __( 'Nachricht', 'spieltafel' ), 'value' => $a['nachricht'] ),
							),
						);
					}
				}
				return array( 'data' => $out, 'done' => true );
			},
		);
		return $exporters;
	}
);

add_filter(
	'wp_privacy_personal_data_erasers',
	function ( $erasers ) {
		$erasers['spieltreff'] = array(
			'eraser_friendly_name' => __( 'Spieltreff', 'spieltafel' ),
			'callback'             => function ( $email ) {
				global $wpdb;
				$user    = get_user_by( 'email', $email );
				$removed = false;
				if ( $user ) {
					$posts = get_posts(
						array(
							'post_type'      => SPIELTREFF_TYPE,
							'author'         => $user->ID,
							'post_status'    => 'any',
							'posts_per_page' => -1,
							'fields'         => 'ids',
						)
					);
					foreach ( $posts as $id ) {
						wp_delete_post( $id, true );
					}
					$rows    = $wpdb->delete( spieltreff_table(), array( 'user_id' => $user->ID ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$removed = $posts || $rows;
				}
				return array(
					'items_removed'  => (bool) $removed,
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			},
		);
		return $erasers;
	}
);
