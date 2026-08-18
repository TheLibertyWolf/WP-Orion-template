<?php
/** Page d’accueil éditoriale Orion 26. @package Orion26 */
get_header();
$featured_args = array(
	'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => min( 3, absint( orion26_setting( 'homepage.featured_posts', 3 ) ) ),
	'ignore_sticky_posts' => false, 'no_found_rows' => true,
);
$featured_tag = orion26_option( 'tag_une_article' );
if ( $featured_tag instanceof WP_Term ) {
	$featured_args['tag_id'] = $featured_tag->term_id;
} elseif ( is_numeric( $featured_tag ) ) {
	$featured_args['tag_id'] = absint( $featured_tag );
}
$flash_tag = get_term_by( 'slug', 'flash', 'post_tag' );
if ( empty( $featured_args['tag_id'] ) && $flash_tag instanceof WP_Term ) {
	$featured_args['tag_id'] = $flash_tag->term_id;
}
$featured = new WP_Query( $featured_args );
$posts = $featured->posts;
?>
<main id="main-content" class="home-main orion-container">
	<div class="section-kicker"><?php esc_html_e( 'À la une', 'orion26' ); ?></div>
	<?php if ( ! empty( $posts ) ) :
		$lead = $posts[0]; setup_postdata( $lead ); ?>
	<section class="home-lead" aria-label="<?php esc_attr_e( 'À la une', 'orion26' ); ?>">
		<article <?php post_class( 'lead-story', $lead->ID ); ?>>
			<?php if ( has_post_thumbnail( $lead ) ) { echo get_the_post_thumbnail( $lead, 'orion26-lead', array( 'class' => 'lead-story__image', 'fetchpriority' => 'high', 'loading' => 'eager', 'decoding' => 'async', 'alt' => get_post_meta( get_post_thumbnail_id( $lead ), '_wp_attachment_image_alt', true ) ?: get_the_title( $lead ) ) ); } ?>
			<div class="lead-story__content"><?php orion26_category_label( $lead->ID ); ?><h1><a href="<?php echo esc_url( get_permalink( $lead ) ); ?>"><?php echo esc_html( get_the_title( $lead ) ); ?></a></h1><p class="lead-story__excerpt"><?php echo esc_html( orion26_excerpt( $lead->ID, 32 ) ); ?></p><?php orion26_post_meta( $lead->ID, true ); ?></div>
		</article>
		<div class="lead-stack">
		<?php foreach ( array_slice( $posts, 1, 2 ) as $side ) : setup_postdata( $side ); ?>
			<article <?php post_class( 'side-story', $side->ID ); ?>>
				<?php if ( has_post_thumbnail( $side ) ) { echo get_the_post_thumbnail( $side, 'orion26-card', array( 'class' => 'side-story__image', 'loading' => 'eager', 'decoding' => 'async', 'alt' => get_post_meta( get_post_thumbnail_id( $side ), '_wp_attachment_image_alt', true ) ?: get_the_title( $side ) ) ); } ?>
				<div class="side-story__content"><?php orion26_category_label( $side->ID ); ?><h2><a href="<?php echo esc_url( get_permalink( $side ) ); ?>"><?php echo esc_html( get_the_title( $side ) ); ?></a></h2><?php orion26_post_meta( $side->ID, true ); ?></div>
			</article>
		<?php endforeach; ?>
		</div>
	</section>
	<?php wp_reset_postdata(); endif; ?>
	<?php orion26_render_ads( 'home_first' ); ?>
	<?php
	$latest_category_ids = array_values( array_unique( array_merge(
		orion26_term_ids( orion26_option( 'homepage_disc_list', array() ) ),
		orion26_term_ids( orion26_option( 'homepage_restnews', array() ) )
	) ) );
	$latest_query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => absint( orion26_setting( 'homepage.latest_count', 8 ) ), 'category__in' => $latest_category_ids, 'post__not_in' => wp_list_pluck( $posts, 'ID' ), 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	$latest_posts = $latest_query->posts;
	?>
	<?php $posts_page = absint( get_option( 'page_for_posts' ) ); ?>
	<section class="latest-compact" aria-labelledby="latest-compact-title">
		<div class="section-heading"><h2 id="latest-compact-title"><?php esc_html_e( 'Dernières actualités', 'orion26' ); ?></h2><a href="<?php echo esc_url( $posts_page ? get_permalink( $posts_page ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Tout le fil', 'orion26' ); ?> →</a></div>
		<div class="latest-compact__grid">
		<?php foreach ( $latest_posts as $latest_post ) : $latest_has_image = has_post_thumbnail( $latest_post ); ?>
			<article class="latest-compact__item<?php echo $latest_has_image ? ' has-media' : ''; ?>">
				<?php if ( $latest_has_image ) : ?><a class="latest-compact__media" href="<?php echo esc_url( get_permalink( $latest_post ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo get_the_post_thumbnail( $latest_post, 'thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?></a><?php endif; ?>
				<div><?php orion26_category_label( $latest_post->ID ); ?>
					<h3><a href="<?php echo esc_url( get_permalink( $latest_post ) ); ?>"><?php echo esc_html( get_the_title( $latest_post ) ); ?></a></h3>
					<?php orion26_date( get_post_time( 'U', true, $latest_post ) ); ?>
				</div>
			</article>
		<?php endforeach; ?>
		</div>
	</section>
	<?php
	$featured_category_ids = orion26_term_ids( orion26_option( 'homepage_disc_list', array() ) );
	$used_featured_ids = array_merge( wp_list_pluck( $posts, 'ID' ), wp_list_pluck( $latest_posts, 'ID' ) );
	$category_posts_count = absint( orion26_setting( 'homepage.category_posts', 5 ) );
	foreach ( array_slice( $featured_category_ids, 0, 6 ) as $featured_category_id ) :
		$featured_category = get_category( $featured_category_id );
		if ( ! $featured_category || is_wp_error( $featured_category ) ) { continue; }
		$featured_category_query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $category_posts_count, 'cat' => $featured_category->term_id, 'post__not_in' => $used_featured_ids, 'no_found_rows' => true ) );
		if ( ! $featured_category_query->have_posts() ) { continue; }
		$used_featured_ids = array_merge( $used_featured_ids, wp_list_pluck( $featured_category_query->posts, 'ID' ) );
		?>
		<section class="featured-rubric" aria-labelledby="featured-rubric-<?php echo esc_attr( $featured_category->term_id ); ?>">
			<div class="section-heading"><h2 id="featured-rubric-<?php echo esc_attr( $featured_category->term_id ); ?>"><?php echo esc_html( $featured_category->name ); ?></h2><a href="<?php echo esc_url( get_category_link( $featured_category ) ); ?>"><?php esc_html_e( 'Toute l’actualité', 'orion26' ); ?> →</a></div>
			<div class="featured-rubric__layout">
				<div class="featured-rubric__lead"><?php $featured_lead = $featured_category_query->posts[0]; $GLOBALS['post'] = $featured_lead; setup_postdata( $featured_lead ); get_template_part( 'template-parts/card', null, array( 'variant' => 'grid', 'show_excerpt' => true, 'overlay_title' => true, 'hide_category' => true ) ); ?></div>
				<div class="featured-rubric__grid"><?php foreach ( array_slice( $featured_category_query->posts, 1, 4 ) as $featured_small ) : $GLOBALS['post'] = $featured_small; setup_postdata( $featured_small ); get_template_part( 'template-parts/card', null, array( 'variant' => 'grid', 'overlay_title' => false, 'hide_category' => true ) ); endforeach; ?></div>
			</div>
		</section>
		<?php wp_reset_postdata();
	endforeach;
	orion26_render_ads( 'home_aftersingle' );

	$discipline_ids = orion26_term_ids( orion26_option( 'homepage_discipline_hub', array() ) );
	if ( ! $discipline_ids ) {
		$discipline_ids = $featured_category_ids;
	}
	$disciplines = array();
	foreach ( array_slice( $discipline_ids, 0, 12 ) as $discipline_id ) {
		$discipline = get_category( $discipline_id );
		if ( ! $discipline || is_wp_error( $discipline ) ) { continue; }
		$latest_ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'cat' => $discipline->term_id, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
		$disciplines[] = array( 'term' => $discipline, 'latest' => $latest_ids ? (int) get_post_time( 'U', true, $latest_ids[0] ) : 0 );
	}
	usort( $disciplines, static function ( $left, $right ) { return $right['latest'] <=> $left['latest']; } );
	if ( $disciplines ) : ?>
		<section class="discipline-hub" aria-labelledby="discipline-hub-title">
			<div class="section-heading discipline-hub__heading">
				<div><span class="section-kicker"><?php esc_html_e( 'Toutes les trajectoires', 'orion26' ); ?></span><h2 id="discipline-hub-title"><?php esc_html_e( 'Disciplines', 'orion26' ); ?></h2></div>
			</div>
			<div class="discipline-hub__grid">
				<?php foreach ( $disciplines as $index => $discipline_data ) : $discipline = $discipline_data['term']; ?>
					<a class="discipline-link" href="<?php echo esc_url( get_category_link( $discipline ) ); ?>">
						<span class="discipline-link__index" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="discipline-link__content"><strong><?php echo esc_html( $discipline->name ); ?></strong><small><?php esc_html_e( 'Dernière actu :', 'orion26' ); ?> <?php if ( $discipline_data['latest'] ) { orion26_date( $discipline_data['latest'] ); } else { esc_html_e( 'aucune', 'orion26' ); } ?></small></span>
						<span class="discipline-link__arrow" aria-hidden="true">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif;
	$secondary_ids = orion26_term_ids( orion26_option( 'homepage_restnews', array() ) );
	if ( $secondary_ids ) : ?>
		<section class="secondary-news" aria-labelledby="secondary-news-title">
			<div class="section-heading"><h2 id="secondary-news-title"><?php esc_html_e( 'Actualités secondaires', 'orion26' ); ?></h2></div>
			<div class="secondary-news__grid">
			<?php foreach ( array_slice( $secondary_ids, 0, 6 ) as $secondary_id ) :
				$secondary_category = get_category( $secondary_id );
				if ( ! $secondary_category || is_wp_error( $secondary_category ) ) { continue; }
				$secondary_query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 4, 'cat' => $secondary_category->term_id, 'post__not_in' => $used_featured_ids, 'no_found_rows' => true ) );
				if ( ! $secondary_query->have_posts() ) { continue; } ?>
				<?php $used_featured_ids = array_merge( $used_featured_ids, wp_list_pluck( $secondary_query->posts, 'ID' ) ); ?>
				<section class="secondary-rubric"><h3><a href="<?php echo esc_url( get_category_link( $secondary_category ) ); ?>"><?php echo esc_html( $secondary_category->name ); ?> <span aria-hidden="true">→</span></a></h3><ol>
					<?php while ( $secondary_query->have_posts() ) : $secondary_query->the_post(); ?><li><span><?php orion26_date( get_post_time( 'U', true ) ); ?></span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li><?php endwhile; ?>
				</ol></section>
				<?php wp_reset_postdata(); endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$special_blocks = array();
	$gaming_category = absint( orion26_setting( 'homepage.gaming_category', 0 ) );
	if ( orion26_setting( 'homepage.show_gaming', true ) && $gaming_category ) { $special_blocks[] = array( 'class' => 'gaming', 'title' => __( 'Actualité Gaming', 'orion26' ), 'query' => array( 'cat' => $gaming_category ) ); }
	if ( orion26_setting( 'homepage.show_press', true ) ) { $special_blocks[] = array( 'class' => 'press', 'title' => __( 'Communiqués de presse', 'orion26' ), 'query' => array( 'meta_key' => 'cp', 'meta_value' => '1' ) ); }
	if ( orion26_setting( 'homepage.show_history', true ) ) { $special_blocks[] = array( 'class' => 'history', 'title' => __( 'Articles historiques', 'orion26' ), 'query' => array( 'meta_key' => 'histo', 'meta_value' => '1' ) ); }
	?>
	<?php if ( $special_blocks ) : ?><section class="special-news" aria-label="<?php esc_attr_e( 'Sélections éditoriales', 'orion26' ); ?>">
		<div class="special-news__grid">
		<?php foreach ( $special_blocks as $special_block ) :
			$special_query = new WP_Query( array_merge( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5, 'post__not_in' => $used_featured_ids, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ), $special_block['query'] ) );
			if ( ! $special_query->have_posts() ) { continue; }
			$used_featured_ids = array_merge( $used_featured_ids, wp_list_pluck( $special_query->posts, 'ID' ) ); ?>
			<section class="special-news__block special-news__block--<?php echo esc_attr( $special_block['class'] ); ?>">
				<h2><?php echo esc_html( $special_block['title'] ); ?></h2><ol>
				<?php while ( $special_query->have_posts() ) : $special_query->the_post(); ?><li><span><?php orion26_date( get_post_time( 'U', true ) ); ?></span><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li><?php endwhile; ?>
				</ol>
			</section>
			<?php wp_reset_postdata(); endforeach; ?>
		</div>
	</section><?php endif; ?>
</main>
<?php get_footer(); ?>
