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

define( 'WPMLTR_NAME',                   'WPMovieLibrary-Trailers' );
define( 'WPMLTR_VERSION',                '1.0' );
define( 'WPMLTR_SLUG',                   'wpml-trailers' );
define( 'WPMLTR_URL',                    plugins_url( basename( __DIR__ ) ) );
define( 'WPMLTR_PATH',                   plugin_dir_path( __FILE__ ) );
define( 'WPMLTR_REQUIRED_PHP_VERSION',   '5.3' );
define( 'WPMLTR_REQUIRED_WP_VERSION',    '3.6' );
define( 'WPMLTR_REQUIRED_WPML_VERSION',  '1.2' );


/**
 * Determine whether WPML is active or not.
 *
 * @since    1.0
 *
 * @return   boolean
 */
function is_wpml_active() {

	return defined( 'WPML_VERSION' );
}

/**
 * Checks if the system requirements are met
 * 
 * @since    1.0
 * 
 * @return   bool    True if system requirements are met, false if not
 */
function wpmltr_requirements_met() {

	global $wp_version;

	if ( version_compare( PHP_VERSION, WPMLTR_REQUIRED_PHP_VERSION, '<=' ) )
		return false;

	if ( version_compare( $wp_version, WPMLTR_REQUIRED_WP_VERSION, '<=' ) )
		return false;

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 * 
 * @since    1.0
 */
function wpmltr_requirements_error() {

	global $wp_version;

	require_once WPMLTR_PATH . '/views/requirements-error.php';
}

/**
 * Prints an error that the system requirements weren't met.
 * 
 * @since    1.0.1
 */
function wpmltr_l10n() {

	$domain = 'wpmovielibrary-trailers';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	load_textdomain( $domain, WPMLTR_PATH . 'languages/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, FALSE, basename( __DIR__ ) . '/languages/' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the
 * plugin requirements are met. Otherwise older PHP installations could crash
 * when trying to parse it.
 */
if ( wpmltr_requirements_met() ) {

	require_once( WPMLTR_PATH . 'includes/class-module.php' );
	require_once( WPMLTR_PATH . 'class-wpml-trailers.php' );

	if ( class_exists( 'WPMovieLibrary_Trailers' ) ) {
		$GLOBALS['wpmltr'] = new WPMovieLibrary_Trailers();
		register_activation_hook(   __FILE__, array( $GLOBALS['wpmltr'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['wpmltr'], 'deactivate' ) );
	}

	WPMovieLibrary_Trailers::require_wpml_first();

	if ( is_admin() ) {
		require_once( WPMLTR_PATH . 'admin/class-wpmltr-api.php' );
		require_once( WPMLTR_PATH . 'admin/class-wpmltr-api-wrapper.php' );
		require_once( WPMLTR_PATH . 'admin/class-wpmltr-allocine.php' );
	}
}
else {
	add_action( 'init', 'wpmltr_l10n' );
	add_action( 'admin_notices', 'wpmltr_requirements_error' );
}
