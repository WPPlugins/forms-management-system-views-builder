<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
function fvb_get_option( $option, $section, $default = '' ) {

	$options = get_option( $section );

	if ( isset( $options[ $option ] ) ) {
		return $options[ $option ];
	}

	return $default;
}

function fvb_get_contact_forms() {
	$array    = array();
	$forms    = get_posts( array( 'post_type' => 'fms_contact_forms', 'numberposts' => - 1 ) );
	$array[0] = __( "None", 'fvb' );
	if ( $forms ) {
		foreach ( $forms as $form ) {
			$array[ $form->ID ] = $form->post_title;
		}
	}

	return $array;
}

function fvb_get_posting_forms() {
	$array = array();
	$forms = get_posts( array( 'post_type' => 'fms_forms', 'numberposts' => - 1, 'suppress_filters' => false ) );

	$array[0] = __( 'Select Form', 'fvb' );
	if ( $forms ) {
		foreach ( $forms as $form ) {
			$array[ $form->ID ] = $form->post_title;
		}
	}

	return $array;
}

function fvb_get_user_roles() {
	global $wp_roles;

	$roles                   = array();
	$roles['logged_in_only'] = "Logged In Users Only";

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$roles = array_merge( $roles, $wp_roles->get_names() );

	return $roles;
}

function FVB_CanAccess( $view_settings ) {

	$user      = wp_get_current_user();
	$canAccess = false;
	foreach ( $view_settings['roles'] as $role ) {
		if ( in_array( $role, $user->roles ) ) {
			$canAccess = true;
		}
	}

	if ( ( $canAccess == false ) && is_user_logged_in() && in_array( 'logged_in_only', $view_settings['roles'] ) ) {
		$canAccess = true;
	}

	return $canAccess;
}

function fvb_after_common_fields( $field_id, $label, $values ) {
	$tpl               = '%s[%d][%s]';
	$html_before_name  = sprintf( $tpl, 'fms_input', $field_id, 'html_before' );
	$html_after_name   = sprintf( $tpl, 'fms_input', $field_id, 'html_after' );
	$html_before_value = $values ? esc_attr( $values['html_before'] ) : '';
	$html_after_value  = $values ? esc_attr( $values['html_after'] ) : '';

	?>
	<div class="fms-form-rows">
		<label><?php esc_html_e( 'HTML Before', 'fvb' ); ?></label>
		<textarea name="<?php echo esc_attr( $html_before_name ); ?>" class="smallipopInput"
		          title="<?php esc_attr_e( 'Add additional HTML that will be before the default field HTML that will be produced using the Views Builder add-on.', 'fvb' ); ?>"><?php echo $html_before_value; ?></textarea>
	</div> <!-- .fms-form-rows -->
	<div class="fms-form-rows">
		<label><?php esc_html_e( 'HTML After', 'fvb' ); ?></label>
		<textarea name="<?php echo esc_attr( $html_after_name ); ?>" class="smallipopInput"
		          title="<?php esc_attr_e( 'Add additional HTML that will be after the default field HTML that will be produced using the Views Builder add-on.', 'fvb' ); ?>"><?php echo $html_after_value; ?></textarea>
	</div> <!-- .fms-form-rows -->
	<?php
}

add_action( 'fms_after_common_fields', 'fvb_after_common_fields', 10, 3 );

function fvb_wrap_field( $html, $field, $value, $view_settings ) {
	global $fvb_form_id, $fms_fields_setting;

	$html_before = '';
	$html_after  = '';

	foreach ( $fms_fields_setting as $field_setting ) {
		if ( $field['name'] != $field_setting['name'] ) {
			continue;
		}

		$html_before = $field_setting['html_before'];
		$html_after  = $field_setting['html_after'];
	}

	return $html_before . $html . $html_after;
}

add_filter( 'fvb_field_html', 'fvb_wrap_field', 10, 4 );

function fvb_field_conditional_logic_validation( $status, $field, $view_settings ) {
	global $fms_fields_setting, $fvb_post_meta;

	$form_field_data = array();

	foreach ( $fms_fields_setting as $item ) {
		if ( $item['name'] == $field['name'] ) {
			$form_field_data = $item;
			break;
		}
	}

	if ( $form_field_data['condition_status'] != 'yes' ) {
		return $status;
	}

	$new_post_meta = array();

	foreach ( $fvb_post_meta as $key => $meta ) {
		$meta  = is_serialized( $meta ) ? unserialize( $meta ) : $meta;
		$label = '';

		foreach ( $fms_fields_setting as $item ) {
			if ( $item['name'] == $key ) {
				$label = $item['label'];
				break;
			}
		}

		$new_post_meta[ $label ] = $meta;
	}

	if ( ! empty( $form_field_data ) && ! fms_is_field_visible( $form_field_data, $new_post_meta ) ) {
		return false;
	}

	return $status;
}

add_filter( 'fvb_is_valid_field', 'fvb_field_conditional_logic_validation', 10, 3 );