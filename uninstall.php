<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
// If uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

// Delete options from options table
delete_option( 'fvb_general_settings' );


