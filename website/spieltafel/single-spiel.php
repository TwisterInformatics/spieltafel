<?php
/**
 * Eigene Seite eines Spiels: /spiele/<id>/
 *
 * @package Spieltafel
 */

get_header();
$game  = spieltafel_current_game();
$games = spieltafel_games();
$index = array_search( $game['id'], wp_list_pluck( $games, 'id' ), true );
$prev  = $games[ $index - 1 ] ?? null;
$next  = $games[ $index + 1 ] ?? null;
?>
<section class="section">
	<div class="wrap" style="max-width:820px">
		<p><a href="<?php echo esc_url( spieltafel_page_url( 'spiele' ) ); ?>">← <?php esc_html_e( 'Alle Spiele', 'spieltafel' ); ?></a></p>
		<?php get_template_part( 'template-parts/guide', null, array( 'game' => $game, 'heading' => 'h1' ) ); ?>
		<div class="hint" style="margin-top:22px">
			<?php
			/* translators: %s: Name des Spiels */
			echo esc_html( sprintf( __( 'In der Spieltafel wählst du %s, trägst die Spieler ein und gibst nach jeder Runde die Punkte ein. Den Rest rechnet die App.', 'spieltafel' ), $game['name'] ) );
			?>
		</div>
		<?php get_template_part( 'template-parts/store-badges' ); ?>
		<nav class="guide-actions" style="margin-top:28px" aria-label="<?php esc_attr_e( 'Weitere Spiele', 'spieltafel' ); ?>">
			<?php if ( $prev ) : ?>
				<a href="<?php echo esc_url( spieltafel_game_url( $prev ) ); ?>">← <?php echo esc_html( $prev['name'] ); ?></a>
			<?php else : ?>
				<span></span>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<a href="<?php echo esc_url( spieltafel_game_url( $next ) ); ?>"><?php echo esc_html( $next['name'] ); ?> →</a>
			<?php endif; ?>
		</nav>
	</div>
</section>
<?php
get_footer();
