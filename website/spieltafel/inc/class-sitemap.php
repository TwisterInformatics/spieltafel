<?php
/**
 * Sitemap mit allen Spielseiten.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

class Spieltafel_Sitemap extends WP_Sitemaps_Provider {
	public function __construct() {
		$this->name        = 'spiele';
		$this->object_type = 'spiele';
	}

	public function get_url_list( $page_num, $object_subtype = '' ) {
		if ( 1 !== (int) $page_num ) {
			return array();
		}
		return array_map(
			function ( $game ) {
				return array( 'loc' => spieltafel_game_url( $game ) );
			},
			spieltafel_games()
		);
	}

	public function get_max_num_pages( $object_subtype = '' ) {
		return 1;
	}
}
