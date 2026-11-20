<?php
/**
 * Ein Spiel als Karte, verlinkt auf seine Anleitung.
 *
 * @package Spieltafel
 * @var array $args { game: array }
 */

$game = $args['game'];
$hay  = strtolower( remove_accents( $game['name'] . ' ' . $game['category'] . ' ' . $game['description'] . ' ' . implode( ' ', $game['keywords'] ?? array() ) ) );
?>
<a class="game-card" href="<?php echo esc_url( spieltafel_game_url( $game ) ); ?>" data-category="<?php echo esc_attr( $game['category'] ); ?>" data-search="<?php echo esc_attr( $hay ); ?>">
	<span class="game-dot" style="background:<?php echo esc_attr( $game['color'] ); ?>" aria-hidden="true"><?php echo esc_html( spieltafel_initial( $game ) ); ?></span>
	<span>
		<strong><?php echo esc_html( $game['name'] ); ?><?php if ( ! empty( $game['upcoming'] ) ) : ?> <span class="badge-soon"><?php esc_html_e( 'Bald', 'spieltafel' ); ?></span><?php endif; ?></strong>
		<span class="meta"><?php echo esc_html( $game['description'] ); ?></span>
	</span>
</a>
