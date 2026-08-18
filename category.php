<?php
/** Archive de catégorie. @package Orion26 */
get_header();
$category = get_queried_object();
?>
<main id="main-content" class="archive-main orion-container">
	<?php orion26_breadcrumbs(); ?>
	<header class="archive-header">
		<span class="archive-header__eyebrow"><?php esc_html_e( 'Discipline', 'orion26' ); ?></span>
		<h1><?php single_cat_title(); ?></h1>
		<?php if ( category_description() ) : ?><div class="taxonomy-description"><?php echo wp_kses_post( category_description() ); ?></div><?php endif; ?>
		<?php
		$discipline_links = array(
			'Site officiel' => get_term_meta( $category->term_id, 'discipline_official_url', true ),
			'Facebook'      => get_term_meta( $category->term_id, 'discipline_facebook_url', true ),
			'Instagram'     => get_term_meta( $category->term_id, 'discipline_instagram_url', true ),
			'YouTube'       => get_term_meta( $category->term_id, 'discipline_youtube_url', true ),
		);
		$discipline_links = array_filter( $discipline_links );
		$discipline_icons = array(
			'Site officiel' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.2 3 14.8 0 18M12 3c-3 3.2-3 14.8 0 18"/></svg>',
			'Facebook'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3.3 0-5 2-5 5v2H6v4h3v7h4v-7h3.4l.6-4h-4V9c0-.7.3-1 1-1Z"/></svg>',
			'Instagram'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle class="discipline-link__dot" cx="17.5" cy="6.5" r="1"/></svg>',
			'YouTube'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8.2c-.2-1.5-1.2-2.5-2.7-2.7C16.2 5.2 14.1 5 12 5s-4.2.2-6.3.5C4.2 5.7 3.2 6.7 3 8.2A25 25 0 0 0 3 15.8c.2 1.5 1.2 2.5 2.7 2.7 2.1.3 4.2.5 6.3.5s4.2-.2 6.3-.5c1.5-.2 2.5-1.2 2.7-2.7a25 25 0 0 0 0-7.6Z"/><path class="discipline-link__play" d="m10 9 5 3-5 3Z"/></svg>',
		);
		if ( $discipline_links ) : ?>
			<nav class="discipline-links" aria-label="<?php echo esc_attr( sprintf( 'Liens officiels — %s', $category->name ) ); ?>">
				<?php foreach ( $discipline_links as $label => $url ) : ?><a class="discipline-link discipline-link--<?php echo esc_attr( sanitize_title( $label ) ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo $discipline_icons[ $label ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique défini ci-dessus. ?><?php echo esc_html( $label ); ?></a><?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</header>
	<?php $discipline_overview = get_term_meta( $category->term_id, 'discipline_long_description', true ); ?>
	<?php if ( $discipline_overview ) : ?>
		<section class="discipline-overview" aria-labelledby="discipline-overview-title">
			<p class="discipline-overview__eyebrow"><?php esc_html_e( 'La discipline', 'orion26' ); ?></p>
			<h2 id="discipline-overview-title"><?php echo esc_html( sprintf( 'Comprendre %s', $category->name ) ); ?></h2>
			<div class="discipline-overview__content"><?php echo wp_kses_post( wpautop( $discipline_overview ) ); ?></div>
		</section>
	<?php endif; ?>
	<?php orion26_render_ads( 'cat_haut' ); ?>
	<div class="archive-list">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/card', null, array( 'variant' => 'list' ) ); endwhile; else : ?><p><?php esc_html_e( 'Aucune actualité dans cette rubrique.', 'orion26' ); ?></p><?php endif; ?>
	</div>
	<?php orion26_pagination(); ?>
</main>
<?php get_footer(); ?>
