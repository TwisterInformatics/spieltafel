<?php
/**
 * Kampagne «Zurück an den Tisch»: Kernbotschaft, Illustration und Bilder für Social Media.
 *
 * Die Botschaft: sich wieder treffen, gemeinsam am Tisch spielen, weg vom Bildschirm.
 * Die Illustration (Spieltisch von oben, das Handy liegt umgedreht daneben) gibt es
 * als SVG für die Webseite und mit GD gezeichnet für die Bilder. Beide nutzen
 * dieselbe Anordnung aus spieltreff_table_layout().
 *
 * @package Spieltafel
 */

defined( 'ABSPATH' ) || exit;

/** Die drei Kampagnen-Sätze. Nummer 1 ist die Kernbotschaft. */
function spieltreff_slogans() {
	return array(
		1 => __( 'Zurück an den Tisch.', 'spieltafel' ),
		2 => __( 'Weniger scrollen. Mehr spielen.', 'spieltafel' ),
		3 => __( 'Handy weg. Karten auf den Tisch.', 'spieltafel' ),
	);
}

/** Die drei Gründe, warum es den Spieltreff gibt. */
function spieltreff_pillars() {
	return array(
		array(
			'icon'  => '🙂',
			'title' => __( 'Gesichter statt Bildschirme', 'spieltafel' ),
			'text'  => __( 'Ein Spielabend ist das schönste Mittel gegen zu viel Bildschirmzeit: echte Leute, echtes Lachen, ein Stapel Karten.', 'spieltafel' ),
		),
		array(
			'icon'  => '🤝',
			'title' => __( 'Neue Leute, bewährte Spiele', 'spieltafel' ),
			'text'  => __( 'Ob Schieber, Brändi Dog oder Azul: Finde Menschen in deiner Nähe, die dasselbe gerne spielen wie du.', 'spieltafel' ),
		),
		array(
			'icon'  => '🪑',
			'title' => __( 'Einfach hingehen', 'spieltafel' ),
			'text'  => __( 'Niemand muss alle Regeln kennen, man lernt sie am besten am Tisch. Wo Neue besonders willkommen sind, steht beim Eintrag «Anfänger willkommen», und du kannst gezielt danach filtern.', 'spieltafel' ),
		),
	);
}

/**
 * Anordnung der Illustration. Einheit: Tischradius = 100, Mitte = 0/0.
 * Karten: x, y, Winkel, Farbe (herz, schaufel, ecke, rueckseite). Würfel: x, y, Winkel, Augen.
 */
function spieltreff_table_layout() {
	return array(
		'cards' => array(
			array( -44, 10, -24, 'herz' ),
			array( -20, 0, -6, 'schaufel' ),
			array( 6, 6, 14, 'ecke' ),
			array( 44, -44, 30, 'rueckseite' ),
			array( 52, -40, 18, 'rueckseite' ),
		),
		'dice'  => array(
			array( 34, 40, 16, 5 ),
			array( 60, 20, -22, 3 ),
		),
		// Das Handy liegt umgedreht neben dem Tisch.
		'phone' => array( 118, 66, 16 ),
	);
}

/** Augen eines Würfels, Einheiten relativ zur halben Kantenlänge. */
function spieltreff_pips( $n ) {
	$all = array(
		1 => array( array( 0, 0 ) ),
		2 => array( array( -.5, -.5 ), array( .5, .5 ) ),
		3 => array( array( -.5, -.5 ), array( 0, 0 ), array( .5, .5 ) ),
		4 => array( array( -.5, -.5 ), array( .5, -.5 ), array( -.5, .5 ), array( .5, .5 ) ),
		5 => array( array( -.5, -.5 ), array( .5, -.5 ), array( 0, 0 ), array( -.5, .5 ), array( .5, .5 ) ),
		6 => array( array( -.5, -.55 ), array( .5, -.55 ), array( -.5, 0 ), array( .5, 0 ), array( -.5, .55 ), array( .5, .55 ) ),
	);
	return $all[ $n ] ?? $all[1];
}

/** Pfad eines Symbols (Herz, Schaufel/Pik, Ecke/Karo) in einem 20er-Feld um 0/0, für SVG. */
function spieltreff_suit_path( $suit ) {
	switch ( $suit ) {
		case 'herz':
			return 'M0 7 C-9 0 -9 -7 -4.5 -7 C-2 -7 0 -5 0 -3.5 C0 -5 2 -7 4.5 -7 C9 -7 9 0 0 7Z';
		case 'schaufel':
			return 'M0 -7 C-9 0 -8 5 -4 5 C-2 5 -1 4 -.6 3 L-2 7 L2 7 L.6 3 C1 4 2 5 4 5 C8 5 9 0 0 -7Z';
		default:
			return 'M0 -8 L6 0 L0 8 L-6 0Z';
	}
}

/**
 * Illustration als SVG.
 *
 * @param string $label Beschreibung für Screenreader.
 */
function spieltreff_table_svg( $label = '' ) {
	$l    = spieltreff_table_layout();
	$out  = '<svg class="st-table" viewBox="-150 -134 300 268" role="img" aria-label="' . esc_attr( $label ? $label : __( 'Ein Spieltisch von oben mit Karten und Würfeln. Das Handy liegt umgedreht daneben.', 'spieltafel' ) ) . '">';
	$out .= '<circle r="132" fill="#f6f3ec"/><circle r="104" fill="#17573a"/><circle r="98" fill="#1f6f4a"/><circle r="88" fill="none" stroke="#2f8a5f" stroke-width="1.5" stroke-dasharray="3 5"/>';
	foreach ( $l['cards'] as $c ) {
		list( $x, $y, $a, $suit ) = $c;
		$out .= sprintf( '<g transform="translate(%s %s) rotate(%s)">', $x, $y, $a );
		if ( 'rueckseite' === $suit ) {
			$out .= '<rect x="-17" y="-25" width="34" height="50" rx="4" fill="#ffffff"/><rect x="-14" y="-22" width="28" height="44" rx="2.5" fill="#e9b949"/><path d="M-14 -8 L0 -22 L14 -8 M-14 8 L0 22 L14 8" stroke="#d4a43a" stroke-width="1.5" fill="none"/>';
		} else {
			$color = 'schaufel' === $suit ? '#1d2420' : '#c0563b';
			$out  .= '<rect x="-17" y="-25" width="34" height="50" rx="4" fill="#ffffff" stroke="#e0dccf" stroke-width=".8"/>';
			$out  .= sprintf( '<path d="%s" fill="%s"/>', spieltreff_suit_path( $suit ), $color );
			$out  .= sprintf( '<path d="%s" fill="%s" transform="translate(-10.5 -17) scale(.42)"/>', spieltreff_suit_path( $suit ), $color );
		}
		$out .= '</g>';
	}
	foreach ( $l['dice'] as $d ) {
		list( $x, $y, $a, $n ) = $d;
		$out .= sprintf( '<g transform="translate(%s %s) rotate(%s)"><rect x="-10" y="-10" width="20" height="20" rx="4" fill="#ffffff"/>', $x, $y, $a );
		foreach ( spieltreff_pips( $n ) as $p ) {
			$out .= sprintf( '<circle cx="%s" cy="%s" r="2" fill="#1d2420"/>', $p[0] * 11, $p[1] * 11 );
		}
		$out .= '</g>';
	}
	list( $x, $y, $a ) = $l['phone'];
	$out .= sprintf( '<g transform="translate(%s %s) rotate(%s)"><rect x="-14" y="-27" width="28" height="54" rx="6" fill="#1d2420"/><rect x="-9" y="-21" width="9" height="12" rx="3" fill="#3a443e"/><circle cx="-4.5" cy="-18" r="2" fill="#6b726d"/><circle cx="-4.5" cy="-12" r="2" fill="#6b726d"/></g>', $x, $y, $a );
	$out .= '</svg>';
	return $out;
}

/* Bilder für Social Media */

add_action( 'admin_post_spieltreff_kampagne', 'spieltreff_handle_campaign_image' );
add_action( 'admin_post_nopriv_spieltreff_kampagne', 'spieltreff_handle_campaign_image' );

/** Formate: og (Facebook/WhatsApp), post (Instagram-Beitrag), story (Instagram/WhatsApp-Status). */
function spieltreff_campaign_formats() {
	return array(
		'og'    => array( 1200, 630, __( 'Facebook / WhatsApp', 'spieltafel' ) ),
		'post'  => array( 1080, 1080, __( 'Instagram-Beitrag', 'spieltafel' ) ),
		'story' => array( 1080, 1920, __( 'Story', 'spieltafel' ) ),
	);
}

/**
 * Adresse eines Kampagnenbildes.
 *
 * @param string $format og | post | story.
 * @param int    $slogan 1–3.
 * @param string $phase  bald | live. Leer = aktueller Stand.
 */
function spieltreff_campaign_url( $format = 'og', $slogan = 1, $phase = '' ) {
	$phase = $phase ? $phase : ( spieltreff_is_live() ? 'live' : 'bald' );
	return add_query_arg(
		array(
			'action' => 'spieltreff_kampagne',
			'f'      => $format,
			't'      => (int) $slogan,
			'p'      => $phase,
			'v'      => SPIELTAFEL_VERSION,
		),
		admin_url( 'admin-post.php' )
	);
}

function spieltreff_handle_campaign_image() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- öffentliches Bild ohne Personendaten.
	$format = isset( $_GET['f'] ) ? sanitize_key( $_GET['f'] ) : 'og';
	$slogan = isset( $_GET['t'] ) ? (int) $_GET['t'] : 1;
	$phase  = isset( $_GET['p'] ) && 'live' === $_GET['p'] ? 'live' : 'bald';
	$dl     = ! empty( $_GET['dl'] );
	// phpcs:enable
	if ( ! isset( spieltreff_campaign_formats()[ $format ] ) || ! isset( spieltreff_slogans()[ $slogan ] ) || ! function_exists( 'imagettftext' ) ) {
		status_header( 404 );
		exit;
	}
	$png  = spieltreff_render_campaign( $format, $slogan, $phase );
	$name = sprintf( 'spieltreff-%s-%d-%s.png', $phase, $slogan, $format );
	header( 'Content-Type: image/png' );
	header( 'Cache-Control: public, max-age=86400' );
	header( 'Content-Disposition: ' . ( $dl ? 'attachment' : 'inline' ) . '; filename="' . $name . '"' );
	echo $png; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Bilddaten.
	exit;
}

/** Punkt um 0/0 drehen (Grad, im Uhrzeigersinn wie in SVG). */
function spieltreff_rot( $x, $y, $deg ) {
	$r = deg2rad( $deg );
	return array( $x * cos( $r ) - $y * sin( $r ), $x * sin( $r ) + $y * cos( $r ) );
}

/** Gedrehtes Rechteck mit abgeschrägten Ecken als Polygon. */
function spieltreff_gd_rect( $im, $cx, $cy, $w, $h, $deg, $k, $s, $color, $chamfer = 0 ) {
	$c   = $chamfer;
	$pts = array( array( -$w / 2 + $c, -$h / 2 ), array( $w / 2 - $c, -$h / 2 ), array( $w / 2, -$h / 2 + $c ), array( $w / 2, $h / 2 - $c ), array( $w / 2 - $c, $h / 2 ), array( -$w / 2 + $c, $h / 2 ), array( -$w / 2, $h / 2 - $c ), array( -$w / 2, -$h / 2 + $c ) );
	$out = array();
	foreach ( $pts as $p ) {
		$r     = spieltreff_rot( $p[0], $p[1], $deg );
		$out[] = (int) round( $k + ( $cx + $r[0] ) * $s );
		$out[] = (int) round( $k + ( $cy + $r[1] ) * $s );
	}
	imagefilledpolygon( $im, $out, $color );
}

/** SVG-ähnliches Symbol: Herz, Schaufel oder Ecke, grob mit Kreisen und Polygonen. */
function spieltreff_gd_suit( $im, $cx, $cy, $deg, $size, $suit, $k, $s, $color ) {
	$map = function ( $x, $y ) use ( $cx, $cy, $deg, $size, $k, $s ) {
		$r = spieltreff_rot( $x * $size, $y * $size, $deg );
		return array( (int) round( $k + ( $cx + $r[0] ) * $s ), (int) round( $k + ( $cy + $r[1] ) * $s ) );
	};
	$poly = function ( $pts ) use ( $im, $map, $color ) {
		$flat = array();
		foreach ( $pts as $p ) {
			$m      = $map( $p[0], $p[1] );
			$flat[] = $m[0];
			$flat[] = $m[1];
		}
		imagefilledpolygon( $im, $flat, $color );
	};
	$dot = function ( $x, $y, $r ) use ( $im, $map, $size, $s, $color ) {
		$m = $map( $x, $y );
		imagefilledellipse( $im, $m[0], $m[1], (int) ( 2 * $r * $size * $s ), (int) ( 2 * $r * $size * $s ), $color );
	};
	if ( 'herz' === $suit ) {
		$dot( -3.6, -3, 3.9 );
		$dot( 3.6, -3, 3.9 );
		$poly( array( array( -7.4, -2 ), array( 7.4, -2 ), array( 0, 7.5 ) ) );
	} elseif ( 'schaufel' === $suit ) {
		$dot( -3.6, 2, 3.9 );
		$dot( 3.6, 2, 3.9 );
		$poly( array( array( -7.4, 1 ), array( 7.4, 1 ), array( 0, -8 ) ) );
		$poly( array( array( -.8, 3 ), array( .8, 3 ), array( 2.4, 8 ), array( -2.4, 8 ) ) );
	} else {
		$poly( array( array( 0, -8 ), array( 6, 0 ), array( 0, 8 ), array( -6, 0 ) ) );
	}
}

/**
 * Zeichnet die Illustration mit GD, doppelt so gross und dann verkleinert (glatte Kanten).
 *
 * @param resource|GdImage $dst Zielbild.
 * @param int              $cx  Mitte x im Ziel.
 * @param int              $cy  Mitte y im Ziel.
 * @param int              $r   Tischradius in Pixeln.
 */
function spieltreff_gd_table( $dst, $cx, $cy, $r ) {
	$s   = $r / 100 * 2; // Pixel pro Einheit in der doppelten Grösse.
	$k   = 140 * $s;     // Verschiebung, damit 0/0 in der Mitte liegt (Platz für das Handy).
	$w   = (int) ( 280 * $s );
	$im  = imagecreatetruecolor( $w, $w );
	imagesavealpha( $im, true );
	imagealphablending( $im, false );
	imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 0, 0, 0, 127 ) );
	imagealphablending( $im, true );
	$col = function ( $hex ) use ( $im ) {
		return imagecolorallocate( $im, hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	};
	$l = spieltreff_table_layout();
	imagefilledellipse( $im, (int) $k, (int) $k, (int) ( 208 * $s ), (int) ( 208 * $s ), $col( '#17573a' ) );
	imagefilledellipse( $im, (int) $k, (int) $k, (int) ( 196 * $s ), (int) ( 196 * $s ), $col( '#1f6f4a' ) );
	// Gestrichelter Ring.
	$ring = $col( '#2f8a5f' );
	for ( $a = 0; $a < 360; $a += 4.6 ) {
		$p = spieltreff_rot( 0, -88, $a );
		imagefilledellipse( $im, (int) ( $k + $p[0] * $s ), (int) ( $k + $p[1] * $s ), (int) ( 1.6 * $s ), (int) ( 1.6 * $s ), $ring );
	}
	foreach ( $l['cards'] as $c ) {
		list( $x, $y, $a, $suit ) = $c;
		if ( 'rueckseite' === $suit ) {
			spieltreff_gd_rect( $im, $x, $y, 34, 50, $a, $k, $s, $col( '#ffffff' ), 3 );
			spieltreff_gd_rect( $im, $x, $y, 28, 44, $a, $k, $s, $col( '#e9b949' ), 2 );
		} else {
			spieltreff_gd_rect( $im, $x, $y, 35, 51, $a, $k, $s, $col( '#e0dccf' ), 3 );
			spieltreff_gd_rect( $im, $x, $y, 34, 50, $a, $k, $s, $col( '#ffffff' ), 3 );
			$color = 'schaufel' === $suit ? $col( '#1d2420' ) : $col( '#c0563b' );
			spieltreff_gd_suit( $im, $x, $y, $a, 1, $suit, $k, $s, $color );
			$o = spieltreff_rot( -10.5, -17, $a );
			spieltreff_gd_suit( $im, $x + $o[0], $y + $o[1], $a, .42, $suit, $k, $s, $color );
		}
	}
	foreach ( $l['dice'] as $d ) {
		list( $x, $y, $a, $n ) = $d;
		spieltreff_gd_rect( $im, $x, $y, 20, 20, $a, $k, $s, $col( '#ffffff' ), 3 );
		foreach ( spieltreff_pips( $n ) as $p ) {
			$o = spieltreff_rot( $p[0] * 11, $p[1] * 11, $a );
			imagefilledellipse( $im, (int) ( $k + ( $x + $o[0] ) * $s ), (int) ( $k + ( $y + $o[1] ) * $s ), (int) ( 4 * $s ), (int) ( 4 * $s ), $col( '#1d2420' ) );
		}
	}
	list( $x, $y, $a ) = $l['phone'];
	spieltreff_gd_rect( $im, $x, $y, 28, 54, $a, $k, $s, $col( '#1d2420' ), 5 );
	$o = spieltreff_rot( -4.5, -15, $a );
	spieltreff_gd_rect( $im, $x + $o[0], $y + $o[1], 9, 12, $a, $k, $s, $col( '#3a443e' ), 2.5 );
	foreach ( array( -18, -12 ) as $py ) {
		$o = spieltreff_rot( -4.5, $py, $a );
		imagefilledellipse( $im, (int) ( $k + ( $x + $o[0] ) * $s ), (int) ( $k + ( $y + $o[1] ) * $s ), (int) ( 4 * $s ), (int) ( 4 * $s ), $col( '#6b726d' ) );
	}
	$half = (int) ( $w / 2 );
	imagecopyresampled( $dst, $im, (int) ( $cx - $k / 2 ), (int) ( $cy - $k / 2 ), 0, 0, $half, $half, $w, $w );
	imagedestroy( $im );
}

/**
 * Zeichnet ein Kampagnenbild im Stil der Teilen-Bilder der Einträge.
 *
 * @return string PNG
 */
function spieltreff_render_campaign( $format, $slogan, $phase ) {
	list( $w, $h ) = spieltreff_campaign_formats()[ $format ];
	$bold          = SPIELTAFEL_DIR . '/assets/fonts/Inter-Bold.otf';
	$reg           = SPIELTAFEL_DIR . '/assets/fonts/Inter-Regular.otf';
	$im            = imagecreatetruecolor( $w, $h );
	imagealphablending( $im, true );
	$col     = function ( $hex ) use ( $im ) {
		return imagecolorallocate( $im, hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	};
	$bg      = $col( '#f6f3ec' );
	$green   = $col( '#1f6f4a' );
	$light   = $col( '#cfe5d9' );
	$ink     = $col( '#1d2420' );
	$muted   = $col( '#5d655f' );
	$white   = $col( '#ffffff' );
	$gold    = $col( '#e9b949' );
	$surface = $col( '#efebe1' );
	imagefilledrectangle( $im, 0, 0, $w, $h, $bg );
	$text = function ( $s, $size, $x, $y, $color, $font ) use ( $im ) {
		imagettftext( $im, $size, 0, (int) $x, (int) $y, $color, $font, $s );
		$box = imagettfbbox( $size, 0, $font, $s );
		return $box[2] - $box[0];
	};

	$og    = 'og' === $format;
	$story = 'story' === $format;
	$m     = $og ? 64 : 84;
	$fh    = $og ? 110 : ( $story ? 300 : 150 ); // Höhe des grünen Fusses.

	// Illustration mit hellem Kreis dahinter (in der Story erst nach dem Text, siehe unten).
	$table = function ( $tx, $ty, $tr ) use ( $im, $surface ) {
		imagefilledellipse( $im, (int) $tx, (int) $ty, (int) ( $tr * 2.75 ), (int) ( $tr * 2.75 ), $surface );
		spieltreff_gd_table( $im, (int) $tx, (int) $ty, (int) $tr );
	};
	if ( $og ) {
		$table( 930, 290, 175 );
	} elseif ( ! $story ) {
		$table( 780, 690, 175 );
	}

	// Kopf: App-Symbol und Name.
	$icon_size = $og ? 76 : ( $story ? 110 : 88 );
	$top       = $og ? 52 : ( $story ? 120 : 76 );
	$icon      = @imagecreatefrompng( SPIELTAFEL_DIR . '/assets/img/app-icon.png' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( $icon ) {
		imagecopyresampled( $im, $icon, $m, $top, 0, 0, $icon_size, $icon_size, imagesx( $icon ), imagesy( $icon ) );
		imagedestroy( $icon );
	}
	$text( get_bloginfo( 'name' ) . ' · ' . __( 'Spieltreff', 'spieltafel' ), $og ? 26 : ( $story ? 38 : 32 ), $m + $icon_size + 24, $top + $icon_size / 2 + ( $og ? 10 : 13 ), $ink, $bold );

	// Etikett.
	$label = mb_strtoupper( 'live' === $phase ? __( 'Jetzt mitspielen', 'spieltafel' ) : __( 'Bald neu', 'spieltafel' ) );
	$y     = $og ? 196 : ( $story ? 420 : 272 );
	$lsize = $og ? 18 : ( $story ? 28 : 24 );
	$box   = imagettfbbox( $lsize, 0, $bold, $label );
	spieltreff_round_rect( $im, $m, $y - $lsize - 22, $m + ( $box[2] - $box[0] ) + 44, $y + 18, $og ? 18 : 24, $gold );
	$text( $label, $lsize, $m + 22, $y, $ink, $bold );

	// Satz, gross.
	$tsize = $og ? 54 : ( $story ? 96 : 66 );
	$tw    = $og ? 560 : ( $story ? $w - 2 * $m : $w - 2 * $m );
	$lines = spieltreff_wrap( spieltreff_slogans()[ $slogan ], $bold, $tsize, $tw, 3 );
	$y    += $og ? 90 : ( $story ? 170 : 110 );
	foreach ( $lines as $line ) {
		$text( $line, $tsize, $m, $y, $ink, $bold );
		$y += (int) ( $tsize * 1.22 );
	}

	// Unterzeile.
	$sub   = 'live' === $phase
		? __( 'Jassrunden, Brett- und Kartenspielabende in deiner Region. Mach mit oder lade selbst ein.', 'spieltafel' )
		: __( 'Der Spieltreff bringt Leute zum Spielen zusammen: Jass, Brett- und Kartenspiele in deiner Region.', 'spieltafel' );
	$ssize = $og ? 24 : ( $story ? 40 : 28 );
	$sw    = $og ? 540 : ( $story ? $w - 2 * $m : 430 );
	$y    += $og ? 0 : 10;
	foreach ( spieltreff_wrap( $sub, $reg, $ssize, $sw, $story ? 3 : 6 ) as $line ) {
		$text( $line, $ssize, $m, $y, $muted, $reg );
		$y += (int) ( $ssize * 1.5 );
	}

	// Story: Illustration im Platz zwischen Text und Fuss.
	if ( $story ) {
		$top   = $y + 10;
		$space = $h - $fh - $top;
		$tr    = min( 280, (int) ( $space / 2.3 ) );
		$table( $w / 2, $top + $space / 2, $tr );
	}

	// Fuss.
	$host  = wp_parse_url( home_url(), PHP_URL_HOST );
	$path  = wp_parse_url( spieltreff_url(), PHP_URL_PATH );
	$where = preg_replace( '/^www\./', '', (string) $host ) . rtrim( (string) $path, '/' );
	$cta   = 'live' === $phase ? __( 'Mitspieler finden:', 'spieltafel' ) : __( 'Bald auf', 'spieltafel' );
	imagefilledrectangle( $im, 0, $h - $fh, $w, $h, $green );
	if ( $story ) {
		$text( $cta, 36, $m, $h - $fh + 110, $light, $reg );
		$text( $where, 46, $m, $h - $fh + 185, $white, $bold );
		$text( 'live' === $phase ? __( 'Link im Sticker antippen', 'spieltafel' ) : __( 'Sag es deiner Jassrunde weiter.', 'spieltafel' ), 30, $m, $h - $fh + 245, $light, $reg );
	} else {
		$size = $og ? 26 : 30;
		$cw   = $text( $cta . ' ', $size, $m, $h - (int) ( $fh / 2 ) + (int) ( $size / 2 ), $light, $reg );
		$text( $where, $size, $m + $cw + 8, $h - (int) ( $fh / 2 ) + (int) ( $size / 2 ), $white, $bold );
	}

	ob_start();
	imagepng( $im, null, 6 );
	imagedestroy( $im );
	return (string) ob_get_clean();
}
