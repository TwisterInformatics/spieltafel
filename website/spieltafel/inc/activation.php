<?php
/**
 * Beim Aktivieren des Themes: Seiten «Spiele» und «Spiel wünschen» anlegen
 * und die Adressen /spiele/<spiel>/ einrichten.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_switch_theme',
	function () {
		$pages = array(
			'spiele'      => array( 'spiele', __( 'Spiele & Anleitungen', 'spieltafel' ), 'page-templates/spiele.php' ),
			'spielwunsch' => array( 'spiel-wuenschen', __( 'Spiel wünschen', 'spieltafel' ), 'page-templates/spielwunsch.php' ),
		);
		foreach ( $pages as $key => $page ) {
			$id = (int) get_option( 'spieltafel_page_' . $key );
			if ( $id && get_post( $id ) ) {
				continue;
			}
			$existing = get_page_by_path( $page[0] );
			if ( $existing ) {
				$id = $existing->ID;
			} else {
				$id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'post_name'    => $page[0],
						'post_title'   => $page[1],
						'post_content' => '',
					)
				);
			}
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', $page[2] );
				update_option( 'spieltafel_page_' . $key, $id );
			}
		}
		// Schöne Adressen sind nötig für /spiele/<spiel>/.
		if ( ! get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}
		flush_rewrite_rules();
	}
);

add_action( 'switch_theme', 'flush_rewrite_rules' );
