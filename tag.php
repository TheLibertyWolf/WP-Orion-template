<?php
/** Archive d’étiquette. @package Orion26 */
get_header();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header"><span class="archive-header__eyebrow"><?php esc_html_e( 'Sujet', 'orion26' ); ?></span><h1>#<?php single_tag_title(); ?></h1><?php if ( tag_description() ) : ?><div class="taxonomy-description"><?php echo wp_kses_post( tag_description() ); ?></div><?php endif; ?></header>
	<?php orion26_render_ads( 'tag_haut' ); ?>
	<div class="archive-list"><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card', null, array( 'variant' => 'list' ) ); endwhile; else : ?><p><?php esc_html_e( 'Aucun article associé à ce sujet.', 'orion26' ); ?></p><?php endif; ?></div>
	<?php orion26_pagination(); ?>
</main>
<?php get_footer(); ?>
