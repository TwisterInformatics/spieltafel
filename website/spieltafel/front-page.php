<?php
/**
 * Startseite.
 *
 * @package Spieltafel
 */

get_header();
$shots = spieltafel_screenshots();
?>
<section class="hero">
	<div class="wrap">
		<div>
			<img class="hero-icon" src="<?php echo esc_url( spieltafel_img( 'app-icon.png' ) ); ?>" alt="<?php esc_attr_e( 'App-Symbol der Spieltafel', 'spieltafel' ); ?>" width="84" height="84">
			<h1><?php echo esc_html( spieltafel_mod( 'hero_title' ) ); ?></h1>
			<p class="lead"><?php echo esc_html( spieltafel_mod( 'hero_text' ) ); ?></p>
			<?php get_template_part( 'template-parts/store-badges' ); ?>
			<p class="hero-note">
				<?php
				/* translators: %d: Anzahl Spiele */
				echo esc_html( sprintf( __( 'Kostenlos · %d Spiele mit Kurzanleitung · kein Konto, keine Werbung', 'spieltafel' ), count( spieltafel_available_games() ) ) );
				?>
			</p>
		</div>
		<div class="phone">
			<img src="<?php echo esc_url( spieltafel_img( $shots[1][0] ) ); ?>" alt="<?php echo esc_attr( $shots[1][1] ); ?>" width="540" height="1173">
		</div>
	</div>
</section>

<section class="section section-alt">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'So geht es', 'spieltafel' ); ?></span>
			<h2><?php esc_html_e( 'Weg mit Zettel und Stift', 'spieltafel' ); ?></h2>
		</div>
		<ol class="steps">
			<?php foreach ( spieltafel_steps() as $step ) : ?>
				<li><h3><?php echo esc_html( $step[0] ); ?></h3><p><?php echo esc_html( $step[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section class="section" id="funktionen">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Funktionen', 'spieltafel' ); ?></span>
			<h2><?php esc_html_e( 'Alles, was es am Spieltisch braucht', 'spieltafel' ); ?></h2>
		</div>
		<div class="features">
			<?php foreach ( spieltafel_features() as $feature ) : ?>
				<div class="feature">
					<div class="feature-icon" aria-hidden="true"><?php echo esc_html( $feature[0] ); ?></div>
					<h3><?php echo esc_html( $feature[1] ); ?></h3>
					<p><?php echo esc_html( $feature[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section-alt">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Einblick', 'spieltafel' ); ?></span>
			<h2><?php esc_html_e( 'So sieht die Spieltafel aus', 'spieltafel' ); ?></h2>
		</div>
		<div class="shots">
			<?php foreach ( $shots as $shot ) : ?>
				<figure>
					<img src="<?php echo esc_url( spieltafel_img( $shot[0] ) ); ?>" alt="<?php echo esc_attr( $shot[1] ); ?>" width="540" height="1173" loading="lazy">
					<figcaption><?php echo esc_html( $shot[1] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section" id="spiele">
	<div class="wrap">
		<div class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Spiele', 'spieltafel' ); ?></span>
			<h2>
				<?php
				/* translators: %d: Anzahl Spiele */
				echo esc_html( sprintf( __( '%d Spiele, eine Tafel', 'spieltafel' ), count( spieltafel_available_games() ) ) );
				?>
			</h2>
			<p><?php esc_html_e( 'Jedes Spiel kennt seine Wertung. Tippe ein Spiel an für die Kurzanleitung.', 'spieltafel' ); ?></p>
		</div>
		<?php get_template_part( 'template-parts/game-list' ); ?>
		<p class="section-foot"><a class="btn btn-secondary" href="<?php echo esc_url( spieltafel_page_url( 'spiele' ) ); ?>"><?php esc_html_e( 'Alle Kurzanleitungen', 'spieltafel' ); ?></a></p>
	</div>
</section>

<?php
get_template_part( 'template-parts/section-wish', null, array( 'alt' => true ) );

if ( have_posts() && 'page' === get_option( 'show_on_front' ) ) :
	while ( have_posts() ) :
		the_post();
		if ( get_the_content() ) :
			?>
			<section class="section"><div class="wrap entry-content"><?php the_content(); ?></div></section>
			<?php
		endif;
	endwhile;
endif;

get_footer();
