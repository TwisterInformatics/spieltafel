<?php
/**
 * Template Name: Spiel wünschen
 *
 * Formular für Spielwünsche.
 *
 * @package Spieltafel
 */

get_header();
?>
<section class="section" id="spielwunsch">
	<div class="wrap wish">
		<div>
			<span class="eyebrow"><?php esc_html_e( 'Spielwunsch', 'spieltafel' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<?php
			while ( have_posts() ) :
				the_post();
				if ( get_the_content() ) {
					echo '<div class="entry-content">';
					the_content();
					echo '</div>';
				} else {
					echo '<p>' . esc_html__( 'Welches Spiel fehlt dir in der Spieltafel? Schreib uns den Namen und am besten, wie bei euch gezählt wird. Wir sammeln alle Wünsche und nehmen die häufigsten in die nächsten Versionen auf.', 'spieltafel' ) . '</p>';
				}
			endwhile;
			?>
			<p class="hint"><?php esc_html_e( 'Bis dein Spiel dabei ist: Mit der freien Zählung lässt sich jedes Spiel mit Punkten festhalten.', 'spieltafel' ); ?></p>
			<p><a href="<?php echo esc_url( spieltafel_page_url( 'spiele' ) ); ?>"><?php esc_html_e( 'Diese Spiele sind schon dabei', 'spieltafel' ); ?></a></p>
		</div>
		<?php get_template_part( 'template-parts/wish-form' ); ?>
	</div>
</section>
<?php
get_footer();
