<?php
/**
 * Spieltafel Theme.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

define( 'SPIELTAFEL_VERSION', '1.1.0' );
define( 'SPIELTAFEL_DIR', get_template_directory() );
define( 'SPIELTAFEL_URI', get_template_directory_uri() );

require SPIELTAFEL_DIR . '/inc/games.php';
require SPIELTAFEL_DIR . '/inc/content.php';
require SPIELTAFEL_DIR . '/inc/customizer.php';
require SPIELTAFEL_DIR . '/inc/wishes.php';
require SPIELTAFEL_DIR . '/inc/activation.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/setup.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/captcha.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/core.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/actions.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/accounts.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/admin.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/share.php';
require SPIELTAFEL_DIR . '/inc/spieltreff/testaccounts.php';

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'spieltafel', SPIELTAFEL_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		register_nav_menus(
			array(
				'primary' => __( 'Hauptmenü', 'spieltafel' ),
				'footer'  => __( 'Fusszeile', 'spieltafel' ),
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'spieltafel', get_stylesheet_uri(), array(), SPIELTAFEL_VERSION );
		wp_enqueue_script( 'spieltafel', SPIELTAFEL_URI . '/assets/js/site.js', array(), SPIELTAFEL_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_enqueue_script( 'spieltafel-spieltreff', SPIELTAFEL_URI . '/assets/js/spieltreff.js', array(), SPIELTAFEL_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

/** Favicon aus dem App-Icon, solange in WordPress kein Website-Icon gesetzt ist. */
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" type="image/png">' . "\n", esc_url( SPIELTAFEL_URI . '/assets/img/favicon.png' ) );
			printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( SPIELTAFEL_URI . '/assets/img/app-icon.png' ) );
		}
		echo '<meta name="theme-color" content="#1f6f4a">' . "\n";
		if ( is_front_page() ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( spieltafel_mod( 'hero_text' ) ) );
		}
	}
);

/** Bild aus dem Theme-Ordner. */
function spieltafel_img( $file ) {
	return SPIELTAFEL_URI . '/assets/img/' . $file;
}

/** Adresse einer Seite des Themes (Spiele, Spielwunsch), mit Rückfall auf die Startseite. */
function spieltafel_page_url( $key ) {
	$id = (int) get_option( 'spieltafel_page_' . $key );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	$anchors = array(
		'spiele'      => '#spiele',
		'spielwunsch' => '#spielwunsch',
	);
	return home_url( '/' ) . ( $anchors[ $key ] ?? '' );
}

/** Menü, wenn im Backend noch keines zugewiesen ist. */
function spieltafel_fallback_menu() {
	$items = array(
		array( home_url( '/#funktionen' ), __( 'Funktionen', 'spieltafel' ), '' ),
		array( spieltafel_page_url( 'spiele' ), __( 'Spiele & Anleitungen', 'spieltafel' ), '' ),
		array( spieltreff_url(), __( 'Spieltreff', 'spieltafel' ), spieltreff_is_live() ? '' : 'nav-soon' ),
		array( spieltafel_page_url( 'spielwunsch' ), __( 'Spiel wünschen', 'spieltafel' ), 'nav-cta' ),
	);
	if ( is_user_logged_in() ) {
		$items[] = array( spieltreff_url( 'konto' ), __( 'Mein Konto', 'spieltafel' ), 'nav-account' );
	}
	echo '<ul>';
	foreach ( $items as $item ) {
		$badge = 'nav-soon' === $item[2] && ! is_user_logged_in() ? ' <span class="badge-soon">' . esc_html__( 'Bald', 'spieltafel' ) . '</span>' : '';
		printf( '<li class="%s"><a href="%s">%s%s</a></li>', esc_attr( $item[2] ), esc_url( $item[0] ), esc_html( $item[1] ), $badge ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oben escaped.
	}
	echo '</ul>';
}

function spieltafel_footer_fallback_menu() {
	echo '<ul>';
	$privacy = get_privacy_policy_url();
	if ( $privacy ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $privacy ), esc_html__( 'Datenschutz', 'spieltafel' ) );
	}
	$impressum = get_page_by_path( 'impressum' );
	if ( $impressum && 'publish' === $impressum->post_status ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $impressum ) ), esc_html__( 'Impressum', 'spieltafel' ) );
	}
	printf( '<li><a href="%s">%s</a></li>', esc_url( spieltafel_page_url( 'spielwunsch' ) ), esc_html__( 'Spiel wünschen', 'spieltafel' ) );
	if ( spieltreff_can_view() ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( spieltreff_rules_url() ), esc_html__( 'Spieltreff-Regeln', 'spieltafel' ) );
	}
	echo '</ul>';
}

/** Keine Emoji-Skripte und -Bilder von wordpress.org laden: weniger fremde Anfragen, die Symbole zeigt das Gerät selbst. */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
);
