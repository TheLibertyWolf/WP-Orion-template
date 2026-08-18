<?php
/**
 * Fonctions du thème Orion 26.
 *
 * @package Orion26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ORION26_VERSION', '3.0.0' );
define( 'ORION26_DIR', __DIR__ );
define( 'ORION26_URI', content_url( '/themes/orion-26' ) );

require_once ORION26_DIR . '/inc/settings.php';
require_once ORION26_DIR . '/inc/consent.php';
require_once ORION26_DIR . '/inc/category-meta.php';
if ( is_admin() ) {
	require_once ORION26_DIR . '/inc/admin.php';
}

function orion26_setup() {
	load_theme_textdomain( 'orion26', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	add_image_size( 'orion26-lead', 1280, 800, true );
	add_image_size( 'orion26-card', 720, 480, true );
	register_nav_menus(
		array(
			'primary' => __( 'Navigation principale', 'orion26' ),
			'footer'  => __( 'Navigation de pied de page', 'orion26' ),
		)
	);
}
add_action( 'after_setup_theme', 'orion26_setup' );

function orion26_assets() {
	$css     = ORION26_DIR . '/style.css';
	$presets = ORION26_DIR . '/assets/css/presets.css';
	$js      = ORION26_DIR . '/assets/js/theme.js';
	wp_enqueue_style( 'orion26', ORION26_URI . '/style.css', array(), file_exists( $css ) ? (string) filemtime( $css ) : ORION26_VERSION );
	wp_enqueue_style( 'orion26-presets', ORION26_URI . '/assets/css/presets.css', array( 'orion26' ), file_exists( $presets ) ? (string) filemtime( $presets ) : ORION26_VERSION );
	wp_enqueue_script( 'orion26', ORION26_URI . '/assets/js/theme.js', array(), file_exists( $js ) ? (string) filemtime( $js ) : ORION26_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'orion26_assets' );

/**
 * Ouvre les liens externes du contenu éditorial dans un nouvel onglet.
 * Les liens internes, ancres, e-mails et numéros de téléphone restent inchangés.
 */
function orion26_external_content_links( $content ) {
	if ( is_admin() || ! is_singular() || ! class_exists( 'WP_HTML_Tag_Processor' ) || false === stripos( $content, '<a' ) ) {
		return $content;
	}

	$site_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	$site_host = preg_replace( '/^www\./', '', $site_host );
	$processor = new WP_HTML_Tag_Processor( $content );

	while ( $processor->next_tag( 'a' ) ) {
		$href = trim( (string) $processor->get_attribute( 'href' ) );
		if ( '' === $href || '#' === $href[0] || preg_match( '/^(?:mailto|tel|sms|javascript):/i', $href ) ) {
			continue;
		}

		$link_host = strtolower( (string) wp_parse_url( $href, PHP_URL_HOST ) );
		if ( '' === $link_host ) {
			continue;
		}
		$link_host = preg_replace( '/^www\./', '', $link_host );
		if ( $link_host === $site_host ) {
			continue;
		}

		$processor->set_attribute( 'target', '_blank' );
		$rel_tokens = preg_split( '/\s+/', strtolower( trim( (string) $processor->get_attribute( 'rel' ) ) ) );
		$rel_tokens = array_filter( array_unique( array_merge( (array) $rel_tokens, array( 'noopener', 'noreferrer', 'external' ) ) ) );
		$processor->set_attribute( 'rel', implode( ' ', $rel_tokens ) );
	}

	return $processor->get_updated_html();
}
add_filter( 'the_content', 'orion26_external_content_links', 20 );

/** Orion gère nativement les thèmes clair/sombre : ne charge pas les CSS AH19 du plugin historique. */
function orion26_disable_legacy_theme_stylesheets() {
	wp_dequeue_style( 'wp_user_stylesheet_switcher_files0' );
	wp_deregister_style( 'wp_user_stylesheet_switcher_files0' );
	wp_dequeue_script( 'wp_user_stylesheet_switcher_script_cookies' );
	wp_dequeue_script( 'wp-user_stylesheet_switcher_use_cookie_when_ready' );
}
add_action( 'wp_enqueue_scripts', 'orion26_disable_legacy_theme_stylesheets', 1000 );

/** Retourne une option ACF existante sans rendre le thème dépendant d’ACF. */
function orion26_option( $name, $fallback = '' ) {
	$map = array(
		'color'                   => 'design.accent',
		'color_hover'             => 'design.accent_hover',
		'article_font'            => 'design.article_font',
		'dark_mode'               => 'identity.dark_mode',
		'menu_uppercase'          => 'navigation.uppercase',
		'homepage_disc_list'      => 'homepage.featured_categories',
		'homepage_discipline_hub' => 'homepage.hub_categories',
		'homepage_restnews'       => 'homepage.secondary_categories',
		'tag_une_article'         => 'homepage.featured_tag',
		'otcher_disc_list'        => 'homepage.other_categories',
		'facebook'                => 'social.facebook',
		'insta'                   => 'social.instagram',
		'orion26_version'         => 'footer.version_label',
		'logo_light'              => 'identity.logo_light_id',
		'logo_dark'               => 'identity.logo_dark_id',
		'logo_footer'             => 'identity.logo_footer_id',
		'logo_height'             => 'identity.logo_height',
		'logo_footer_height'      => 'identity.footer_logo_height',
		'var_google-site-verification' => 'consent.google_verification',
		'var_msvalidate'          => 'consent.bing_verification',
		'var_google-analytic'     => 'consent.google_analytics_id',
		'var_adsense'             => 'consent.adsense_client',
		'var_matomo'              => 'consent.analytics_head',
	);
	if ( get_option( ORION26_SETTINGS_OPTION, null ) !== null ) {
		if ( 'logo' === $name ) {
			return array( 'img' => orion26_setting( 'identity.logo_light_id', 0 ), 'height' => orion26_setting( 'identity.logo_height', 40 ) );
		}
		if ( 'logo_img' === $name ) {
			return orion26_setting( 'identity.logo_light_id', $fallback );
		}
		if ( 'var' === $name ) {
			return array(
				'google-site-verification' => orion26_setting( 'consent.google_verification', '' ),
				'msvalidate'              => orion26_setting( 'consent.bing_verification', '' ),
				'google-analytic'         => orion26_setting( 'consent.google_analytics_id', '' ),
				'adsense'                 => orion26_setting( 'consent.adsense_client', '' ),
				'matomo'                  => orion26_setting( 'consent.analytics_head', '' ),
			);
		}
		if ( isset( $map[ $name ] ) ) {
			return orion26_setting( $map[ $name ], $fallback );
		}
	}
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name, 'option' );
		if ( false !== $value && null !== $value && '' !== $value ) {
			return $value;
		}
	}
	$value = get_option( 'options_' . $name, $fallback );
	return '' === $value ? $fallback : $value;
}

/** Autorisation dédiée aux administrateurs et aux utilisateurs sélectionnés. */
function orion26_settings_user_capability( $allcaps, $caps, $args, $user ) {
	if ( empty( $args[0] ) || 'manage_orion26_settings' !== $args[0] ) {
		return $allcaps;
	}
	if ( ! empty( $allcaps['manage_options'] ) ) {
		$allcaps['manage_orion26_settings'] = true;
		return $allcaps;
	}
	$allowed = function_exists( 'orion26_setting' ) ? orion26_setting( 'identity.access_users', array() ) : get_option( 'options_orion26_settings_users', array() );
	$allowed = is_array( $allowed ) ? $allowed : array( $allowed );
	if ( in_array( (int) $user->ID, array_map( 'absint', $allowed ), true ) ) {
		$allcaps['manage_orion26_settings'] = true;
	}
	return $allcaps;
}
add_filter( 'user_has_cap', 'orion26_settings_user_capability', 20, 4 );

/** La délégation d’accès elle-même reste réservée aux administrateurs. */
function orion26_prepare_access_field( $field ) {
	return current_user_can( 'manage_options' ) ? $field : false;
}
add_filter( 'acf/prepare_field/key=field_orion26_settings_users', 'orion26_prepare_access_field' );

function orion26_load_access_user_choices( $field ) {
	$field['choices'] = array();
	$users = get_users( array( 'orderby' => 'display_name', 'order' => 'ASC', 'fields' => array( 'ID', 'display_name', 'user_email' ) ) );
	foreach ( $users as $user ) {
		$field['choices'][ $user->ID ] = sprintf( '%s — %s', $user->display_name, $user->user_email );
	}
	return $field;
}
add_filter( 'acf/load_field/key=field_orion26_settings_users', 'orion26_load_access_user_choices' );

function orion26_protect_access_field_value( $value ) {
	if ( current_user_can( 'manage_options' ) ) {
		return $value;
	}
	return get_option( 'options_orion26_settings_users', array() );
}
add_filter( 'acf/update_value/key=field_orion26_settings_users', 'orion26_protect_access_field_value' );

function orion26_access_field_admin_style() {
	if ( empty( $_GET['page'] ) || 'orion-26' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
		return;
	}
	echo '<style id="orion26-access-admin-style">.orion26-access-list .acf-checkbox-list{max-height:230px;overflow-y:auto;margin:0;padding:7px 9px;border:1px solid #dcdcde;background:#fff}.orion26-access-list .acf-checkbox-list li{margin:0;padding:4px 0}.orion26-access-list .acf-checkbox-list label{display:block}</style>';
}
add_action( 'admin_head', 'orion26_access_field_admin_style' );

function orion26_term_ids( $value ) {
	$ids = array();
	foreach ( (array) $value as $item ) {
		if ( $item instanceof WP_Term ) {
			$ids[] = $item->term_id;
		} elseif ( is_object( $item ) && isset( $item->term_id ) ) {
			$ids[] = (int) $item->term_id;
		} else {
			$ids[] = absint( $item );
		}
	}
	return array_values( array_filter( array_unique( $ids ) ) );
}

function orion26_primary_color() {
	$value = sanitize_hex_color( (string) orion26_option( 'color', '#ed2438' ) );
	return $value ? $value : '#ed2438';
}

/** Normalise les formats ACF (tableau, identifiant ou URL) en image exploitable. */
function orion26_image_value( $value ) {
	if ( is_array( $value ) && ! empty( $value['url'] ) ) {
		return $value;
	}
	if ( is_array( $value ) && ! empty( $value['img'] ) ) {
		return orion26_image_value( $value['img'] );
	}
	$id = is_array( $value ) ? absint( $value['ID'] ?? $value['id'] ?? 0 ) : absint( $value );
	if ( $id ) {
		$source = wp_get_attachment_image_src( $id, 'full' );
		if ( $source ) {
			return array( 'ID' => $id, 'url' => $source[0], 'width' => $source[1], 'height' => $source[2] );
		}
	}
	if ( is_string( $value ) && filter_var( $value, FILTER_VALIDATE_URL ) ) {
		return array( 'url' => $value );
	}
	return array();
}

/** Retourne les logos historiques adaptés aux deux modes. */
function orion26_logos() {
	$legacy = orion26_option( 'logo', array() );
	$light  = orion26_image_value( orion26_option( 'logo_light', array() ) );
	$dark   = orion26_image_value( orion26_option( 'logo_dark', array() ) );
	$footer = orion26_image_value( orion26_option( 'logo_footer', array() ) );
	if ( empty( $light['url'] ) && is_array( $legacy ) ) {
		$light = orion26_image_value( $legacy['img'] ?? array() );
	}
	if ( empty( $light['url'] ) ) {
		$light = orion26_image_value( orion26_option( 'logo_img', get_option( 'options_logo_img' ) ) );
	}
	if ( empty( $dark['url'] ) ) {
		$dark = $footer ?: orion26_image_value( get_option( 'options_logo_footer' ) );
	}
	if ( empty( $dark['url'] ) ) {
		$dark = $light;
	}
	return array( 'light' => $light, 'dark' => $dark, 'footer' => $footer ?: $dark );
}

/** Retourne la hauteur d'affichage configurée, sans imposer de largeur au logo. */
function orion26_logo_display_height( $location = 'header' ) {
	if ( 'footer' === $location ) {
		$height = absint( orion26_option( 'logo_footer_height', 48 ) );
		return min( 160, max( 20, $height ?: 48 ) );
	}

	$logo   = orion26_option( 'logo', array() );
	$height = is_array( $logo ) ? absint( $logo['height'] ?? 0 ) : 0;
	if ( ! $height ) {
		$height = absint( orion26_option( 'logo_height', 37 ) );
	}
	return min( 160, max( 20, $height ?: 37 ) );
}

/** Dernier élément permanent du menu principal. */
function orion26_more_news_menu_item() {
	$url = (string) orion26_setting( 'navigation.more_url', '/others/' );
	$url = str_starts_with( $url, '/' ) ? home_url( $url ) : $url;
	$label = (string) orion26_setting( 'navigation.more_label', __( 'Plus d’actualités', 'orion26' ) );
	return sprintf( '<li class="menu-item menu-item-more-news"><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
}

function orion26_append_more_news_menu( $items, $args ) {
	if ( ! orion26_setting( 'navigation.append_more', true ) || ! isset( $args->theme_location ) || 'primary' !== $args->theme_location || false !== strpos( $items, (string) orion26_setting( 'navigation.more_url', '/others/' ) ) ) {
		return $items;
	}
	return $items . orion26_more_news_menu_item();
}
add_filter( 'wp_nav_menu_items', 'orion26_append_more_news_menu', 20, 2 );

/** Rend le menu configuré historiquement dans AH19 si aucun menu WordPress n’est affecté. */
function orion26_configured_menu() {
	$items = (array) orion26_option( 'menu', array() );
	if ( ! $items ) {
		return false;
	}
	echo '<ul class="primary-menu">';
	foreach ( $items as $item ) {
		$type = sanitize_key( $item['type'] ?? '' );
		$url  = '';
		if ( 'dsc' === $type && ! empty( $item['discipline'] ) ) {
			$url = get_category_link( absint( $item['discipline'] ) );
		} elseif ( 'pge' === $type ) {
			$url = $item['link_int'] ?? '';
		} elseif ( 'lk' === $type ) {
			$url = $item['lien_simple_lien'] ?? '';
		}
		if ( is_wp_error( $url ) || ! $url || empty( $item['title'] ) ) {
			continue;
		}
		printf( '<li><a href="%1$s"%2$s>%3$s</a></li>', esc_url( $url ), 'lk' === $type ? ' target="_blank" rel="noopener noreferrer"' : '', esc_html( $item['title'] ) );
	}
	echo orion26_more_news_menu_item();
	echo '</ul>';
	return true;
}

/** Mentions éditoriales héritées d’AH19, affichées en tête de sidebar. */
function orion26_editorial_notices( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$notices = array(
		'sponso' => array( 'title' => __( 'Article sponsorisé', 'orion26' ), 'text' => __( 'Ce contenu a été réalisé dans le cadre d’un partenariat commercial.', 'orion26' ), 'icon' => '◇' ),
		'cp' => array( 'title' => __( 'Communiqué de presse', 'orion26' ), 'text' => __( 'Cette information provient d’un communiqué transmis à la rédaction.', 'orion26' ), 'icon' => '◈' ),
		'histo' => array( 'title' => __( 'Article historique', 'orion26' ), 'text' => __( 'Une archive éditoriale remise en lumière pour les lecteurs.', 'orion26' ), 'icon' => '◎' ),
	);
	foreach ( $notices as $meta_key => $notice ) {
		if ( '1' !== (string) get_post_meta( $post_id, $meta_key, true ) ) {
			continue;
		}
		printf( '<section class="article-notice article-notice--%1$s"><span aria-hidden="true">%2$s</span><div><h2>%3$s</h2><p>%4$s</p></div></section>', esc_attr( $meta_key ), esc_html( $notice['icon'] ), esc_html( $notice['title'] ), esc_html( $notice['text'] ) );
	}
}

/** Réutilise les favicons configurés dans AH19. */
function orion26_favicons() {
	$icons = (array) orion26_option( 'favicon', array() );
	foreach ( array( '16', '32', '192', '512' ) as $size ) {
		if ( ! empty( $icons[ $size ]['url'] ) ) {
			printf( '<link rel="icon" href="%1$s" sizes="%2$sx%2$s">', esc_url( $icons[ $size ]['url'] ), esc_attr( $size ) );
		}
	}
	if ( ! empty( $icons['180']['url'] ) ) {
		printf( '<link rel="apple-touch-icon" href="%s" sizes="180x180">', esc_url( $icons['180']['url'] ) );
	}
}
add_action( 'wp_head', 'orion26_favicons', 4 );

function orion26_service_value( $key ) {
	$services = (array) orion26_option( 'var', array() );
	$value = $services[ $key ] ?? '';
	return '' !== trim( (string) $value ) ? $value : orion26_option( 'var_' . $key, '' );
}

function orion26_service_meta() {
	$google = sanitize_text_field( (string) orion26_service_value( 'google-site-verification' ) );
	$bing   = sanitize_text_field( (string) orion26_service_value( 'msvalidate' ) );
	if ( $google ) {
		printf( '<meta name="google-site-verification" content="%s">', esc_attr( $google ) );
	}
	if ( $bing ) {
		printf( '<meta name="msvalidate.01" content="%s">', esc_attr( $bing ) );
	}
}
add_action( 'wp_head', 'orion26_service_meta', 5 );

/** Google Analytics et AdSense, compatibles avec les clés historiques d’AH19. */
function orion26_google_services() {
	if ( orion26_consent_enabled() ) {
		return;
	}
	$analytics = sanitize_text_field( (string) orion26_service_value( 'google-analytic' ) );
	$analytics = preg_match( '/^(?:G|UA|AW)-[A-Z0-9-]+$/i', $analytics ) ? $analytics : '';
	if ( $analytics ) {
		printf( '<script async src="%s"></script>', esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $analytics ) ) );
		wp_print_inline_script_tag( 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config",' . wp_json_encode( $analytics ) . ');', array( 'id' => 'orion26-google-analytics' ) );
	}

	$adsense = sanitize_text_field( (string) orion26_service_value( 'adsense' ) );
	$adsense = preg_match( '/^ca-pub-[0-9]+$/', $adsense ) ? $adsense : '';
	if ( $adsense ) {
		printf( '<script async src="%1$s" crossorigin="anonymous"></script>', esc_url( 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( $adsense ) ) );
		wp_print_inline_script_tag( '(adsbygoogle=window.adsbygoogle||[]).push({google_ad_client:' . wp_json_encode( $adsense ) . ',enable_page_level_ads:true});', array( 'id' => 'orion26-adsense' ) );
	}
}
add_action( 'wp_head', 'orion26_google_services', 6 );

/** Ajoute le code de suivi Matomo configuré par un administrateur. */
function orion26_matomo_tag() {
	if ( orion26_consent_enabled() ) {
		return;
	}
	$matomo = trim( (string) orion26_service_value( 'matomo' ) );
	if ( ! $matomo ) {
		return;
	}
	/* Complianz génère déjà la balise avec le consentement RGPD : ne pas la doubler. */
	if ( function_exists( 'cmplz_get_value' ) && 'matomo' === cmplz_get_value( 'compile_statistics' ) ) {
		return;
	}
	$noscript = '';
	if ( preg_match( '#<noscript\b[^>]*>(.*?)</noscript>#is', $matomo, $noscript_match ) ) {
		$noscript = $noscript_match[1];
	}
	if ( false !== stripos( $matomo, '<script' ) ) {
		preg_match_all( '#<script\b[^>]*>(.*?)</script>#is', $matomo, $scripts );
		$matomo = isset( $scripts[1] ) ? trim( implode( "\n", $scripts[1] ) ) : '';
	}
	if ( $matomo ) {
		wp_print_inline_script_tag( $matomo, array( 'id' => 'orion26-matomo' ) );
	}
	if ( $noscript ) {
		echo '<noscript>' . wp_kses( $noscript, array( 'p' => array(), 'img' => array( 'src' => true, 'alt' => true, 'style' => true, 'width' => true, 'height' => true, 'referrerpolicy' => true ) ) ) . '</noscript>';
	}
}
add_action( 'wp_footer', 'orion26_matomo_tag', 20 );

function orion26_body_classes( $classes ) {
	$classes[] = 'orion26';
	$classes[] = 'orion-preset-' . sanitize_html_class( (string) orion26_setting( 'identity.preset', 'minimal' ) );
	if ( orion26_setting( 'navigation.sticky', false ) ) {
		$classes[] = 'orion-sticky-header';
	}
	if ( orion26_option( 'menu_uppercase', true ) ) {
		$classes[] = 'menu-uppercase';
	}
	if ( is_singular() && has_post_thumbnail() ) {
		$classes[] = 'has-featured-image';
	}
	return $classes;
}
add_filter( 'body_class', 'orion26_body_classes' );

/** Polices éditoriales auto-hébergées disponibles pour le corps des articles. */
function orion26_article_fonts() {
	return array(
		'source-serif-4'             => array( 'label' => 'Source Serif 4 — éditoriale et équilibrée', 'stack' => '"Source Serif 4", Georgia, serif' ),
		'atkinson-hyperlegible-next' => array( 'label' => 'Atkinson Hyperlegible Next — lisibilité maximale', 'stack' => '"Atkinson Hyperlegible Next", Arial, sans-serif' ),
		'lora'                       => array( 'label' => 'Lora — élégante et chaleureuse', 'stack' => 'Lora, Georgia, serif' ),
		'merriweather'               => array( 'label' => 'Merriweather — classique et robuste', 'stack' => 'Merriweather, Georgia, serif' ),
		'literata'                   => array( 'label' => 'Literata — magazine et grands reportages', 'stack' => 'Literata, Georgia, serif' ),
		'ibm-plex-sans'              => array( 'label' => 'IBM Plex Sans — moderne et sans-serif', 'stack' => '"IBM Plex Sans", Arial, sans-serif' ),
	);
}

function orion26_selected_article_font() {
	$fonts = orion26_article_fonts();
	$key   = sanitize_key( (string) orion26_option( 'article_font', 'source-serif-4' ) );
	return isset( $fonts[ $key ] ) ? $fonts[ $key ] : $fonts['source-serif-4'];
}

function orion26_font_stack( $key, $context = 'body' ) {
	$fonts = array(
		'system'                     => 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
		'condensed'                  => '"Arial Narrow", "Roboto Condensed", Impact, sans-serif',
		'source-serif-4'             => '"Source Serif 4", Georgia, serif',
		'atkinson-hyperlegible-next' => '"Atkinson Hyperlegible Next", Arial, sans-serif',
		'lora'                       => 'Lora, Georgia, serif',
		'merriweather'               => 'Merriweather, Georgia, serif',
		'literata'                   => 'Literata, Georgia, serif',
		'ibm-plex-sans'              => '"IBM Plex Sans", Arial, sans-serif',
	);
	$fallback = 'display' === $context ? 'condensed' : 'system';
	return $fonts[ $key ] ?? $fonts[ $fallback ];
}

function orion26_inline_colors() {
	$color = static function ( $path, $fallback ) { return sanitize_hex_color( (string) orion26_setting( $path, $fallback ) ) ?: $fallback; };
	$primary = $color( 'design.accent', '#ed2438' );
	$hover   = $color( 'design.accent_hover', '#b61629' );
	$article_font = orion26_font_stack( sanitize_key( (string) orion26_setting( 'design.article_font', 'source-serif-4' ) ), 'article' );
	$body_font    = orion26_font_stack( sanitize_key( (string) orion26_setting( 'design.body_font', 'system' ) ) );
	$display_font = orion26_font_stack( sanitize_key( (string) orion26_setting( 'design.display_font', 'condensed' ) ), 'display' );
	$values = array(
		'--orion-accent' => $primary, '--orion-accent-hover' => $hover,
		'--orion-gold' => $color( 'design.secondary', '#b68b3d' ), '--orion-bg' => $color( 'design.background', '#f7f5ef' ),
		'--orion-surface' => $color( 'design.surface', '#ffffff' ), '--orion-surface-2' => $color( 'design.surface_alt', '#eeece6' ),
		'--orion-text' => $color( 'design.text', '#12151b' ), '--orion-muted' => $color( 'design.muted', '#626873' ), '--orion-line' => $color( 'design.line', '#d9d6cf' ),
		'--orion-body' => $body_font, '--orion-display' => $display_font, '--orion-article-font' => $article_font,
		'--orion-base-size' => max( 14, min( 22, absint( orion26_setting( 'design.base_size', 16 ) ) ) ) . 'px',
		'--orion-article-size' => max( 15, min( 26, absint( orion26_setting( 'design.article_size', 18 ) ) ) ) . 'px',
		'--orion-article-leading' => max( 1.3, min( 2.2, (float) orion26_setting( 'design.article_line_height', 1.8 ) ) ),
		'--orion-title-case' => 'none' === orion26_setting( 'design.title_case', 'uppercase' ) ? 'none' : 'uppercase',
		'--orion-radius' => max( 0, min( 30, absint( orion26_setting( 'design.radius', 3 ) ) ) ) . 'px',
		'--orion-quote-accent' => $color( 'design.blockquote_accent', '#b68b3d' ), '--orion-quote-style' => 'normal' === orion26_setting( 'design.blockquote_style', 'italic' ) ? 'normal' : 'italic',
		'--orion-code-bg' => $color( 'design.code_background', '#171b24' ), '--orion-code-text' => $color( 'design.code_text', '#f1eee7' ),
		'--orion-container-width' => max( 960, min( 1800, absint( orion26_setting( 'identity.container_width', 1240 ) ) ) ) . 'px',
	);
	$root = '';
	foreach ( $values as $property => $value ) { $root .= $property . ':' . $value . ';'; }
	$dark = '--orion-bg:' . $color( 'design.dark_background', '#0b0d12' ) . ';--orion-surface:' . $color( 'design.dark_surface', '#11141b' ) . ';--orion-surface-2:' . $color( 'design.dark_surface_alt', '#171b24' ) . ';--orion-text:' . $color( 'design.dark_text', '#f1eee7' ) . ';--orion-muted:' . $color( 'design.dark_muted', '#a1a7b3' ) . ';--orion-line:' . $color( 'design.dark_line', '#2b303b' ) . ';';
	printf(
		'<style id="orion26-brand-color">:root{%1$s}:root[data-theme="dark"]{%2$s}@media(prefers-color-scheme:dark){:root:not([data-theme="light"]){%2$s}}.news-article .article-content h2,.news-article .article-content h3,.news-article .article-content h4,.news-article .article-content h5,.news-article .article-content h6{color:%3$s!important}.home-main .story-category,.article-header .story-category{background:%3$s!important;color:#fff!important}</style>',
		$root, // Valeurs bornées ou issues de listes statiques.
		$dark,
		esc_html( $primary )
	);
}
add_action( 'wp_head', 'orion26_inline_colors', 2 );

/** Temps de lecture, calculé sur le contenu textuel. */
function orion26_reading_time( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$content = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	$words   = preg_split( '/\s+/u', trim( $content ) );
	$count   = is_array( $words ) && '' !== trim( $content ) ? count( $words ) : 0;
	return max( 1, (int) ceil( $count / 220 ) );
}

/** Libellé relatif éditorial : minutes, heures, sept jours, semaine dernière, puis date. */
function orion26_human_date_text( $timestamp ) {
	$timestamp = (int) $timestamp;
	$delta = time() - $timestamp;
	if ( $delta < 0 ) {
		return wp_date( 'j F Y', $timestamp );
	}
	if ( $delta < MINUTE_IN_SECONDS ) {
		return __( 'à l’instant', 'orion26' );
	}
	if ( $delta < HOUR_IN_SECONDS ) {
		$minutes = max( 1, (int) floor( $delta / MINUTE_IN_SECONDS ) );
		return sprintf( _n( 'il y a %d minute', 'il y a %d minutes', $minutes, 'orion26' ), $minutes );
	}
	if ( $delta < DAY_IN_SECONDS ) {
		$hours = max( 1, (int) floor( $delta / HOUR_IN_SECONDS ) );
		return sprintf( _n( 'il y a %d heure', 'il y a %d heures', $hours, 'orion26' ), $hours );
	}
	if ( $delta <= 7 * DAY_IN_SECONDS ) {
		$days = max( 1, (int) floor( $delta / DAY_IN_SECONDS ) );
		if ( 1 === $days ) {
			return __( 'hier', 'orion26' );
		}
		return sprintf( _n( 'il y a %d jour', 'il y a %d jours', $days, 'orion26' ), $days );
	}
	if ( $delta <= 14 * DAY_IN_SECONDS ) {
		return __( 'la semaine dernière', 'orion26' );
	}
	return wp_date( 'j F Y', $timestamp );
}

/** Date cliquable : le texte relatif bascule discrètement vers la date exacte. */
function orion26_date( $timestamp, $absolute_format = 'j F Y', $itemprop = '' ) {
	$timestamp = (int) $timestamp;
	$relative = orion26_human_date_text( $timestamp );
	$absolute = wp_date( $absolute_format, $timestamp );
	printf(
		'<time class="orion-date" datetime="%1$s" data-relative="%2$s" data-absolute="%3$s" role="switch" aria-checked="false" tabindex="0"%4$s title="%5$s">%2$s</time>',
		esc_attr( wp_date( DATE_W3C, $timestamp ) ),
		esc_attr( $relative ),
		esc_attr( $absolute ),
		$itemprop ? ' itemprop="' . esc_attr( $itemprop ) . '"' : '',
		esc_attr__( 'Afficher la date exacte', 'orion26' )
	);
}

function orion26_post_meta( $post_id = 0, $compact = false ) {
	$post_id  = $post_id ?: get_the_ID();
	$author_id = (int) get_post_field( 'post_author', $post_id );
	$reading_time = orion26_reading_time( $post_id );
	?>
	<div class="entry-meta<?php echo $compact ? ' entry-meta--compact' : ''; ?>">
		<?php if ( $compact ) : ?>
			<?php orion26_date( get_post_time( 'U', true, $post_id ) ); ?>
		<?php else : ?>
			<span><?php esc_html_e( 'Publié', 'orion26' ); ?> <?php orion26_date( get_post_time( 'U', true, $post_id ), 'j F Y à H\hi' ); ?></span>
			<span aria-hidden="true">·</span>
			<span><?php esc_html_e( 'Mis à jour', 'orion26' ); ?> <?php orion26_date( get_post_modified_time( 'U', true, $post_id ), 'j F Y à H\hi' ); ?></span>
		<?php endif; ?>
		<span aria-hidden="true">·</span>
		<span><?php echo esc_html( sprintf( $compact ? __( '%d min', 'orion26' ) : __( '%d min de lecture', 'orion26' ), $reading_time ) ); ?></span>
		<?php if ( ! $compact ) : ?>
			<span aria-hidden="true">·</span>
			<span><?php esc_html_e( 'Par', 'orion26' ); ?> <a rel="author" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></a></span>
		<?php endif; ?>
	</div>
	<?php
}

function orion26_excerpt( $post_id = 0, $length = 28 ) {
	$post_id = $post_id ?: get_the_ID();
	$text = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : get_post_field( 'post_content', $post_id );
	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $text ) ), $length, '…' );
}

function orion26_category_label( $post_id = 0 ) {
	$categories = get_the_category( $post_id ?: get_the_ID() );
	if ( empty( $categories ) ) {
		return;
	}
	printf( '<a class="story-category" href="%1$s">%2$s</a>', esc_url( get_category_link( $categories[0] ) ), esc_html( $categories[0]->name ) );
}

function orion26_pagination( $query = null ) {
	global $wp_query;
	$original_query = $wp_query;
	if ( $query instanceof WP_Query ) {
		$wp_query = $query;
	}
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'prev_text' => '<span aria-hidden="true">←</span> ' . __( 'Précédent', 'orion26' ),
			'next_text' => __( 'Suivant', 'orion26' ) . ' <span aria-hidden="true">→</span>',
			'screen_reader_text' => __( 'Navigation des articles', 'orion26' ),
		)
	);
	$wp_query = $original_query;
}

/** Nettoie les entités HTML avant leur passage en JSON-LD. */
function orion26_schema_text( $text ) {
	return trim( wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) ) );
}

/** URL canonique de la vue courante, sans paramètres de suivi. */
function orion26_current_page_url() {
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$url = get_term_link( get_queried_object() );
	} elseif ( is_author() ) {
		$url = get_author_posts_url( get_queried_object_id() );
	} elseif ( is_search() ) {
		$url = get_search_link( get_search_query() );
	} else {
		$url = home_url( '/' );
	}
	if ( is_wp_error( $url ) ) {
		$url = home_url( '/' );
	}
	return $paged > 1 ? trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' ) : $url;
}

/** Construit le chemin éditorial utilisé à l’écran et dans les données structurées. */
function orion26_breadcrumb_items() {
	if ( is_front_page() ) {
		return array();
	}
	$items = array( array( home_url( '/' ), __( 'Accueil', 'orion26' ) ) );
	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();
		if ( $categories ) {
			foreach ( array_reverse( get_ancestors( $categories[0]->term_id, 'category', 'taxonomy' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, 'category' );
				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$items[] = array( get_category_link( $ancestor ), $ancestor->name );
				}
			}
			$items[] = array( get_category_link( $categories[0] ), $categories[0]->name );
		}
		$items[] = array( '', get_the_title() );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor_id ) {
			$items[] = array( get_permalink( $ancestor_id ), get_the_title( $ancestor_id ) );
		}
		$items[] = array( '', get_the_title() );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$items[] = array( '', $term instanceof WP_Term ? $term->name : single_term_title( '', false ) );
	} elseif ( is_author() ) {
		$author = get_queried_object();
		$items[] = array( '', $author instanceof WP_User ? $author->display_name : get_the_archive_title() );
	} elseif ( is_search() ) {
		$items[] = array( '', sprintf( __( 'Recherche : %s', 'orion26' ), get_search_query() ) );
	} elseif ( is_home() ) {
		$items[] = array( '', __( 'Toutes les actualités', 'orion26' ) );
	} else {
		$items[] = array( '', wp_strip_all_tags( get_the_archive_title() ) );
	}
	return $items;
}

/** Fil d’Ariane sémantique, volontairement léger. */
function orion26_breadcrumbs() {
	$items = orion26_breadcrumb_items();
	if ( ! $items ) {
		return;
	}
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Fil d’Ariane', 'orion26' ) . '"><ol>';
	foreach ( $items as $index => $item ) {
		echo '<li>';
		if ( $item[0] && $index < count( $items ) - 1 ) {
			printf( '<a href="%1$s">%2$s</a>', esc_url( $item[0] ), esc_html( $item[1] ) );
		} else {
			echo '<span aria-current="page">' . esc_html( $item[1] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

/**
 * Affiche les publicités d’un post type `ad` compatible, sans dupliquer leur stockage.
 * Les restrictions d’article, catégorie, date et pagination sont respectées.
 */
function orion26_render_ads( $placement ) {
	$placement = sanitize_key( $placement );
	if ( ! $placement ) {
		return;
	}
	$query = new WP_Query(
		array(
			'post_type'              => 'ad',
			'post_status'            => 'publish',
			'posts_per_page'         => 10,
			'no_found_rows'          => true,
			'orderby'                => 'meta_value_num',
			'meta_key'               => 'order',
			'order'                  => 'ASC',
			'update_post_term_cache' => false,
			'meta_query'             => array(
				'relation' => 'AND',
				array( 'key' => 'statut', 'value' => '1' ),
				array( 'key' => 'place', 'value' => $placement ),
			),
		)
	);
	if ( ! $query->have_posts() ) {
		return;
	}
	ob_start();
	while ( $query->have_posts() ) {
		$query->the_post();
		$ad_id = get_the_ID();
		$restriction = function_exists( 'get_field' ) ? (array) get_field( 'restriction', $ad_id ) : array();
		if ( ! orion26_ad_is_allowed( $restriction ) ) {
			continue;
		}
		$ad = function_exists( 'get_field' ) ? (array) get_field( 'ad', $ad_id ) : array();
		if ( ! $ad ) {
			$ad = array(
				'type'       => get_post_meta( $ad_id, 'ad_type', true ),
				'img'        => get_post_meta( $ad_id, 'ad_img', true ),
				'link_img'   => get_post_meta( $ad_id, 'ad_link_img', true ),
				'name'       => get_post_meta( $ad_id, 'ad_name', true ),
				'alt_img'    => get_post_meta( $ad_id, 'ad_alt_img', true ),
				'link'       => get_post_meta( $ad_id, 'ad_link', true ),
				'opt_link'   => get_post_meta( $ad_id, 'ad_opt_link', true ),
				'data-ad-slot' => get_post_meta( $ad_id, 'ad_data-ad-slot', true ),
				'code'       => get_post_meta( $ad_id, 'ad_code', true ),
			);
		}
		orion26_render_ad_content( $ad, $ad_id );
	}
	$content = trim( (string) ob_get_clean() );
	wp_reset_postdata();
	if ( '' === $content ) {
		return;
	}
	echo '<aside class="orion-ad orion-ad--' . esc_attr( $placement ) . '" aria-label="' . esc_attr__( 'Publicité', 'orion26' ) . '"><span class="orion-ad__label">' . esc_html__( 'Publicité', 'orion26' ) . '</span>';
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenu déjà échappé par le moteur publicitaire.
	echo '</aside>';
}

function orion26_ad_is_allowed( $restriction ) {
	if ( ! empty( $restriction['paged_off'] ) && is_paged() ) {
		return false;
	}
	$today = current_time( 'Ymd' );
	if ( ! empty( $restriction['date_debut'] ) && $today < preg_replace( '/\D/', '', (string) $restriction['date_debut'] ) ) {
		return false;
	}
	if ( ! empty( $restriction['date_fin'] ) && $today > preg_replace( '/\D/', '', (string) $restriction['date_fin'] ) ) {
		return false;
	}
	if ( ! empty( $restriction['single'] ) && is_singular() ) {
		$ids = wp_parse_id_list( $restriction['single'] );
		if ( $ids && ! in_array( get_queried_object_id(), $ids, true ) ) {
			return false;
		}
	}
	if ( ! empty( $restriction['cat'] ) ) {
		$ids = wp_parse_id_list( $restriction['cat'] );
		$current = is_category() ? get_queried_object_id() : ( is_singular( 'post' ) ? wp_get_post_categories( get_queried_object_id() ) : array() );
		if ( $ids && ! array_intersect( $ids, (array) $current ) ) {
			return false;
		}
	}
	return true;
}

function orion26_render_ad_content( $ad, $ad_id ) {
	$type = sanitize_key( $ad['type'] ?? '' );
	if ( in_array( $type, array( 'img', 'link' ), true ) ) {
		$image = $ad['img'] ?? '';
		$src = 'img' === $type ? ( is_array( $image ) ? ( $image['url'] ?? '' ) : wp_get_attachment_image_url( absint( $image ), 'full' ) ) : ( $ad['link_img'] ?? '' );
		if ( ! $src ) {
			return;
		}
		$rel = array( 'noopener', 'noreferrer', 'sponsored' );
		foreach ( (array) ( $ad['opt_link'] ?? array() ) as $value ) {
			$value = strtolower( sanitize_key( $value ) );
			if ( in_array( $value, array( 'nofollow', 'ugc', 'sponsored' ), true ) ) {
				$rel[] = $value;
			}
		}
		printf( '<a class="orion-ad__creative" href="%1$s" target="_blank" rel="%2$s"><img src="%3$s" alt="%4$s" title="%5$s" loading="lazy" decoding="async"></a>', esc_url( $ad['link'] ?? '' ), esc_attr( implode( ' ', array_unique( $rel ) ) ), esc_url( $src ), esc_attr( $ad['alt_img'] ?? '' ), esc_attr( $ad['name'] ?? '' ) );
	} elseif ( 'adsense' === $type ) {
		$client = preg_match( '/^ca-pub-[0-9]+$/', (string) orion26_option( 'var_adsense' ) ) ? orion26_option( 'var_adsense' ) : orion26_option( 'adsense' );
		$slot = preg_replace( '/\D/', '', (string) ( $ad['data-ad-slot'] ?? '' ) );
		if ( preg_match( '/^ca-pub-[0-9]+$/', (string) $client ) && $slot ) {
			printf( '<ins class="adsbygoogle" style="display:block" data-ad-client="%1$s" data-ad-slot="%2$s" data-ad-format="auto" data-full-width-responsive="true"></ins><script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>', esc_attr( $client ), esc_attr( $slot ) );
		}
	} elseif ( 'code' === $type && ! empty( $ad['code'] ) ) {
		// Le module publicitaire limite l’enregistrement de ce code aux utilisateurs autorisés.
		echo apply_filters( 'orion26_ad_code', $ad['code'], $ad_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

function orion26_adsense_script() {
	if ( orion26_consent_enabled() ) {
		return;
	}
	$client = (string) orion26_option( 'var_adsense', orion26_option( 'adsense', '' ) );
	if ( preg_match( '/^ca-pub-[0-9]+$/', $client ) ) {
		printf( '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=%1$s" crossorigin="anonymous"></script>', esc_attr( $client ) );
	}
}
add_action( 'wp_head', 'orion26_adsense_script', 20 );

/** Schémas éditoriaux complémentaires à SEOPress. */
function orion26_structured_data() {
	$graph = array();
	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$author_id = (int) get_post_field( 'post_author', $post_id );
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
		$author_url = get_author_posts_url( $author_id );
		$author = array(
			'@type' => 'Person',
			'@id'   => $author_url . '#person',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
			'url'   => $author_url,
		);
		$author_website = get_the_author_meta( 'url', $author_id );
		if ( $author_website ) {
			$author['sameAs'] = array( esc_url_raw( $author_website ) );
		}
		preg_match_all( '/[\p{L}\p{N}]+(?:[’\'-][\p{L}\p{N}]+)*/u', wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), $words );
		$article = array(
			'@type' => 'NewsArticle',
			'@id' => get_permalink( $post_id ) . '#newsarticle',
			'url' => get_permalink( $post_id ),
			'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink( $post_id ) ),
			'headline' => orion26_schema_text( get_the_title( $post_id ) ),
			'description' => orion26_excerpt( $post_id, 45 ),
			'datePublished' => get_the_date( DATE_W3C, $post_id ),
			'dateModified' => get_the_modified_date( DATE_W3C, $post_id ),
			'inLanguage' => get_bloginfo( 'language' ),
			'isAccessibleForFree' => true,
			'author' => $author,
			'publisher' => array( '@type' => 'NewsMediaOrganization', '@id' => home_url( '/#organization' ), 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
			'articleSection' => wp_get_post_categories( $post_id, array( 'fields' => 'names' ) ),
			'wordCount' => count( $words[0] ),
		);
		$keywords = wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
		if ( $keywords ) {
			$article['keywords'] = array_values( $keywords );
		}
		if ( $image ) {
			$article['image'] = array( '@type' => 'ImageObject', 'url' => $image[0], 'width' => (int) $image[1], 'height' => (int) $image[2] );
			$article['thumbnailUrl'] = $image[0];
		}
		$brand_logos = orion26_logos();
		$logo = (array) $brand_logos['light'];
		if ( ! empty( $logo['url'] ) ) {
			$article['publisher']['logo'] = array( '@type' => 'ImageObject', 'url' => esc_url_raw( $logo['url'] ) );
		}
		$graph[] = $article;
	} elseif ( is_author() ) {
		$author = get_queried_object();
		if ( $author instanceof WP_User ) {
			$author_url = get_author_posts_url( $author->ID );
			$person = array(
				'@type' => 'Person',
				'@id' => $author_url . '#person',
				'identifier' => (string) $author->ID,
				'name' => $author->display_name,
				'url' => $author_url,
			);
			$bio = wp_strip_all_tags( get_the_author_meta( 'description', $author->ID ) );
			if ( $bio ) {
				$person['description'] = $bio;
			}
			$avatar = get_avatar_data( $author->ID, array( 'size' => 256, 'default' => 'blank' ) );
			if ( ! empty( $avatar['found_avatar'] ) && ! empty( $avatar['url'] ) ) {
				$person['image'] = esc_url_raw( $avatar['url'] );
			}
			$author_website = get_the_author_meta( 'url', $author->ID );
			if ( $author_website ) {
				$person['sameAs'] = array( esc_url_raw( $author_website ) );
			}
			$profile = array(
				'@type' => 'ProfilePage',
				'@id' => $author_url . '#profile',
				'url' => $author_url,
				'name' => sprintf( 'Articles de %s', $author->display_name ),
				'dateCreated' => get_date_from_gmt( $author->user_registered, DATE_W3C ),
				'mainEntity' => $person,
			);
			$recent = get_posts( array( 'author' => $author->ID, 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5, 'no_found_rows' => true ) );
			if ( $recent ) {
				$profile['hasPart'] = array_map( static function ( $post ) use ( $person ) {
					return array( '@type' => 'NewsArticle', 'headline' => orion26_schema_text( get_the_title( $post ) ), 'url' => get_permalink( $post ), 'datePublished' => get_the_date( DATE_W3C, $post ), 'author' => array( '@id' => $person['@id'] ) );
				}, $recent );
			}
			$graph[] = $profile;
		}
	}
	$breadcrumb_items = function_exists( 'orion26_breadcrumb_items' ) ? orion26_breadcrumb_items() : array();
	if ( count( $breadcrumb_items ) > 1 ) {
		$list = array();
		foreach ( $breadcrumb_items as $position => $item ) {
			$crumb = array( '@type' => 'ListItem', 'position' => $position + 1, 'name' => orion26_schema_text( $item[1] ) );
			if ( $item[0] ) {
				$crumb['item'] = $item[0];
			}
			$list[] = $crumb;
		}
		$graph[] = array( '@type' => 'BreadcrumbList', '@id' => orion26_current_page_url() . '#breadcrumb', 'itemListElement' => $list );
	}
	if ( $graph ) {
		echo '<script id="orion26-schema" type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>';
	}
}
add_action( 'wp_head', 'orion26_structured_data', 35 );

/** Repli SEO uniquement si SEOPress n’est plus actif. */
function orion26_fallback_meta() {
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}
	$description = '';
	if ( is_singular() ) {
		$description = orion26_excerpt( get_queried_object_id(), 35 );
	} elseif ( is_category() || is_tag() ) {
		$description = term_description();
	} elseif ( is_author() ) {
		$description = get_the_author_meta( 'description', get_queried_object_id() );
	} elseif ( is_front_page() ) {
		$description = get_bloginfo( 'description' );
	}
	if ( $description ) {
		printf( '<meta name="description" content="%s">', esc_attr( wp_html_excerpt( wp_strip_all_tags( $description ), 160, '…' ) ) );
	}
}
add_action( 'wp_head', 'orion26_fallback_meta', 5 );

function orion26_robots( $robots ) {
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		return $robots;
	}
	if ( is_search() || is_404() ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}
	$robots['max-image-preview'] = 'large';
	return $robots;
}
add_filter( 'wp_robots', 'orion26_robots' );

function orion26_archive_query( $query ) {
	if ( ! is_admin() && $query->is_main_query() && ( $query->is_archive() || $query->is_search() || $query->is_home() ) ) {
		$query->set( 'posts_per_page', 18 );
		$query->set( 'ignore_sticky_posts', false );
	}
}
add_action( 'pre_get_posts', 'orion26_archive_query' );

function orion26_cleanup_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'orion26_cleanup_head' );

/** Réutilise les options AH19 tout en donnant un écran de réglages à Orion 26. */
function orion26_acf_options() {
	if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$location = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'orion-26' ) ) );
	acf_add_options_page(
		array(
			'page_title' => 'Orion 26',
			'menu_title' => 'Orion 26',
			'menu_slug' => 'orion-26',
			'capability' => 'manage_orion26_settings',
			'icon_url' => 'dashicons-star-filled',
			'position' => 4,
			'post_id' => 'options',
			'redirect' => false,
		)
	);
	acf_add_local_field_group(
		array(
			'key' => 'group_orion26_access',
			'title' => 'Orion 26 — Accès aux réglages',
			'fields' => array(
				array(
					'key' => 'field_orion26_settings_users',
					'label' => 'Utilisateurs autorisés',
					'name' => 'orion26_settings_users',
					'type' => 'checkbox',
					'choices' => array(),
					'layout' => 'vertical',
					'toggle' => 0,
					'wrapper' => array( 'class' => 'orion26-access-list' ),
					'instructions' => 'Ces utilisateurs pourront ouvrir et modifier les réglages Orion 26. Les administrateurs conservent toujours l’accès.',
				),
			),
			'location' => $location,
			'menu_order' => -1,
			'position' => 'side',
			'style' => 'default',
		)
	);
	acf_add_local_field_group(
		array(
			'key' => 'group_orion26_identity',
			'title' => 'Orion 26 — Identité et apparence',
			'fields' => array(
				array( 'key' => 'field_orion26_color', 'label' => 'Couleur principale', 'name' => 'color', 'type' => 'color_picker', 'default_value' => '#ed2438' ),
				array( 'key' => 'field_orion26_color_hover', 'label' => 'Couleur au survol', 'name' => 'color_hover', 'type' => 'color_picker', 'default_value' => '#b61629' ),
				array(
					'key' => 'field_orion26_article_font', 'label' => 'Police du corps des articles', 'name' => 'article_font', 'type' => 'select',
					'choices' => array(
						'source-serif-4' => 'Source Serif 4 — éditoriale et équilibrée',
						'atkinson-hyperlegible-next' => 'Atkinson Hyperlegible Next — lisibilité maximale',
						'lora' => 'Lora — élégante et chaleureuse',
						'merriweather' => 'Merriweather — classique et robuste',
						'literata' => 'Literata — magazine et grands reportages',
						'ibm-plex-sans' => 'IBM Plex Sans — moderne et sans-serif',
					),
					'default_value' => 'source-serif-4', 'ui' => 1, 'allow_null' => 0,
					'instructions' => 'S’applique uniquement au texte des articles. Les polices sont hébergées localement et seule la famille utilisée est téléchargée par le navigateur.',
				),
				array(
					'key' => 'field_orion26_logo_group', 'label' => 'Logo clair', 'name' => 'logo', 'type' => 'group', 'layout' => 'block',
					'sub_fields' => array(
						array( 'key' => 'field_orion26_logo_image', 'label' => 'Image', 'name' => 'img', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
						array( 'key' => 'field_orion26_logo_height', 'label' => 'Hauteur d’affichage', 'name' => 'height', 'type' => 'number', 'append' => 'px', 'default_value' => 37, 'min' => 20, 'max' => 160, 'instructions' => 'La largeur est calculée automatiquement selon les proportions du logo.' ),
					),
				),
				array( 'key' => 'field_orion26_logo_dark', 'label' => 'Logo sombre', 'name' => 'logo_dark', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium', 'instructions' => 'Si vide, le logo de footer historique est utilisé.' ),
				array( 'key' => 'field_orion26_dark_mode', 'label' => 'Afficher le sélecteur clair / sombre', 'name' => 'dark_mode', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1 ),
				array( 'key' => 'field_orion26_menu_uppercase', 'label' => 'Menu en majuscules', 'name' => 'menu_uppercase', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1 ),
				array(
					'key' => 'field_orion26_favicon', 'label' => 'Favicons', 'name' => 'favicon', 'type' => 'group', 'layout' => 'block',
					'sub_fields' => array(
						array( 'key' => 'field_orion26_favicon_16', 'label' => '16 × 16', 'name' => '16', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ),
						array( 'key' => 'field_orion26_favicon_32', 'label' => '32 × 32', 'name' => '32', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ),
						array( 'key' => 'field_orion26_favicon_180', 'label' => 'Apple 180 × 180', 'name' => '180', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ),
						array( 'key' => 'field_orion26_favicon_192', 'label' => 'Android 192 × 192', 'name' => '192', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ),
						array( 'key' => 'field_orion26_favicon_512', 'label' => 'Android 512 × 512', 'name' => '512', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ),
					),
				),
			),
			'location' => $location,
			'menu_order' => 0,
		)
	);
	acf_add_local_field_group(
		array(
			'key' => 'group_orion26_navigation',
			'title' => 'Orion 26 — Navigation',
			'fields' => array(
				array(
					'key' => 'field_orion26_menu', 'label' => 'Éléments du menu', 'name' => 'menu', 'type' => 'repeater', 'layout' => 'row', 'button_label' => 'Ajouter un élément',
					'sub_fields' => array(
						array( 'key' => 'field_orion26_menu_type', 'label' => 'Type', 'name' => 'type', 'type' => 'select', 'choices' => array( 'dsc' => 'Discipline', 'pge' => 'Page ou article', 'lk' => 'Lien externe' ), 'return_format' => 'value' ),
						array( 'key' => 'field_orion26_menu_category', 'label' => 'Discipline', 'name' => 'discipline', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'select', 'return_format' => 'id', 'add_term' => 0 ),
						array( 'key' => 'field_orion26_menu_page', 'label' => 'Page ou article', 'name' => 'link_int', 'type' => 'page_link', 'allow_archives' => 0 ),
						array( 'key' => 'field_orion26_menu_url', 'label' => 'URL externe', 'name' => 'lien_simple_lien', 'type' => 'url' ),
						array( 'key' => 'field_orion26_menu_title', 'label' => 'Libellé', 'name' => 'title', 'type' => 'text', 'required' => 1 ),
					),
				),
			),
			'location' => $location,
			'menu_order' => 1,
		)
	);
	acf_add_local_field_group(
		array(
			'key' => 'group_orion26_homepage',
			'title' => 'Orion 26 — Accueil et rubriques',
			'fields' => array(
				array( 'key' => 'field_orion26_home_disc', 'label' => 'Actualités par bloc mises en avant', 'name' => 'homepage_disc_list', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'multi_select', 'multiple' => 1, 'return_format' => 'id', 'instructions' => 'Blocs éditoriaux prioritaires affichés juste après la Une.' ),
				array( 'key' => 'field_orion26_discipline_hub', 'label' => 'Bloc Disciplines', 'name' => 'homepage_discipline_hub', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'multi_select', 'multiple' => 1, 'return_format' => 'id', 'instructions' => 'Disciplines affichées sous forme de liens directs vers leurs pages catégorie.' ),
				array( 'key' => 'field_orion26_home_rest', 'label' => 'Actualités secondaires', 'name' => 'homepage_restnews', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'multi_select', 'multiple' => 1, 'return_format' => 'id', 'instructions' => 'Rubriques compactes affichées après le bloc Disciplines.' ),
				array( 'key' => 'field_orion26_home_tag', 'label' => 'Tag de la Une', 'name' => 'tag_une_article', 'type' => 'taxonomy', 'taxonomy' => 'post_tag', 'field_type' => 'select', 'return_format' => 'id' ),
				array( 'key' => 'field_orion26_other_disc', 'label' => 'Disciplines de la page Autres', 'name' => 'otcher_disc_list', 'type' => 'taxonomy', 'taxonomy' => 'category', 'field_type' => 'multi_select', 'multiple' => 1, 'return_format' => 'id' ),
			),
			'location' => $location,
			'menu_order' => 2,
		)
	);
	acf_add_local_field_group(
		array(
			'key' => 'group_orion26_services',
			'title' => 'Orion 26 — Footer, réseaux et services',
			'fields' => array(
				array( 'key' => 'field_orion26_logo_footer', 'label' => 'Logo sombre / footer', 'name' => 'logo_footer', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
				array( 'key' => 'field_orion26_logo_footer_height', 'label' => 'Hauteur du logo du footer', 'name' => 'logo_footer_height', 'type' => 'number', 'append' => 'px', 'default_value' => 48, 'min' => 20, 'max' => 160, 'instructions' => 'La largeur est calculée automatiquement selon les proportions du logo.' ),
				array( 'key' => 'field_orion26_facebook', 'label' => 'Facebook', 'name' => 'facebook', 'type' => 'url' ),
				array( 'key' => 'field_orion26_instagram', 'label' => 'Instagram', 'name' => 'insta', 'type' => 'url' ),
				array( 'key' => 'field_orion26_theme_version', 'label' => 'Libellé de version', 'name' => 'orion26_version', 'type' => 'text', 'default_value' => 'Orion 26', 'instructions' => 'Affiché après © année FranceRacing.fr dans le footer. Ce réglage est propre à Orion et ne modifie pas AH19.' ),
				array(
					'key' => 'field_orion26_services_group', 'label' => 'Services et vérifications', 'name' => 'var', 'type' => 'group', 'layout' => 'block',
					'sub_fields' => array(
						array( 'key' => 'field_orion26_google_verify', 'label' => 'Google Site Verification', 'name' => 'google-site-verification', 'type' => 'text', 'instructions' => 'Uniquement la valeur de l’attribut content.' ),
						array( 'key' => 'field_orion26_bing_verify', 'label' => 'Bing Verification', 'name' => 'msvalidate', 'type' => 'text', 'instructions' => 'Uniquement la valeur de l’attribut content.' ),
						array( 'key' => 'field_orion26_google_analytics', 'label' => 'Google Analytics', 'name' => 'google-analytic', 'type' => 'text' ),
						array( 'key' => 'field_orion26_adsense', 'label' => 'Compte AdSense', 'name' => 'adsense', 'type' => 'text', 'instructions' => 'Format : ca-pub-…' ),
						array( 'key' => 'field_orion26_matomo', 'label' => 'Balise Matomo', 'name' => 'matomo', 'type' => 'textarea', 'rows' => 12, 'instructions' => 'Balise reprise de la configuration historique AH19. Lorsque Complianz gère Matomo, il reste chargé par Complianz afin de respecter le consentement RGPD et Orion évite tout double comptage.' ),
					),
				),
			),
			'location' => $location,
			'menu_order' => 3,
		)
	);
}
// L’ancien panneau ACF reste défini uniquement pour la migration ; il n’est plus enregistré.
