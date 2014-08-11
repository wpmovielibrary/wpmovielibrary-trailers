<?php
/**
 * WPMovieLibrary-Trailers
 *
 * Add Trailers support to WPMovieLibrary
 *
 * @package   WPMovieLibrary-Trailers
 * @author    Charlie MERLAND <charlie@caercam.org>
 * @license   GPL-3.0
 * @link      http://www.caercam.org/
 * @copyright 2014 CaerCam.org
 *
 * @wordpress-plugin
 * Plugin Name: WPMovieLibrary-Trailers
 * Plugin URI:  http://wpmovielibrary.com
 * Description: Add Trailers support to WPMovieLibrary
 * Version:     1.0
 * Author:      Charlie MERLAND
 * Author URI:  http://www.caercam.org/
 * Text Domain: wpml-trailers
 * License:     GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * GitHub Plugin URI: https://github.com/Askelon/wpmovielibrary-trailers
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPMLLB_NAME',                   'WPMovieLibrary-Trailers' );
define( 'WPMLLB_VERSION',                '1.0' );
define( 'WPMLLB_SLUG',                   'wpml-trailers' );
define( 'WPMLLB_URL',                    plugins_url( basename( __DIR__ ) ) );
define( 'WPMLLB_PATH',                   plugin_dir_path( __FILE__ ) );
define( 'WPMLLB_REQUIRED_PHP_VERSION',   '5.3' );
define( 'WPMLLB_REQUIRED_WP_VERSION',    '3.6' );

/**
 * Checks if the system requirements are met
 * 
 * @since    1.0
 * 
 * @return   bool    True if system requirements are met, false if not
 */
function wpmllb_requirements_met() {

	global $wp_version;

	if ( version_compare( PHP_VERSION, WPMLLB_REQUIRED_PHP_VERSION, '<' ) )
		return false;

	if ( version_compare( $wp_version, WPMLLB_REQUIRED_WP_VERSION, '<' ) )
		return false;

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 * 
 * @since    1.0
 */
function wpmllb_requirements_error() {
	global $wp_version;

	require_once WPMLLB_PATH . '/views/requirements-error.php';
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the
 * plugin requirements are met. Otherwise older PHP installations could crash
 * when trying to parse it.
 */
if ( wpmllb_requirements_met() ) {

	require_once( WPMLLB_PATH . 'class-wpml-trailers.php' );

	if ( class_exists( 'WPMovieLibrary_Trailers' ) ) {
		$GLOBALS['wpmllb'] = new WPMovieLibrary_Trailers();
		register_activation_hook(   __FILE__, array( $GLOBALS['wpmllb'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['wpmllb'], 'deactivate' ) );
	}
}
else {
	
	
}
