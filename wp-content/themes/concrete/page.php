<?php get_header(); ?>
<?php include (TEMPLATEPATH . '/inc/nav.php' ); ?>
This right below the header
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="post" id="post-<?php the_ID(); ?>">

			<h2 id="page-title"><?php the_title(); ?></h2>

			<div class="entry">

				<?php the_content(); ?>

				<?php wp_link_pages(array('before' => 'Pages: ', 'next_or_number' => 'number')); ?>

			</div>

			<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>

		</div>

		<?php // comments_template(); ?>

		<?php endwhile; endif; ?>


This right above the footer
<?php get_footer(); ?>
