<?php
/**
 * Abschnitt auf der Startseite: vor der Freischaltung ein Hinweis, danach die nächsten Spieltreffs.
 *
 * @package Spieltafel
 */

if ( ! spieltreff_can_view() ) {
	get_template_part( 'template-parts/spieltreff/teaser', null, array( 'alt' => true ) );
	return;
}
$next = spieltreff_query( array( 'typ' => 'angebot', 'anzahl' => 3 ) );
?>
<section class="section section-alt" id="spieltreff">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Spieltreff · Mitspieler finden', 'spieltafel' ); ?><?php echo spieltreff_is_live() ? '' : ' · ' . esc_html__( 'Testbetrieb', 'spieltafel' ); ?></span>
			<h2><?php echo esc_html( spieltreff_slogans()[1] ); ?></h2>
			<p><?php esc_html_e( 'Weniger scrollen, mehr spielen: Im Spieltreff findest du Leute in deiner Nähe für Jassrunden, Brettspielabende und feste Gruppen. Biete einen Spielabend an oder melde dich bei anderen an.', 'spieltafel' ); ?></p>
		</div>
		<?php if ( $next->have_posts() ) : ?>
			<div class="st-list">
				<?php
				while ( $next->have_posts() ) :
					$next->the_post();
					get_template_part( 'template-parts/spieltreff/card', null, array( 'post' => get_post() ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>
		<p class="section-foot">
			<a class="btn" href="<?php echo esc_url( spieltreff_url() ); ?>"><?php esc_html_e( 'Zum Spieltreff', 'spieltafel' ); ?></a>
			<a class="btn btn-secondary" href="<?php echo esc_url( spieltreff_url( 'neu' ) ); ?>"><?php esc_html_e( 'Spielabend anbieten', 'spieltafel' ); ?></a>
		</p>
	</div>
</section>
