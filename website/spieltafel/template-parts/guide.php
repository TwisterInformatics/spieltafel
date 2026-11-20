<?php
/**
 * Kurzanleitung eines Spiels.
 *
 * @package Spieltafel
 * @var array $args { game: array, heading: 'h1'|'h2' }
 */

$game    = $args['game'];
$heading = $args['heading'] ?? 'h2';
?>
<article class="guide" id="spiel-<?php echo esc_attr( $game['id'] ); ?>">
	<div class="guide-head">
		<span class="game-dot" style="background:<?php echo esc_attr( $game['color'] ); ?>" aria-hidden="true"><?php echo esc_html( spieltafel_initial( $game ) ); ?></span>
		<div>
			<<?php echo esc_html( $heading ); ?>><?php echo esc_html( $game['name'] ); ?></<?php echo esc_html( $heading ); ?>>
			<span class="meta"><?php echo esc_html( $game['category'] ); ?><?php if ( ! empty( $game['upcoming'] ) ) : ?> · <span class="badge-soon"><?php esc_html_e( 'Bald', 'spieltafel' ); ?></span><?php endif; ?></span>
		</div>
	</div>
	<?php if ( ! empty( $game['upcoming'] ) ) : ?>
		<p class="hint"><?php esc_html_e( 'Kommt mit der nächsten Version der App. Bis dahin lässt sich das Spiel mit der freien Zählung festhalten.', 'spieltafel' ); ?></p>
	<?php endif; ?>
	<ul class="facts">
		<li><?php echo esc_html( $game['players'] ); ?></li>
		<li><?php echo esc_html( $game['winner'] ); ?></li>
		<li><?php echo esc_html( $game['end'] ); ?></li>
	</ul>
	<h3><?php esc_html_e( 'Kurzanleitung', 'spieltafel' ); ?></h3>
	<ol>
		<?php foreach ( $game['guide'] as $line ) : ?>
			<li><?php echo esc_html( $line ); ?></li>
		<?php endforeach; ?>
	</ol>
	<?php if ( ! empty( $game['modes'] ) ) : ?>
		<div class="modes">
			<h3><?php esc_html_e( 'Spielarten und Faktoren in der App', 'spieltafel' ); ?></h3>
			<table>
				<thead><tr><th><?php esc_html_e( 'Spielart', 'spieltafel' ); ?></th><th><?php esc_html_e( 'Faktor', 'spieltafel' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $game['modes'] as $mode ) : ?>
					<tr><td><?php echo esc_html( $mode['name'] ); ?></td><td>× <?php echo esc_html( $mode['factor'] ); ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="meta"><small><?php esc_html_e( 'Mit diesen Faktoren rechnet die App. Viele Runden haben eigene Hausregeln.', 'spieltafel' ); ?></small></p>
		</div>
	<?php endif; ?>
	<div class="guide-actions">
		<?php if ( ! empty( $game['rules'] ) ) : ?>
			<a class="rules-link" href="<?php echo esc_url( $game['rules']['url'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php
				/* translators: %s: Quelle, z. B. Wikipedia */
				echo esc_html( sprintf( __( 'Ausführliche Anleitung bei %s', 'spieltafel' ), $game['rules']['source'] ) );
				?>
				<span aria-hidden="true">↗</span>
			</a>
		<?php else : ?>
			<span></span>
		<?php endif; ?>
		<?php if ( 'h2' === $heading ) : ?>
			<a class="back" href="<?php echo esc_url( spieltafel_game_url( $game ) ); ?>"><?php esc_html_e( 'Eigene Seite', 'spieltafel' ); ?></a>
		<?php endif; ?>
	</div>
</article>
