<?php
/**
 * Seite nicht gefunden.
 *
 * @package Spieltafel
 */

get_header();
?>
<div class="page-content">
	<div class="wrap">
		<h1><?php esc_html_e( 'Diese Seite gibt es nicht', 'spieltafel' ); ?></h1>
		<p><?php esc_html_e( 'Vielleicht suchst du eines dieser Spiele:', 'spieltafel' ); ?></p>
		<?php get_template_part( 'template-parts/game-list' ); ?>
	</div>
</div>
<?php
get_footer();
