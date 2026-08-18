<?php
/** Résultats de recherche. @package Orion26 */
get_header();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header"><span class="archive-header__eyebrow"><?php esc_html_e( 'Recherche', 'orion26' ); ?></span><h1><?php echo esc_html( sprintf( 'Résultats pour « %s »', get_search_query() ) ); ?></h1></header>
	<?php get_search_form(); ?>
	<div class="archive-list"><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card', null, array( 'variant' => 'list' ) ); endwhile; else : ?><div class="empty-state"><p><?php esc_html_e( 'Aucun résultat. Essayez avec un pilote, une écurie ou un championnat.', 'orion26' ); ?></p></div><?php endif; ?></div>
	<?php orion26_pagination(); ?>
</main>
<?php get_footer(); ?>
