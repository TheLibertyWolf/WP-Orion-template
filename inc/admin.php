<?php
/** Panneau d’administration natif du thème Orion. @package Orion26 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function orion26_admin_sections() {
	return array_keys( orion26_settings_schema() );
}

function orion26_admin_capability() {
	return 'manage_orion26_settings';
}

function orion26_register_admin_menu() {
	$capability = orion26_admin_capability();
	$schema     = orion26_settings_schema();
	add_menu_page( 'Orion Theme', 'Orion', $capability, 'orion26-identity', 'orion26_render_settings_page', 'dashicons-star-filled', 3 );
	foreach ( $schema as $section => $config ) {
		add_submenu_page(
			'orion26-identity',
			'Orion — ' . $config['label'],
			$config['label'],
			$capability,
			'orion26-' . $section,
			'orion26_render_settings_page'
		);
	}
}
add_action( 'admin_menu', 'orion26_register_admin_menu', 20 );

function orion26_current_admin_section() {
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$section = str_starts_with( $page, 'orion26-' ) ? substr( $page, 8 ) : 'identity';
	return in_array( $section, orion26_admin_sections(), true ) ? $section : 'identity';
}

function orion26_admin_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'orion26-' ) ) {
		return;
	}
	wp_enqueue_media();
	$css = ORION26_DIR . '/assets/admin/admin.css';
	$js  = ORION26_DIR . '/assets/admin/admin.js';
	wp_enqueue_style( 'orion26-admin', ORION26_URI . '/assets/admin/admin.css', array(), file_exists( $css ) ? filemtime( $css ) : ORION26_VERSION );
	wp_enqueue_script( 'orion26-admin', ORION26_URI . '/assets/admin/admin.js', array(), file_exists( $js ) ? filemtime( $js ) : ORION26_VERSION, true );
	wp_localize_script(
		'orion26-admin',
		'OrionAdmin',
		array(
			'mediaTitle'  => __( 'Choisir une image', 'orion26' ),
			'mediaButton' => __( 'Utiliser cette image', 'orion26' ),
			'remove'      => __( 'Retirer', 'orion26' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'orion26_admin_assets' );

function orion26_field_name( $section, $key ) {
	return 'orion26[' . $section . '][' . $key . ']';
}

function orion26_field_id( $section, $key ) {
	return 'orion26-' . sanitize_html_class( $section . '-' . $key );
}

function orion26_render_media_field( $section, $key, $field, $value ) {
	$id       = orion26_field_id( $section, $key );
	$image_id = absint( $value );
	$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	?>
	<div class="orion-media-control" data-orion-media>
		<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( orion26_field_name( $section, $key ) ); ?>" value="<?php echo esc_attr( $image_id ); ?>">
		<div class="orion-media-preview<?php echo $image ? ' has-image' : ''; ?>" data-orion-media-preview><?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt=""><?php else : ?><span class="dashicons dashicons-format-image" aria-hidden="true"></span><?php endif; ?></div>
		<p><button type="button" class="button" data-orion-media-select><?php esc_html_e( 'Choisir', 'orion26' ); ?></button> <button type="button" class="button-link-delete" data-orion-media-remove<?php echo $image ? '' : ' hidden'; ?>><?php esc_html_e( 'Retirer', 'orion26' ); ?></button></p>
	</div>
	<?php
}

function orion26_dynamic_choices( $type ) {
	$choices = array();
	if ( 'menus' === $type ) {
		foreach ( wp_get_nav_menus() as $menu ) {
			$choices[ $menu->term_id ] = $menu->name;
		}
	} elseif ( in_array( $type, array( 'category', 'categories' ), true ) ) {
		foreach ( get_categories( array( 'hide_empty' => false, 'orderby' => 'name' ) ) as $term ) {
			$choices[ $term->term_id ] = $term->name;
		}
	} elseif ( 'tags' === $type ) {
		foreach ( get_tags( array( 'hide_empty' => false, 'orderby' => 'name', 'number' => 500 ) ) as $term ) {
			$choices[ $term->term_id ] = $term->name;
		}
	} elseif ( 'users' === $type ) {
		foreach ( get_users( array( 'orderby' => 'display_name', 'fields' => array( 'ID', 'display_name', 'user_email' ) ) ) as $user ) {
			$choices[ $user->ID ] = sprintf( '%s — %s', $user->display_name, $user->user_email );
		}
	}
	return $choices;
}

function orion26_render_field( $section, $key, $field, $value ) {
	$type = $field['type'] ?? 'text';
	$id   = orion26_field_id( $section, $key );
	$name = orion26_field_name( $section, $key );
	$preview = isset( $field['preview'] ) ? ' data-orion-preview-control="' . esc_attr( $field['preview'] ) . '"' : '';
	if ( 'media' === $type ) {
		orion26_render_media_field( $section, $key, $field, $value );
		return;
	}
	if ( 'checkbox' === $type ) {
		printf( '<label class="orion-switch"><input id="%1$s" type="checkbox" name="%2$s" value="1"%3$s><span aria-hidden="true"></span><b>%4$s</b></label>', esc_attr( $id ), esc_attr( $name ), checked( (bool) $value, true, false ), esc_html( $field['label'] ) );
		return;
	}
	if ( 'preset' === $type ) {
		echo '<div class="orion-preset-grid">';
		foreach ( $field['choices'] as $choice_value => $label ) {
			printf( '<label class="orion-preset-card orion-preset-card--%1$s"><input type="radio" name="%2$s" value="%1$s"%3$s><span class="orion-preset-card__visual"><i></i><i></i><i></i></span><b>%4$s</b></label>', esc_attr( $choice_value ), esc_attr( $name ), checked( (string) $value, (string) $choice_value, false ), esc_html( $label ) );
		}
		echo '</div>';
		return;
	}
	if ( in_array( $type, array( 'select', 'menus', 'tags', 'category', 'categories', 'users' ), true ) ) {
		$choices  = isset( $field['choices'] ) ? $field['choices'] : orion26_dynamic_choices( $type );
		$multiple = in_array( $type, array( 'categories', 'users' ), true );
		printf( '<select id="%1$s" name="%2$s%3$s"%4$s%5$s>', esc_attr( $id ), esc_attr( $name ), $multiple ? '[]' : '', $multiple ? ' multiple size="9"' : '', $preview );
		if ( ! $multiple ) {
			echo '<option value="">' . esc_html__( '— Sélectionner —', 'orion26' ) . '</option>';
		}
		foreach ( $choices as $choice_value => $label ) {
			$is_selected = $multiple ? in_array( (string) $choice_value, array_map( 'strval', (array) $value ), true ) : (string) $value === (string) $choice_value;
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $choice_value ), selected( $is_selected, true, false ), esc_html( $label ) );
		}
		echo '</select>';
		if ( 'menus' === $type ) {
			echo '<p><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Créer ou modifier les menus WordPress', 'orion26' ) . '</a></p>';
		}
		return;
	}
	if ( in_array( $type, array( 'textarea', 'code' ), true ) ) {
		printf( '<textarea id="%1$s" name="%2$s" rows="%3$d" class="%4$s"%5$s>%6$s</textarea>', esc_attr( $id ), esc_attr( $name ), 'code' === $type ? 10 : 5, 'code' === $type ? 'large-text code' : 'large-text', $preview, esc_textarea( (string) $value ) );
		return;
	}
	$input_type = in_array( $type, array( 'color', 'number', 'url' ), true ) ? $type : 'text';
	$attrs = '';
	foreach ( array( 'min', 'max', 'step' ) as $attribute ) {
		if ( isset( $field[ $attribute ] ) ) {
			$attrs .= ' ' . $attribute . '="' . esc_attr( $field[ $attribute ] ) . '"';
		}
	}
	printf( '<div class="orion-input-with-suffix"><input id="%1$s" type="%2$s" name="%3$s" value="%4$s" class="%5$s"%6$s%7$s>%8$s</div>', esc_attr( $id ), esc_attr( $input_type ), esc_attr( $name ), esc_attr( $value ), 'color' === $type ? 'orion-color' : 'regular-text', $attrs, $preview, isset( $field['suffix'] ) ? '<span>' . esc_html( $field['suffix'] ) . '</span>' : '' );
}

function orion26_render_live_preview( $values ) {
	?>
	<aside class="orion-live-preview" data-orion-preview>
		<div class="orion-preview-browser">
			<div class="orion-preview-browser__bar"><i></i><i></i><i></i></div>
			<div class="orion-preview-page">
				<p class="orion-preview-kicker"><?php esc_html_e( 'Prévisualisation en direct', 'orion26' ); ?></p>
				<h2><?php esc_html_e( 'Un grand titre éditorial', 'orion26' ); ?></h2>
				<p class="orion-preview-article"><?php esc_html_e( 'Le corps de l’article doit rester agréable à lire, avec une hiérarchie claire et un rythme suffisamment aéré sur tous les écrans.', 'orion26' ); ?></p>
				<blockquote><?php esc_html_e( 'Une citation importante mise en évidence sans casser la lecture.', 'orion26' ); ?></blockquote>
				<pre><code>const orion = "personnalisable";</code></pre>
				<a href="#"><?php esc_html_e( 'Exemple de lien', 'orion26' ); ?></a>
			</div>
		</div>
	</aside>
	<?php
}

function orion26_render_settings_page() {
	if ( ! current_user_can( orion26_admin_capability() ) ) {
		wp_die( esc_html__( 'Vous n’avez pas accès aux réglages Orion.', 'orion26' ) );
	}
	$section = orion26_current_admin_section();
	$schema  = orion26_settings_schema();
	$config  = $schema[ $section ];
	$settings= orion26_get_settings();
	$values  = $settings[ $section ];
	?>
	<div class="wrap orion-admin-wrap">
		<header class="orion-admin-header">
			<div><span class="orion-admin-brand">ORION</span><h1><?php echo esc_html( $config['label'] ); ?></h1><p><?php echo esc_html( $config['description'] ); ?></p></div>
			<span class="orion-admin-version">v<?php echo esc_html( ORION26_VERSION ); ?></span>
		</header>
		<?php if ( isset( $_GET['updated'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Réglages enregistrés.', 'orion26' ); ?></p></div><?php endif; ?>
		<?php if ( 'consent' === $section && ( defined( 'cmplz_plugin' ) || function_exists( 'cmplz_get_value' ) ) ) : ?><div class="notice notice-warning"><p><?php esc_html_e( 'Complianz est actif. N’activez pas simultanément les deux bandeaux de consentement : configurez Orion, puis désactivez l’autre gestionnaire avant d’activer celui-ci.', 'orion26' ); ?></p></div><?php endif; ?>
		<div class="orion-settings-layout<?php echo ! empty( $config['preview'] ) ? ' has-preview' : ''; ?>">
			<form class="orion-settings-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="orion26_save_settings">
				<input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>">
				<?php wp_nonce_field( 'orion26_save_' . $section ); ?>
				<div class="orion-settings-card">
				<?php foreach ( $config['fields'] as $key => $field ) : ?>
					<div class="orion-field orion-field--<?php echo esc_attr( $field['type'] ); ?>">
						<?php if ( ! in_array( $field['type'], array( 'checkbox', 'preset' ), true ) ) : ?><label for="<?php echo esc_attr( orion26_field_id( $section, $key ) ); ?>"><?php echo esc_html( $field['label'] ); ?></label><?php endif; ?>
						<div class="orion-field__control"><?php orion26_render_field( $section, $key, $field, $values[ $key ] ?? '' ); ?><?php if ( ! empty( $field['description'] ) ) : ?><p class="description"><?php echo esc_html( $field['description'] ); ?></p><?php endif; ?></div>
					</div>
				<?php endforeach; ?>
				</div>
				<?php submit_button( __( 'Enregistrer les réglages', 'orion26' ), 'primary large' ); ?>
			</form>
			<?php if ( ! empty( $config['preview'] ) ) { orion26_render_live_preview( $values ); } ?>
		</div>
	</div>
	<?php
}

function orion26_sanitize_setting( $value, $field, $old_value ) {
	$type = $field['type'] ?? 'text';
	if ( ! empty( $field['capability'] ) && ! current_user_can( $field['capability'] ) ) {
		return $old_value;
	}
	if ( 'checkbox' === $type ) {
		return empty( $value ) ? 0 : 1;
	}
	if ( in_array( $type, array( 'media', 'menus', 'tags', 'category' ), true ) ) {
		return absint( $value );
	}
	if ( in_array( $type, array( 'categories', 'users' ), true ) ) {
		return array_values( array_unique( array_filter( array_map( 'absint', (array) $value ) ) ) );
	}
	if ( 'color' === $type ) {
		return sanitize_hex_color( (string) $value ) ?: $old_value;
	}
	if ( 'number' === $type ) {
		$number = is_numeric( $value ) ? (float) $value : (float) $old_value;
		$number = isset( $field['min'] ) ? max( (float) $field['min'], $number ) : $number;
		$number = isset( $field['max'] ) ? min( (float) $field['max'], $number ) : $number;
		return isset( $field['step'] ) && (float) $field['step'] < 1 ? $number : (int) $number;
	}
	if ( in_array( $type, array( 'select', 'preset' ), true ) ) {
		$key = sanitize_key( (string) $value );
		return isset( $field['choices'][ $key ] ) ? $key : $old_value;
	}
	if ( 'url' === $type ) {
		return esc_url_raw( (string) $value );
	}
	if ( 'url_or_path' === $type ) {
		$value = trim( sanitize_text_field( (string) $value ) );
		return str_starts_with( $value, '/' ) ? '/' . ltrim( $value, '/' ) : esc_url_raw( $value );
	}
	if ( 'textarea' === $type ) {
		return sanitize_textarea_field( (string) $value );
	}
	if ( 'code' === $type ) {
		return (string) $value;
	}
	return sanitize_text_field( (string) $value );
}

function orion26_save_settings() {
	$section = isset( $_POST['section'] ) ? sanitize_key( wp_unslash( $_POST['section'] ) ) : '';
	$schema  = orion26_settings_schema();
	if ( ! current_user_can( orion26_admin_capability() ) || ! isset( $schema[ $section ] ) ) {
		wp_die( esc_html__( 'Enregistrement non autorisé.', 'orion26' ) );
	}
	check_admin_referer( 'orion26_save_' . $section );
	$posted   = isset( $_POST['orion26'][ $section ] ) && is_array( $_POST['orion26'][ $section ] ) ? wp_unslash( $_POST['orion26'][ $section ] ) : array();
	$settings = orion26_get_settings();
	foreach ( $schema[ $section ]['fields'] as $key => $field ) {
		$value = 'checkbox' === $field['type'] ? ( $posted[ $key ] ?? 0 ) : ( $posted[ $key ] ?? ( $settings[ $section ][ $key ] ?? '' ) );
		$settings[ $section ][ $key ] = orion26_sanitize_setting( $value, $field, $settings[ $section ][ $key ] ?? '' );
	}
	$settings['_version'] = ORION26_SETTINGS_VERSION;
	update_option( ORION26_SETTINGS_OPTION, $settings, false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'orion26-' . $section, 'updated' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_orion26_save_settings', 'orion26_save_settings' );
