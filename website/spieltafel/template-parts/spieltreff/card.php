<?php
/**
 * Ein Eintrag in der Übersicht.
 *
 * @package Spieltafel
 * @var array $args { post: WP_Post }
 */

$e      = spieltreff_entry( $args['post'] );
$c      = spieltreff_counts( $e['id'] );
$games  = spieltreff_game_names( $e );
$free   = spieltreff_free_spots( $e );
$state  = spieltreff_state( $e );
?>
<a class="st-card" href="<?php echo esc_url( get_permalink( $e['id'] ) ); ?>">
	<div class="st-card-when">
		<?php if ( 'angebot' === $e['typ'] && $e['start'] ) : ?>
			<span class="st-day"><?php echo esc_html( wp_date( 'j', $e['start'] ) ); ?></span>
			<span class="st-month"><?php echo esc_html( mb_substr( spieltreff_format_day( $e['start'] ), 0, 2 ) . ' ' . wp_date( 'n.', $e['start'] ) ); ?></span>
		<?php else : ?>
			<span class="st-day" aria-hidden="true">🔎</span>
			<span class="st-month"><?php esc_html_e( 'Gesuch', 'spieltafel' ); ?></span>
		<?php endif; ?>
	</div>
	<div class="st-card-body">
		<strong class="st-card-title"><?php echo esc_html( $e['titel'] ); ?></strong>
		<span class="st-meta">
			<?php echo esc_html( spieltreff_when( $e, true ) ); ?>
			<?php if ( spieltreff_where( $e ) ) : ?>
				· <?php echo esc_html( spieltreff_where( $e ) ); ?>
			<?php endif; ?>
		</span>
		<?php if ( $games ) : ?>
			<span class="st-tags">
				<?php foreach ( array_slice( $games, 0, 4 ) as $name ) : ?>
					<span class="st-tag"><?php echo esc_html( $name ); ?></span>
				<?php endforeach; ?>
				<?php if ( count( $games ) > 4 ) : ?>
					<span class="st-tag">+<?php echo (int) ( count( $games ) - 4 ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="st-card-side">
		<?php if ( $e['max'] ) : ?>
			<span class="st-spots<?php echo $free <= 0 ? ' is-full' : ''; ?>">
				<?php
				echo esc_html(
					$free > 0
						/* translators: %d: freie Plätze */
						? sprintf( _n( '%d Platz frei', '%d Plätze frei', $free, 'spieltafel' ), $free )
						: __( 'Voll, Warteliste', 'spieltafel' )
				);
				?>
			</span>
		<?php endif; ?>
		<?php if ( 'offen' === $state ) : ?>
			<span class="st-badge st-badge-open">
				<?php
				/* translators: %d: fehlende Anmeldungen */
				echo esc_html( sprintf( __( 'Noch %d nötig', 'spieltafel' ), max( 0, $e['min'] - $c['bestaetigt'] - 1 ) ) );
				?>
			</span>
		<?php endif; ?>
		<?php if ( $e['anfaenger'] ) : ?>
			<span class="st-badge"><?php esc_html_e( 'Anfänger willkommen', 'spieltafel' ); ?></span>
		<?php endif; ?>
	</div>
</a>
