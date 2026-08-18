<?php
/**
 * Noyau de configuration natif d’Orion.
 *
 * @package Orion26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ORION26_SETTINGS_OPTION = 'orion26_settings';
const ORION26_SETTINGS_VERSION = 1;

/** Valeurs neutres permettant de distribuer Orion sur un autre site. */
function orion26_settings_defaults() {
	return array(
		'_version' => ORION26_SETTINGS_VERSION,
		'identity' => array(
			'preset'            => 'minimal',
			'logo_light_id'     => 0,
			'logo_dark_id'      => 0,
			'logo_footer_id'    => 0,
			'logo_height'       => 40,
			'footer_logo_height'=> 48,
			'dark_mode'         => 1,
			'default_scheme'    => 'auto',
			'container_width'   => 1240,
			'access_users'      => array(),
		),
		'design' => array(
			'accent'              => '#ed2438',
			'accent_hover'        => '#b61629',
			'secondary'           => '#b68b3d',
			'background'          => '#f7f5ef',
			'surface'             => '#ffffff',
			'surface_alt'         => '#eeece6',
			'text'                => '#12151b',
			'muted'               => '#626873',
			'line'                => '#d9d6cf',
			'dark_background'     => '#0b0d12',
			'dark_surface'        => '#11141b',
			'dark_surface_alt'    => '#171b24',
			'dark_text'           => '#f1eee7',
			'dark_muted'          => '#a1a7b3',
			'dark_line'           => '#2b303b',
			'body_font'           => 'system',
			'article_font'        => 'source-serif-4',
			'display_font'        => 'condensed',
			'base_size'           => 16,
			'article_size'        => 18,
			'article_line_height' => 1.8,
			'title_case'          => 'uppercase',
			'radius'              => 3,
			'shadow_strength'     => 8,
			'blockquote_accent'   => '#b68b3d',
			'blockquote_style'    => 'italic',
			'code_background'     => '#171b24',
			'code_text'           => '#f1eee7',
		),
		'navigation' => array(
			'primary_menu'       => 0,
			'uppercase'          => 1,
			'sticky'             => 0,
			'show_search'        => 1,
			'show_theme_toggle'  => 1,
			'append_more'        => 1,
			'more_label'         => 'Plus d’actualités',
			'more_url'           => '/others/',
			'mobile_breakpoint'  => 900,
		),
		'homepage' => array(
			'featured_tag'       => 0,
			'featured_categories'=> array(),
			'hub_categories'     => array(),
			'secondary_categories'=> array(),
			'other_categories'   => array(),
			'latest_count'       => 8,
			'featured_posts'     => 5,
			'category_posts'     => 5,
			'show_gaming'        => 1,
			'gaming_category'    => 0,
			'show_press'         => 1,
			'show_history'       => 1,
		),
		'footer' => array(
			'show_description'   => 1,
			'show_categories'    => 1,
			'category_count'     => 14,
			'columns'            => 4,
			'footer_menu'        => 0,
			'copyright'          => '© {year} {site_name}',
			'version_label'      => '',
			'show_login'         => 1,
		),
		'social' => array(
			'facebook'  => '',
			'instagram' => '',
			'x'         => '',
			'youtube'   => '',
			'linkedin'  => '',
			'tiktok'    => '',
			'twitch'    => '',
			'rss'       => 1,
			'new_tab'   => 1,
		),
		'consent' => array(
			'enabled'                => 0,
			'layout'                 => 'banner',
			'position'               => 'bottom',
			'title'                  => 'Vos choix de confidentialité',
			'message'                => 'Nous utilisons des technologies nécessaires au fonctionnement du site et, avec votre accord, des outils de mesure d’audience et de personnalisation.',
			'accept_label'           => 'Tout accepter',
			'reject_label'           => 'Refuser les options',
			'customize_label'        => 'Personnaliser',
			'save_label'             => 'Enregistrer mes choix',
			'privacy_url'            => '/politique-de-confidentialite/',
			'privacy_label'          => 'Politique de confidentialité',
			'cookie_days'            => 180,
			'consent_version'        => '1',
			'respect_gpc'            => 1,
			'accent'                 => '#ed2438',
			'background'             => '#ffffff',
			'text'                   => '#12151b',
			'analytics_enabled'      => 1,
			'marketing_enabled'      => 1,
			'personalization_enabled'=> 0,
			'analytics_head'         => '',
			'analytics_footer'       => '',
			'marketing_head'         => '',
			'marketing_footer'       => '',
			'personalization_head'   => '',
			'personalization_footer' => '',
			'google_analytics_id'     => '',
			'adsense_client'          => '',
			'google_verification'     => '',
			'bing_verification'       => '',
		),
	);
}

/** Schéma unique utilisé par l’interface et la validation serveur. */
function orion26_settings_schema() {
	$font_choices = array(
		'source-serif-4'             => 'Source Serif 4 — éditoriale et équilibrée',
		'atkinson-hyperlegible-next' => 'Atkinson Hyperlegible Next — lisibilité maximale',
		'lora'                       => 'Lora — élégante et chaleureuse',
		'merriweather'               => 'Merriweather — classique et robuste',
		'literata'                   => 'Literata — magazine et grands reportages',
		'ibm-plex-sans'              => 'IBM Plex Sans — moderne et sans-serif',
	);
	$color = array( 'type' => 'color' );

	return array(
		'identity' => array(
			'label' => 'Identité et apparence', 'icon' => 'dashicons-admin-customizer',
			'description' => 'Identité visuelle, preset global, logos et dimensions générales.',
			'fields' => array(
				'preset' => array( 'label' => 'Style visuel', 'type' => 'preset', 'choices' => array( 'minimal' => 'Orion Minimal', 'expressive' => 'Orion Expressif', 'editorial' => 'Orion Éditorial', 'contrast' => 'Orion Contraste' ) ),
				'logo_light_id' => array( 'label' => 'Logo sur fond clair', 'type' => 'media' ),
				'logo_dark_id' => array( 'label' => 'Logo sur fond sombre', 'type' => 'media' ),
				'logo_footer_id' => array( 'label' => 'Logo du footer', 'type' => 'media' ),
				'logo_height' => array( 'label' => 'Hauteur du logo du header', 'type' => 'number', 'min' => 20, 'max' => 180, 'suffix' => 'px' ),
				'footer_logo_height' => array( 'label' => 'Hauteur du logo du footer', 'type' => 'number', 'min' => 20, 'max' => 180, 'suffix' => 'px' ),
				'dark_mode' => array( 'label' => 'Activer les variantes claire et sombre', 'type' => 'checkbox' ),
				'default_scheme' => array( 'label' => 'Mode initial', 'type' => 'select', 'choices' => array( 'auto' => 'Préférence du système', 'light' => 'Clair', 'dark' => 'Sombre' ) ),
				'container_width' => array( 'label' => 'Largeur maximale du site', 'type' => 'number', 'min' => 960, 'max' => 1800, 'suffix' => 'px' ),
				'access_users' => array( 'label' => 'Utilisateurs autorisés à régler Orion', 'type' => 'users' ),
			),
		),
		'design' => array(
			'label' => 'Couleurs / typographies', 'icon' => 'dashicons-art', 'preview' => true,
			'description' => 'Réglages globaux des textes, titres, citations, blocs de code et surfaces.',
			'fields' => array(
				'accent' => $color + array( 'label' => 'Couleur principale', 'preview' => '--preview-accent' ),
				'accent_hover' => $color + array( 'label' => 'Couleur au survol' ),
				'secondary' => $color + array( 'label' => 'Couleur secondaire', 'preview' => '--preview-secondary' ),
				'background' => $color + array( 'label' => 'Fond clair', 'preview' => '--preview-background' ),
				'surface' => $color + array( 'label' => 'Surface claire', 'preview' => '--preview-surface' ),
				'surface_alt' => $color + array( 'label' => 'Surface secondaire claire' ),
				'text' => $color + array( 'label' => 'Texte clair', 'preview' => '--preview-text' ),
				'muted' => $color + array( 'label' => 'Texte secondaire clair' ),
				'line' => $color + array( 'label' => 'Bordures claires' ),
				'dark_background' => $color + array( 'label' => 'Fond sombre' ),
				'dark_surface' => $color + array( 'label' => 'Surface sombre' ),
				'dark_surface_alt' => $color + array( 'label' => 'Surface secondaire sombre' ),
				'dark_text' => $color + array( 'label' => 'Texte sombre' ),
				'dark_muted' => $color + array( 'label' => 'Texte secondaire sombre' ),
				'dark_line' => $color + array( 'label' => 'Bordures sombres' ),
				'body_font' => array( 'label' => 'Police générale', 'type' => 'select', 'choices' => array( 'system' => 'Police système', 'ibm-plex-sans' => 'IBM Plex Sans', 'atkinson-hyperlegible-next' => 'Atkinson Hyperlegible Next' ), 'preview' => 'body-font' ),
				'article_font' => array( 'label' => 'Police du corps des articles', 'type' => 'select', 'choices' => $font_choices, 'preview' => 'article-font' ),
				'display_font' => array( 'label' => 'Police des titres', 'type' => 'select', 'choices' => array( 'condensed' => 'Condensée', 'system' => 'Système', 'ibm-plex-sans' => 'IBM Plex Sans', 'source-serif-4' => 'Source Serif 4' ), 'preview' => 'display-font' ),
				'base_size' => array( 'label' => 'Taille générale', 'type' => 'number', 'min' => 14, 'max' => 22, 'suffix' => 'px', 'preview' => 'base-size' ),
				'article_size' => array( 'label' => 'Taille du texte des articles', 'type' => 'number', 'min' => 15, 'max' => 26, 'suffix' => 'px', 'preview' => 'article-size' ),
				'article_line_height' => array( 'label' => 'Interligne des articles', 'type' => 'number', 'min' => 1.3, 'max' => 2.2, 'step' => 0.05, 'preview' => 'line-height' ),
				'title_case' => array( 'label' => 'Casse des titres', 'type' => 'select', 'choices' => array( 'uppercase' => 'Majuscules', 'none' => 'Normale' ), 'preview' => 'title-case' ),
				'radius' => array( 'label' => 'Arrondi des composants', 'type' => 'number', 'min' => 0, 'max' => 30, 'suffix' => 'px', 'preview' => 'radius' ),
				'shadow_strength' => array( 'label' => 'Intensité des ombres', 'type' => 'number', 'min' => 0, 'max' => 30, 'suffix' => '%' ),
				'blockquote_accent' => $color + array( 'label' => 'Accent des citations', 'preview' => '--preview-quote' ),
				'blockquote_style' => array( 'label' => 'Style des citations', 'type' => 'select', 'choices' => array( 'normal' => 'Normal', 'italic' => 'Italique' ), 'preview' => 'quote-style' ),
				'code_background' => $color + array( 'label' => 'Fond des blocs de code' ),
				'code_text' => $color + array( 'label' => 'Texte des blocs de code' ),
			),
		),
		'navigation' => array(
			'label' => 'Navigation', 'icon' => 'dashicons-menu',
			'description' => 'Sélection du menu WordPress et comportement du header.',
			'fields' => array(
				'primary_menu' => array( 'label' => 'Menu principal', 'type' => 'menus' ),
				'uppercase' => array( 'label' => 'Afficher le menu en majuscules', 'type' => 'checkbox' ),
				'sticky' => array( 'label' => 'Header fixe au défilement', 'type' => 'checkbox' ),
				'show_search' => array( 'label' => 'Afficher la recherche', 'type' => 'checkbox' ),
				'show_theme_toggle' => array( 'label' => 'Afficher le bouton clair/sombre', 'type' => 'checkbox' ),
				'append_more' => array( 'label' => 'Ajouter un lien final automatiquement', 'type' => 'checkbox' ),
				'more_label' => array( 'label' => 'Libellé du lien final', 'type' => 'text' ),
				'more_url' => array( 'label' => 'URL du lien final', 'type' => 'url_or_path' ),
				'mobile_breakpoint' => array( 'label' => 'Point de bascule mobile', 'type' => 'number', 'min' => 640, 'max' => 1280, 'suffix' => 'px' ),
			),
		),
		'homepage' => array(
			'label' => 'Homepage et rubriques', 'icon' => 'dashicons-admin-home',
			'description' => 'Composition éditoriale de l’accueil et de la page regroupant les autres rubriques.',
			'fields' => array(
				'featured_tag' => array( 'label' => 'Tag de la Une', 'type' => 'tags' ),
				'featured_categories' => array( 'label' => 'Rubriques mises en avant', 'type' => 'categories' ),
				'hub_categories' => array( 'label' => 'Bloc de liens vers les rubriques', 'type' => 'categories' ),
				'secondary_categories' => array( 'label' => 'Rubriques secondaires', 'type' => 'categories' ),
				'other_categories' => array( 'label' => 'Rubriques de la page « autres »', 'type' => 'categories' ),
				'latest_count' => array( 'label' => 'Nombre de dernières actualités', 'type' => 'number', 'min' => 4, 'max' => 24 ),
				'featured_posts' => array( 'label' => 'Nombre d’articles dans la Une', 'type' => 'number', 'min' => 3, 'max' => 8 ),
				'category_posts' => array( 'label' => 'Articles par bloc de rubrique', 'type' => 'number', 'min' => 3, 'max' => 12 ),
				'show_gaming' => array( 'label' => 'Afficher le bloc Gaming', 'type' => 'checkbox' ),
				'gaming_category' => array( 'label' => 'Rubrique du bloc Gaming', 'type' => 'category' ),
				'show_press' => array( 'label' => 'Afficher le bloc Communiqués', 'type' => 'checkbox' ),
				'show_history' => array( 'label' => 'Afficher le bloc Historique', 'type' => 'checkbox' ),
			),
		),
		'footer' => array(
			'label' => 'Footer', 'icon' => 'dashicons-editor-insertmore',
			'description' => 'Contenu, colonnes et mentions du pied de page.',
			'fields' => array(
				'show_description' => array( 'label' => 'Afficher la description du site', 'type' => 'checkbox' ),
				'show_categories' => array( 'label' => 'Afficher les rubriques', 'type' => 'checkbox' ),
				'category_count' => array( 'label' => 'Nombre de rubriques', 'type' => 'number', 'min' => 0, 'max' => 40 ),
				'columns' => array( 'label' => 'Nombre de colonnes', 'type' => 'number', 'min' => 1, 'max' => 6 ),
				'footer_menu' => array( 'label' => 'Menu du footer', 'type' => 'menus' ),
				'copyright' => array( 'label' => 'Copyright', 'type' => 'text', 'description' => 'Variables : {year}, {site_name}.' ),
				'version_label' => array( 'label' => 'Libellé de version', 'type' => 'text' ),
				'show_login' => array( 'label' => 'Afficher le lien de connexion', 'type' => 'checkbox' ),
			),
		),
		'social' => array(
			'label' => 'Réseaux sociaux', 'icon' => 'dashicons-share',
			'description' => 'Profils publics et comportement de leurs liens.',
			'fields' => array(
				'facebook' => array( 'label' => 'Facebook', 'type' => 'url' ),
				'instagram' => array( 'label' => 'Instagram', 'type' => 'url' ),
				'x' => array( 'label' => 'X / Twitter', 'type' => 'url' ),
				'youtube' => array( 'label' => 'YouTube', 'type' => 'url' ),
				'linkedin' => array( 'label' => 'LinkedIn', 'type' => 'url' ),
				'tiktok' => array( 'label' => 'TikTok', 'type' => 'url' ),
				'twitch' => array( 'label' => 'Twitch', 'type' => 'url' ),
				'rss' => array( 'label' => 'Afficher le flux RSS', 'type' => 'checkbox' ),
				'new_tab' => array( 'label' => 'Ouvrir les réseaux dans un nouvel onglet', 'type' => 'checkbox' ),
			),
		),
		'consent' => array(
			'label' => 'Consentement', 'icon' => 'dashicons-privacy',
			'description' => 'Bandeau de consentement et chargement conditionnel des services non essentiels.',
			'fields' => array(
				'enabled' => array( 'label' => 'Activer le gestionnaire Orion', 'type' => 'checkbox' ),
				'layout' => array( 'label' => 'Présentation', 'type' => 'select', 'choices' => array( 'banner' => 'Bandeau', 'box' => 'Encart', 'modal' => 'Fenêtre modale' ) ),
				'position' => array( 'label' => 'Position', 'type' => 'select', 'choices' => array( 'bottom' => 'Bas', 'top' => 'Haut', 'bottom-left' => 'Bas gauche', 'bottom-right' => 'Bas droite', 'center' => 'Centre' ) ),
				'title' => array( 'label' => 'Titre', 'type' => 'text' ),
				'message' => array( 'label' => 'Message', 'type' => 'textarea' ),
				'accept_label' => array( 'label' => 'Bouton accepter', 'type' => 'text' ),
				'reject_label' => array( 'label' => 'Bouton refuser', 'type' => 'text' ),
				'customize_label' => array( 'label' => 'Bouton personnaliser', 'type' => 'text' ),
				'save_label' => array( 'label' => 'Bouton enregistrer', 'type' => 'text' ),
				'privacy_url' => array( 'label' => 'URL de confidentialité', 'type' => 'url_or_path' ),
				'privacy_label' => array( 'label' => 'Libellé du lien de confidentialité', 'type' => 'text' ),
				'cookie_days' => array( 'label' => 'Durée du choix', 'type' => 'number', 'min' => 1, 'max' => 395, 'suffix' => 'jours' ),
				'consent_version' => array( 'label' => 'Version du consentement', 'type' => 'text', 'description' => 'Changez-la pour redemander le choix aux visiteurs.' ),
				'respect_gpc' => array( 'label' => 'Respecter Global Privacy Control', 'type' => 'checkbox' ),
				'accent' => $color + array( 'label' => 'Couleur principale du bandeau' ),
				'background' => $color + array( 'label' => 'Fond du bandeau' ),
				'text' => $color + array( 'label' => 'Texte du bandeau' ),
				'analytics_enabled' => array( 'label' => 'Proposer la catégorie Mesure d’audience', 'type' => 'checkbox' ),
				'marketing_enabled' => array( 'label' => 'Proposer la catégorie Publicité', 'type' => 'checkbox' ),
				'personalization_enabled' => array( 'label' => 'Proposer la catégorie Personnalisation', 'type' => 'checkbox' ),
				'google_analytics_id' => array( 'label' => 'Identifiant Google Analytics', 'type' => 'text' ),
				'adsense_client' => array( 'label' => 'Compte Google AdSense', 'type' => 'text' ),
				'analytics_head' => array( 'label' => 'Scripts de mesure — head', 'type' => 'code', 'capability' => 'unfiltered_html' ),
				'analytics_footer' => array( 'label' => 'Scripts de mesure — footer', 'type' => 'code', 'capability' => 'unfiltered_html' ),
				'marketing_head' => array( 'label' => 'Scripts publicitaires — head', 'type' => 'code', 'capability' => 'unfiltered_html' ),
				'marketing_footer' => array( 'label' => 'Scripts publicitaires — footer', 'type' => 'code', 'capability' => 'unfiltered_html' ),
				'personalization_head' => array( 'label' => 'Scripts de personnalisation — head', 'type' => 'code', 'capability' => 'unfiltered_html' ),
				'personalization_footer' => array( 'label' => 'Scripts de personnalisation — footer', 'type' => 'code', 'capability' => 'unfiltered_html' ),
				'google_verification' => array( 'label' => 'Google Site Verification', 'type' => 'text' ),
				'bing_verification' => array( 'label' => 'Bing Verification', 'type' => 'text' ),
			),
		),
	);
}

function orion26_array_merge_settings( $defaults, $values ) {
	foreach ( (array) $values as $key => $value ) {
		if ( isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) && is_array( $value ) && ! array_is_list( $defaults[ $key ] ) ) {
			$defaults[ $key ] = orion26_array_merge_settings( $defaults[ $key ], $value );
		} else {
			$defaults[ $key ] = $value;
		}
	}
	return $defaults;
}

function orion26_get_settings() {
	$stored = get_option( ORION26_SETTINGS_OPTION, array() );
	return orion26_array_merge_settings( orion26_settings_defaults(), is_array( $stored ) ? $stored : array() );
}

function orion26_setting( $path, $fallback = null ) {
	$value = orion26_get_settings();
	foreach ( explode( '.', (string) $path ) as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $fallback;
		}
		$value = $value[ $segment ];
	}
	return $value;
}

function orion26_legacy_image_id( $value ) {
	if ( is_array( $value ) ) {
		return absint( $value['ID'] ?? $value['id'] ?? 0 );
	}
	return is_numeric( $value ) ? absint( $value ) : 0;
}

/** Convertit une seule fois les options historiques ACF vers le format natif. */
function orion26_migrate_legacy_settings() {
	if ( get_option( ORION26_SETTINGS_OPTION, null ) !== null ) {
		return;
	}

	$settings = orion26_settings_defaults();
	$legacy = static function ( $name, $fallback = '' ) {
		$value = get_option( 'options_' . $name, $fallback );
		return '' === $value ? $fallback : $value;
	};

	$settings['identity']['preset']             = 'orion-26-plus' === sanitize_key( (string) get_user_meta( get_current_user_id(), 'wp_simple_template_switch_theme', true ) ) ? 'expressive' : 'minimal';
	$settings['identity']['logo_light_id']       = orion26_legacy_image_id( $legacy( 'logo_img' ) );
	$settings['identity']['logo_dark_id']        = orion26_legacy_image_id( $legacy( 'logo_dark' ) );
	$settings['identity']['logo_footer_id']      = orion26_legacy_image_id( $legacy( 'logo_footer' ) );
	$settings['identity']['logo_height']         = absint( $legacy( 'logo_height', 40 ) );
	$settings['identity']['footer_logo_height']  = absint( $legacy( 'logo_footer_height', 48 ) ) ?: 48;
	$settings['identity']['dark_mode']           = (int) (bool) $legacy( 'dark_mode', 1 );
	$settings['identity']['access_users']        = array_values( array_filter( array_map( 'absint', (array) $legacy( 'orion26_settings_users', array() ) ) ) );
	$settings['design']['accent']                = sanitize_hex_color( (string) $legacy( 'color' ) ) ?: $settings['design']['accent'];
	$settings['design']['accent_hover']          = sanitize_hex_color( (string) $legacy( 'color_hover' ) ) ?: $settings['design']['accent_hover'];
	$settings['design']['article_font']          = sanitize_key( (string) $legacy( 'article_font', 'source-serif-4' ) );
	$settings['navigation']['uppercase']         = (int) (bool) $legacy( 'menu_uppercase', 1 );
	$settings['homepage']['featured_tag']        = absint( $legacy( 'tag_une_article' ) );
	$settings['homepage']['featured_categories']= array_values( array_filter( array_map( 'absint', (array) $legacy( 'homepage_disc_list', array() ) ) ) );
	$settings['homepage']['hub_categories']      = array_values( array_filter( array_map( 'absint', (array) $legacy( 'homepage_discipline_hub', array() ) ) ) );
	$settings['homepage']['secondary_categories']= array_values( array_filter( array_map( 'absint', (array) $legacy( 'homepage_restnews', array() ) ) ) );
	$settings['homepage']['other_categories']    = array_values( array_filter( array_map( 'absint', (array) $legacy( 'otcher_disc_list', array() ) ) ) );
	$gaming = get_category_by_slug( 'gaming' );
	$settings['homepage']['gaming_category']     = $gaming instanceof WP_Term ? (int) $gaming->term_id : 0;
	$settings['footer']['version_label']         = sanitize_text_field( (string) $legacy( 'orion26_version' ) );
	$settings['social']['facebook']              = esc_url_raw( (string) $legacy( 'facebook' ) );
	$settings['social']['instagram']             = esc_url_raw( (string) $legacy( 'insta' ) );
	$settings['consent']['google_analytics_id']  = sanitize_text_field( (string) $legacy( 'var_google-analytic' ) );
	$settings['consent']['adsense_client']       = sanitize_text_field( (string) $legacy( 'var_adsense' ) );
	$settings['consent']['google_verification']  = sanitize_text_field( (string) $legacy( 'var_google-site-verification' ) );
	$settings['consent']['bing_verification']    = sanitize_text_field( (string) $legacy( 'var_msvalidate' ) );
	$settings['consent']['analytics_head']       = (string) $legacy( 'var_matomo' );

	update_option( ORION26_SETTINGS_OPTION, $settings, false );
	update_option( 'orion26_settings_migrated_at', time(), false );
}
add_action( 'admin_init', 'orion26_migrate_legacy_settings', 2 );
