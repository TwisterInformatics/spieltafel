<?php
/**
 * Normale Seite, zum Beispiel Impressum oder Datenschutz.
 *
 * @package Spieltafel
 */

get_header();
?>
<div class="page-content">
	<div class="wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</div>
<?php
get_footer();
