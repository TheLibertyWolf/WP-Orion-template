<?php
/** Footer Orion. @package Orion26 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$logos       = orion26_logos();
$logo_height = orion26_logo_display_height( 'footer' );
$logo        = ! empty( $logos['footer']['url'] ) ? $logos['footer'] : $logos['light'];
$socials     = array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'linkedin' => 'LinkedIn', 'tiktok' => 'TikTok', 'twitch' => 'Twitch' );
$target      = orion26_setting( 'navigation.external_new_tab', true ) ? ' target="_blank"' : '';
$count       = absint( orion26_setting( 'footer.category_count', 14 ) );
$columns     = max( 1, absint( orion26_setting( 'footer.columns', 4 ) ) );
$categories  = orion26_setting( 'footer.show_categories', true ) ? orion26_footer_categories( $count ) : array();
$chunks      = $categories ? array_chunk( $categories, max( 1, (int) ceil( count( $categories ) / max( 1, $columns - 2 ) ) ) ) : array();
$footer_menu = absint( orion26_setting( 'footer.footer_menu', 0 ) );
$copyright   = strtr( (string) orion26_setting( 'footer.copyright', '© {year} {site_name}' ), array( '{year}' => wp_date( 'Y' ), '{site_name}' => get_bloginfo( 'name' ) ) );
$version     = sanitize_text_field( (string) orion26_setting( 'footer.version_label', '' ) );
$description_mode = sanitize_key( (string) orion26_setting( 'footer.description_mode', 'site' ) );
$settings_version = absint( orion26_setting( '_version', ORION26_SETTINGS_VERSION ) );
if ( $settings_version < 4 && ! orion26_setting( 'footer.show_description', true ) ) {
	$description_mode = 'none';
}
$description = 'custom' === $description_mode ? (string) orion26_setting( 'footer.description', '' ) : get_bloginfo( 'description' );
$description = 'none' === $description_mode ? '' : $description;
$footer_width = 'full' === orion26_setting( 'footer.width', 'contained' ) ? 'orion-layout-width--full' : 'orion-container';
$copyright_width = 'full' === orion26_setting( 'footer.copyright_width', 'contained' ) ? 'orion-layout-width--full' : 'orion-container';
$footer_classes = array( 'site-footer' );
if ( orion26_setting( 'footer.top_border', true ) ) { $footer_classes[] = 'has-top-border'; }
if ( orion26_setting( 'footer.column_separators', false ) ) { $footer_classes[] = 'has-column-separators'; }
if ( orion26_setting( 'footer.copyright_border', true ) ) { $footer_classes[] = 'has-copyright-border'; }
?>
<footer class="<?php echo esc_attr( implode( ' ', $footer_classes ) ); ?>">
	<div class="<?php echo esc_attr( $footer_width ); ?> site-footer__grid" style="--orion-footer-columns:<?php echo esc_attr( $columns ); ?>">
		<div class="site-footer__identity">
			<a class="footer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="--orion-footer-logo-height:<?php echo esc_attr( $logo_height ); ?>px">
				<?php if ( ! empty( $logo['url'] ) ) : ?><img class="footer-brand__image" src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="<?php echo esc_attr( absint( $logo['width'] ?? 566 ) ); ?>" height="<?php echo esc_attr( absint( $logo['height'] ?? 92 ) ); ?>" loading="lazy" decoding="async"><?php else : ?><span class="site-brand__name"><?php bloginfo( 'name' ); ?></span><?php endif; ?>
			</a>
			<?php if ( '' !== trim( $description ) ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?>
			<ul class="footer-socials footer-socials--inline">
				<?php foreach ( $socials as $key => $social ) : $url = orion26_setting( 'social.' . $key, '' ); if ( ! $url ) { continue; } ?><li><a href="<?php echo esc_url( $url ); ?>" rel="me noopener noreferrer"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Valeur statique. ?> aria-label="<?php echo esc_attr( $social ); ?>"><?php echo orion26_social_icon( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique interne. ?><span class="screen-reader-text"><?php echo esc_html( $social ); ?></span></a></li><?php endforeach; ?>
				<?php if ( orion26_setting( 'social.rss', true ) ) : ?><li><a href="<?php echo esc_url( get_feed_link() ); ?>" aria-label="<?php esc_attr_e( 'Flux RSS', 'orion26' ); ?>"><?php echo orion26_social_icon( 'rss' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique interne. ?><span class="screen-reader-text"><?php esc_html_e( 'Flux RSS', 'orion26' ); ?></span></a></li><?php endif; ?>
			</ul>
		</div>
		<?php foreach ( $chunks as $index => $column ) : ?><div><h2><?php echo 0 === $index ? esc_html__( 'Rubriques', 'orion26' ) : esc_html__( 'À découvrir', 'orion26' ); ?></h2><ul class="footer-categories"><?php foreach ( $column as $category ) : ?><li><a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li><?php endforeach; ?></ul></div><?php endforeach; ?>
		<?php if ( $footer_menu || has_nav_menu( 'footer' ) ) : ?><div><h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2><?php wp_nav_menu( array( 'menu' => $footer_menu ?: '', 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-links', 'depth' => 1, 'fallback_cb' => false ) ); ?><?php if ( orion26_setting( 'footer.show_login', true ) ) : ?><ul class="footer-links"><li><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Connexion', 'orion26' ); ?></a></li></ul><?php endif; ?></div><?php elseif ( orion26_setting( 'footer.show_login', true ) ) : ?><div><h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2><ul class="footer-links"><li><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Connexion', 'orion26' ); ?></a></li></ul></div><?php endif; ?>
	</div>
	<div class="site-footer__copyright"><div class="site-footer__bottom <?php echo esc_attr( $copyright_width ); ?>"><span><?php echo esc_html( $copyright ); ?><?php if ( $version ) : ?> — <?php echo esc_html( $version ); ?><?php endif; ?></span><?php orion26_consent_manage_button(); ?></div></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
