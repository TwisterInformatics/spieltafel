<?php
/**
 * Eigene Seiten des Spieltreffs: /spieltreff/neu/, /konto/, /registrieren/, /anmelden/, /bestaetigen/, /regeln/
 *
 * @package Spieltafel
 */

$view = get_query_var( 'spieltreff_view' );

// Wer angemeldet sein muss, kommt zuerst zur Anmeldung.
if ( in_array( $view, array( 'neu', 'konto' ), true ) && ! is_user_logged_in() ) {
	wp_safe_redirect( spieltreff_url( 'anmelden', array( 'zurueck' => rawurlencode( spieltreff_url( $view ) ) ) ) );
	exit;
}
if ( in_array( $view, array( 'registrieren', 'anmelden' ), true ) && is_user_logged_in() ) {
	wp_safe_redirect( spieltreff_url( 'konto' ) );
	exit;
}

get_header();
?>
<section class="section">
	<div class="wrap st-view st-view-<?php echo esc_attr( $view ); ?>">
		<?php
		if ( is_user_logged_in() ) {
			get_template_part( 'template-parts/spieltreff/testbanner' );
		}
		get_template_part( 'template-parts/spieltreff/view', $view );
		?>
	</div>
</section>
<?php
get_footer();
