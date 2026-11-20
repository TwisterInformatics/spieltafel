<?php
/**
 * Spielwünsche: Formular auf der Webseite, gespeichert als eigener Beitragstyp.
 * Die Wünsche sind nur im Backend unter «Spielwünsche» sichtbar.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

const SPIELTAFEL_WISH_TYPE = 'spielwunsch';

/** Arten, die man im Formular auswählen kann. */
function spieltafel_wish_kinds() {
	return array(
		'karten'  => __( 'Kartenspiel', 'spieltafel' ),
		'wuerfel' => __( 'Würfelspiel', 'spieltafel' ),
		'brett'   => __( 'Brett- oder Legespiel', 'spieltafel' ),
		'sport'   => __( 'Sport oder Geschicklichkeit', 'spieltafel' ),
		'anderes' => __( 'Anderes', 'spieltafel' ),
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			SPIELTAFEL_WISH_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Spielwünsche', 'spieltafel' ),
					'singular_name' => __( 'Spielwunsch', 'spieltafel' ),
					'all_items'     => __( 'Alle Spielwünsche', 'spieltafel' ),
					'edit_item'     => __( 'Spielwunsch ansehen', 'spieltafel' ),
					'search_items'  => __( 'Spielwünsche durchsuchen', 'spieltafel' ),
					'not_found'     => __( 'Noch keine Spielwünsche.', 'spieltafel' ),
					'menu_name'     => __( 'Spielwünsche', 'spieltafel' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'menu_icon'           => 'dashicons-lightbulb',
				'menu_position'       => 25,
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);
	}
);

/** Sendet das Formular ab: für Gäste und angemeldete Benutzer. */
add_action( 'admin_post_nopriv_spieltafel_wunsch', 'spieltafel_handle_wish' );
add_action( 'admin_post_spieltafel_wunsch', 'spieltafel_handle_wish' );

function spieltafel_handle_wish() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- wird unten geprüft.
	$back = wp_get_referer();
	if ( ! $back || wp_parse_url( $back, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		$back = spieltafel_page_url( 'spielwunsch' );
	}
	$back = remove_query_arg( 'wunsch', strtok( $back, '#' ) );

	$fail = function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'wunsch', $code, $back ) . '#spielwunsch' );
		exit;
	};

	if ( ! isset( $_POST['_spieltafel_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_spieltafel_nonce'] ) ), 'spieltafel_wunsch' ) ) {
		$fail( 'abgelaufen' );
	}

	// Spamschutz: verstecktes Feld muss leer bleiben, und Menschen brauchen ein paar Sekunden.
	$started = isset( $_POST['t'] ) ? (int) $_POST['t'] : 0;
	if ( ! empty( $_POST['website'] ) || ( $started && time() - $started < 3 ) ) {
		wp_safe_redirect( add_query_arg( 'wunsch', 'danke', $back ) . '#spielwunsch' );
		exit;
	}

	// Höchstens 5 Wünsche pro Stunde und Adresse. Gespeichert wird nur ein Hash.
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key   = 'spieltafel_wish_' . substr( wp_hash( $ip ), 0, 20 );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		$fail( 'zuviel' );
	}

	$game  = isset( $_POST['spiel'] ) ? sanitize_text_field( wp_unslash( $_POST['spiel'] ) ) : '';
	$kind  = isset( $_POST['art'] ) ? sanitize_key( wp_unslash( $_POST['art'] ) ) : '';
	$how   = isset( $_POST['zaehlung'] ) ? sanitize_textarea_field( wp_unslash( $_POST['zaehlung'] ) ) : '';
	$link  = isset( $_POST['link'] ) ? esc_url_raw( wp_unslash( $_POST['link'] ), array( 'http', 'https' ) ) : '';
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$ok    = ! empty( $_POST['datenschutz'] );
	// phpcs:enable

	if ( '' === $game || mb_strlen( $game ) > 120 || ! $ok ) {
		$fail( 'fehlt' );
	}
	if ( ! isset( spieltafel_wish_kinds()[ $kind ] ) ) {
		$kind = 'anderes';
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => SPIELTAFEL_WISH_TYPE,
			'post_status' => 'private',
			'post_title'  => $game,
			'meta_input'  => array(
				'_art'      => $kind,
				'_zaehlung' => mb_substr( $how, 0, 3000 ),
				'_link'     => $link,
				'_name'     => mb_substr( $name, 0, 120 ),
				'_email'    => $email,
			),
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		$fail( 'fehler' );
	}

	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	if ( spieltafel_mod( 'wish_notify' ) ) {
		$to = spieltafel_mod( 'wish_email' );
		$to = $to ? $to : get_option( 'admin_email' );
		/* translators: %s: Name des gewünschten Spiels */
		$subject = sprintf( __( 'Neuer Spielwunsch: %s', 'spieltafel' ), $game );
		$body    = implode(
			"\n",
			array_filter(
				array(
					__( 'Spiel:', 'spieltafel' ) . ' ' . $game,
					__( 'Art:', 'spieltafel' ) . ' ' . spieltafel_wish_kinds()[ $kind ],
					$how ? __( 'Wie wird gezählt:', 'spieltafel' ) . "\n" . $how : '',
					$link ? __( 'Anleitung:', 'spieltafel' ) . ' ' . $link : '',
					$name ? __( 'Name:', 'spieltafel' ) . ' ' . $name : '',
					$email ? __( 'E-Mail:', 'spieltafel' ) . ' ' . $email : '',
					'',
					admin_url( 'edit.php?post_type=' . SPIELTAFEL_WISH_TYPE ),
				)
			)
		);
		$headers = $email ? array( 'Reply-To: ' . ( $name ? $name . ' ' : '' ) . '<' . $email . '>' ) : array();
		wp_mail( $to, $subject, $body, $headers );
	}

	wp_safe_redirect( add_query_arg( 'wunsch', 'danke', $back ) . '#spielwunsch' );
	exit;
}

/** Meldung nach dem Absenden. */
function spieltafel_wish_notice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nur Anzeige.
	$state = isset( $_GET['wunsch'] ) ? sanitize_key( $_GET['wunsch'] ) : '';
	$texts = array(
		'danke'      => array( 'ok', __( 'Danke! Dein Spielwunsch ist angekommen.', 'spieltafel' ) ),
		'fehlt'      => array( 'error', __( 'Bitte gib den Namen des Spiels an und bestätige den Datenschutz-Hinweis.', 'spieltafel' ) ),
		'abgelaufen' => array( 'error', __( 'Das Formular war zu lange offen. Bitte sende es noch einmal.', 'spieltafel' ) ),
		'zuviel'     => array( 'error', __( 'Danke für die vielen Wünsche! Bitte versuche es in einer Stunde wieder.', 'spieltafel' ) ),
		'fehler'     => array( 'error', __( 'Das hat leider nicht geklappt. Bitte versuche es später noch einmal.', 'spieltafel' ) ),
	);
	if ( isset( $texts[ $state ] ) ) {
		printf( '<div class="notice notice-%s" role="status">%s</div>', esc_attr( $texts[ $state ][0] ), esc_html( $texts[ $state ][1] ) );
	}
}

/* Backend: Spalten und Detailansicht */

add_filter(
	'manage_' . SPIELTAFEL_WISH_TYPE . '_posts_columns',
	function () {
		return array(
			'cb'       => '<input type="checkbox" />',
			'title'    => __( 'Spiel', 'spieltafel' ),
			'art'      => __( 'Art', 'spieltafel' ),
			'zaehlung' => __( 'Wie wird gezählt', 'spieltafel' ),
			'absender' => __( 'Von', 'spieltafel' ),
			'date'     => __( 'Datum', 'spieltafel' ),
		);
	}
);

add_action(
	'manage_' . SPIELTAFEL_WISH_TYPE . '_posts_custom_column',
	function ( $column, $post_id ) {
		switch ( $column ) {
			case 'art':
				$kinds = spieltafel_wish_kinds();
				$kind  = get_post_meta( $post_id, '_art', true );
				echo esc_html( $kinds[ $kind ] ?? '' );
				break;
			case 'zaehlung':
				echo esc_html( wp_trim_words( get_post_meta( $post_id, '_zaehlung', true ), 25 ) );
				$link = get_post_meta( $post_id, '_link', true );
				if ( $link ) {
					printf( '<br><a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( $link ), esc_html__( 'Anleitung', 'spieltafel' ) );
				}
				break;
			case 'absender':
				$name  = get_post_meta( $post_id, '_name', true );
				$email = get_post_meta( $post_id, '_email', true );
				echo esc_html( $name ? $name : '–' );
				if ( $email ) {
					printf( '<br><a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
				}
				break;
		}
	},
	10,
	2
);

add_action(
	'add_meta_boxes_' . SPIELTAFEL_WISH_TYPE,
	function () {
		add_meta_box(
			'spieltafel_wish',
			__( 'Angaben aus dem Formular', 'spieltafel' ),
			function ( $post ) {
				$kinds = spieltafel_wish_kinds();
				$rows  = array(
					__( 'Art', 'spieltafel' )              => esc_html( $kinds[ get_post_meta( $post->ID, '_art', true ) ] ?? '' ),
					__( 'Wie wird gezählt', 'spieltafel' ) => nl2br( esc_html( get_post_meta( $post->ID, '_zaehlung', true ) ) ),
					__( 'Anleitung', 'spieltafel' )        => make_clickable( esc_url( get_post_meta( $post->ID, '_link', true ) ) ),
					__( 'Name', 'spieltafel' )             => esc_html( get_post_meta( $post->ID, '_name', true ) ),
					__( 'E-Mail', 'spieltafel' )           => make_clickable( esc_html( get_post_meta( $post->ID, '_email', true ) ) ),
				);
				echo '<table class="form-table" role="presentation">';
				foreach ( $rows as $label => $value ) {
					printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $label ), $value ? $value : '–' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oben escaped.
				}
				echo '</table>';
			},
			null,
			'normal',
			'high'
		);
	}
);

/** Anzahl neuer Wünsche der letzten 7 Tage als Zahl im Menü. */
add_action(
	'admin_menu',
	function () {
		global $menu;
		$recent = get_posts(
			array(
				'post_type'      => SPIELTAFEL_WISH_TYPE,
				'post_status'    => 'private',
				'date_query'     => array( array( 'after' => '7 days ago' ) ),
				'fields'         => 'ids',
				'posts_per_page' => 99,
			)
		);
		if ( ! $recent ) {
			return;
		}
		foreach ( $menu as $i => $item ) {
			if ( 'edit.php?post_type=' . SPIELTAFEL_WISH_TYPE === $item[2] ) {
				$menu[ $i ][0] .= sprintf( ' <span class="awaiting-mod">%d</span>', count( $recent ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}
	}
);

/** Vorschlag für die Datenschutzerklärung (Einstellungen → Datenschutz). */
add_action(
	'admin_init',
	function () {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		wp_add_privacy_policy_content(
			__( 'Spieltafel', 'spieltafel' ),
			wp_kses_post(
				'<h2>' . __( 'Spielwünsche', 'spieltafel' ) . '</h2><p>' .
				__( 'Wenn du über das Formular ein Spiel wünschst, speichern wir den Namen des Spiels, deine Angaben zur Zählweise und, falls angegeben, einen Link, deinen Namen und deine E-Mail-Adresse. Wir nutzen die Angaben nur, um die App zu erweitern und dir allenfalls zu antworten. Zum Schutz vor Missbrauch wird deine IP-Adresse eine Stunde lang als verschlüsselter Wert (Hash) zwischengespeichert. Wünsche löschen wir, sobald wir sie nicht mehr brauchen, auf Anfrage sofort.', 'spieltafel' ) .
				'</p><h2>' . __( 'Die App', 'spieltafel' ) . '</h2><p>' .
				__( 'Die App Spieltafel speichert alle Partien ausschliesslich lokal auf deinem Gerät. Sie hat kein Konto, keinen Server, keine Werbung und keine Analyse- oder Tracking-Dienste.', 'spieltafel' ) .
				'</p>'
			)
		);
	}
);
