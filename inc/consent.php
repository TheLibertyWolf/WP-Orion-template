<?php
/** Gestionnaire de consentement natif Orion. @package Orion26 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function orion26_consent_enabled() {
	return (bool) orion26_setting( 'consent.enabled', false );
}

function orion26_consent_payloads() {
	$analytics = (string) orion26_setting( 'consent.analytics_head', '' );
	$marketing = (string) orion26_setting( 'consent.marketing_head', '' );
	$ga_id = sanitize_text_field( (string) orion26_setting( 'consent.google_analytics_id', '' ) );
	if ( preg_match( '/^(?:G|UA|AW)-[A-Z0-9-]+$/i', $ga_id ) ) {
		$analytics .= sprintf(
			'<script async src="https://www.googletagmanager.com/gtag/js?id=%1$s"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag("js",new Date());gtag("config",%2$s);</script>',
			esc_attr( rawurlencode( $ga_id ) ),
			wp_json_encode( $ga_id )
		);
	}
	$adsense = sanitize_text_field( (string) orion26_setting( 'consent.adsense_client', '' ) );
	if ( preg_match( '/^ca-pub-[0-9]+$/', $adsense ) ) {
		$marketing .= '<script async crossorigin="anonymous" src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr( rawurlencode( $adsense ) ) . '"></script>';
	}
	return array(
		'analytics' => array( 'head' => $analytics, 'footer' => (string) orion26_setting( 'consent.analytics_footer', '' ) ),
		'marketing' => array( 'head' => $marketing, 'footer' => (string) orion26_setting( 'consent.marketing_footer', '' ) ),
		'personalization' => array( 'head' => (string) orion26_setting( 'consent.personalization_head', '' ), 'footer' => (string) orion26_setting( 'consent.personalization_footer', '' ) ),
	);
}

function orion26_consent_config() {
	return array(
		'cookieName' => 'orion26_consent',
		'days'       => max( 1, min( 395, absint( orion26_setting( 'consent.cookie_days', 180 ) ) ) ),
		'version'    => sanitize_text_field( (string) orion26_setting( 'consent.consent_version', '1' ) ),
		'respectGpc' => (bool) orion26_setting( 'consent.respect_gpc', true ),
		'categories' => array(
			'analytics'       => (bool) orion26_setting( 'consent.analytics_enabled', true ),
			'marketing'       => (bool) orion26_setting( 'consent.marketing_enabled', true ),
			'personalization' => (bool) orion26_setting( 'consent.personalization_enabled', false ),
		),
		'payloads' => orion26_consent_payloads(),
	);
}

function orion26_consent_assets() {
	if ( ! orion26_consent_enabled() ) {
		return;
	}
	$css = ORION26_DIR . '/assets/css/consent.css';
	$js  = ORION26_DIR . '/assets/js/consent.js';
	wp_enqueue_style( 'orion26-consent', ORION26_URI . '/assets/css/consent.css', array(), file_exists( $css ) ? filemtime( $css ) : ORION26_VERSION );
	wp_enqueue_script( 'orion26-consent', ORION26_URI . '/assets/js/consent.js', array(), file_exists( $js ) ? filemtime( $js ) : ORION26_VERSION, false );
	wp_add_inline_script( 'orion26-consent', 'window.OrionConsentConfig=' . wp_json_encode( orion26_consent_config(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'orion26_consent_assets', 25 );

function orion26_consent_banner() {
	if ( ! orion26_consent_enabled() ) {
		return;
	}
	$layout   = sanitize_html_class( (string) orion26_setting( 'consent.layout', 'banner' ) );
	$position = sanitize_html_class( (string) orion26_setting( 'consent.position', 'bottom' ) );
	$privacy  = (string) orion26_setting( 'consent.privacy_url', '/politique-de-confidentialite/' );
	if ( str_starts_with( $privacy, '/' ) ) {
		$privacy = home_url( $privacy );
	}
	$style = sprintf(
		'--orion-consent-accent:%s;--orion-consent-bg:%s;--orion-consent-text:%s',
		esc_attr( sanitize_hex_color( (string) orion26_setting( 'consent.accent', '#ed2438' ) ) ?: '#ed2438' ),
		esc_attr( sanitize_hex_color( (string) orion26_setting( 'consent.background', '#ffffff' ) ) ?: '#ffffff' ),
		esc_attr( sanitize_hex_color( (string) orion26_setting( 'consent.text', '#12151b' ) ) ?: '#12151b' )
	);
	?>
	<div class="orion-consent orion-consent--<?php echo esc_attr( $layout . ' orion-consent--' . $position ); ?>" data-orion-consent hidden style="<?php echo esc_attr( $style ); ?>">
		<div class="orion-consent__backdrop" data-consent-close></div>
		<section class="orion-consent__panel" role="dialog" aria-modal="true" aria-labelledby="orion-consent-title" aria-describedby="orion-consent-message">
			<div class="orion-consent__summary">
				<div><p class="orion-consent__kicker"><?php esc_html_e( 'Confidentialité', 'orion26' ); ?></p><h2 id="orion-consent-title"><?php echo esc_html( orion26_setting( 'consent.title' ) ); ?></h2></div>
				<p id="orion-consent-message"><?php echo esc_html( orion26_setting( 'consent.message' ) ); ?> <a href="<?php echo esc_url( $privacy ); ?>"><?php echo esc_html( orion26_setting( 'consent.privacy_label' ) ); ?></a></p>
			</div>
			<div class="orion-consent__details" data-consent-details hidden>
				<div class="orion-consent__category"><div><b><?php esc_html_e( 'Nécessaires', 'orion26' ); ?></b><small><?php esc_html_e( 'Sécurité, préférences essentielles et fonctionnement du site.', 'orion26' ); ?></small></div><span><?php esc_html_e( 'Toujours actifs', 'orion26' ); ?></span></div>
				<?php if ( orion26_setting( 'consent.analytics_enabled', true ) ) : ?><label class="orion-consent__category"><span><b><?php esc_html_e( 'Mesure d’audience', 'orion26' ); ?></b><small><?php esc_html_e( 'Nous aide à comprendre l’utilisation du site.', 'orion26' ); ?></small></span><input type="checkbox" data-consent-category="analytics"><i aria-hidden="true"></i></label><?php endif; ?>
				<?php if ( orion26_setting( 'consent.marketing_enabled', true ) ) : ?><label class="orion-consent__category"><span><b><?php esc_html_e( 'Publicité', 'orion26' ); ?></b><small><?php esc_html_e( 'Permet l’affichage et la mesure des contenus publicitaires.', 'orion26' ); ?></small></span><input type="checkbox" data-consent-category="marketing"><i aria-hidden="true"></i></label><?php endif; ?>
				<?php if ( orion26_setting( 'consent.personalization_enabled', false ) ) : ?><label class="orion-consent__category"><span><b><?php esc_html_e( 'Personnalisation', 'orion26' ); ?></b><small><?php esc_html_e( 'Adapte certains contenus à vos préférences.', 'orion26' ); ?></small></span><input type="checkbox" data-consent-category="personalization"><i aria-hidden="true"></i></label><?php endif; ?>
			</div>
			<div class="orion-consent__actions">
				<button type="button" class="orion-consent__button orion-consent__button--primary" data-consent-accept><?php echo esc_html( orion26_setting( 'consent.accept_label' ) ); ?></button>
				<button type="button" class="orion-consent__button" data-consent-reject><?php echo esc_html( orion26_setting( 'consent.reject_label' ) ); ?></button>
				<button type="button" class="orion-consent__button" data-consent-customize><?php echo esc_html( orion26_setting( 'consent.customize_label' ) ); ?></button>
				<button type="button" class="orion-consent__button orion-consent__button--primary" data-consent-save hidden><?php echo esc_html( orion26_setting( 'consent.save_label' ) ); ?></button>
			</div>
		</section>
	</div>
	<?php
}
add_action( 'wp_footer', 'orion26_consent_banner', 90 );

function orion26_consent_manage_button() {
	if ( orion26_consent_enabled() ) {
		echo '<button type="button" class="orion-consent-manage" data-consent-manage>' . esc_html__( 'Gérer mes cookies', 'orion26' ) . '</button>';
	}
}

