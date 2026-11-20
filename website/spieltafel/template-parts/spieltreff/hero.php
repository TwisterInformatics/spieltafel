<?php
/**
 * Kopf der Spieltreff-Übersicht nach der Freischaltung, Kampagne «Zurück an den Tisch».
 *
 * @package Spieltafel
 * @var array $args { angebote: int, gesuche: int }
 */

$angebote = (int) ( $args['angebote'] ?? 0 );
$gesuche  = (int) ( $args['gesuche'] ?? 0 );
?>
<section class="st-hero st-hero-live">
	<div class="wrap st-hero-grid">
		<div class="st-hero-text">
			<span class="st-pill"><?php esc_html_e( 'Spieltreff · Mitspieler finden', 'spieltafel' ); ?></span>
			<h1><?php echo esc_html( spieltreff_slogans()[1] ); ?></h1>
			<p class="lead"><?php esc_html_e( 'Jassrunde, Brettspielabend oder eine feste Gruppe für jeden Donnerstag: Hier findest du Leute in deiner Nähe, die lieber Karten mischen als scrollen. Mach mit oder lade selbst ein.', 'spieltafel' ); ?></p>
			<div class="st-hero-actions">
				<a class="btn btn-light" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => 'angebot' ) ) ); ?>"><?php esc_html_e( 'Spielabend anbieten', 'spieltafel' ); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => 'gesuch' ) ) ); ?>"><?php esc_html_e( 'Mitspieler suchen', 'spieltafel' ); ?></a>
			</div>
			<?php if ( $angebote || $gesuche ) : ?>
				<p class="st-hero-note">
					<?php
					$parts = array();
					if ( $angebote ) {
						/* translators: %d: Anzahl Spielabende */
						$parts[] = sprintf( _n( '%d offener Spielabend', '%d offene Spielabende', $angebote, 'spieltafel' ), $angebote );
					}
					if ( $gesuche ) {
						/* translators: %d: Anzahl Gesuche */
						$parts[] = sprintf( _n( '%d Gesuch', '%d Gesuche', $gesuche, 'spieltafel' ), $gesuche );
					}
					echo esc_html( implode( ' · ', $parts ) );
					?>
				</p>
			<?php endif; ?>
		</div>
		<div class="st-hero-art"><?php echo spieltreff_table_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- im Theme gebautes SVG. ?></div>
	</div>
</section>
