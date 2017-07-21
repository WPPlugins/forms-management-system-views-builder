<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
/*
  Plugin Name: Forms Management System Views Builder
  Plugin URI: http://mostasharoon.org
  Description: An easy way to display the custom fields at the frontend.
  Version: 1.2.0.3
  Author: Mohammed Thaer
  Author URI: http://mostasharoon.org
  Text Domain: fvb
 */

define( 'FVB_VERSION', '1.2.0.3' );

/* ----------------------------------------------------------------------------------- */
/* 	Includes required files.
/*----------------------------------------------------------------------------------- */

// Check if FMS is active. If not display warning message and don't do anything
add_action( 'plugins_loaded', 'fvb_fms_checker' );
function fvb_fms_checker() {
	if ( ! defined( 'FMS_VERSION' ) ) {
		add_action( 'admin_notices', 'fvb_no_fms_warning' );

		return false;
		//Check if FMS is old
	} elseif ( version_compare( FMS_VERSION, '3.0', '<' ) ) {
		add_action( 'admin_notices', 'fvb_old_fms_warning' );

		return false;
	}

	return true;
}

function fvb_no_fms_warning() {
	?>
	<div class="message error">
		<p><?php printf( __( 'FMS Views Builder is enabled but not effective. It requires <a href="%s">FMS</a> in order to work.', 'fvb' ),
				'https://mostasharoon.org/wordpress/plugins/forms-management-system/' ); ?></p></div>
	<?php
}

function fvb_old_fms_warning() {
	?>
	<div class="message error">
		<p><?php printf( __( 'FMS Views Builder is enabled but not effective. It is not compatible with  <a href="%s">FMS</a> versions prior 3.0.', 'fvb' ),
				'https://mostasharoon.org/wordpress/plugins/forms-management-system/' ); ?></p></div>
	<?php
}

// Dir to the plugin
define( 'FVB_DIR', plugin_dir_path( __FILE__ ) );
// URL to the plugin
define( 'FVB_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'fvb_load_translation' );

/**
 *Loads a translation files.
 */
function fvb_load_translation() {
	load_plugin_textdomain( 'fvb', false, 'fms-custom-fields-restrictor/languages' );
}


require_once( 'includes/fvb-functions.php' );
require_once( 'classes/admin/FVB_Settings.php' );
require_once( 'classes/frontend/FVB_Core.php' );
require_once( 'classes/admin/FVB_View_Post_Type.php' );
