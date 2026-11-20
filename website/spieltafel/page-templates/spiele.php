<?php
/**
 * Template Name: Spiele & Anleitungen
 *
 * Alle Spiele der App mit Kurzanleitung.
 *
 * @package Spieltafel
 */

get_header();
$games = spieltafel_games();
?>
<section class="section">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Spiele & Anleitungen', 'spieltafel' ); ?></span>
			<h1><?php the_title(); ?></h1>
			<p>
				<?php
				/* translators: %d: Anzahl Spiele */
				echo esc_html( sprintf( __( 'Diese %d Spiele kennt die Spieltafel, weitere sind mit «Bald» markiert. Zu jedem gibt es eine Kurzanleitung, die auch in der App steht, und wo vorhanden einen Link zur ausführlichen Anleitung.', 'spieltafel' ), count( spieltafel_available_games() ) ) );
				?>
			</p>
		</div>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( get_the_content() ) {
				echo '<div class="entry-content" style="margin-bottom:28px">';
				the_content();
				echo '</div>';
			}
		endwhile;
		?>
		<nav class="facts" aria-label="<?php esc_attr_e( 'Spiele', 'spieltafel' ); ?>">
			<?php foreach ( $games as $game ) : ?>
				<a class="chip" href="#spiel-<?php echo esc_attr( $game['id'] ); ?>"><?php echo esc_html( $game['name'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="guide-list">
			<?php
			foreach ( $games as $game ) {
				get_template_part( 'template-parts/guide', null, array( 'game' => $game ) );
			}
			?>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/section-wish', null, array( 'alt' => true ) );
get_footer();
