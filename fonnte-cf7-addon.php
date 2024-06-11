<?php
/*
Plugin Name: Contact Form 7 - Fonnte Add-on
Description: Fonnte addon for contact form 7
Author: Fonnte
Version: 1.3
Author URI: https://fonnte.com
*/

if ( !function_exists( 'add_action' ) ) {
	exit;
}

// constant
$version = '1.3';
define( 'FONNTE_CF7_ADDON_DIR', plugin_dir_path( __FILE__ ) );
define( 'FONNTE_CF7_ADDON_ENV', 'staging' );
if ( FONNTE_CF7_ADDON_ENV === 'staging' ) {
	$version = time();
}
define( 'FONNTE_CF7_ADDON_VERSION', $version );

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
		remove_action( 'template_redirect', 'dnd_cf7_auto_clean_dir' );
		include( FONNTE_CF7_ADDON_DIR . 'includes/cf7.fonnte.php' );
	}
}


add_action('init', 'fonnte_cf7_check_update');

function fonnte_cf7_check_update() {
	global $wp_version;
	global $pagenow;

	$plugin_name = "fonnte_cf7_addon";
	$this_file = __FILE__;

	if ( is_admin() and $pagenow == "plugins.php" ) {
		if( ! function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );
		$version = $plugin_data['Version'];

		$plugin_folder = plugin_basename( dirname( $this_file ) );
		$plugin_file = basename( ( $this_file ) );
		
		$response 	= wp_remote_get( "https://fonnte.com/plugin/update.php" );

		if (!is_array($response) && empty($response['body'])) {
			return false;
		}
		
		$parse		= json_decode($response['body'], true);

		if ( !isset($parse[$plugin_name]) ) {
			return;
		}

		$updateData = $parse[$plugin_name];
		
		if ( isset($updateData['Version']) and isset($updateData['Package']) ) {
			$new_version 	= $updateData['Version'];
			$package		= $updateData['Package'];
			
			if ( $version == $new_version ) {
				return;
			}

			$plugin_transient = get_site_transient('update_plugins');
			$a = array(
				'slug' => $plugin_folder,
				'new_version' => $new_version,
				'url' => $package,
				'package' => $package
			);

			$o = (object) $a;
			$plugin_transient->response[$plugin_folder.'/'.$plugin_file] = $o;
			set_site_transient('update_plugins', $plugin_transient);

		}
	}
}