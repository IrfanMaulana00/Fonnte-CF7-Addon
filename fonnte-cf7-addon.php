<?php
/*
Plugin Name: Contact Form 7 - Fonnte Add-on
Description: Fontte addon for contact form 7
Author: Fonnte
Version: 1.1.0
Author URI: https://fonnte.com
*/

if ( !function_exists( 'add_action' ) ) {
	exit;
}

// constant
$version = '1.0.0';
define( 'FONTTE_CF7_ADDON_DIR', plugin_dir_path( __FILE__ ) );
define( 'FONTTE_CF7_ADDON_ENV', 'staging' );
if ( FONTTE_CF7_ADDON_ENV === 'staging' ) {
	$version = time();
}
define( 'FONTTE_CF7_ADDON_VERSION', $version );

/**
 * Hooks
 */
add_action( 'plugins_loaded', 'fonnte_cf7_addon' ); 

/**
 * [fonntecf7_addon description]
 * @return [type] [description]
 */
function fonnte_cf7_addon() {
	if ( class_exists( 'WPCF7_ContactForm' ) ) {
		include( FONTTE_CF7_ADDON_DIR . 'includes/cf7.fonnte.php' );
	}
}