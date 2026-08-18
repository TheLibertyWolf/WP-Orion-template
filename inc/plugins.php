<?php
/** Installation sécurisée des extensions complémentaires Orion. @package Orion26 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function orion26_recommended_plugins() {
	return array(
		'wp-simple-pwa' => array(
			'name' => 'WP Simple PWA', 'description' => 'Progressive Web App légère, manifest, installation et expérience hors ligne.',
			'repository' => 'https://github.com/TheLibertyWolf/WP-Simple-PWA',
			'package' => 'https://github.com/TheLibertyWolf/WP-Simple-PWA/releases/latest/download/wp-simple-pwa.zip',
			'file_names' => array( 'wp-simple-pwa.php' ),
		),
		'wp-turnsite' => array(
			'name' => 'WP TurnSite', 'description' => 'Outils complémentaires de gestion et de bascule pour les installations WordPress.',
			'repository' => 'https://github.com/TheLibertyWolf/WP-TurnSite',
			'package' => 'https://github.com/TheLibertyWolf/WP-TurnSite/archive/refs/heads/main.zip',
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

function orion26_render_plugins_page() {
	$result = isset( $_GET['plugin_result'] ) ? sanitize_key( wp_unslash( $_GET['plugin_result'] ) ) : '';
	$messages = array( 'installed' => __( 'Plugin installé et activé.', 'orion26' ), 'activated' => __( 'Plugin activé.', 'orion26' ), 'failed' => __( 'L’installation n’a pas abouti. Vérifiez les droits d’écriture et réessayez.', 'orion26' ), 'disabled' => __( 'L’installation de fichiers est désactivée par la configuration WordPress.', 'orion26' ) );
	if ( isset( $messages[ $result ] ) ) {
		printf( '<div class="notice %1$s"><p>%2$s</p></div>', in_array( $result, array( 'failed', 'disabled' ), true ) ? 'notice-error' : 'notice-success', esc_html( $messages[ $result ] ) );
	}
	$file_mods_allowed = ( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS ) && current_user_can( 'install_plugins' );
	if ( ! $file_mods_allowed ) { echo '<div class="notice notice-warning"><p>' . esc_html__( 'Les installations sont bloquées par la politique de sécurité WordPress (DISALLOW_FILE_MODS). Les plugins déjà présents peuvent toujours être activés.', 'orion26' ) . '</p></div>'; }
	?>
	<div class="orion-plugin-grid">
	<?php foreach ( orion26_recommended_plugins() as $slug => $config ) : $plugin_file = orion26_find_plugin_file( $config ); $active = $plugin_file && is_plugin_active( $plugin_file ); ?>
		<section class="orion-plugin-card">
			<div><span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span><h2><?php echo esc_html( $config['name'] ); ?></h2></div>
			<p><?php echo esc_html( $config['description'] ); ?></p>
			<p><a href="<?php echo esc_url( $config['repository'] ); ?>" target="_blank" rel="noopener noreferrer external">GitHub ↗</a></p>
			<?php if ( $active ) : ?><span class="orion-plugin-status is-active"><?php esc_html_e( 'Installé et actif', 'orion26' ); ?></span><?php elseif ( ! $plugin_file && ! $file_mods_allowed ) : ?><button class="button" type="button" disabled><?php esc_html_e( 'Installation désactivée', 'orion26' ); ?></button><?php else : ?>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="orion26_install_plugin"><input type="hidden" name="plugin" value="<?php echo esc_attr( $slug ); ?>"><?php wp_nonce_field( 'orion26_install_plugin_' . $slug ); ?><button class="button button-primary" type="submit"><?php echo $plugin_file ? esc_html__( 'Activer', 'orion26' ) : esc_html__( 'Installer et activer', 'orion26' ); ?></button></form>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
	</div>
	<?php
}

function orion26_install_recommended_plugin() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Installation non autorisée.', 'orion26' ) ); }
	$slug = isset( $_POST['plugin'] ) ? sanitize_key( wp_unslash( $_POST['plugin'] ) ) : '';
	$plugins = orion26_recommended_plugins();
	if ( ! isset( $plugins[ $slug ] ) ) { wp_die( esc_html__( 'Plugin non autorisé.', 'orion26' ) ); }
	check_admin_referer( 'orion26_install_plugin_' . $slug );
	$config = $plugins[ $slug ];
	$plugin_file = orion26_find_plugin_file( $config );
	$result = 'failed';
	$was_installed = (bool) $plugin_file;
	if ( ! $plugin_file ) {
		if ( ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) || ! current_user_can( 'install_plugins' ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'orion26-plugins', 'plugin_result' => 'disabled' ), admin_url( 'admin.php' ) ) );
			exit;
		}
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		if ( true === $upgrader->install( $config['package'] ) ) { wp_clean_plugins_cache( true ); $plugin_file = orion26_find_plugin_file( $config ); }
	}
	if ( $plugin_file ) {
		if ( ! current_user_can( 'activate_plugins' ) ) { wp_die( esc_html__( 'Activation non autorisée.', 'orion26' ) ); }
		$activated = activate_plugin( $plugin_file, '', false, false );
		$result = ! is_wp_error( $activated ) && is_plugin_active( $plugin_file ) ? ( $was_installed ? 'activated' : 'installed' ) : 'failed';
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'orion26-plugins', 'plugin_result' => $result ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_orion26_install_plugin', 'orion26_install_recommended_plugin' );
