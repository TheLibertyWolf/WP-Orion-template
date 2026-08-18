<?php
/** Archives génériques. @package Orion26 */
get_header();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header">
		<span class="archive-header__eyebrow"><?php esc_html_e( 'Archives', 'orion26' ); ?></span>
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
	</header>
	<div class="archive-list">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card', null, array( 'variant' => 'list' ) ); endwhile; else : ?><p><?php esc_html_e( 'Aucun article trouvé.', 'orion26' ); ?></p><?php endif; ?>
	</div>
	<?php orion26_pagination(); ?>
</main>
<?php get_footer(); ?>
