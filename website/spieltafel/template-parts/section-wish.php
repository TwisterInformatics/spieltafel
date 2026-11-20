<?php
/**
 * Abschnitt «Spiel wünschen» mit Text und Formular.
 *
 * @package Spieltafel
 * @var array $args { alt: bool }
 */

?>
<section class="section<?php echo ! empty( $args['alt'] ) ? ' section-alt' : ''; ?>" id="spielwunsch">
	<div class="wrap wish">
		<div>
			<span class="eyebrow"><?php esc_html_e( 'Spielwunsch', 'spieltafel' ); ?></span>
			<h2><?php esc_html_e( 'Dein Spiel fehlt?', 'spieltafel' ); ?></h2>
			<p><?php esc_html_e( 'Sag uns, welches Spiel du in der Spieltafel möchtest. Am meisten hilft uns, wenn du kurz beschreibst, wie bei euch gezählt wird.', 'spieltafel' ); ?></p>
			<p class="hint"><?php esc_html_e( 'Bis dein Spiel dabei ist: Mit der freien Zählung lässt sich jedes Spiel mit Punkten festhalten.', 'spieltafel' ); ?></p>
		</div>
		<?php get_template_part( 'template-parts/wish-form' ); ?>
	</div>
</section>
