<?php
/** Installation sécurisée des extensions complémentaires Orion. @package Orion26 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function orion26_recommended_plugins() {
	return array(
		'wp-simple-pwa' => array(
			'name' => 'WP Simple PWA', 'description' => 'Progressive Web App légère, manifest, installation et expérience hors ligne.',
			'repository' => 'https://github.com/TheLibertyWolf/WP-Simple-PWA',
			'package' => 'https://github.com/TheLibertyWolf/WP-Simple-PWA/archive/refs/heads/main.zip',
			'version_url' => 'https://raw.githubusercontent.com/TheLibertyWolf/WP-Simple-PWA/main/wp-simple-pwa.php',
			'file_names' => array( 'wp-simple-pwa.php' ),
		),
		'wp-turnsite' => array(
			'name' => 'WP TurnSite', 'description' => 'Outils complémentaires de gestion et de bascule pour les installations WordPress.',
			'repository' => 'https://github.com/TheLibertyWolf/WP-TurnSite',
			'package' => 'https://github.com/TheLibertyWolf/WP-TurnSite/archive/refs/heads/main.zip',
			'version_url' => 'https://raw.githubusercontent.com/TheLibertyWolf/WP-TurnSite/main/wp-turnsite.php',
			'file_names' => array( 'wp-turnsite.php' ),
		),
	);
}

function orion26_find_plugin_file( $config ) {
	if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
	foreach ( get_plugins() as $plugin_file => $data ) {
		if ( in_array( basename( $plugin_file ), $config['file_names'], true ) ) { return $plugin_file; }
	}
	return '';
}

function orion26_recommended_plugin_version( $slug, $config ) {
	$cache_key = 'orion26_plugin_version_' . sanitize_key( $slug );
	$cached = get_transient( $cache_key );
	if ( is_string( $cached ) && '' !== $cached ) { return 'indisponible' === $cached ? '' : $cached; }
	$response = wp_safe_remote_get( $config['version_url'], array( 'timeout' => 8, 'redirection' => 3, 'user-agent' => 'Orion26/' . ORION26_VERSION ) );
	$version = '';
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && preg_match( '/^[ \t\/*#@]*Version:\s*([0-9][0-9A-Za-z.\-+]*)/mi', wp_remote_retrieve_body( $response ), $match ) ) {
		$version = sanitize_text_field( $match[1] );
	}
	set_transient( $cache_key, $version ?: 'indisponible', 6 * HOUR_IN_SECONDS );
	return $version;
}

function orion26_normalize_plugin_source( $source ) {
	$slug = sanitize_key( (string) ( $GLOBALS['orion26_installing_plugin_slug'] ?? '' ) );
	if ( ! $slug || is_wp_error( $source ) || basename( untrailingslashit( $source ) ) === $slug ) { return $source; }
	global $wp_filesystem;
	$target = trailingslashit( dirname( untrailingslashit( $source ) ) ) . $slug;
	if ( ! $wp_filesystem || $wp_filesystem->exists( $target ) || ! $wp_filesystem->move( untrailingslashit( $source ), $target, true ) ) {
		return new WP_Error( 'orion26_package_normalization_failed', __( 'Le paquet GitHub n’a pas pu être préparé.', 'orion26' ) );
	}
	return trailingslashit( $target );
}

function orion26_plugin_action_form( $slug, $task, $label, $primary = false, $disabled = false, $confirm = '' ) {
	?>
	<form class="orion-plugin-action" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"<?php if ( $confirm ) : ?> data-confirm="<?php echo esc_attr( $confirm ); ?>"<?php endif; ?>>
		<input type="hidden" name="action" value="orion26_install_plugin"><input type="hidden" name="plugin" value="<?php echo esc_attr( $slug ); ?>"><input type="hidden" name="plugin_task" value="<?php echo esc_attr( $task ); ?>"><?php wp_nonce_field( 'orion26_plugin_' . $task . '_' . $slug ); ?>
		<button class="button<?php echo $primary ? ' button-primary' : ''; ?>" type="submit"<?php disabled( $disabled ); ?>><?php echo esc_html( $label ); ?></button>
	</form>
	<?php
}

function orion26_run_plugin_package( $slug, $config, $overwrite = false ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$GLOBALS['orion26_installing_plugin_slug'] = $slug;
	add_filter( 'upgrader_source_selection', 'orion26_normalize_plugin_source', 9, 1 );
	$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
	$result = $upgrader->install( $config['package'], array( 'overwrite_package' => (bool) $overwrite ) );
	remove_filter( 'upgrader_source_selection', 'orion26_normalize_plugin_source', 9 );
	unset( $GLOBALS['orion26_installing_plugin_slug'] );
	wp_clean_plugins_cache( true );
	return $result;
}

function orion26_render_plugins_page() {
	$result = isset( $_GET['plugin_result'] ) ? sanitize_key( wp_unslash( $_GET['plugin_result'] ) ) : '';
	$messages = array( 'installed' => __( 'Plugin installé et activé.', 'orion26' ), 'activated' => __( 'Plugin activé.', 'orion26' ), 'updated' => __( 'Plugin mis à jour.', 'orion26' ), 'uninstalled' => __( 'Plugin désinstallé.', 'orion26' ), 'failed' => __( 'L’opération n’a pas abouti. Vérifiez les droits d’écriture et réessayez.', 'orion26' ), 'disabled' => __( 'La modification des fichiers est désactivée par la configuration WordPress.', 'orion26' ) );
	if ( isset( $messages[ $result ] ) ) {
		printf( '<div class="notice %1$s"><p>%2$s</p></div>', in_array( $result, array( 'failed', 'disabled' ), true ) ? 'notice-error' : 'notice-success', esc_html( $messages[ $result ] ) );
	}
	$file_mods_allowed = ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS;
	if ( ! $file_mods_allowed ) { echo '<div class="notice notice-warning"><p>' . esc_html__( 'Les installations, mises à jour et désinstallations sont bloquées par la politique WordPress DISALLOW_FILE_MODS. Les plugins déjà présents peuvent toujours être activés.', 'orion26' ) . '</p></div>'; }
	?>
	<div class="orion-plugin-grid">
	<?php foreach ( orion26_recommended_plugins() as $slug => $config ) :
		$plugin_file = orion26_find_plugin_file( $config );
		$plugins = get_plugins();
		$installed_version = $plugin_file ? (string) ( $plugins[ $plugin_file ]['Version'] ?? '' ) : '';
		$available_version = orion26_recommended_plugin_version( $slug, $config );
		$active = $plugin_file && is_plugin_active( $plugin_file );
		$update_available = $installed_version && $available_version && version_compare( $available_version, $installed_version, '>' );
		?>
		<section class="orion-plugin-card">
			<div class="orion-plugin-card__head"><a class="orion-plugin-github" href="<?php echo esc_url( $config['repository'] ); ?>" target="_blank" rel="noopener noreferrer external" aria-label="GitHub — <?php echo esc_attr( $config['name'] ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 .7A11.5 11.5 0 0 0 8.4 23c.6.1.8-.3.8-.6v-2.2c-3.4.7-4.1-1.4-4.1-1.4-.6-1.4-1.4-1.8-1.4-1.8-1.1-.8.1-.8.1-.8 1.3.1 2 1.3 2 1.3 1.1 2 3 1.4 3.7 1.1.1-.8.4-1.4.8-1.7-2.7-.3-5.5-1.3-5.5-5.7 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.6.1-3.1 0 0 1-.3 3.2 1.2a11 11 0 0 1 5.8 0c2.2-1.5 3.2-1.2 3.2-1.2.6 1.5.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.4-2.8 5.4-5.5 5.7.4.4.8 1.1.8 2.2v3.5c0 .3.2.7.8.6A11.5 11.5 0 0 0 12 .7Z"/></svg></a><div><h2><?php echo esc_html( $config['name'] ); ?></h2><span><?php echo $active ? esc_html__( 'Actif', 'orion26' ) : ( $plugin_file ? esc_html__( 'Inactif', 'orion26' ) : esc_html__( 'Non installé', 'orion26' ) ); ?></span></div></div>
			<p><?php echo esc_html( $config['description'] ); ?></p>
			<dl class="orion-plugin-versions"><div><dt><?php esc_html_e( 'Version installée', 'orion26' ); ?></dt><dd><?php echo esc_html( $installed_version ?: '—' ); ?></dd></div><div><dt><?php esc_html_e( 'Version disponible', 'orion26' ); ?></dt><dd><?php echo esc_html( $available_version ?: __( 'Indisponible', 'orion26' ) ); ?></dd></div></dl>
			<div class="orion-plugin-actions">
			<?php
			if ( ! $plugin_file ) {
				orion26_plugin_action_form( $slug, 'install', __( 'Installer', 'orion26' ), true, ! $file_mods_allowed || ! current_user_can( 'install_plugins' ) );
			} else {
				if ( ! $active ) { orion26_plugin_action_form( $slug, 'activate', __( 'Activer', 'orion26' ), true, ! current_user_can( 'activate_plugins' ) ); }
				if ( $update_available ) { orion26_plugin_action_form( $slug, 'update', __( 'Mettre à jour', 'orion26' ), true, ! $file_mods_allowed || ! current_user_can( 'update_plugins' ) ); }
				orion26_plugin_action_form( $slug, 'uninstall', __( 'Désinstaller', 'orion26' ), false, ! $file_mods_allowed || ! current_user_can( 'delete_plugins' ), __( 'Désinstaller définitivement ce plugin et supprimer ses fichiers ?', 'orion26' ) );
			}
			?>
			</div>
		</section>
	<?php endforeach; ?>
	</div>
	<?php
}

function orion26_install_recommended_plugin() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Installation non autorisée.', 'orion26' ) ); }
	$slug = isset( $_POST['plugin'] ) ? sanitize_key( wp_unslash( $_POST['plugin'] ) ) : '';
	$task = isset( $_POST['plugin_task'] ) ? sanitize_key( wp_unslash( $_POST['plugin_task'] ) ) : '';
	$plugins = orion26_recommended_plugins();
	if ( ! isset( $plugins[ $slug ] ) || ! in_array( $task, array( 'install', 'activate', 'update', 'uninstall' ), true ) ) { wp_die( esc_html__( 'Plugin non autorisé.', 'orion26' ) ); }
	check_admin_referer( 'orion26_plugin_' . $task . '_' . $slug );
	$config = $plugins[ $slug ];
	$plugin_file = orion26_find_plugin_file( $config );
	$result = 'failed';
	$file_mods_allowed = ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS;
	if ( 'install' === $task && ! $plugin_file ) {
		if ( ! $file_mods_allowed || ! current_user_can( 'install_plugins' ) ) { $result = 'disabled'; }
		elseif ( true === orion26_run_plugin_package( $slug, $config, false ) ) {
			$plugin_file = orion26_find_plugin_file( $config );
			$activated = $plugin_file && current_user_can( 'activate_plugins' ) ? activate_plugin( $plugin_file, '', false, false ) : new WP_Error( 'activation_not_allowed' );
			$result = $plugin_file && ! is_wp_error( $activated ) && is_plugin_active( $plugin_file ) ? 'installed' : 'failed';
		}
	} elseif ( 'activate' === $task && $plugin_file && current_user_can( 'activate_plugins' ) ) {
		$activated = activate_plugin( $plugin_file, '', false, false );
		$result = ! is_wp_error( $activated ) && is_plugin_active( $plugin_file ) ? 'activated' : 'failed';
	} elseif ( 'update' === $task && $plugin_file ) {
		if ( ! $file_mods_allowed || ! current_user_can( 'update_plugins' ) ) { $result = 'disabled'; }
		elseif ( true === orion26_run_plugin_package( $slug, $config, true ) ) { delete_transient( 'orion26_plugin_version_' . $slug ); $result = 'updated'; }
	} elseif ( 'uninstall' === $task && $plugin_file ) {
		if ( ! $file_mods_allowed || ! current_user_can( 'delete_plugins' ) ) { $result = 'disabled'; }
		else {
			if ( is_plugin_active( $plugin_file ) ) { deactivate_plugins( $plugin_file, false, false ); }
			$deleted = delete_plugins( array( $plugin_file ) );
			$result = true === $deleted ? 'uninstalled' : 'failed';
		}
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'orion26-plugins', 'plugin_result' => $result ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_orion26_install_plugin', 'orion26_install_recommended_plugin' );
