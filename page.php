<?php
/** Pages éditoriales. @package Orion26 */
get_header();
the_post();
?>
<main id="main-content" class="page-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<article <?php post_class( 'page-shell' ); ?>>
		<header><h1><?php the_title(); ?></h1></header>
		<?php if ( has_post_thumbnail() ) : ?><figure class="page-featured"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?></figure><?php endif; ?>
		<div class="page-content"><?php the_content(); wp_link_pages(); ?></div>
	</article>
</main>
<?php get_footer(); ?>
