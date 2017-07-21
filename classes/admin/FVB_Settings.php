<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class FVB_Settings {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
		add_action( 'wp_ajax_fvb_save_settings', array( $this, 'fvb_save_settings' ) );
	}

	function admin_menu() {
		$fvb_settings_page_hook_suffix = add_submenu_page( 'fms_post_types', 'Views Settings', 'Views Settings', 'manage_options', 'fvb_settings', array(
			$this,
			'page'
		), null );

		add_action( 'admin_print_scripts-' . $fvb_settings_page_hook_suffix, array(
			$this,
			'admin_scripts'
		) );
	}

	function general_fields() {
		$fields = array(
			array(
				'label' => 'Disable Frontend Styles',
				'name'  => 'fvb_general_settings[disable_styles]',
				'type'  => 'checkbox'
			)
//			array(
//				'label' => 'Disable Frontend Scripts',
//				'name'  => 'fvb_general_settings[disable_scripts]',
//				'type'  => 'checkbox'
//			)
		);

		return $fields;
	}

	function page() {
		?>
		<div class="container">
			<div class="row">
				<h3><?php esc_html_e( 'FMS Views Builder Settings', 'fvb' ) ?></h3>
			</div>
			<div class="row">
				<div class="col-xs-8">
					<div id="fvb-user-settings">
						<form id="form-horizontal" class="fvb-settings-form" role="form">
							<?php echo $this->field_renderer( $this->general_fields(), 'fvb_general_settings' ); ?>
							<?php wp_nonce_field( 'fvb_save_settings_nonce', 'fvb_save_settings_nonce_field' ); ?>
						</form>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="well">
						<button type="button" id="fvb-save-settings" class="btn btn-primary"><span
								class="glyphicon glyphicon-floppy-disk"
								aria-hidden="true"></span> <?php _e( 'Save All Changes', 'fvb' ) ?>
						</button>
						<span class="fvb-saving" style="display: none">
								<img
									src="<?php echo FVB_URL . 'images/loading.GIF' ?>"> <?php _e( 'Saving ...', 'fvb' ) ?>
						</span>
						<span class="fvb-saved" style="display: none"><span class="glyphicon glyphicon-floppy-saved"
						                                                    aria-hidden="true"></span> <?php _e( 'Saved successfully!', 'fvb' ) ?></span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	function admin_scripts() {
		wp_enqueue_style( 'fvb-bootstrap', FVB_URL . 'lib/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_style( 'fvb-bootstrap-theme', FVB_URL . 'lib/bootstrap/css/bootstrap-theme.min.css', array(
			'fvb-bootstrap'
		) );
		wp_enqueue_script( 'fvb-bootstrap-js', FVB_URL . 'lib/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );

//		wp_enqueue_style( 'chosen-jquery-css', MOSTA_INSERT_URL . 'lib/chosen_v1.2.0/chosen.min.css' );
//		wp_enqueue_script( 'chosen-jquery-js', MOSTA_INSERT_URL . 'lib/chosen_v1.2.0/chosen.jquery.min.js', array( 'jquery' ) );

		wp_enqueue_script( 'fvb-settings-js', FVB_URL . 'js/admin/settings.js', array( 'fvb-bootstrap-js' ) );
		wp_enqueue_style( 'fvb-settings-css', FVB_URL . 'css/admin/settings.css', array(
			'fvb-bootstrap-theme'
		) );
	}

	function field_renderer( $fields, $settings_section ) {
		$html = '';
		foreach ( $fields as $field ) {
			//Capturing text between square brackets
			preg_match( "/\[(.*?)\]/", $field['name'], $matches );
			$val  = fvb_get_option( $matches[1], $settings_section, '' );
			$help = isset( $field['help'] ) ? $field['help'] : '';
			switch ( $field['type'] ) {
				case 'password':
					$html .= '<div class="form-group">';
					$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';
					$html .= '<input type="password" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" name="' . $field['name'] . '" value="' . $val . '" class="form-control" id="' . $field['name'] . '" placeholder="">';
					$html .= '</div>';
					$html .= '</div>';
					break;

				case 'text':
					$html .= '<div class="form-group">';
					$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';

					$html .= '<input type="text" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" name="' . $field['name'] . '" value="' . $val . '" class="form-control" id="' . $field['name'] . '" placeholder="">';
					$html .= '</div>';
					$html .= '</div>';
					break;

				case 'textarea':
					$html .= '<div class="form-group">';
					$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';
					$html .= '<textarea name="' . $field['name'] . '"  data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" id="' . $field['name'] . '" placeholder="" class="form-control" rows="10">' . $val . '</textarea>';
					$html .= '</div>';
					$html .= '</div>';

					break;

				case 'email':
					$html .= '<div class="form-group">';
					$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';
					$html .= '<input type="email" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" name="' . $field['name'] . '" value="' . $val . '" class="form-control" id="' . $field['name'] . '" placeholder="">';
					$html .= '</div>';
					$html .= '</div>';
					break;

				case 'select':
					$html .= '<div class="form-group">';
					$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';
					$html .= '<select  data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" name="' . $field['name'] . '" id="' . $field['name'] . '" class="form-control">';
					foreach ( $field['options'] as $id => $title ) {
						$html .= '<option ' . selected( $val, $id, false ) . ' value="' . $id . '">' . $title . '</option>';
					}
					$html .= '</select>';
					$html .= '</div>';
					$html .= '</div>';
					break;

				case 'multi_select':
					$html .= '<div class="form-group">';
					$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';
					$html .= '<select  data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" name="' . $field['name'] . '[]" id="' . $field['name'] . '" class="form-control" multiple>';
					foreach ( $field['options'] as $id => $title ) {

						$selected = '';
						foreach ( $val as $single_val ) {
							if ( ! empty( $selected ) ) {
								continue;
							}
							$selected = selected( (int) $id, (int) $single_val, false );
						}

						$html .= '<option ' . $selected . ' value="' . $id . '">' . $title . '</option>';
					}
					$html .= '</select>';
					$html .= '</div>';
					$html .= '</div>';
					break;

				case 'radio':
					$html .= '<div class="form-group">';
					$html .= '<label class="col-sm-4 control-label">' . $field['label'] . '</label>';
					$html .= '<div class="col-sm-8">';

					foreach ( $field['options'] as $id => $title ) {
						$html .= '<div class="radio">';
						$html .= '<label>';
						$html .= '<input data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" type="radio" name="' . $field['name'] . '" value="' . $id . '" ' . checked( $val, $id, false ) . '>';
						$html .= $title;
						$html .= '</label>';
						$html .= '</div>';
					}
					$html .= '</div>';
					$html .= '</div>';
					break;
				case 'checkbox':
					$html .= '<div class="form-group">';
					$html .= '<div class="col-sm-offset-4 col-sm-8">';
					$html .= '<div class="checkbox">';
					$html .= '<label>';
					$html .= '<input ' . checked( $val, 'yes', false ) . ' type="checkbox" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . $help . '" value="yes" name="' . $field['name'] . '"> ' . $field['label'];
					$html .= '</label>';
					$html .= '</div>';
					$html .= '</div>';
					$html .= '</div>';
					break;
				case 'custom':
					//Incompatible with PHP7
//					$html .= $this->$field['custom_type']( $field, $val );
					$html .= call_user_func_array( array( $this, $field['custom_type'] ), array( $field, $val ) );
					break;
			}
		}

		return $html;
	}

	function fvb_save_settings() {
		if ( isset( $_POST['fvb_save_settings_nonce_field'] ) && wp_verify_nonce( $_POST['fvb_save_settings_nonce_field'], 'fvb_save_settings_nonce' ) ) {
			//because $_POST['fvb_general_settings'] are multidimentional array we will use this method to sanitize it from http://stackoverflow.com/questions/4085623/array-map-for-multidimensional-arrays
			array_walk_recursive( $_POST['fvb_general_settings'], array( $this, 'special_sanitize' ) );
			update_option( 'fvb_general_settings', $_POST['fvb_general_settings'] );
		}
		exit;
	}

	function special_sanitize( &$item, $key ) {
		$item = sanitize_text_field( $item );
	}
}

$FVB_Settings = new FVB_Settings();