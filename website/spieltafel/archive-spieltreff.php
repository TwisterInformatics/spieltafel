<?php
/**
 * Spieltreff-Übersicht: /spieltreff/
 * Vor der Freischaltung sehen Besucher nur den Hinweis, angemeldete Benutzer die Testversion.
 *
 * @package Spieltafel
 */

get_header();

if ( ! spieltreff_can_view() ) {
	get_template_part( 'template-parts/spieltreff/teaser', null, array( 'heading' => 'h1' ) );
	get_footer();
	return;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nur Filter.
$filter = array(
	'typ'       => isset( $_GET['typ'] ) && 'gesuch' === $_GET['typ'] ? 'gesuch' : 'angebot',
	'kanton'    => isset( $_GET['kanton'] ) ? strtoupper( sanitize_key( $_GET['kanton'] ) ) : '',
	'spiel'     => isset( $_GET['spiel'] ) ? sanitize_key( $_GET['spiel'] ) : '',
	'suche'     => isset( $_GET['suche'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_GET['suche'] ) ), 0, 60 ) : '',
	'anfaenger' => ! empty( $_GET['anfaenger'] ),
	'seite'     => isset( $_GET['seite'] ) ? max( 1, (int) $_GET['seite'] ) : 1,
);
// phpcs:enable
if ( ! isset( spieltreff_regions()[ $filter['kanton'] ] ) ) {
	$filter['kanton'] = '';
}
$query   = spieltreff_query( $filter );
$other   = spieltreff_query( array_merge( $filter, array( 'typ' => 'angebot' === $filter['typ'] ? 'gesuch' : 'angebot', 'anzahl' => 1, 'seite' => 1 ) ) );
$counts  = array(
	$filter['typ'] => (int) $query->found_posts,
	( 'angebot' === $filter['typ'] ? 'gesuch' : 'angebot' ) => (int) $other->found_posts,
);
$base    = spieltreff_url();
$keep    = array_filter( array_diff_key( $filter, array( 'typ' => 1, 'seite' => 1 ) ) );
$filters = (bool) $keep;
?>
<section class="section st-head">
	<div class="wrap">
		<?php get_template_part( 'template-parts/spieltreff/testbanner' ); ?>
		<?php spieltreff_notice(); ?>
		<div class="st-intro">
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Spieltreff', 'spieltafel' ); ?></span>
				<h1><?php esc_html_e( 'Mitspieler finden', 'spieltafel' ); ?></h1>
				<p class="lead"><?php esc_html_e( 'Jassrunde, Brettspielabend oder eine feste Gruppe für jeden Donnerstag: Hier findest du Leute, die gerne spielen. Mach mit bei einem Angebot oder schreib selbst eines aus.', 'spieltafel' ); ?></p>
			</div>
			<div class="st-intro-actions">
				<a class="btn" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => 'angebot' ) ) ); ?>"><?php esc_html_e( 'Spielabend anbieten', 'spieltafel' ); ?></a>
				<a class="btn btn-secondary" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => 'gesuch' ) ) ); ?>"><?php esc_html_e( 'Mitspieler suchen', 'spieltafel' ); ?></a>
			</div>
		</div>

		<nav class="st-tabs" aria-label="<?php esc_attr_e( 'Angebote oder Gesuche', 'spieltafel' ); ?>">
			<?php foreach ( array( 'angebot' => __( 'Angebote', 'spieltafel' ), 'gesuch' => __( 'Gesuche', 'spieltafel' ) ) as $typ => $label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array_merge( $keep, array( 'typ' => $typ ) ), $base ) ); ?>" <?php echo $filter['typ'] === $typ ? 'aria-current="page"' : ''; ?>>
					<?php echo esc_html( $label ); ?> <span class="st-count"><?php echo (int) $counts[ $typ ]; ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<form class="st-filter" method="get" action="<?php echo esc_url( $base ); ?>">
			<input type="hidden" name="typ" value="<?php echo esc_attr( $filter['typ'] ); ?>">
			<label class="screen-reader-text" for="st-suche"><?php esc_html_e( 'Suchen', 'spieltafel' ); ?></label>
			<input class="game-search" type="search" id="st-suche" name="suche" value="<?php echo esc_attr( $filter['suche'] ); ?>" placeholder="<?php esc_attr_e( 'Suchen, z. B. Jass oder Bern', 'spieltafel' ); ?>">
			<label class="screen-reader-text" for="st-kanton"><?php esc_html_e( 'Region', 'spieltafel' ); ?></label>
			<select id="st-kanton" name="kanton">
				<option value=""><?php esc_html_e( 'Ganze Schweiz', 'spieltafel' ); ?></option>
				<?php foreach ( spieltreff_regions() as $code => $name ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $filter['kanton'], $code ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
			<label class="screen-reader-text" for="st-spiel"><?php esc_html_e( 'Spiel', 'spieltafel' ); ?></label>
			<select id="st-spiel" name="spiel">
				<option value=""><?php esc_html_e( 'Alle Spiele', 'spieltafel' ); ?></option>
				<?php foreach ( spieltafel_games() as $game ) : ?>
					<option value="<?php echo esc_attr( $game['id'] ); ?>" <?php selected( $filter['spiel'], $game['id'] ); ?>><?php echo esc_html( $game['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<label class="st-check"><input type="checkbox" name="anfaenger" value="1" <?php checked( $filter['anfaenger'] ); ?>> <?php esc_html_e( 'Anfänger willkommen', 'spieltafel' ); ?></label>
			<button class="btn" type="submit"><?php esc_html_e( 'Filtern', 'spieltafel' ); ?></button>
			<?php if ( $filters ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'typ', $filter['typ'], $base ) ); ?>"><?php esc_html_e( 'Filter löschen', 'spieltafel' ); ?></a>
			<?php endif; ?>
		</form>

		<?php if ( $query->have_posts() ) : ?>
			<div class="st-list">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/spieltreff/card', null, array( 'post' => get_post() ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php if ( $query->max_num_pages > 1 ) : ?>
				<nav class="st-pages" aria-label="<?php esc_attr_e( 'Seiten', 'spieltafel' ); ?>">
					<?php for ( $i = 1; $i <= $query->max_num_pages; $i++ ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array_merge( $keep, array( 'typ' => $filter['typ'], 'seite' => $i ) ), $base ) ); ?>" <?php echo $i === $filter['seite'] ? 'aria-current="page"' : ''; ?>><?php echo (int) $i; ?></a>
					<?php endfor; ?>
				</nav>
			<?php endif; ?>
		<?php else : ?>
			<div class="st-empty">
				<p><strong>
					<?php
					if ( $filters ) {
						esc_html_e( 'Keine Einträge mit diesen Filtern.', 'spieltafel' );
					} else {
						'angebot' === $filter['typ'] ? esc_html_e( 'Noch keine Angebote.', 'spieltafel' ) : esc_html_e( 'Noch keine Gesuche.', 'spieltafel' );
					}
					?>
				</strong></p>
				<p><?php esc_html_e( 'Mach den Anfang: Wer einen Eintrag erstellt, findet oft schneller Mitspieler, als man denkt.', 'spieltafel' ); ?></p>
				<a class="btn" href="<?php echo esc_url( spieltreff_url( 'neu', array( 'typ' => $filter['typ'] ) ) ); ?>">
					<?php 'angebot' === $filter['typ'] ? esc_html_e( 'Spielabend anbieten', 'spieltafel' ) : esc_html_e( 'Mitspieler suchen', 'spieltafel' ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/spieltreff/how' ); ?>
	</div>
</section>
<?php
get_footer();
