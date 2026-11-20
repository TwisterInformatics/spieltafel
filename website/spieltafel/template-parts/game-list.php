<?php
/**
 * Alle Spiele mit Suche und Kategorien, wie in der App.
 *
 * @package Spieltafel
 */

?>
<div class="game-tools">
	<label class="screen-reader-text" for="spielsuche"><?php esc_html_e( 'Spiel suchen', 'spieltafel' ); ?></label>
	<input class="game-search" id="spielsuche" type="search" placeholder="<?php esc_attr_e( 'Spiel suchen, zum Beispiel Jass', 'spieltafel' ); ?>" data-game-search hidden>
	<div class="chips" role="group" aria-label="<?php esc_attr_e( 'Kategorie', 'spieltafel' ); ?>" data-game-chips hidden>
		<button class="chip" type="button" data-category="" aria-pressed="true"><?php esc_html_e( 'Alle', 'spieltafel' ); ?></button>
		<?php foreach ( spieltafel_categories() as $category ) : ?>
			<button class="chip" type="button" data-category="<?php echo esc_attr( $category ); ?>" aria-pressed="false"><?php echo esc_html( $category ); ?></button>
		<?php endforeach; ?>
	</div>
</div>
<div class="games" data-games>
	<?php
	foreach ( spieltafel_games() as $game ) {
		get_template_part( 'template-parts/game-card', null, array( 'game' => $game ) );
	}
	?>
</div>
<p class="games-empty" data-games-empty hidden>
	<?php esc_html_e( 'Kein Spiel gefunden. In der App geht jedes Spiel mit der freien Zählung, und du kannst es dir unten wünschen.', 'spieltafel' ); ?>
</p>
