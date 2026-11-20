<?php
/**
 * Einstellungen unter Design → Customizer → Spieltafel.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

function spieltafel_mod_defaults() {
	return array(
		'hero_title'    => __( 'Punkte zählen für Jass & Co.', 'spieltafel' ),
		'hero_text'     => __( 'Spieltafel ersetzt Zettel und Stift am Spieltisch. Spiel wählen, Namen eintragen, nach jeder Runde die Punkte eingeben: Die App rechnet, zeigt den Spielstand und ermittelt den Sieger.', 'spieltafel' ),
		'app_store_url' => '',
		'play_url'      => '',
		'wish_email'    => '',
		'wish_from'     => '',
		'wish_from_name' => '',
		'wish_notify'   => true,
	);
}

/** Wert aus dem Customizer, mit Vorgabe. */
function spieltafel_mod( $key ) {
	$defaults = spieltafel_mod_defaults();
	return get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$d = spieltafel_mod_defaults();
		$wp_customize->add_section(
			'spieltafel',
			array(
				'title'    => __( 'Spieltafel', 'spieltafel' ),
				'priority' => 30,
			)
		);

		$fields = array(
			'hero_title'    => array( __( 'Titel auf der Startseite', 'spieltafel' ), 'text', 'sanitize_text_field' ),
			'hero_text'     => array( __( 'Einleitung auf der Startseite', 'spieltafel' ), 'textarea', 'sanitize_textarea_field' ),
			'app_store_url' => array( __( 'Link zum App Store (leer = «bald»)', 'spieltafel' ), 'url', 'esc_url_raw' ),
			'play_url'      => array( __( 'Link zu Google Play (leer = «bald»)', 'spieltafel' ), 'url', 'esc_url_raw' ),
			'wish_email'    => array( __( 'Spielwünsche an diese E-Mail (leer = Admin-E-Mail)', 'spieltafel' ), 'email', 'sanitize_email' ),
			'wish_from'     => array( __( 'Absender-Adresse der Wunsch-E-Mails (leer = WordPress-Standard)', 'spieltafel' ), 'email', 'sanitize_email' ),
			'wish_from_name' => array( __( 'Absender-Name der Wunsch-E-Mails (leer = Name der Website)', 'spieltafel' ), 'text', 'sanitize_text_field' ),
		);
		foreach ( $fields as $key => $f ) {
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $d[ $key ],
					'sanitize_callback' => $f[2],
				)
			);
			$wp_customize->add_control(
				$key,
				array(
					'label'   => $f[0],
					'section' => 'spieltafel',
					'type'    => $f[1],
				)
			);
		}

		$wp_customize->add_setting(
			'wish_notify',
			array(
				'default'           => $d['wish_notify'],
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		$wp_customize->add_control(
			'wish_notify',
			array(
				'label'   => __( 'Bei jedem Spielwunsch eine E-Mail senden', 'spieltafel' ),
				'section' => 'spieltafel',
				'type'    => 'checkbox',
			)
		);
	}
);
