<?php
/** Index des actualités lorsqu’une page d’accueil statique est utilisée. @package Orion26 */
get_header();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header"><span class="archive-header__eyebrow"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span><h1><?php esc_html_e( 'Toutes les actualités', 'orion26' ); ?></h1><div class="taxonomy-description"><p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p></div></header>
	<div class="archive-list"><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card', null, array( 'variant' => 'list' ) ); endwhile; else : ?><p><?php esc_html_e( 'Aucune actualité publiée.', 'orion26' ); ?></p><?php endif; ?></div>
	<?php orion26_pagination(); ?>
</main>
<?php get_footer(); ?>
