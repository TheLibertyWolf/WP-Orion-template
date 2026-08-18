<?php
/** Page « Plus d’actualités » alimentée par les disciplines configurées. @package Orion26 */
get_header();
the_post();

$discipline_ids = orion26_term_ids( orion26_option( 'otcher_disc_list', array() ) );
$disciplines = array();
foreach ( $discipline_ids as $discipline_id ) {
	$discipline = get_category( $discipline_id );
	if ( ! $discipline || is_wp_error( $discipline ) ) {
		continue;
	}
	$latest_ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'cat' => $discipline->term_id, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
	$disciplines[] = array( 'term' => $discipline, 'latest' => $latest_ids ? (int) get_post_time( 'U', true, $latest_ids[0] ) : 0 );
}
usort( $disciplines, static function ( $left, $right ) { return $right['latest'] <=> $left['latest']; } );
$used_article_ids = array();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header">
		<span class="archive-header__eyebrow"><?php esc_html_e( 'Plus d’actualités', 'orion26' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<?php if ( get_the_content() ) : ?><div class="taxonomy-description"><?php the_content(); ?></div><?php endif; ?>
	</header>

	<div class="others-rubrics">
	<?php if ( $disciplines ) : foreach ( $disciplines as $discipline_data ) :
		$discipline = $discipline_data['term'];
		$discipline_query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5, 'cat' => $discipline->term_id, 'post__not_in' => $used_article_ids, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
		if ( ! $discipline_query->have_posts() ) { continue; }
		$used_article_ids = array_merge( $used_article_ids, wp_list_pluck( $discipline_query->posts, 'ID' ) ); ?>
		<section class="featured-rubric" aria-labelledby="others-rubric-<?php echo esc_attr( $discipline->term_id ); ?>">
			<div class="section-heading"><h2 id="others-rubric-<?php echo esc_attr( $discipline->term_id ); ?>"><?php echo esc_html( $discipline->name ); ?></h2><a href="<?php echo esc_url( get_category_link( $discipline ) ); ?>"><?php esc_html_e( 'Toute l’actualité', 'orion26' ); ?> →</a></div>
			<div class="featured-rubric__layout">
				<div class="featured-rubric__lead"><?php $discipline_lead = $discipline_query->posts[0]; $GLOBALS['post'] = $discipline_lead; setup_postdata( $discipline_lead ); get_template_part( 'template-parts/card', null, array( 'variant' => 'grid', 'show_excerpt' => true, 'overlay_title' => true, 'hide_category' => true ) ); ?></div>
				<div class="featured-rubric__grid"><?php foreach ( array_slice( $discipline_query->posts, 1, 4 ) as $discipline_small ) : $GLOBALS['post'] = $discipline_small; setup_postdata( $discipline_small ); get_template_part( 'template-parts/card', null, array( 'variant' => 'grid', 'overlay_title' => true, 'hide_category' => true ) ); endforeach; ?></div>
			</div>
		</section>
		<?php wp_reset_postdata(); endforeach; else : ?>
		<p><?php esc_html_e( 'Aucune discipline n’est configurée pour cette page.', 'orion26' ); ?></p>
	<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
