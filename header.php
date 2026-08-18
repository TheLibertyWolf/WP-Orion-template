<?php
/** Header Orion 26. @package Orion26 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$logos = orion26_logos();
$logo_height = orion26_logo_display_height( 'header' );
$light_width = absint( $logos['light']['width'] ?? 567 );
$light_height = absint( $logos['light']['height'] ?? 92 );
$dark_width = absint( $logos['dark']['width'] ?? $light_width );
$dark_height = absint( $logos['dark']['height'] ?? $light_height );
$default_scheme = orion26_setting( 'identity.default_scheme', 'auto' );
$primary_menu   = absint( orion26_setting( 'header.primary_menu', 0 ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0b0d12" media="(prefers-color-scheme: dark)">
	<meta name="theme-color" content="#f7f5ef" media="(prefers-color-scheme: light)">
	<script>window.OrionThemeDefault=<?php echo wp_json_encode( $default_scheme ); ?>;try{var t=localStorage.getItem('orion26-theme'),d=window.OrionThemeDefault;if(!t){var m=document.cookie.match(/(?:^|; )wp_user_stylesheet_switcher_js=([^;]*)/);if(m){var c=JSON.parse(decodeURIComponent(m[1]));t=String(c.s0)==='1'?'dark':'light'}}document.documentElement.dataset.theme=t||(d==='auto'?(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):d)}catch(e){}</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Aller au contenu', 'orion26' ); ?></a>
<div class="site-accent" aria-hidden="true"></div>
<header class="site-header">
	<div class="site-header__main orion-container">
		<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — Accueil" style="--orion-header-logo-height:<?php echo esc_attr( $logo_height ); ?>px">
			<?php if ( ! empty( $logos['light']['url'] ) ) : ?>
				<img class="site-brand__image site-brand__image--light" src="<?php echo esc_url( wp_make_link_relative( $logos['light']['url'] ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="<?php echo esc_attr( $light_width ); ?>" height="<?php echo esc_attr( $light_height ); ?>" decoding="async">
				<img class="site-brand__image site-brand__image--dark" src="<?php echo esc_url( wp_make_link_relative( $logos['dark']['url'] ) ); ?>" alt="" width="<?php echo esc_attr( $dark_width ); ?>" height="<?php echo esc_attr( $dark_height ); ?>" decoding="async" aria-hidden="true">
			<?php else : ?>
				<span class="orion-mark" aria-hidden="true"><i>26</i></span>
				<span><span class="site-brand__name"><?php bloginfo( 'name' ); ?></span><?php if ( orion26_setting( 'header.show_tagline', true ) ) : ?><span class="site-brand__tagline"><?php bloginfo( 'description' ); ?></span><?php endif; ?></span>
			<?php endif; ?>
		</a>
		<div class="site-actions">
			<?php if ( orion26_setting( 'header.show_theme_toggle', true ) && orion26_option( 'dark_mode', true ) ) : ?><button class="icon-button theme-toggle" type="button" data-theme-toggle aria-pressed="false" aria-label="<?php esc_attr_e( 'Changer de thème', 'orion26' ); ?>"><span data-theme-icon aria-hidden="true">☾</span></button><?php endif; ?>
			<?php if ( orion26_setting( 'header.show_search', true ) ) : ?><button class="icon-button" type="button" data-search-toggle aria-controls="site-search" aria-expanded="false" aria-label="<?php esc_attr_e( 'Rechercher', 'orion26' ); ?>">⌕</button>
			<div id="site-search" class="site-search" hidden><div class="site-search__inner"><?php get_search_form(); ?></div></div><?php endif; ?>
			<button class="menu-toggle" type="button" data-menu-toggle aria-controls="site-navigation" aria-expanded="false"><span aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Ouvrir le menu', 'orion26' ); ?></span></button>
		</div>
	</div>
	<nav id="site-navigation" class="site-navigation" aria-label="<?php esc_attr_e( 'Navigation principale', 'orion26' ); ?>" hidden>
		<div class="orion-container">
		<?php
		if ( $primary_menu || has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array( 'menu' => $primary_menu ?: '', 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'primary-menu', 'depth' => 2, 'fallback_cb' => false ) );
		} elseif ( ! orion26_configured_menu() ) {
			echo '<ul class="primary-menu">';
			wp_list_categories( array( 'title_li' => '', 'number' => 8, 'orderby' => 'count', 'order' => 'DESC', 'show_count' => false ) );
			echo orion26_more_news_menu_item();
			echo '</ul>';
		}
		?>
		</div>
	</nav>
</header>
