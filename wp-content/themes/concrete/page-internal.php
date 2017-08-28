<?php /* template name: Internal */
$slug = get_post_field( 'post_name', get_post() );
get_header(); ?>
<div id="big-image" class="interior" style="background-image:url('/wp-content/uploads/banners/<?php global $post; echo $post->post_name; ?>.jpg')">

	<div id="interior-container">
		<h1><?php get_slug ?></h1>
		<a href="/">
	  	<div id="logo-internal"><img src="/wp-content/uploads/2016/10/interior_logo_16.png"></div>
		</a>
		<div id="nav" class="interior">
<?php
wp_nav_menu( array(
    'derouin-int-nav' => 'derouin-int-nav',
    'interior' => 'custom-menu-class' ) );
?>

			<div id="contact-info">
				<span class="contact-email"><i class="fa fa-envelope"></i> joe@derouinconcrete.com</span> <span class="contact-phone"><i class="fa fa-phone"></i> (253) 677-1776</span>
			</div>
		</div>


	  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div class="post" id="post-<?php the_ID(); ?>">
				<h2 id="page-title"><?php the_title(); ?></h2>
				<div class="entry">
					<?php the_content(); ?>
					<?php wp_link_pages(array('before' => 'Pages: ', 'next_or_number' => 'number')); ?>
				</div>
				<div style="display:inline-block;font-size:12px;">
					<?php edit_post_link('Edit this Page.', '<p><i class="fa fa-edit fa-small"></i>', '</p>'); ?>
				</div>
			</div>
		<?php // comments_template(); ?>
		<?php endwhile; endif; ?>
	</div>

<?php get_footer(); ?>
