<?php
/**
 * Spamschutz ohne fremde Dienste: Rechenaufgabe für den Browser (Proof of Work,
 * nach dem offenen ALTCHA-Verfahren). Der Browser sucht im Hintergrund eine Zahl,
 * deren SHA-256-Wert zur Aufgabe passt. Das dauert für Menschen etwa eine Sekunde,
 * macht Massen-Anmeldungen durch Bots aber teuer. Keine Cookies, kein Tracking,
 * keine Daten an Dritte (passt zum revDSG).
 *
 * Dazu kommen: verstecktes Feld (Honeypot), Mindestzeit und Begrenzungen pro Adresse.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

const SPIELTREFF_POW_MAX = 60000;

function spieltreff_pow_key() {
	return wp_salt( 'nonce' ) . 'spieltreff-pow';
}

/** Neue Aufgabe: 20 Minuten gültig. */
function spieltreff_pow_challenge() {
	$salt      = bin2hex( random_bytes( 12 ) ) . '?expires=' . ( time() + 20 * MINUTE_IN_SECONDS );
	$number    = random_int( 1000, SPIELTREFF_POW_MAX );
	$challenge = hash( 'sha256', $salt . $number );
	return array(
		'algorithm' => 'SHA-256',
		'challenge' => $challenge,
		'maxnumber' => SPIELTREFF_POW_MAX,
		'salt'      => $salt,
		'signature' => hash_hmac( 'sha256', $challenge, spieltreff_pow_key() ),
	);
}

/** Aufgabe per Abruf, damit Seiten-Caches keine alten Aufgaben ausliefern. */
add_action( 'wp_ajax_spieltreff_pow', 'spieltreff_pow_ajax' );
add_action( 'wp_ajax_nopriv_spieltreff_pow', 'spieltreff_pow_ajax' );
function spieltreff_pow_ajax() {
	nocache_headers();
	wp_send_json( spieltreff_pow_challenge() );
}

/** Prüft die Lösung aus dem Feld «st_pow». Jede Lösung gilt nur einmal. */
function spieltreff_pow_verify() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Teil der Formularprüfung.
	$raw = isset( $_POST['st_pow'] ) ? sanitize_text_field( wp_unslash( $_POST['st_pow'] ) ) : '';
	if ( '' === $raw ) {
		return false;
	}
	$data = json_decode( (string) base64_decode( $raw, true ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( ! is_array( $data ) || ! isset( $data['salt'], $data['number'], $data['challenge'], $data['signature'] ) ) {
		return false;
	}
	$salt   = (string) $data['salt'];
	$number = (int) $data['number'];
	if ( ! preg_match( '/\?expires=(\d+)$/', $salt, $m ) || (int) $m[1] < time() ) {
		return false;
	}
	$challenge = hash( 'sha256', $salt . $number );
	if ( ! hash_equals( $challenge, (string) $data['challenge'] ) ) {
		return false;
	}
	if ( ! hash_equals( hash_hmac( 'sha256', $challenge, spieltreff_pow_key() ), (string) $data['signature'] ) ) {
		return false;
	}
	$used = 'st_pow_' . substr( $challenge, 0, 32 );
	if ( get_transient( $used ) ) {
		return false;
	}
	set_transient( $used, 1, 25 * MINUTE_IN_SECONDS );
	return true;
}

/** Felder für ein Formular: Honeypot, Zeitstempel und Rechenaufgabe. */
function spieltreff_captcha_fields() {
	?>
	<div class="hp-field" aria-hidden="true">
		<label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
	</div>
	<input type="hidden" name="t" value="<?php echo esc_attr( time() ); ?>">
	<?php
	spieltreff_pow_field();
}

/** Nur die Rechenaufgabe, für Formulare mit eigenem Honeypot. */
function spieltreff_pow_field() {
	?>
	<div class="captcha" data-pow="<?php echo esc_url( admin_url( 'admin-ajax.php?action=spieltreff_pow' ) ); ?>">
		<input type="hidden" name="st_pow" value="">
		<span class="captcha-box" aria-hidden="true"></span>
		<span class="captcha-text" role="status" data-wait="<?php esc_attr_e( 'Spamschutz wird geprüft …', 'spieltafel' ); ?>" data-done="<?php esc_attr_e( 'Kein Roboter, danke!', 'spieltafel' ); ?>" data-fail="<?php esc_attr_e( 'Spamschutz fehlgeschlagen. Bitte Seite neu laden.', 'spieltafel' ); ?>"><?php esc_html_e( 'Spamschutz wird geprüft …', 'spieltafel' ); ?></span>
		<noscript><span class="captcha-text"><?php esc_html_e( 'Bitte JavaScript einschalten, das Formular braucht es für den Spamschutz.', 'spieltafel' ); ?></span></noscript>
	</div>
	<?php
}

/** Honeypot und Mindestzeit (Sekunden). */
function spieltreff_bot_check( $min_seconds = 3 ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST['website'] ) ) {
		return false;
	}
	$started = isset( $_POST['t'] ) ? (int) $_POST['t'] : 0;
	// phpcs:enable
	return ! ( $started && time() - $started < $min_seconds );
}

/** Begrenzung pro Adresse (nur als Hash gespeichert). Gibt false zurück, wenn das Limit erreicht ist. */
function spieltreff_rate_limit( $bucket, $limit, $seconds, $count = true ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'st_rl_' . $bucket . '_' . substr( wp_hash( $ip ), 0, 16 );
	$n   = (int) get_transient( $key );
	if ( $n >= $limit ) {
		return false;
	}
	if ( $count ) {
		set_transient( $key, $n + 1, $seconds );
	}
	return true;
}

/* Schutz für alle Anmeldungen, auch über wp-login.php: nach 8 Fehlversuchen in 15 Minuten gesperrt. */

add_action(
	'wp_login_failed',
	function () {
		spieltreff_rate_limit( 'login', 1000, 15 * MINUTE_IN_SECONDS );
	}
);

add_filter(
	'authenticate',
	function ( $user ) {
		if ( ! spieltreff_rate_limit( 'login', 8, 15 * MINUTE_IN_SECONDS, false ) ) {
			return new WP_Error( 'spieltreff_zuviel', __( 'Zu viele Versuche. Bitte warte 15 Minuten.', 'spieltafel' ) );
		}
		return $user;
	},
	99
);
