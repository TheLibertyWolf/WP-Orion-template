<?php
/** Carte d’article. @package Orion26 */
$variant = $args['variant'] ?? 'grid';
$image_size = 'list' === $variant ? 'orion26-card' : 'orion26-card';
$overlay_title = ! empty( $args['overlay_title'] ) && has_post_thumbnail();
$card_class = 'list' === $variant ? 'list-story' : 'story-card' . ( $overlay_title ? ' story-card--overlay' : '' );
?>
<article <?php post_class( $card_class ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="<?php echo 'list' === $variant ? 'list-story__media' : 'story-card__media'; ?>" href="<?php the_permalink(); ?>"<?php if ( ! $overlay_title ) : ?> tabindex="-1" aria-hidden="true"<?php endif; ?>>
			<?php the_post_thumbnail( $image_size, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ) ?: get_the_title() ) ); ?>
			<?php if ( $overlay_title ) : ?><h2 class="story-card__overlay-title"><?php the_title(); ?></h2><?php endif; ?>
		</a>
	<?php endif; ?>
	<div>
		<?php if ( empty( $args['hide_category'] ) ) { orion26_category_label(); } ?>
		<?php if ( ! $overlay_title ) : ?><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php endif; ?>
		<?php orion26_post_meta( get_the_ID(), true ); ?>
		<?php if ( 'list' === $variant || ! empty( $args['show_excerpt'] ) ) : ?><p><?php echo esc_html( orion26_excerpt( get_the_ID(), 'list' === $variant ? 28 : 38 ) ); ?></p><?php endif; ?>
	</div>
</article>
