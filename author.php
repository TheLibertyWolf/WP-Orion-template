<?php
/** Page auteur. @package Orion26 */
get_header();
$author = get_queried_object();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header author-profile">
		<?php echo get_avatar( $author->ID, 128, '', $author->display_name, array( 'loading' => 'eager' ) ); ?>
		<div><span class="archive-header__eyebrow"><?php esc_html_e( 'La rédaction', 'orion26' ); ?></span><h1><?php echo esc_html( $author->display_name ); ?></h1><?php if ( get_the_author_meta( 'description', $author->ID ) ) : ?><div class="taxonomy-description"><p><?php echo esc_html( get_the_author_meta( 'description', $author->ID ) ); ?></p></div><?php endif; ?></div>
	</header>
	<?php orion26_render_ads( 'author_haut' ); ?>
	<div class="archive-list"><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card', null, array( 'variant' => 'list' ) ); endwhile; else : ?><p><?php esc_html_e( 'Cet auteur n’a pas encore publié d’article.', 'orion26' ); ?></p><?php endif; ?></div>
	<?php orion26_pagination(); ?>
</main>
<?php get_footer(); ?>
