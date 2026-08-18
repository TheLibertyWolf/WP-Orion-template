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
		$section_capability = $config['capability'] ?? $capability;
		add_submenu_page(
			'orion26-identity',
			'Orion — ' . $config['label'],
			$config['label'],
			$section_capability,
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

function orion26_render_checklist( $section, $key, $type, $value ) {
	$choices  = orion26_dynamic_choices( $type );
	$selected = array_map( 'absint', (array) $value );
	if ( 'categories' === $type && $selected ) {
		$ordered = array();
		foreach ( $selected as $selected_id ) {
			if ( isset( $choices[ $selected_id ] ) ) {
				$ordered[ $selected_id ] = $choices[ $selected_id ];
			}
		}
		$choices = $ordered + $choices;
	}
	?>
	<fieldset class="orion-checklist<?php echo 'categories' === $type ? ' orion-checklist--sortable' : ''; ?>" data-orion-checklist<?php echo 'categories' === $type ? ' data-orion-sortable' : ''; ?>>
		<div class="orion-checklist__tools"><input type="search" class="regular-text" placeholder="<?php esc_attr_e( 'Filtrer la liste…', 'orion26' ); ?>" data-orion-checklist-search><button type="button" class="button-link" data-orion-checklist-all><?php esc_html_e( 'Tout cocher', 'orion26' ); ?></button><button type="button" class="button-link" data-orion-checklist-none><?php esc_html_e( 'Tout décocher', 'orion26' ); ?></button></div>
		<div class="orion-checklist__items">
		<?php foreach ( $choices as $choice_value => $label ) : ?><label<?php echo 'categories' === $type ? ' draggable="true"' : ''; ?>><?php if ( 'categories' === $type ) : ?><span class="orion-checklist__handle dashicons dashicons-menu" aria-hidden="true"></span><?php endif; ?><input type="checkbox" name="<?php echo esc_attr( orion26_field_name( $section, $key ) ); ?>[]" value="<?php echo esc_attr( $choice_value ); ?>"<?php checked( in_array( absint( $choice_value ), $selected, true ) ); ?>><span><?php echo esc_html( $label ); ?></span><?php if ( 'categories' === $type ) : ?><span class="orion-checklist__moves"><button type="button" class="button-link" data-orion-move="up" aria-label="<?php esc_attr_e( 'Monter', 'orion26' ); ?>">↑</button><button type="button" class="button-link" data-orion-move="down" aria-label="<?php esc_attr_e( 'Descendre', 'orion26' ); ?>">↓</button></span><?php endif; ?></label><?php endforeach; ?>
		</div>
		<?php if ( 'categories' === $type ) : ?><p class="description orion-checklist__hint"><?php esc_html_e( 'Les rubriques cochées sont affichées dans cet ordre. Glissez-les ou utilisez les flèches, puis enregistrez.', 'orion26' ); ?></p><?php endif; ?>
	</fieldset>
	<?php
}

function orion26_render_headings_field( $section, $key, $value ) {
	$fonts = array( 'system' => 'Système', 'condensed' => 'Condensée', 'source-serif-4' => 'Source Serif 4', 'atkinson-hyperlegible-next' => 'Atkinson Hyperlegible Next', 'lora' => 'Lora', 'merriweather' => 'Merriweather', 'literata' => 'Literata', 'ibm-plex-sans' => 'IBM Plex Sans' );
	?>
	<div class="orion-heading-styles">
		<div class="orion-heading-styles__head"><span><?php esc_html_e( 'Niveau', 'orion26' ); ?></span><span><?php esc_html_e( 'Clair', 'orion26' ); ?></span><span><?php esc_html_e( 'Sombre', 'orion26' ); ?></span><span><?php esc_html_e( 'Police', 'orion26' ); ?></span><span><?php esc_html_e( 'Taille', 'orion26' ); ?></span><span><?php esc_html_e( 'Graisse', 'orion26' ); ?></span><span><?php esc_html_e( 'Casse', 'orion26' ); ?></span><span><?php esc_html_e( 'Interligne', 'orion26' ); ?></span></div>
		<?php foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $level ) : $style = (array) ( $value[ $level ] ?? array() ); $base = orion26_field_name( $section, $key ) . '[' . $level . ']'; ?>
		<div class="orion-heading-style" data-heading-level="<?php echo esc_attr( $level ); ?>">
			<strong><?php echo esc_html( strtoupper( $level ) ); ?></strong>
			<input type="color" name="<?php echo esc_attr( $base ); ?>[color]" value="<?php echo esc_attr( $style['color'] ?? '#12151b' ); ?>" data-heading-prop="color" aria-label="<?php echo esc_attr( strtoupper( $level ) . ' ' . __( 'couleur', 'orion26' ) ); ?>">
			<input type="color" name="<?php echo esc_attr( $base ); ?>[dark_color]" value="<?php echo esc_attr( $style['dark_color'] ?? '#f1eee7' ); ?>" aria-label="<?php echo esc_attr( strtoupper( $level ) . ' ' . __( 'couleur sombre', 'orion26' ) ); ?>">
			<select name="<?php echo esc_attr( $base ); ?>[font]" data-heading-prop="font"><?php foreach ( $fonts as $font => $label ) : ?><option value="<?php echo esc_attr( $font ); ?>"<?php selected( $style['font'] ?? 'condensed', $font ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select>
			<input type="number" min="12" max="96" name="<?php echo esc_attr( $base ); ?>[size]" value="<?php echo esc_attr( $style['size'] ?? 24 ); ?>" data-heading-prop="size" aria-label="<?php echo esc_attr( strtoupper( $level ) . ' ' . __( 'taille', 'orion26' ) ); ?>">
			<select name="<?php echo esc_attr( $base ); ?>[weight]" data-heading-prop="weight"><?php foreach ( array( 400, 500, 600, 700, 800, 900 ) as $weight ) : ?><option value="<?php echo esc_attr( $weight ); ?>"<?php selected( absint( $style['weight'] ?? 800 ), $weight ); ?>><?php echo esc_html( $weight ); ?></option><?php endforeach; ?></select>
			<select name="<?php echo esc_attr( $base ); ?>[case]" data-heading-prop="case"><option value="none"<?php selected( $style['case'] ?? 'none', 'none' ); ?>><?php esc_html_e( 'Normale', 'orion26' ); ?></option><option value="uppercase"<?php selected( $style['case'] ?? 'none', 'uppercase' ); ?>><?php esc_html_e( 'Majuscules', 'orion26' ); ?></option></select>
			<input type="number" min="0.8" max="2" step="0.01" name="<?php echo esc_attr( $base ); ?>[line_height]" value="<?php echo esc_attr( $style['line_height'] ?? 1.2 ); ?>" data-heading-prop="line-height" aria-label="<?php echo esc_attr( strtoupper( $level ) . ' ' . __( 'interligne', 'orion26' ) ); ?>">
		</div><?php endforeach; ?>
	</div>
	<?php
}

function orion26_render_title_styles_field( $section, $key, $value ) {
	$fonts = array( 'system' => 'Système', 'condensed' => 'Condensée', 'source-serif-4' => 'Source Serif 4', 'atkinson-hyperlegible-next' => 'Atkinson Hyperlegible Next', 'lora' => 'Lora', 'merriweather' => 'Merriweather', 'literata' => 'Literata', 'ibm-plex-sans' => 'IBM Plex Sans' );
	$rows  = array( 'card' => __( 'Carte hors image', 'orion26' ), 'overlay' => __( 'Titre sur image', 'orion26' ), 'category' => __( 'Titre de rubrique', 'orion26' ) );
	?>
	<div class="orion-heading-styles orion-title-styles">
		<div class="orion-heading-styles__head"><span><?php esc_html_e( 'Élément', 'orion26' ); ?></span><span><?php esc_html_e( 'Clair', 'orion26' ); ?></span><span><?php esc_html_e( 'Sombre', 'orion26' ); ?></span><span><?php esc_html_e( 'Police', 'orion26' ); ?></span><span><?php esc_html_e( 'Taille', 'orion26' ); ?></span><span><?php esc_html_e( 'Graisse', 'orion26' ); ?></span><span><?php esc_html_e( 'Casse', 'orion26' ); ?></span></div>
		<?php foreach ( $rows as $row_key => $row_label ) : $style = (array) ( $value[ $row_key ] ?? array() ); $base = orion26_field_name( $section, $key ) . '[' . $row_key . ']'; ?>
		<div class="orion-heading-style">
			<strong><?php echo esc_html( $row_label ); ?></strong>
			<input type="color" name="<?php echo esc_attr( $base ); ?>[color]" value="<?php echo esc_attr( $style['color'] ?? '#12151b' ); ?>" aria-label="<?php echo esc_attr( $row_label . ' ' . __( 'couleur', 'orion26' ) ); ?>">
			<input type="color" name="<?php echo esc_attr( $base ); ?>[dark_color]" value="<?php echo esc_attr( $style['dark_color'] ?? '#f1eee7' ); ?>" aria-label="<?php echo esc_attr( $row_label . ' ' . __( 'couleur sombre', 'orion26' ) ); ?>">
			<select name="<?php echo esc_attr( $base ); ?>[font]"><?php foreach ( $fonts as $font => $label ) : ?><option value="<?php echo esc_attr( $font ); ?>"<?php selected( $style['font'] ?? 'condensed', $font ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select>
			<input type="number" min="12" max="96" name="<?php echo esc_attr( $base ); ?>[size]" value="<?php echo esc_attr( $style['size'] ?? 25 ); ?>" aria-label="<?php echo esc_attr( $row_label . ' ' . __( 'taille', 'orion26' ) ); ?>">
			<select name="<?php echo esc_attr( $base ); ?>[weight]"><?php foreach ( array( 400, 500, 600, 700, 800, 900 ) as $weight ) : ?><option value="<?php echo esc_attr( $weight ); ?>"<?php selected( absint( $style['weight'] ?? 800 ), $weight ); ?>><?php echo esc_html( $weight ); ?></option><?php endforeach; ?></select>
			<select name="<?php echo esc_attr( $base ); ?>[case]"><option value="none"<?php selected( $style['case'] ?? 'none', 'none' ); ?>><?php esc_html_e( 'Normale', 'orion26' ); ?></option><option value="uppercase"<?php selected( $style['case'] ?? 'none', 'uppercase' ); ?>><?php esc_html_e( 'Majuscules', 'orion26' ); ?></option></select>
		</div><?php endforeach; ?>
	</div>
	<?php
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
	if ( in_array( $type, array( 'categories', 'users' ), true ) ) {
		orion26_render_checklist( $section, $key, $type, $value );
		return;
	}
	if ( 'headings' === $type ) {
		orion26_render_headings_field( $section, $key, $value );
		return;
	}
	if ( 'title_styles' === $type ) {
		orion26_render_title_styles_field( $section, $key, $value );
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
	if ( in_array( $type, array( 'select', 'menus', 'tags', 'category' ), true ) ) {
		$choices  = isset( $field['choices'] ) ? $field['choices'] : orion26_dynamic_choices( $type );
		$multiple = false;
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
				<div class="orion-preview-headings"><?php foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $level ) : ?><<?php echo esc_attr( $level ); ?> data-preview-heading="<?php echo esc_attr( $level ); ?>"><?php echo esc_html( strtoupper( $level ) . ' — ' . __( 'Titre éditorial', 'orion26' ) ); ?></<?php echo esc_attr( $level ); ?>><?php endforeach; ?></div>
				<p class="orion-preview-article"><?php esc_html_e( 'Le corps de l’article doit rester agréable à lire, avec une hiérarchie claire et un rythme suffisamment aéré sur tous les écrans.', 'orion26' ); ?></p>
				<blockquote><?php esc_html_e( 'Une citation importante mise en évidence sans casser la lecture.', 'orion26' ); ?></blockquote>
				<pre><code>const orion = "personnalisable";</code></pre>
				<a href="#"><?php esc_html_e( 'Exemple de lien', 'orion26' ); ?></a>
			</div>
		</div>
	</aside>
	<?php
}

function orion26_render_about_page() {
	?>
	<div class="orion-about-grid">
		<section class="orion-about-hero"><span class="orion-about-mark" aria-hidden="true">O</span><div><h2><?php esc_html_e( 'Un thème éditorial libre et ambitieux', 'orion26' ); ?></h2><p><?php esc_html_e( 'Orion associe vitesse, lisibilité, personnalisation et maîtrise des données pour construire des sites d’actualité singuliers.', 'orion26' ); ?></p></div></section>
		<section class="orion-about-card"><h2><?php esc_html_e( 'Auteur', 'orion26' ); ?></h2><p><strong>SAS Jessy System</strong><br><?php esc_html_e( 'Conception, développement et maintenance du thème Orion.', 'orion26' ); ?></p><a class="button button-primary" href="https://jessysystem.com" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Découvrir Jessy System', 'orion26' ); ?></a></section>
		<section class="orion-about-card"><svg class="orion-github-logo" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 .7A11.5 11.5 0 0 0 8.4 23c.6.1.8-.3.8-.6v-2.2c-3.4.7-4.1-1.4-4.1-1.4-.6-1.4-1.4-1.8-1.4-1.8-1.1-.8.1-.8.1-.8 1.3.1 2 1.3 2 1.3 1.1 2 3 1.4 3.7 1.1.1-.8.4-1.4.8-1.7-2.7-.3-5.5-1.3-5.5-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.6.1-3.1 0 0 1-.3 3.2 1.2a11 11 0 0 1 5.8 0c2.2-1.5 3.2-1.2 3.2-1.2.6 1.5.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.8 5.4-5.5 5.7.4.4.8 1.1.8 2.2v3.5c0 .3.2.7.8.6A11.5 11.5 0 0 0 12 .7Z"/></svg><h2>GitHub</h2><p><?php esc_html_e( 'Consultez le code source, les versions, la feuille de route et proposez vos améliorations.', 'orion26' ); ?></p><a class="button" href="https://github.com/TheLibertyWolf/WP-Orion-template" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ouvrir le dépôt Orion', 'orion26' ); ?></a></section>
		<section class="orion-about-card"><h2><?php esc_html_e( 'Licence et version', 'orion26' ); ?></h2><p><strong>Orion <?php echo esc_html( ORION26_VERSION ); ?></strong><br>GPL-2.0-or-later</p><p><?php esc_html_e( 'Vous pouvez utiliser, étudier, modifier et redistribuer Orion selon les termes de sa licence.', 'orion26' ); ?></p></section>
	</div>
	<?php
}

function orion26_render_settings_page() {
	$section = orion26_current_admin_section();
	$schema  = orion26_settings_schema();
	$config  = $schema[ $section ];
	$required_capability = $config['capability'] ?? orion26_admin_capability();
	if ( ! current_user_can( $required_capability ) ) {
		wp_die( esc_html__( 'Vous n’avez pas accès aux réglages Orion.', 'orion26' ) );
	}
	$settings= orion26_get_settings();
	$values  = $settings[ $section ] ?? array();
	?>
	<div class="orion-admin-notices">
		<?php if ( isset( $_GET['updated'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['updated'] ) ) ) : ?><div class="notice notice-success is-dismissible orion-save-notice"><p><?php esc_html_e( 'Réglages enregistrés.', 'orion26' ); ?></p></div><?php endif; ?>
		<?php if ( 'consent' === $section && ( defined( 'cmplz_plugin' ) || function_exists( 'cmplz_get_value' ) ) ) : ?><div class="notice notice-warning"><p><?php esc_html_e( 'Complianz est actif. N’activez pas simultanément les deux bandeaux de consentement : configurez Orion, puis désactivez l’autre gestionnaire avant d’activer celui-ci.', 'orion26' ); ?></p></div><?php endif; ?>
		<?php if ( 'plugins' === $section ) { orion26_render_plugin_notices(); } ?>
	</div>
	<div class="wrap orion-admin-wrap">
		<header class="orion-admin-header">
			<div><span class="orion-admin-brand">ORION</span><h1><?php echo esc_html( $config['label'] ); ?></h1><p><?php echo esc_html( $config['description'] ); ?></p></div>
			<span class="orion-admin-version">v<?php echo esc_html( ORION26_VERSION ); ?></span>
		</header>
		<?php if ( ! empty( $config['about'] ) ) { orion26_render_about_page(); echo '</div>'; return; } ?>
		<?php if ( ! empty( $config['plugins'] ) ) { orion26_render_plugins_page(); echo '</div>'; return; } ?>
		<div class="orion-settings-layout<?php echo ! empty( $config['preview'] ) ? ' has-preview' : ''; ?>">
			<form class="orion-settings-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="orion26_save_settings">
				<input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>">
				<?php wp_nonce_field( 'orion26_save_' . $section ); ?>
				<div class="orion-settings-card">
				<?php foreach ( $config['fields'] as $key => $field ) : ?>
					<?php if ( ! empty( $field['capability'] ) && ! current_user_can( $field['capability'] ) ) { continue; } ?>
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
		$id = absint( $value );
		if ( ! $id ) {
			return 0;
		}
		if ( 'media' === $type ) {
			return wp_attachment_is_image( $id ) ? $id : 0;
		}
		if ( 'menus' === $type ) {
			return wp_get_nav_menu_object( $id ) ? $id : 0;
		}
		return term_exists( $id, 'tags' === $type ? 'post_tag' : 'category' ) ? $id : 0;
	}
	if ( in_array( $type, array( 'categories', 'users' ), true ) ) {
		$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $value ) ) ) );
		return array_values(
			array_filter(
				$ids,
				static function ( $id ) use ( $type ) {
					return 'users' === $type ? (bool) get_user_by( 'id', $id ) : (bool) term_exists( $id, 'category' );
				}
			)
		);
	}
	if ( 'headings' === $type ) {
		$defaults = orion26_settings_defaults()['design']['headings'];
		$output   = array();
		$fonts    = array( 'system', 'condensed', 'source-serif-4', 'atkinson-hyperlegible-next', 'lora', 'merriweather', 'literata', 'ibm-plex-sans' );
		foreach ( array_keys( $defaults ) as $level ) {
			$row = isset( $value[ $level ] ) && is_array( $value[ $level ] ) ? $value[ $level ] : array();
			$font = sanitize_key( (string) ( $row['font'] ?? $defaults[ $level ]['font'] ) );
			$output[ $level ] = array(
				'color'       => sanitize_hex_color( (string) ( $row['color'] ?? '' ) ) ?: $defaults[ $level ]['color'],
				'dark_color'  => sanitize_hex_color( (string) ( $row['dark_color'] ?? '' ) ) ?: $defaults[ $level ]['dark_color'],
				'font'        => in_array( $font, $fonts, true ) ? $font : $defaults[ $level ]['font'],
				'size'        => max( 12, min( 96, absint( $row['size'] ?? $defaults[ $level ]['size'] ) ) ),
				'weight'      => in_array( absint( $row['weight'] ?? 800 ), array( 400, 500, 600, 700, 800, 900 ), true ) ? absint( $row['weight'] ) : $defaults[ $level ]['weight'],
				'case'        => 'uppercase' === ( $row['case'] ?? '' ) ? 'uppercase' : 'none',
				'line_height' => max( .8, min( 2, (float) ( $row['line_height'] ?? $defaults[ $level ]['line_height'] ) ) ),
			);
		}
		return $output;
	}
	if ( 'title_styles' === $type ) {
		$defaults = orion26_settings_defaults()['design']['title_styles'];
		$output   = array();
		$fonts    = array( 'system', 'condensed', 'source-serif-4', 'atkinson-hyperlegible-next', 'lora', 'merriweather', 'literata', 'ibm-plex-sans' );
		foreach ( $defaults as $row_key => $row_defaults ) {
			$row  = isset( $value[ $row_key ] ) && is_array( $value[ $row_key ] ) ? $value[ $row_key ] : array();
			$font = sanitize_key( (string) ( $row['font'] ?? $row_defaults['font'] ) );
			$output[ $row_key ] = array(
				'color'      => sanitize_hex_color( (string) ( $row['color'] ?? '' ) ) ?: $row_defaults['color'],
				'dark_color' => sanitize_hex_color( (string) ( $row['dark_color'] ?? '' ) ) ?: $row_defaults['dark_color'],
				'font'       => in_array( $font, $fonts, true ) ? $font : $row_defaults['font'],
				'size'       => max( 12, min( 96, absint( $row['size'] ?? $row_defaults['size'] ) ) ),
				'weight'     => in_array( absint( $row['weight'] ?? 800 ), array( 400, 500, 600, 700, 800, 900 ), true ) ? absint( $row['weight'] ) : $row_defaults['weight'],
				'case'       => 'uppercase' === ( $row['case'] ?? '' ) ? 'uppercase' : 'none',
			);
		}
		return $output;
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
	if ( ! isset( $schema[ $section ] ) ) {
		wp_die( esc_html__( 'Enregistrement non autorisé.', 'orion26' ) );
	}
	$required_capability = $schema[ $section ]['capability'] ?? orion26_admin_capability();
	if ( ! current_user_can( $required_capability ) || ! empty( $schema[ $section ]['about'] ) ) {
		wp_die( esc_html__( 'Enregistrement non autorisé.', 'orion26' ) );
	}
	check_admin_referer( 'orion26_save_' . $section );
	$posted   = isset( $_POST['orion26'][ $section ] ) && is_array( $_POST['orion26'][ $section ] ) ? wp_unslash( $_POST['orion26'][ $section ] ) : array();
	$settings = orion26_get_settings();
	foreach ( $schema[ $section ]['fields'] as $key => $field ) {
		if ( ! empty( $field['capability'] ) && ! current_user_can( $field['capability'] ) ) {
			continue;
		}
		$value = in_array( $field['type'], array( 'checkbox', 'categories', 'users' ), true ) ? ( $posted[ $key ] ?? ( 'checkbox' === $field['type'] ? 0 : array() ) ) : ( $posted[ $key ] ?? ( $settings[ $section ][ $key ] ?? '' ) );
		$settings[ $section ][ $key ] = orion26_sanitize_setting( $value, $field, $settings[ $section ][ $key ] ?? '' );
	}
	$settings['_version'] = ORION26_SETTINGS_VERSION;
	update_option( ORION26_SETTINGS_OPTION, $settings, false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'orion26-' . $section, 'updated' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_orion26_save_settings', 'orion26_save_settings' );
