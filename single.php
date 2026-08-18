<?php
/** Article d’actualité. @package Orion26 */
if ( 'post' !== get_post_type() ) {
	status_header( 404 );
	nocache_headers();
	include get_404_template();
	return;
}
get_header();
the_post();
$post_id    = get_the_ID();
$author_id  = (int) get_post_field( 'post_author', $post_id );
$categories = get_the_category( $post_id );
$discipline = $categories ? $categories[0]->name : '';
$multi      = function_exists( 'get_field' ) && get_field( 'multi_opt', $post_id );
$parts      = $multi && function_exists( 'get_field' ) ? (array) get_field( 'multi_page', $post_id ) : array();
?>
<main id="main-content" class="single-main">
	<div class="orion-container"><?php orion26_breadcrumbs(); ?></div>
	<article <?php post_class( 'news-article' ); ?>>
		<header class="article-header orion-container"<?php if ( $discipline ) : ?> data-discipline="<?php echo esc_attr( $discipline ); ?>"<?php endif; ?>>
			<?php orion26_category_label( $post_id ); ?>
			<h1><?php the_title(); ?></h1>
			<?php orion26_post_meta( $post_id ); ?>
		</header>
		<div class="orion-container"><?php orion26_render_ads( 'single_aftertitle' ); ?></div>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="article-hero">
				<?php the_post_thumbnail( 'full', array( 'fetchpriority' => 'high', 'loading' => 'eager', 'decoding' => 'async', 'alt' => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title() ) ); ?>
			</figure>
		<?php endif; ?>
		<div class="article-layout orion-container">
			<div>
				<div class="article-content">
					<?php the_content(); ?>
					<?php foreach ( $parts as $part ) : if ( empty( $part['contenu'] ) ) { continue; } ?>
						<section class="article-part">
							<?php if ( ! empty( $part['titre'] ) ) : ?><h2><?php echo esc_html( $part['titre'] ); ?></h2><?php endif; ?>
							<?php echo wp_kses_post( $part['contenu'] ); ?>
						</section>
					<?php endforeach; ?>
				</div>
				<?php the_tags( '<div class="article-tags"><span class="screen-reader-text">' . esc_html__( 'Sujets :', 'orion26' ) . '</span>', '', '</div>' ); ?>
				<?php orion26_render_ads( 'single_aftercontent' ); ?>
			</div>
			<aside class="article-sidebar" aria-label="<?php esc_attr_e( 'À propos et articles liés', 'orion26' ); ?>">
				<?php orion26_editorial_notices( $post_id ); ?>
				<?php orion26_render_ads( 'single_sidebar1' ); ?>
				<section class="author-box">
					<div class="author-box__head"><?php echo get_avatar( $author_id, 64, '', get_the_author_meta( 'display_name', $author_id ), array( 'loading' => 'lazy' ) ); ?><div><span class="story-category"><?php esc_html_e( 'La rédaction', 'orion26' ); ?></span><h2><a rel="author" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></a></h2></div></div>
					<?php if ( get_the_author_meta( 'description', $author_id ) ) : ?><p><?php echo esc_html( get_the_author_meta( 'description', $author_id ) ); ?></p><?php endif; ?>
				</section>
				<?php if ( $categories ) :
					$related = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5, 'post__not_in' => array( $post_id ), 'cat' => $categories[0]->term_id, 'no_found_rows' => true ) );
					if ( $related->have_posts() ) : ?>
					<section class="related-list"><h2><?php echo esc_html( sprintf( 'À lire en %s', $categories[0]->name ) ); ?></h2><ul><?php while ( $related->have_posts() ) : $related->the_post(); ?><li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li><?php endwhile; ?></ul></section>
					<?php endif; wp_reset_postdata(); endif; ?>
				<?php $latest_sidebar = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5, 'post__not_in' => array( $post_id ), 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
				if ( $latest_sidebar->have_posts() ) : ?>
					<section class="related-list related-list--latest"><h2><?php esc_html_e( 'Derniers articles', 'orion26' ); ?></h2><ul><?php while ( $latest_sidebar->have_posts() ) : $latest_sidebar->the_post(); ?><li><span><?php orion26_date( get_post_time( 'U', true ) ); ?></span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li><?php endwhile; ?></ul></section>
				<?php endif; wp_reset_postdata(); ?>
				<?php orion26_render_ads( 'single_sidebar2' ); ?>
			</aside>
		</div>
	</article>
	<div class="orion-container"><?php the_post_navigation( array( 'prev_text' => '<span class="story-category">Article précédent</span><br>%title', 'next_text' => '<span class="story-category">Article suivant</span><br>%title' ) ); ?></div>
</main>
<?php get_footer(); ?>
