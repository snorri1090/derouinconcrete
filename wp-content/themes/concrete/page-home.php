<?php /* template name: Home */
get_header(); ?>
<div id="big-image" class="home">
  <div id="home-container">
    <div id="header">
      <div id="logo">
				<img src="/wp-content/uploads/2017/01/dc_logo_layered_1-5b.png">
      <div id="contact-photo">

<div id="nav" class="home" style="margin-top: 4em;">
<?php
wp_nav_menu( array(
    'derouin-home-nav' => 'derouin-home-nav',
    'home' => 'custom-menu-class' ) );
?>

	<div id="content">
	<h2 class="home-contact">
          (253) 677-1776<br>
          <a href="mailto:joe@derouinconcrete.com"><span class="email">joe@derouinconcrete.com</span></a><s></s>
        </h2>
      <p class="home-text">That is our motto at Derouin Concrete Ltd.  No matter what the job entails, no matter how creative and abstract the project, we strive to bring whatever can be conceived in the mind, into existence.</p>

		</div>
	</div>
</div>

<?php get_footer(); ?>
