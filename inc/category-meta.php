<?php
/** Métadonnées natives des rubriques, sans dépendance à ACF. @package Orion26 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function orion26_category_meta_fields() {
	return array(
		'discipline_long_description' => array( 'label' => __( 'Présentation détaillée', 'orion26' ), 'type' => 'textarea' ),
		'discipline_official_url'     => array( 'label' => __( 'Site officiel', 'orion26' ), 'type' => 'url' ),
		'discipline_facebook_url'     => array( 'label' => 'Facebook', 'type' => 'url' ),
		'discipline_instagram_url'    => array( 'label' => 'Instagram', 'type' => 'url' ),
		'discipline_youtube_url'      => array( 'label' => 'YouTube', 'type' => 'url' ),
	);
}

function orion26_category_add_fields() {
	wp_nonce_field( 'orion26_save_category_meta', 'orion26_category_nonce' );
	foreach ( orion26_category_meta_fields() as $key => $field ) : ?>
		<div class="form-field"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label><?php if ( 'textarea' === $field['type'] ) : ?><textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" rows="7"></textarea><?php else : ?><input type="url" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"><?php endif; ?></div>
	<?php endforeach;
}
add_action( 'category_add_form_fields', 'orion26_category_add_fields' );

function orion26_category_edit_fields( $term ) {
	wp_nonce_field( 'orion26_save_category_meta', 'orion26_category_nonce' );
	foreach ( orion26_category_meta_fields() as $key => $field ) : $value = get_term_meta( $term->term_id, $key, true ); ?>
		<tr class="form-field"><th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th><td><?php if ( 'textarea' === $field['type'] ) : ?><textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" rows="10" class="large-text"><?php echo esc_textarea( $value ); ?></textarea><?php else : ?><input type="url" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text"><?php endif; ?></td></tr>
	<?php endforeach;
}
add_action( 'category_edit_form_fields', 'orion26_category_edit_fields' );

function orion26_save_category_meta( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) || empty( $_POST['orion26_category_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['orion26_category_nonce'] ) ), 'orion26_save_category_meta' ) ) { return; }
	foreach ( orion26_category_meta_fields() as $key => $field ) {
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$value = 'url' === $field['type'] ? esc_url_raw( $value ) : wp_kses_post( $value );
		if ( '' === $value ) { delete_term_meta( $term_id, $key ); } else { update_term_meta( $term_id, $key, $value ); }
	}
}
add_action( 'created_category', 'orion26_save_category_meta' );
add_action( 'edited_category', 'orion26_save_category_meta' );
