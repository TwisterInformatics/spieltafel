<?php
/**
 * Hinweis auf den Spieltreff vor der Freischaltung.
 *
 * @package Spieltafel
 * @var array $args { heading: h1|h2, alt: bool }
 */

$tag  = ( $args['heading'] ?? 'h2' ) === 'h1' ? 'h1' : 'h2';
$text = spieltreff_setting( 'teaser_text' );
$text = $text ? $text : spieltreff_default_teaser();
?>
<section class="section<?php echo ! empty( $args['alt'] ) ? ' section-alt' : ''; ?>" id="spieltreff">
	<div class="wrap st-teaser">
		<div>
			<span class="eyebrow"><?php esc_html_e( 'Bald neu', 'spieltafel' ); ?></span>
			<<?php echo esc_html( $tag ); ?>><?php esc_html_e( 'Spieltreff: Mitspieler finden', 'spieltafel' ); ?></<?php echo esc_html( $tag ); ?>>
			<p class="lead"><?php echo esc_html( $text ); ?></p>
			<p class="hint"><?php esc_html_e( 'Wir bauen gerade daran. Schau bald wieder vorbei!', 'spieltafel' ); ?></p>
			<?php if ( ! is_user_logged_in() ) : ?>
				<p class="st-small"><a href="<?php echo esc_url( spieltreff_url( 'anmelden' ) ); ?>"><?php esc_html_e( 'Anmelden für Testerinnen und Tester', 'spieltafel' ); ?></a></p>
			<?php endif; ?>
		</div>
		<ul class="st-teaser-list">
			<li><span aria-hidden="true">🎲</span><div><strong><?php esc_html_e( 'Spielabend anbieten', 'spieltafel' ); ?></strong><?php esc_html_e( 'Datum, Ort und Spiele angeben, Plätze festlegen. Du bestätigst, wer dabei ist.', 'spieltafel' ); ?></div></li>
			<li><span aria-hidden="true">🔎</span><div><strong><?php esc_html_e( 'Mitspieler suchen', 'spieltafel' ); ?></strong><?php esc_html_e( 'Zum Beispiel: Gruppe für Brändi Dog, jeden Donnerstag in Bern.', 'spieltafel' ); ?></div></li>
			<li><span aria-hidden="true">📋</span><div><strong><?php esc_html_e( 'Warteliste und Mindestanzahl', 'spieltafel' ); ?></strong><?php esc_html_e( 'Ist alles voll, rückst du automatisch nach. Ein Abend kann auch nur bei genügend Anmeldungen stattfinden.', 'spieltafel' ); ?></div></li>
			<li><span aria-hidden="true">🔒</span><div><strong><?php esc_html_e( 'Deine Daten bleiben geschützt', 'spieltafel' ); ?></strong><?php esc_html_e( 'Andere sehen nur deinen Anzeigenamen. Die Adresse sehen nur bestätigte Teilnehmende.', 'spieltafel' ); ?></div></li>
		</ul>
	</div>
</section>
