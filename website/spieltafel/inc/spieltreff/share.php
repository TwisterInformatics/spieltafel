<?php
/**
 * Spieltreff teilen: Facebook, WhatsApp, Instagram und Link kopieren.
 *
 * Datenschutz: Es werden keine Skripte oder Knöpfe von Facebook oder Instagram
 * geladen. Die Knöpfe sind normale Links; Daten gehen erst an Meta, wenn jemand
 * selbst darauf tippt. Instagram kennt keine Links zum Teilen, darum gibt es ein
 * fertiges Bild für die Story, das auf dem Handy direkt an Instagram geht.
 *
 * Für schöne Vorschauen auf Facebook und WhatsApp setzt das Theme Open-Graph-Angaben
 * mit einem eigenen Vorschaubild. Darin stehen nur öffentliche Angaben (nie die Adresse).
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/** Darf der Eintrag geteilt werden? Nur veröffentlicht, nicht abgesagt, nicht vorbei. */
function spieltreff_shareable( $e ) {
	return 'publish' === $e['status'] && ! $e['abgesagt'] && ! spieltreff_is_past( $e );
}

/** Kurztext für Vorschau und Teilen: «Di, 15.12., 19:30 Uhr · Bern · Skyjo, Jass» */
function spieltreff_share_text( $e ) {
	$parts = array_filter(
		array(
			spieltreff_when( $e, true ),
			spieltreff_where( $e ),
			implode( ', ', array_slice( spieltreff_game_names( $e ), 0, 4 ) ),
		)
	);
	return implode( ' · ', $parts );
}

/** Adresse des Bildes: og (1200 × 630) oder story (1080 × 1920). */
function spieltreff_image_url( $post_id, $format = 'og' ) {
	return add_query_arg(
		array(
			'action' => 'spieltreff_bild',
			'id'     => (int) $post_id,
			'f'      => 'story' === $format ? 'story' : 'og',
			'v'      => (int) get_post_modified_time( 'U', true, $post_id ),
		),
		admin_url( 'admin-post.php' )
	);
}

/* Open Graph für Facebook, WhatsApp und Co. */

add_action(
	'wp_head',
	function () {
		if ( is_post_type_archive( SPIELTREFF_TYPE ) ) {
			$tags = array(
				'og:type'         => 'website',
				'og:title'        => spieltreff_is_live() ? __( 'Spieltreff: Zurück an den Tisch', 'spieltafel' ) : __( 'Bald neu: der Spieltreff', 'spieltafel' ),
				'og:description'  => spieltreff_is_live()
					? __( 'Weniger scrollen, mehr spielen: Jassrunden, Brettspielabende und feste Gruppen in deiner Region. Mach mit oder lade selbst ein.', 'spieltafel' )
					: __( 'Weniger scrollen, mehr spielen: Bald findest du hier Leute für Jassrunden, Brettspielabende und feste Gruppen in deiner Region.', 'spieltafel' ),
				'og:url'          => spieltreff_url(),
				'og:image'        => spieltreff_campaign_url( 'og', 1 ),
				'og:image:width'  => '1200',
				'og:image:height' => '630',
				'og:image:alt'    => __( 'Zurück an den Tisch. Ein Spieltisch mit Karten und Würfeln, das Handy liegt umgedreht daneben.', 'spieltafel' ),
				'twitter:card'    => 'summary_large_image',
			);
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $tags['og:description'] ) );
		} elseif ( is_singular( SPIELTREFF_TYPE ) && spieltreff_can_view() ) {
			$e = spieltreff_entry( get_queried_object() );
			if ( ! spieltreff_shareable( $e ) ) {
				return;
			}
			$tags = array(
				'og:type'             => 'website',
				'og:title'            => $e['titel'],
				'og:description'      => spieltreff_share_text( $e ),
				'og:url'              => get_permalink( $e['id'] ),
				'og:image'            => spieltreff_image_url( $e['id'], 'og' ),
				'og:image:width'      => '1200',
				'og:image:height'     => '630',
				'og:image:alt'        => $e['titel'] . ' · ' . spieltreff_share_text( $e ),
				'twitter:card'        => 'summary_large_image',
			);
		} else {
			return;
		}
		$tags['og:site_name'] = get_bloginfo( 'name' );
		$tags['og:locale']    = 'de_CH';
		foreach ( $tags as $property => $content ) {
			$attr = 0 === strpos( $property, 'twitter:' ) ? 'name' : 'property';
			printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $attr ), esc_attr( $property ), esc_attr( $content ) );
		}
		if ( is_singular( SPIELTREFF_TYPE ) ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $tags['og:description'] ) );
		}
	},
	2
);

/* Bilder zum Teilen, mit GD gezeichnet */

add_action( 'admin_post_spieltreff_bild', 'spieltreff_handle_image' );
add_action( 'admin_post_nopriv_spieltreff_bild', 'spieltreff_handle_image' );

function spieltreff_handle_image() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- öffentliches Bild.
	$post_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
	$format  = isset( $_GET['f'] ) && 'story' === $_GET['f'] ? 'story' : 'og';
	// phpcs:enable
	$post = $post_id ? get_post( $post_id ) : null;
	if ( ! $post || SPIELTREFF_TYPE !== $post->post_type || ! spieltreff_can_view() || ! function_exists( 'imagettftext' ) ) {
		status_header( 404 );
		exit;
	}
	$e = spieltreff_entry( $post );
	if ( ! spieltreff_shareable( $e ) ) {
		status_header( 404 );
		exit;
	}
	$png = spieltreff_render_image( $e, $format );
	header( 'Content-Type: image/png' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'Content-Disposition: inline; filename="spieltreff-' . $post_id . ( 'story' === $format ? '-story' : '' ) . '.png"' );
	header( 'X-Robots-Tag: noindex' );
	echo $png; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Bilddaten.
	exit;
}

/** Text auf Zeilen umbrechen, höchstens $max Zeilen (letzte mit «…»). */
function spieltreff_wrap( $text, $font, $size, $width, $max ) {
	$words = preg_split( '/\s+/u', trim( $text ) );
	$lines = array();
	$line  = '';
	foreach ( $words as $word ) {
		$try = '' === $line ? $word : $line . ' ' . $word;
		$box = imagettfbbox( $size, 0, $font, $try );
		if ( $box[2] - $box[0] > $width && '' !== $line ) {
			$lines[] = $line;
			$line    = $word;
		} else {
			$line = $try;
		}
	}
	if ( '' !== $line ) {
		$lines[] = $line;
	}
	if ( count( $lines ) > $max ) {
		$lines            = array_slice( $lines, 0, $max );
		$lines[ $max - 1 ] = rtrim( $lines[ $max - 1 ], ' ,.' ) . ' …';
	}
	return $lines;
}

/** Rechteck mit runden Ecken. */
function spieltreff_round_rect( $im, $x1, $y1, $x2, $y2, $r, $color ) {
	imagefilledrectangle( $im, $x1 + $r, $y1, $x2 - $r, $y2, $color );
	imagefilledrectangle( $im, $x1, $y1 + $r, $x2, $y2 - $r, $color );
	foreach ( array( array( $x1 + $r, $y1 + $r ), array( $x2 - $r, $y1 + $r ), array( $x1 + $r, $y2 - $r ), array( $x2 - $r, $y2 - $r ) ) as $c ) {
		imagefilledellipse( $im, $c[0], $c[1], $r * 2, $r * 2, $color );
	}
}

/**
 * Zeichnet das Bild.
 *
 * @param array  $e      Eintrag.
 * @param string $format og (1200 × 630, Facebook-Vorschau) oder story (1080 × 1920, Instagram-Story).
 * @return string PNG
 */
function spieltreff_render_image( $e, $format ) {
	$story = 'story' === $format;
	$w     = $story ? 1080 : 1200;
	$h     = $story ? 1920 : 630;
	$bold  = SPIELTAFEL_DIR . '/assets/fonts/Inter-Bold.otf';
	$reg   = SPIELTAFEL_DIR . '/assets/fonts/Inter-Regular.otf';
	$im    = imagecreatetruecolor( $w, $h );
	imagealphablending( $im, true );
	imageantialias( $im, true );
	$col = function ( $hex ) use ( $im ) {
		return imagecolorallocate( $im, hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	};
	$bg      = $col( '#f6f3ec' );
	$green   = $col( '#1f6f4a' );
	$light   = $col( '#cfe5d9' );
	$ink     = $col( '#1d2420' );
	$muted   = $col( '#5d655f' );
	$white   = $col( '#ffffff' );
	$gold    = $col( '#e9b949' );
	$surface = $col( '#f1eee6' );
	imagefilledrectangle( $im, 0, 0, $w, $h, $bg );
	// Dezenter Akzent oben rechts.
	imagefilledellipse( $im, $w - ( $story ? 40 : 30 ), $story ? 40 : 30, $story ? 260 : 160, $story ? 260 : 160, $surface );

	$m    = $story ? 90 : 64;   // Rand.
	$text = function ( $s, $size, $x, $y, $color, $font ) use ( $im ) {
		imagettftext( $im, $size, 0, (int) $x, (int) $y, $color, $font, $s );
		$box = imagettfbbox( $size, 0, $font, $s );
		return $box[2] - $box[0];
	};

	// Kopf: App-Symbol und «Spieltreff».
	$icon_size = $story ? 110 : 76;
	$icon      = @imagecreatefrompng( SPIELTAFEL_DIR . '/assets/img/app-icon.png' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$top       = $story ? 120 : 52;
	if ( $icon ) {
		imagecopyresampled( $im, $icon, $m, $top, 0, 0, $icon_size, $icon_size, imagesx( $icon ), imagesy( $icon ) );
		imagedestroy( $icon );
	}
	$text( get_bloginfo( 'name' ) . ' · ' . __( 'Spieltreff', 'spieltafel' ), $story ? 38 : 26, $m + $icon_size + 24, $top + $icon_size / 2 + ( $story ? 14 : 10 ), $ink, $bold );

	// Art des Eintrags als Etikett.
	$label = mb_strtoupper( 'gesuch' === $e['typ'] ? __( 'Mitspieler gesucht', 'spieltafel' ) : __( 'Spielabend', 'spieltafel' ) );
	$y     = $story ? 420 : 200;
	$lsize = $story ? 28 : 18;
	$box   = imagettfbbox( $lsize, 0, $bold, $label );
	spieltreff_round_rect( $im, $m, $y - $lsize - 22, $m + ( $box[2] - $box[0] ) + 44, $y + 18, $story ? 26 : 18, $gold );
	$text( $label, $lsize, $m + 22, $y, $ink, $bold );

	// Titel.
	$tsize = $story ? 78 : 48;
	$lines = spieltreff_wrap( $e['titel'], $bold, $tsize, $w - 2 * $m - ( $story ? 0 : 0 ), $story ? 4 : 2 );
	$y    += $story ? 150 : 82;
	foreach ( $lines as $line ) {
		$text( $line, $tsize, $m, $y, $ink, $bold );
		$y += (int) ( $tsize * 1.3 );
	}

	// Wann, Wo, Spiele.
	$isize = $story ? 40 : 25;
	$rows  = array();
	if ( 'angebot' === $e['typ'] && $e['start'] ) {
		$time   = $e['zeit'] . ( $e['ende'] ? '–' . $e['ende'] : '' ) . ' ' . __( 'Uhr', 'spieltafel' );
		$rows[] = $story ? spieltreff_format_day( $e['start'] ) : spieltreff_format_day( $e['start'] ) . ', ' . $time;
		if ( $story ) {
			$rows[] = $time;
		}
	} elseif ( $e['wann'] ) {
		$rows[] = $e['wann'];
	}
	$rows[] = spieltreff_where( $e );
	$y     += $story ? 30 : 4;
	foreach ( array_filter( $rows ) as $row ) {
		foreach ( spieltreff_wrap( $row, $reg, $isize, $w - 2 * $m, 2 ) as $line ) {
			$text( $line, $isize, $m, $y, $muted, $reg );
			$y += (int) ( $isize * 1.55 );
		}
	}

	// Spiele: in der Vorschau als eine Zeile, in der Story als Etiketten.
	if ( ! $story ) {
		$games = spieltreff_wrap( implode( ' · ', spieltreff_game_names( $e ) ), $bold, 25, $w - 2 * $m, 1 );
		if ( $games && $y < $h - 140 ) {
			$text( $games[0], 25, $m, $y + 14, $green, $bold );
		}
	} else {
		$y    += 40;
		$x     = $m;
		$gsize = 34;
		foreach ( array_slice( spieltreff_game_names( $e ), 0, 8 ) as $name ) {
			$box = imagettfbbox( $gsize, 0, $bold, $name );
			$bw  = $box[2] - $box[0] + 56;
			if ( $x + $bw > $w - $m ) {
				$x  = $m;
				$y += 96;
			}
			if ( $y > 1500 ) {
				break;
			}
			spieltreff_round_rect( $im, $x, $y - 52, $x + $bw, $y + 26, 39, $white );
			$text( $name, $gsize, $x + 28, $y, $ink, $bold );
			$x += $bw + 16;
		}
		// Plätze und Anfänger.
		$extra = array();
		if ( 'angebot' === $e['typ'] && $e['max'] ) {
			$free    = spieltreff_free_spots( $e );
			/* translators: %d: freie Plätze */
			$extra[] = $free > 0 ? sprintf( _n( '%d Platz frei', '%d Plätze frei', $free, 'spieltafel' ), $free ) : __( 'Warteliste offen', 'spieltafel' );
		}
		if ( $e['anfaenger'] ) {
			$extra[] = __( 'Anfänger willkommen', 'spieltafel' );
		}
		if ( $extra ) {
			$y += 110;
			$text( implode( ' · ', $extra ), 38, $m, $y, $green, $bold );
		}
		// Kernbotschaft im freien Platz über dem Fuss.
		if ( $y < 1400 ) {
			$tag = spieltreff_slogans()[3];
			$box = imagettfbbox( 34, 0, $bold, $tag );
			spieltreff_round_rect( $im, $m, 1470, $m + ( $box[2] - $box[0] ) + 64, 1560, 45, $light );
			$text( $tag, 34, $m + 32, 1528, $green, $bold );
		}
	}

	// Fuss: grüner Balken mit Aufruf und Adresse.
	$host  = wp_parse_url( home_url(), PHP_URL_HOST );
	$path  = wp_parse_url( spieltreff_url(), PHP_URL_PATH );
	$where = preg_replace( '/^www\./', '', $host ) . rtrim( (string) $path, '/' );
	$fh    = $story ? 300 : 110;
	imagefilledrectangle( $im, 0, $h - $fh, $w, $h, $green );
	if ( $story ) {
		$text( __( 'Jetzt anmelden:', 'spieltafel' ), 36, $m, $h - $fh + 110, $light, $reg );
		$text( $where, 46, $m, $h - $fh + 185, $white, $bold );
		$text( __( 'Link im Sticker antippen', 'spieltafel' ), 30, $m, $h - $fh + 245, $light, $reg );
	} else {
		$cta = __( 'Jetzt anmelden:', 'spieltafel' ) . ' ';
		$cw  = $text( $cta, 26, $m, $h - 45, $light, $reg );
		$text( $where, 26, $m + $cw + 8, $h - 45, $white, $bold );
	}

	ob_start();
	imagepng( $im, null, 6 );
	imagedestroy( $im );
	return (string) ob_get_clean();
}
