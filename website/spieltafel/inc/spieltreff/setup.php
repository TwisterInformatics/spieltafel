<?php
/**
 * Spieltreff: Grundlagen. Einstellungen, Beitragstyp, Rolle, Tabelle, Adressen.
 *
 * Der Spieltreff ist eine Plattform nur auf der Webseite: Wer gerne spielt,
 * legt ein Konto an, macht Angebote (zum Beispiel «Brettspielabend am 15.12.»)
 * oder Gesuche («Gruppe für Brändi Dog jeden Donnerstag») und meldet sich bei
 * anderen an. Solange der Admin den Spieltreff nicht freigeschaltet hat, sehen
 * Besucher nur einen Hinweis; angemeldete Benutzer können ihn schon testen.
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

const SPIELTREFF_TYPE       = 'spieltreff';
const SPIELTREFF_ROLE       = 'spieler';
const SPIELTREFF_DB_VERSION = '1';

/** Einstellungen mit Vorgaben. */
function spieltreff_settings() {
	$defaults = array(
		'live'           => false,    // Für alle sichtbar.
		'register_test'  => false,    // Registrierung schon vor der Freischaltung offen.
		'moderation'     => 'erster', // keine | erster | alle.
		'report_limit'   => 3,        // So viele Meldungen blenden einen Eintrag aus.
		'keep_days'      => 30,       // Vergangene Einträge so lange behalten, dann löschen.
		'gesuch_days'    => 90,       // Gesuche laufen nach so vielen Tagen ab (verlängerbar).
		'min_age'        => 16,
		'notify_email'   => '',       // Leer = Admin-E-Mail.
		'notify_new'     => false,    // Auch bei veröffentlichten neuen Einträgen eine E-Mail.
		'teaser_text'    => '',
	);
	$saved = get_option( 'spieltafel_spieltreff', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

function spieltreff_setting( $key ) {
	$s = spieltreff_settings();
	return $s[ $key ] ?? null;
}

/** Text des Hinweises vor dem Start. */
function spieltreff_default_teaser() {
	return __( 'Wann hast du zuletzt gejasst, bis jemand gähnt? Bald findest du hier Leute, die lieber Karten mischen als scrollen: Jassrunden, Brettspielabende und feste Gruppen in deiner Region.', 'spieltafel' );
}

/** Für alle freigeschaltet? */
function spieltreff_is_live() {
	return (bool) spieltreff_setting( 'live' );
}

/** Darf der aktuelle Besucher den Spieltreff sehen? Vor der Freischaltung nur angemeldete Benutzer (Testbetrieb). */
function spieltreff_can_view() {
	return spieltreff_is_live() || is_user_logged_in();
}

/** Kann man sich gerade registrieren? */
function spieltreff_registration_open() {
	return spieltreff_is_live() || spieltreff_setting( 'register_test' );
}

/** Kantone und weitere Regionen für Ort und Filter. */
function spieltreff_regions() {
	return array(
		'AG' => 'Aargau',
		'AI' => 'Appenzell Innerrhoden',
		'AR' => 'Appenzell Ausserrhoden',
		'BE' => 'Bern',
		'BL' => 'Basel-Landschaft',
		'BS' => 'Basel-Stadt',
		'FR' => 'Freiburg',
		'GE' => 'Genf',
		'GL' => 'Glarus',
		'GR' => 'Graubünden',
		'JU' => 'Jura',
		'LU' => 'Luzern',
		'NE' => 'Neuenburg',
		'NW' => 'Nidwalden',
		'OW' => 'Obwalden',
		'SG' => 'St. Gallen',
		'SH' => 'Schaffhausen',
		'SO' => 'Solothurn',
		'SZ' => 'Schwyz',
		'TG' => 'Thurgau',
		'TI' => 'Tessin',
		'UR' => 'Uri',
		'VD' => 'Waadt',
		'VS' => 'Wallis',
		'ZG' => 'Zug',
		'ZH' => 'Zürich',
		'FL' => 'Liechtenstein',
		'XX' => __( 'Ausland', 'spieltafel' ),
		'ON' => __( 'Online', 'spieltafel' ),
	);
}

/** Wo man sich trifft. */
function spieltreff_place_kinds() {
	return array(
		'privat'     => __( 'Bei jemandem zuhause', 'spieltafel' ),
		'restaurant' => __( 'Restaurant, Café oder Bar', 'spieltafel' ),
		'laden'      => __( 'Spieleladen oder Ludothek', 'spieltafel' ),
		'verein'     => __( 'Verein oder Quartiertreff', 'spieltafel' ),
		'draussen'   => __( 'Draussen', 'spieltafel' ),
		'online'     => __( 'Online', 'spieltafel' ),
		'anderes'    => __( 'Anderes', 'spieltafel' ),
	);
}

/** Text zu Angebot und Gesuch. */
function spieltreff_kinds() {
	return array(
		'angebot' => __( 'Angebot', 'spieltafel' ),
		'gesuch'  => __( 'Gesuch', 'spieltafel' ),
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			SPIELTREFF_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Spieltreff', 'spieltafel' ),
					'singular_name' => __( 'Spieltreff-Eintrag', 'spieltafel' ),
					'all_items'     => __( 'Alle Einträge', 'spieltafel' ),
					'edit_item'     => __( 'Eintrag ansehen', 'spieltafel' ),
					'search_items'  => __( 'Einträge durchsuchen', 'spieltafel' ),
					'not_found'     => __( 'Noch keine Einträge.', 'spieltafel' ),
					'menu_name'     => __( 'Spieltreff', 'spieltafel' ),
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'has_archive'         => 'spieltreff',
				'rewrite'             => array(
					'slug'       => 'spieltreff',
					'with_front' => false,
					'feeds'      => false,
				),
				'menu_icon'           => 'dashicons-groups',
				'menu_position'       => 26,
				'supports'            => array( 'title', 'editor', 'author' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);

		// Eigene Seiten des Spieltreffs: /spieltreff/neu/, /spieltreff/konto/ …
		add_rewrite_rule( '^spieltreff/(' . implode( '|', spieltreff_views() ) . ')/?$', 'index.php?spieltreff_view=$matches[1]', 'top' );
	}
);

/** Adressen der eigenen Seiten unter /spieltreff/. */
function spieltreff_views() {
	return array( 'neu', 'konto', 'registrieren', 'anmelden', 'bestaetigen', 'regeln' );
}

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'spieltreff_view';
		return $vars;
	}
);

/** Einträge dürfen nicht so heissen wie die eigenen Seiten. */
add_filter(
	'wp_unique_post_slug',
	function ( $slug, $post_id, $status, $type ) {
		if ( SPIELTREFF_TYPE === $type && in_array( $slug, spieltreff_views(), true ) ) {
			$slug .= '-1';
		}
		return $slug;
	},
	10,
	4
);

/** Adresse einer Spieltreff-Seite. */
function spieltreff_url( $view = '', $args = array() ) {
	if ( '' === $view ) {
		$url = get_post_type_archive_link( SPIELTREFF_TYPE );
		if ( ! $url ) {
			$url = home_url( '/spieltreff/' );
		}
	} elseif ( get_option( 'permalink_structure' ) ) {
		$url = home_url( user_trailingslashit( 'spieltreff/' . $view ) );
	} else {
		$url = add_query_arg( 'spieltreff_view', $view, home_url( '/' ) );
	}
	return $args ? add_query_arg( $args, $url ) : $url;
}

/** Spielerrolle: darf nur lesen. Alles andere läuft über die Formulare auf der Webseite. */
function spieltreff_add_role() {
	if ( ! get_role( SPIELTREFF_ROLE ) ) {
		add_role( SPIELTREFF_ROLE, __( 'Spieler', 'spieltafel' ), array( 'read' => true ) );
	}
}

/** Tabelle für Anmeldungen und Warteliste. */
function spieltreff_table() {
	global $wpdb;
	return $wpdb->prefix . 'spieltreff_teilnahmen';
}

function spieltreff_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$table   = spieltreff_table();
	dbDelta(
		"CREATE TABLE $table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'angefragt',
			nachricht text NOT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY post_user (post_id,user_id),
			KEY user_id (user_id)
		) $charset;"
	);
	spieltreff_add_role();
	spieltreff_create_pages();
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	// Schweizer Zeit, falls noch UTC eingestellt ist.
	if ( ! get_option( 'timezone_string' ) && ! (float) get_option( 'gmt_offset' ) ) {
		update_option( 'timezone_string', 'Europe/Zurich' );
	}
	update_option( 'spieltafel_spieltreff_db', SPIELTREFF_DB_VERSION );
	flush_rewrite_rules();
}

/** Beim Aktivieren und nach einem Theme-Update (dann läuft after_switch_theme nicht). */
add_action( 'after_switch_theme', 'spieltreff_install', 20 );
add_action(
	'init',
	function () {
		if ( get_option( 'spieltafel_spieltreff_db' ) !== SPIELTREFF_DB_VERSION ) {
			spieltreff_install();
		}
	},
	99
);

/** Seite mit den Regeln des Spieltreffs als Entwurf zum Anpassen. */
function spieltreff_create_pages() {
	$id = (int) get_option( 'spieltafel_page_regeln' );
	if ( $id && get_post( $id ) ) {
		return;
	}
	$existing = get_page_by_path( 'spieltreff-regeln' );
	if ( $existing ) {
		update_option( 'spieltafel_page_regeln', $existing->ID );
		return;
	}
	ob_start();
	get_template_part( 'template-parts/spieltreff/rules-text' );
	$content = ob_get_clean();
	$id      = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'spieltreff-regeln',
			'post_title'   => __( 'Spieltreff: Regeln und Nutzungsbedingungen', 'spieltafel' ),
			'post_content' => $content,
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_option( 'spieltafel_page_regeln', $id );
	}
}

/** Adresse der Regeln (Seite, sonst die eingebaute Fassung). */
function spieltreff_rules_url() {
	$id = (int) get_option( 'spieltafel_page_regeln' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	return spieltreff_url( 'regeln' );
}

/* Ansichten und Zugriff */

add_action(
	'template_redirect',
	function () {
		$view = get_query_var( 'spieltreff_view' );
		if ( $view ) {
			global $wp_query;
			$wp_query->is_404  = false;
			$wp_query->is_home = false;
			status_header( 200 );
			nocache_headers();
			return;
		}
		if ( is_singular( SPIELTREFF_TYPE ) || is_post_type_archive( SPIELTREFF_TYPE ) ) {
			if ( is_user_logged_in() ) {
				nocache_headers();
			}
		}
	}
);

add_filter(
	'template_include',
	function ( $template ) {
		$view = get_query_var( 'spieltreff_view' );
		if ( $view && in_array( $view, spieltreff_views(), true ) ) {
			return SPIELTAFEL_DIR . '/spieltreff-view.php';
		}
		return $template;
	}
);

add_filter(
	'document_title_parts',
	function ( $parts ) {
		$titles = array(
			'neu'          => __( 'Neuer Eintrag', 'spieltafel' ),
			'konto'        => __( 'Mein Konto', 'spieltafel' ),
			'registrieren' => __( 'Registrieren', 'spieltafel' ),
			'anmelden'     => __( 'Anmelden', 'spieltafel' ),
			'bestaetigen'  => __( 'E-Mail bestätigen', 'spieltafel' ),
			'regeln'       => __( 'Regeln', 'spieltafel' ),
		);
		$view = get_query_var( 'spieltreff_view' );
		if ( isset( $titles[ $view ] ) ) {
			$parts['title'] = $titles[ $view ] . ' · ' . __( 'Spieltreff', 'spieltafel' );
		} elseif ( is_post_type_archive( SPIELTREFF_TYPE ) ) {
			$parts['title'] = __( 'Spieltreff: Mitspieler finden', 'spieltafel' );
		}
		return $parts;
	}
);

/**
 * Suchmaschinen: Einträge nicht indexieren (Schutz der Teilnehmenden), die Übersicht
 * erst nach der Freischaltung. Eigene Seiten (Konto, Anmelden …) nie.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( get_query_var( 'spieltreff_view' ) || is_singular( SPIELTREFF_TYPE ) || ( is_post_type_archive( SPIELTREFF_TYPE ) && ! spieltreff_is_live() ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);

/** Einträge nicht in der Sitemap. */
add_filter(
	'wp_sitemaps_post_types',
	function ( $types ) {
		unset( $types[ SPIELTREFF_TYPE ] );
		return $types;
	}
);

/** Einen Eintrag in Prüfung kann der Ersteller unter seiner Adresse ansehen. */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( SPIELTREFF_TYPE === $query->get( 'post_type' ) && ( $query->get( 'name' ) || $query->get( 'p' ) ) && is_user_logged_in() ) {
			$query->set( 'post_status', array( 'publish', 'pending' ) );
		}
	}
);

add_filter(
	'the_posts',
	function ( $posts, $query ) {
		if ( is_admin() || ! $query->is_main_query() || 1 !== count( $posts ) ) {
			return $posts;
		}
		$post = $posts[0];
		if ( SPIELTREFF_TYPE === $post->post_type && 'pending' === $post->post_status ) {
			$uid = get_current_user_id();
			if ( ! $uid || ( (int) $post->post_author !== $uid && ! current_user_can( 'edit_others_posts' ) ) ) {
				return array();
			}
		}
		return $posts;
	},
	10,
	2
);

/* Aufräumen: einmal am Tag */

add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'spieltreff_daily' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'spieltreff_daily' );
		}
	}
);

add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'spieltreff_daily' );
} );

add_action(
	'spieltreff_daily',
	function () {
		$cutoff = time() - DAY_IN_SECONDS * max( 1, (int) spieltreff_setting( 'keep_days' ) );
		$old    = get_posts(
			array(
				'post_type'      => SPIELTREFF_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 200,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_st_sichtbar_bis',
						'value'   => $cutoff,
						'compare' => '<',
						'type'    => 'NUMERIC',
					),
				),
			)
		);
		foreach ( $old as $id ) {
			wp_delete_post( $id, true );
		}

		// Konten, deren E-Mail nach 7 Tagen nicht bestätigt ist.
		$unverified = get_users(
			array(
				'role'       => SPIELTREFF_ROLE,
				'meta_key'   => '_st_verify_until',
				'meta_value' => time(),
				'meta_compare' => '<',
				'meta_type'  => 'NUMERIC',
				'fields'     => 'ID',
				'number'     => 200,
			)
		);
		if ( $unverified ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			foreach ( $unverified as $uid ) {
				wp_delete_user( (int) $uid );
			}
		}

		// Erinnerung an Gesuche, die in 7 Tagen ablaufen.
		$soon = get_posts(
			array(
				'post_type'      => SPIELTREFF_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'meta_query'     => array(
					array( 'key' => '_st_typ', 'value' => 'gesuch' ),
					array(
						'key'     => '_st_sichtbar_bis',
						'value'   => array( time() + 6 * DAY_IN_SECONDS, time() + 7 * DAY_IN_SECONDS ),
						'compare' => 'BETWEEN',
						'type'    => 'NUMERIC',
					),
				),
			)
		);
		foreach ( $soon as $post ) {
			spieltreff_mail(
				(int) $post->post_author,
				/* translators: %s: Titel */
				sprintf( __( 'Dein Gesuch läuft bald ab: %s', 'spieltafel' ), $post->post_title ),
				array(
					__( 'Dein Gesuch wird in einer Woche ausgeblendet. Wenn du noch suchst, verlängere es mit einem Klick in deinem Konto:', 'spieltafel' ),
					spieltreff_url( 'konto' ),
				)
			);
		}
	}
);

/** Wird ein Eintrag gelöscht, fallen auch alle Anmeldungen weg. */
add_action(
	'before_delete_post',
	function ( $post_id ) {
		if ( SPIELTREFF_TYPE !== get_post_type( $post_id ) ) {
			return;
		}
		global $wpdb;
		$wpdb->delete( spieltreff_table(), array( 'post_id' => $post_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
);

/** Wird ein Konto gelöscht, auch seine Anmeldungen (seine Einträge löscht WordPress selbst). */
add_action(
	'delete_user',
	function ( $user_id ) {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT post_id, status FROM ' . spieltreff_table() . ' WHERE user_id = %d', $user_id ) ); // phpcs:ignore WordPress.DB
		$wpdb->delete( spieltreff_table(), array( 'user_id' => $user_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $rows as $row ) {
			if ( 'bestaetigt' === $row->status ) {
				spieltreff_fill_spots( (int) $row->post_id );
			}
		}
		// Teilnehmende eigener, kommender Angebote informieren.
		$own = get_posts(
			array(
				'post_type'      => SPIELTREFF_TYPE,
				'author'         => $user_id,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
			)
		);
		foreach ( $own as $post ) {
			if ( ! spieltreff_is_past( $post ) && ! get_post_meta( $post->ID, '_st_abgesagt', true ) ) {
				spieltreff_notify_all(
					$post,
					/* translators: %s: Titel */
					sprintf( __( 'Abgesagt: %s', 'spieltafel' ), $post->post_title ),
					array( __( 'Der Eintrag wurde gelöscht, weil die Person, die ihn erstellt hat, ihr Konto gelöscht hat.', 'spieltafel' ) )
				);
			}
		}
	}
);
