<?php
/**
 * Rückfall für Beiträge und Archive.
 *
 * @package Spieltafel
 */

get_header();
?>
<div class="page-content">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?> style="margin-bottom:48px">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="meta"><?php echo esc_html( get_the_date() ); ?></p>
					<div class="entry-content"><?php is_singular() ? the_content() : the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			the_posts_pagination();
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'Hier gibt es noch nichts.', 'spieltafel' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
