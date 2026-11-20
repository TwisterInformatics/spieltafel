<?php
/**
 * Spiele aus der App. Die Daten stehen in data/games.json und werden aus
 * src/domain/templates.ts erzeugt (npm run website:data im App-Repo).
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/** Alle Spiele in der Reihenfolge der App. */
function spieltafel_games() {
	static $games = null;
	if ( null === $games ) {
		$raw   = file_get_contents( SPIELTAFEL_DIR . '/data/games.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$games = json_decode( (string) $raw, true );
		if ( ! is_array( $games ) ) {
			$games = array();
		}
		/** Erlaubt Plugins, die Spielliste zu ergänzen oder zu ändern. */
		$games = apply_filters( 'spieltafel_games', $games );
	}
	return $games;
}

function spieltafel_game( $id ) {
	foreach ( spieltafel_games() as $game ) {
		if ( $game['id'] === $id ) {
			return $game;
		}
	}
	return null;
}

/** Spiele, die schon in der App im Store sind (ohne «bald»). */
function spieltafel_available_games() {
	return array_values(
		array_filter(
			spieltafel_games(),
			function ( $game ) {
				return empty( $game['upcoming'] );
			}
		)
	);
}

/** Kategorien in der Reihenfolge, in der sie zuerst vorkommen. */
function spieltafel_categories() {
	return array_values( array_unique( wp_list_pluck( spieltafel_games(), 'category' ) ) );
}

/** Adresse der Anleitung eines Spiels: /spiele/<id>/ */
function spieltafel_game_url( $game ) {
	if ( get_option( 'permalink_structure' ) ) {
		return home_url( user_trailingslashit( 'spiele/' . $game['id'] ) );
	}
	return add_query_arg( 'spieltafel_spiel', $game['id'], home_url( '/' ) );
}

/** Anfangsbuchstabe für das farbige Feld. */
function spieltafel_initial( $game ) {
	$name = preg_replace( '/^Jass\s+/u', '', $game['name'] );
	return mb_strtoupper( mb_substr( $name, 0, 1 ) );
}

/* Eigene Seite pro Spiel unter /spiele/<id>/ */

add_action(
	'init',
	function () {
		add_rewrite_rule( '^spiele/([a-z0-9-]+)/?$', 'index.php?spieltafel_spiel=$matches[1]', 'top' );
	}
);

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'spieltafel_spiel';
		return $vars;
	}
);

/** Das aktuell angezeigte Spiel, oder null. */
function spieltafel_current_game() {
	$id = get_query_var( 'spieltafel_spiel' );
	return $id ? spieltafel_game( sanitize_key( $id ) ) : null;
}

add_action(
	'template_redirect',
	function () {
		if ( ! get_query_var( 'spieltafel_spiel' ) ) {
			return;
		}
		global $wp_query;
		if ( ! spieltafel_current_game() ) {
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		status_header( 200 );
	}
);

add_filter(
	'template_include',
	function ( $template ) {
		if ( spieltafel_current_game() ) {
			return SPIELTAFEL_DIR . '/single-spiel.php';
		}
		return $template;
	}
);

add_filter(
	'document_title_parts',
	function ( $parts ) {
		$game = spieltafel_current_game();
		if ( $game ) {
			/* translators: %s: Name des Spiels */
			$parts['title'] = sprintf( __( '%s: Kurzanleitung und Punkte zählen', 'spieltafel' ), $game['name'] );
		}
		return $parts;
	}
);

add_action(
	'wp_head',
	function () {
		$game = spieltafel_current_game();
		if ( $game ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $game['name'] . ': ' . $game['guide'][0] ) );
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( spieltafel_game_url( $game ) ) );
		}
	},
	1
);

/** Spielseiten in der XML-Sitemap von WordPress. */
add_action(
	'init',
	function () {
		if ( ! function_exists( 'wp_register_sitemap_provider' ) ) {
			return;
		}
		require_once SPIELTAFEL_DIR . '/inc/class-sitemap.php';
		wp_register_sitemap_provider( 'spiele', new Spieltafel_Sitemap() );
	}
);
