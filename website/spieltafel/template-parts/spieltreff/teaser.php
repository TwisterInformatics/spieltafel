<?php
/**
 * Vorschau auf den Spieltreff vor der Freischaltung, Kampagne «Zurück an den Tisch».
 * Auf /spieltreff/ ausführlich (heading h1), auf der Startseite kürzer.
 *
 * @package Spieltafel
 * @var array $args { heading: h1|h2, alt: bool }
 */

$full = ( $args['heading'] ?? 'h2' ) === 'h1';
$tag  = $full ? 'h1' : 'h2';
$text = spieltreff_setting( 'teaser_text' );
$text = $text ? $text : spieltreff_default_teaser();
?>
<section class="st-hero" id="spieltreff">
	<div class="wrap st-hero-grid">
		<div class="st-hero-text">
			<span class="st-pill"><?php esc_html_e( 'Bald neu · Spieltreff', 'spieltafel' ); ?></span>
			<<?php echo esc_html( $tag ); ?>><?php echo esc_html( spieltreff_slogans()[1] ); ?></<?php echo esc_html( $tag ); ?>>
			<p class="lead"><?php echo esc_html( $text ); ?></p>
			<p class="st-hero-note"><span aria-hidden="true">🃏</span> <?php esc_html_e( 'Wir decken gerade den Tisch. Bald geht es los.', 'spieltafel' ); ?></p>
			<?php if ( ! is_user_logged_in() ) : ?>
				<p class="st-small"><a href="<?php echo esc_url( spieltreff_url( 'anmelden' ) ); ?>"><?php esc_html_e( 'Anmelden für Testerinnen und Tester', 'spieltafel' ); ?></a></p>
			<?php endif; ?>
		</div>
		<div class="st-hero-art"><?php echo spieltreff_table_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- im Theme gebautes SVG. ?></div>
	</div>
</section>
<section class="section<?php echo $full ? '' : ' section-alt'; ?> st-after-hero">
	<div class="wrap">
		<?php get_template_part( 'template-parts/spieltreff/manifest', null, array( 'heading' => 'h2' ) ); ?>
		<?php if ( $full ) : ?>
			<h2 class="st-manifest-title"><?php esc_html_e( 'Was dich erwartet', 'spieltafel' ); ?></h2>
			<ul class="st-teaser-list">
				<li><span aria-hidden="true">🎲</span><div><strong><?php esc_html_e( 'Spielabend anbieten', 'spieltafel' ); ?></strong><?php esc_html_e( 'Datum, Ort und Spiele angeben, Plätze festlegen. Du bestätigst, wer dabei ist.', 'spieltafel' ); ?></div></li>
				<li><span aria-hidden="true">🔎</span><div><strong><?php esc_html_e( 'Mitspieler suchen', 'spieltafel' ); ?></strong><?php esc_html_e( 'Zum Beispiel: Gruppe für Brändi Dog, jeden Donnerstag in Bern.', 'spieltafel' ); ?></div></li>
				<li><span aria-hidden="true">📋</span><div><strong><?php esc_html_e( 'Warteliste und Mindestanzahl', 'spieltafel' ); ?></strong><?php esc_html_e( 'Ist alles voll, rückst du automatisch nach. Ein Abend kann auch nur bei genügend Anmeldungen stattfinden.', 'spieltafel' ); ?></div></li>
				<li><span aria-hidden="true">🔒</span><div><strong><?php esc_html_e( 'Deine Daten bleiben geschützt', 'spieltafel' ); ?></strong><?php esc_html_e( 'Andere sehen nur deinen Anzeigenamen. Die Adresse sehen nur bestätigte Teilnehmende.', 'spieltafel' ); ?></div></li>
			</ul>
		<?php endif; ?>
	</div>
</section>
