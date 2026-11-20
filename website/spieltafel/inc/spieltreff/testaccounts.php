<?php
/**
 * Testkonten im Frontend: Wer Benutzer anlegen darf (Admin), erfasst unter
 * «Mein Konto» neue Spieler-Konten zum Testen, ohne ins Backend zu wechseln.
 * Die Konten sind sofort bestätigt und als Testkonto markiert, damit man sie
 * vor dem Start mit einem Klick wieder löschen kann.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

function spieltreff_can_manage_testers() {
	return current_user_can( 'create_users' ) && current_user_can( 'delete_users' );
}

/** Alle Testkonten, neueste zuerst. */
function spieltreff_test_accounts() {
	return get_users(
		array(
			'meta_key' => '_st_testkonto', // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'  => 'registered',
			'order'    => 'DESC',
			'number'   => 200,
		)
	);
}

add_action( 'admin_post_spieltreff_testkonto', 'spieltreff_handle_test_account' );

function spieltreff_handle_test_account() {
	$back = spieltreff_url( 'konto' );
	if ( ! spieltreff_can_manage_testers() ) {
		spieltreff_redirect( $back, 'nicht_moeglich' );
	}
	spieltreff_check_nonce( 'spieltreff_testkonto', $back );
	$do = spieltreff_post_field( 'do', 'text', 20 );

	if ( 'loeschen' === $do || 'alle_loeschen' === $do ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		$ids = 'alle_loeschen' === $do ? wp_list_pluck( spieltreff_test_accounts(), 'ID' ) : array( spieltreff_post_field( 'user', 'int' ) );
		foreach ( $ids as $id ) {
			// Nur echte Testkonten ohne Redaktionsrechte.
			if ( $id && get_user_meta( $id, '_st_testkonto', true ) && ! user_can( $id, 'edit_posts' ) ) {
				wp_delete_user( (int) $id );
			}
		}
		spieltreff_redirect( $back, 'test_geloescht', 'testkonten' );
	}

	$name  = preg_replace( '/\s+/u', ' ', spieltreff_post_field( 'anzeigename', 'text', 30 ) );
	$email = sanitize_email( spieltreff_post_field( 'email', 'text', 100 ) );
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwort wird nicht verändert.
	$pass   = isset( $_POST['passwort'] ) ? (string) wp_unslash( $_POST['passwort'] ) : '';
	$kanton = strtoupper( spieltreff_post_field( 'kanton', 'text', 2 ) );
	$send   = spieltreff_post_field( 'senden', 'bool' );

	if ( mb_strlen( $name ) < 3 || preg_match( '/[<>@]|https?:/i', $name ) ) {
		spieltreff_redirect( $back, 'fehlt', 'testkonten' );
	}
	if ( spieltreff_name_taken( $name ) ) {
		spieltreff_redirect( $back, 'name_vergeben', 'testkonten' );
	}
	if ( '' === $email ) {
		// Ohne E-Mail: Platzhalter, der nie zugestellt wird (.invalid ist dafür reserviert).
		$email = 'test-' . strtolower( wp_generate_password( 8, false ) ) . '@spieltreff.invalid';
		$send  = false;
	} elseif ( ! is_email( $email ) || email_exists( $email ) ) {
		spieltreff_redirect( $back, 'email_vergeben', 'testkonten' );
	}
	if ( '' === $pass ) {
		$pass = wp_generate_password( 14, false );
	} elseif ( mb_strlen( $pass ) < 10 ) {
		spieltreff_redirect( $back, 'passwort_kurz', 'testkonten' );
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => 'test_' . strtolower( wp_generate_password( 8, false ) ),
			'user_pass'    => $pass,
			'user_email'   => $email,
			'display_name' => $name,
			'nickname'     => $name,
			'role'         => SPIELTREFF_ROLE,
		)
	);
	if ( is_wp_error( $user_id ) ) {
		spieltreff_redirect( $back, 'fehler', 'testkonten' );
	}
	update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
	update_user_meta( $user_id, '_st_testkonto', get_current_user_id() );
	update_user_meta( $user_id, '_st_zustimmung', time() );
	if ( isset( spieltreff_regions()[ $kanton ] ) ) {
		update_user_meta( $user_id, '_st_kanton', $kanton );
	}
	if ( $send ) {
		spieltreff_mail(
			(int) $user_id,
			__( 'Dein Testzugang zum Spieltreff', 'spieltafel' ),
			array(
				__( 'Für dich wurde ein Testkonto im Spieltreff angelegt. Danke, dass du mittestest!', 'spieltafel' ),
				'',
				__( 'Anmelden:', 'spieltafel' ) . ' ' . spieltreff_url( 'anmelden' ),
				__( 'E-Mail:', 'spieltafel' ) . ' ' . $email,
				__( 'Passwort:', 'spieltafel' ) . ' ' . $pass,
				'',
				__( 'Bitte ändere das Passwort nach der ersten Anmeldung unter «Mein Konto».', 'spieltafel' ),
			)
		);
	}
	// Zugangsdaten einmal anzeigen (5 Minuten, nur für diesen Admin).
	set_transient(
		'st_test_' . get_current_user_id(),
		array(
			'name'  => $name,
			'email' => $email,
			'pass'  => $pass,
			'mail'  => $send,
		),
		5 * MINUTE_IN_SECONDS
	);
	spieltreff_redirect( $back, 'test_erstellt', 'testkonten' );
}
