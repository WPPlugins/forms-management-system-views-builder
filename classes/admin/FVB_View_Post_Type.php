<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class FVB_Views {

	function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'save_post', array( $this, 'save_custom_meta' ) );
		add_action( 'init', array( $this, 'fvb_views' ), 15 );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ) );
		add_action( 'wp_ajax_fvb_get_form_fields', array( $this, 'get_fields' ) );
	}

	function get_fields() {
		//Security Check
		$nonce = $_REQUEST['fvb_views_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'fvb_views_nonce' ) ) {
			wp_die( 'Security check' );
		}

		// check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Security check' );
		}

		$form_id            = (int) $_REQUEST['fvb_form_id'];
		$fms_fields_setting = get_post_meta( $form_id, 'fms_form', true );
		echo $this->get_the_fields_table( $fms_fields_setting );
		exit;
	}

	// Register Custom Post Type
	function fvb_views() {
		$capability = 'manage_options';
		$labels     = array(
			'name'               => _x( 'Views', 'Post Type General Name', 'fvb' ),
			'singular_name'      => _x( 'View', 'Post Type Singular Name', 'fvb' ),
			'menu_name'          => __( 'Views', 'fvb' ),
			'parent_item_colon'  => __( 'Parent View:', 'fvb' ),
			'view_item'          => __( 'View View', 'fvb' ),
			'add_new_item'       => __( 'Add New View', 'fvb' ),
			'add_new'            => __( 'Add New', 'fvb' ),
			'edit_item'          => __( 'Edit View', 'fvb' ),
			'update_item'        => __( 'Update View', 'fvb' ),
			'search_items'       => __( 'Search View', 'fvb' ),
			'not_found'          => __( 'Not found', 'fvb' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'fvb' ),
		);
		$args       = array(
			'labels'          => $labels,
			'supports'        => array( 'title' ),
			'hierarchical'    => false,
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'fms_post_types',//or string
			'rewrite'         => array( 'slug' => 'fvb_view' ),
			'can_export'      => true,
			'capability_type' => 'post',
			'capabilities'    => array(
				'publish_posts'       => $capability,
				'edit_posts'          => $capability,
				'edit_others_posts'   => $capability,
				'delete_posts'        => $capability,
				'delete_others_posts' => $capability,
				'read_private_posts'  => $capability,
				'edit_post'           => $capability,
				'delete_post'         => $capability,
				'read_post'           => $capability,
			)
		);
		register_post_type( 'fvb_views', $args );
	}

//	function admin_column( $columns ) {
//		$columns['view_code'] = __( 'View Code', 'fvb' );
//		$columns['start_date']  = __( 'Start Date', 'fvb' );
//		$columns['expiration']  = __( 'Expiration', 'fvb' );
//		$columns['status']      = __( 'Status', 'fvb' );
//		$columns['max_users']   = __( 'Max Uses', 'fvb' );
//
//		return $columns;
//	}

//	function admin_column_value( $column_name, $post_id ) {
//		$meta = get_post_meta( $post_id, 'fvb_view', true );
//		switch ( $column_name ) {
//			case 'view_code':
//				echo $meta['fvb_view_code'];
//				break;
//
//			case 'start_date':
//				echo $meta['fvb_start_date'];
//				break;
//
//			case 'expiration':
//				echo $meta['fvb_expiration_date'];
//				break;
//
//			case 'max_users':
//				echo empty( $meta['fvb_max_users'] ) ? __('Unlimited','fvb') : $meta['fvb_max_users'];
//				break;
//
//			case 'status':
//				echo ucfirst($meta['fvb_status']);
//				break;
//		}
//	}

	function scripts() {
		global $pagenow, $post;
		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		if ( ! in_array( $post->post_type, array( 'fvb_views' ) ) ) {
			return;
		}

		wp_enqueue_style( 'fvb-bootstrap', FVB_URL . 'lib/bootstrap/css/bootstrap.min.css' );
		wp_enqueue_style( 'fvb-bootstrap-theme', FVB_URL . 'lib/bootstrap/css/bootstrap-theme.min.css', array(
			'fvb-bootstrap'
		) );
		wp_enqueue_script( 'fvb-bootstrap-js', FVB_URL . 'lib/bootstrap/js/bootstrap.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'fvb-views-post-type-js', FVB_URL . 'js/admin/Views_Post_Type.js', array(
			'jquery',
			'fvb-bootstrap-js'
		) );


		wp_enqueue_style( 'fvb-views-post-type-css', FVB_URL . 'css/admin/Views_Post_Type.css', array(
			'fvb-bootstrap-theme'
		) );
//		wp_enqueue_style( 'fvb-datetimepicker-css', FVB_URL . 'lib/datetimepicker-master/jquery.datetimepicker.css' );
//		wp_enqueue_script( 'fvb-datetimepicker-js', FVB_URL . 'lib/datetimepicker-master/jquery.datetimepicker.js', array( 'jquery' ) );

//		wp_enqueue_script( 'fvb-views-js', FVB_URL . 'js/admin/fvb-views.js', array(
//			'fvb-datetimepicker-js'
//		) );
	}

	function fields() {
		$fields = array(
			array(
				'label'       => __( 'Form', 'fvb' ),
				'name'        => 'fvb_form',
				'type'        => 'custom',
				'custom_type' => 'form_id',
				'options'     => fvb_get_posting_forms()
			),
			array(
				'label' => 'Custom Fields Section Title',
				'name'  => 'fvb_settings[title]',
				'type'  => 'text',
				'help'  => __( 'Leave it empty to disable the title.', 'fvb' )
			),
			array(
				'label' => 'Wrapper Class',
				'name'  => 'fvb_settings[wrapper_class]',
				'type'  => 'text',
				'help'  => __( 'You can inject your own class in the wrapper div.', 'fvb' )
			),
			array(
				'label' => __( 'Restrict Custom Fields Access?', 'fvb' ),
				'name'  => 'fvb_settings[restrict]',
				'type'  => 'checkbox',
				'help'  => ''
			),
			array(
				'label'   => 'Allow Access To',
				'name'    => 'fvb_settings[roles]',
				'options' => fvb_get_user_roles(),
				'type'    => 'multi_select',
				'help'    => __( '', 'fvb' )
			),
			array(
				'label' => 'Restriction Message',
				'name'  => 'fvb_settings[restriction_message]',
				'type'  => 'textarea',
				'help'  => __( '', 'mdm' )
			),
			array(
				'label'       => __( 'Fields', 'fvb' ),
				'name'        => 'fvb_settings[fields]',
				'type'        => 'custom',
				'custom_type' => 'fields_table'
			)
		);

		return $fields;
	}

	function form_id( $field, $val ) {
		global $post;
		$values = get_post_meta( $post->ID, 'fvb_form', true );

		$html = '<div class="form-group">';
		$html .= '<label for="' . $field['name'] . '" class="col-sm-4 control-label">' . $field['label'] . '</label>';
		$html .= '<div class="col-sm-8">';
		$html .= '<select name="' . $field['name'] . '" id="' . $field['name'] . '" class="form-control">';
		foreach ( $field['options'] as $id => $title ) {
			$html .= '<option ' . selected( $values, (int) $id, false ) . ' value="' . $id . '">' . $title . '</option>';
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	// Add the Meta Box
	function add_custom_meta_box() {
		add_meta_box(
			'fvb', // $id
			'View Settings', // $title
			array( $this, 'show_fvb' ), // $callback
			'fvb_views', // $page
			'normal', // $context
			'high' ); // $priority
	}

	// Save the Data
	function save_custom_meta( $post_id ) {

		// verify nonce
		if ( ( ! isset( $_REQUEST['fvb_views_nonce_field'] ) ) || ( ! wp_verify_nonce( $_POST['fvb_views_nonce_field'], 'fvb_views_nonce' ) ) ) {
			return $post_id;
		}
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		// check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		array_walk_recursive( $_POST['fvb_settings'], array( $this, 'special_sanitize' ) );
		update_post_meta( $post_id, 'fvb_settings', $_POST['fvb_settings'] );
		update_post_meta( $post_id, 'fvb_form', (int) $_POST['fvb_form'] );

		return $post_id;
	}

	function special_sanitize( &$item, $key ) {
		$item = wp_kses_post( $item );
	}

	//show the custom fields
	function show_fvb() {
		?>
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div id="fvb-user-settings">
						<form id="form-horizontal" class="fvb-settings-form" role="form">
							<span class="fvb-loading" style="display: none">
								<img src="<?php echo FVB_URL . 'images/loading.GIF' ?>">
							</span>
							<?php echo $this->field_renderer( $this->fields() ); ?>
							<?php wp_nonce_field( 'fvb_views_nonce', 'fvb_views_nonce_field' ); ?>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	function field_renderer( $fields ) {
		global $post;
		$values = get_post_meta( $post->ID, 'fvb_settings', true );
		$html   = '';
		foreach ( $fields as $field ) {
			//Capturing text between square brackets
			preg_match( "/\[(.*?)\]/", $field['name'], $matches );
//			$val  = fvb_get_option( $matches[1], $settings_section, '' );
			$val  = $values[ $matches[1] ];
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
						$selected = in_array( $id, $val ) ? 'selected="selected"' : '';
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

	function fields_table( $field, $val ) {

		$html = '<div class="fvb_fields_area">';
//		if ( ! empty( $val ) ) {
		if ( ! empty( $val ) ) {
			//get the form id
			$form_id            = $val['form'];
			$fms_fields_setting = get_post_meta( $form_id, 'fms_form', true );
			$html .= $this->get_the_fields_table( $fms_fields_setting, $val );
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * @param $val
	 *
	 * @return string
	 */
	public function get_the_fields_table( $fms_fields_setting, $val = '' ) {

		$correct_fields_order = array();
		for ($x = 1; $x <= 6; $x++) {
			foreach ( $fms_fields_setting as $index => $field ) {
				if( !isset($field['step']) ){
					$correct_fields_order[$index] = $field;
					continue;
				}

				if((int)$field['step']==$x){
					$correct_fields_order[]=$field;
				}
			}
		}

		$fms_fields_setting = $correct_fields_order;

		$html = '<table class="fvb_fields_table table table-bordered">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . __( "Move", 'fvb' ) . '</th>';
		$html .= '<th>' . __( "Field Label", 'fvb' ) . '</th>';
		$html .= '<th>' . __( "Meta Key", 'fvb' ) . '</th>';
		$html .= '<th>' . __( "New Label", 'fvb' ) . '</th>';
		$html .= '<th>' . __( "Remove", 'fvb' ) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		if ( ! empty( $val ) ) {
			if ( is_array( $val ) ) {
				foreach ( $val as $id => $details ) {
					$html .= '<tr class="fvb_repeatable_row" data-index="' . $id . '">';
					$html .= '<th scope="row"><span class="fvb_draghandle"></span></th>';
					$html .= '<td><code>' . $details['label'] . '</code><input type="hidden" name="fvb_settings[fields][' . $id . '][label]" value="' . $details['label'] . '"></td>';
					$html .= '<td><code>' . $details['name'] . '</code><input type="hidden" name="fvb_settings[fields][' . $id . '][name]" value="' . $details['name'] . '"><input type="hidden" name="fvb_settings[fields][' . $id . '][template]" value="' . $details['template'] . '"></td>';
					$html .= '<td><input type="text" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . __( 'Leave it empty for no label.', 'fvb' ) . '" name="fvb_settings[fields][' . $id . '][new_label]" value="' . $details['new_label'] . '" size="20" /></td>';
					$html .= '<td><a href="#" class="fvb_remove_repeatable" style="background: url(' . FVB_URL . 'images/xit.gif) no-repeat;">×</a></td>';
					$html .= '</tr>';
				}
			}
		} else {
			$useless_fields = array(
				'recaptcha',
				'really_simple_captcha',
				'fms_message',
				'toc',
				'google_map',
				'featured_image',
				'post_title',
				'post_content',
				'taxonomy',
				'post_tags',
				'post_excerpt'
			);
			if ( is_array( $fms_fields_setting ) ) {
				$index = 1;
				foreach ( $fms_fields_setting as $field ) {
					if ( in_array( $field['template'], $useless_fields ) ) {
						continue;
					}

					$html .= '<tr class="fvb_repeatable_row" data-index="' . $index . '">';
					$html .= '<th scope="row"><span class="fvb_draghandle"></span></th>';
					$html .= '<td><code>' . $field['label'] . '</code><input type="hidden" name="fvb_settings[fields][' . $index . '][label]" value="' . $field['label'] . '"></td>';
					$html .= '<td><code>' . $field['name'] . '</code><input type="hidden" name="fvb_settings[fields][' . $index . '][name]" value="' . $field['name'] . '"><input type="hidden" name="fvb_settings[fields][' . $index . '][template]" value="' . $field['template'] . '"></td>';
					$html .= '<td><input type="text" data-toggle="popover" data-placement="bottom" data-trigger="focus" data-content="' . __( 'Leave it empty for no label.', 'fvb' ) . '" name="fvb_settings[fields][' . $index . '][new_label]" value="' . $val['new_label'] . '" size="20" /></td>';
					$html .= '<td><a href="#" class="fvb_remove_repeatable" style="background: url(' . FVB_URL . 'images/xit.gif) no-repeat;">×</a></td>';
					$html .= '</tr>';

					$index ++;
				}
			}
		}

		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<p class="bg-info">' . __( '* Please note if you changed the form meta keys later, you must modify this view in order to reflect that change.', 'fvb' ) . '</p>';

		return $html;
	}
}

$fvb_views = new FVB_Views();
