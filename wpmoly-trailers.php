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
 * Plugin URI:  http://wpmovielibrary.com/extensions/wpmovielibrary-trailers/
 * Description: Add Trailers support to WPMovieLibrary
 * Version:     2.0
 * Author:      Charlie MERLAND
 * Author URI:  http://www.caercam.org/
 * Text Domain: wpml-trailers
 * License:     GPL-3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/CaerCam/wpmovielibrary-trailers
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPMOLYTR_NAME',                    'WPMovieLibrary-Trailers' );
define( 'WPMOLYTR_VERSION',                 '2.0' );
define( 'WPMOLYTR_SLUG',                    'wpmoly-trailers' );
define( 'WPMOLYTR_URL',                     plugins_url( basename( __DIR__ ) ) );
define( 'WPMOLYTR_PATH',                    plugin_dir_path( __FILE__ ) );
define( 'WPMOLYTR_REQUIRED_PHP_VERSION',    '5.4' );
define( 'WPMOLYTR_REQUIRED_WP_VERSION',     '3.8' );
define( 'WPMOLYTR_REQUIRED_WPMOLY_VERSION', '2.0' );

/**
 * Determine whether WPMOLY is active or not.
 *
 * @since    1.0
 *
 * @return   boolean
 */
if ( ! function_exists( 'is_wpmoly_active' ) ) :
	function is_wpmoly_active() {

		return defined( 'WPMOLYVERSION' );
	}
endif;

/**
 * Checks if the system requirements are met
 * 
 * @since    1.0
 * 
 * @return   bool    True if system requirements are met, false if not
 */
function wpmolytr_requirements_met() {

	global $wp_version;

	if ( version_compare( PHP_VERSION, WPMOLYTR_REQUIRED_PHP_VERSION, '<=' ) )
		return false;

	if ( version_compare( $wp_version, WPMOLYTR_REQUIRED_WP_VERSION, '<=' ) )
		return false;

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 * 
 * @since    1.0
 */
function wpmolytr_requirements_error() {

	global $wp_version;

	require_once WPMOLYTR_PATH . '/views/requirements-error.php';
}

/**
 * Prints an error that the system requirements weren't met.
 * 
 * @since    1.0.1
 */
function wpmolytr_l10n() {

	$domain = 'wpmovielibrary-trailers';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	load_textdomain( $domain, WPMOLYTR_PATH . 'languages/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, FALSE, basename( __DIR__ ) . '/languages/' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the
 * plugin requirements are met. Otherwise older PHP installations could crash
 * when trying to parse it.
 */
if ( wpmolytr_requirements_met() ) {

	require_once( WPMOLYTR_PATH . 'includes/class-module.php' );
	require_once( WPMOLYTR_PATH . 'class-wpmoly-trailers.php' );

	if ( class_exists( 'WPMovieLibrary_Trailers' ) ) {
		$GLOBALS['wpmolytr'] = new WPMovieLibrary_Trailers();
		register_activation_hook(   __FILE__, array( $GLOBALS['wpmolytr'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['wpmolytr'], 'deactivate' ) );
	}

	WPMovieLibrary_Trailers::require_wpmoly_first();

	if ( is_admin() ) {
		require_once( WPMOLYTR_PATH . 'admin/class-wpmoly-trailers-api.php' );
		require_once( WPMOLYTR_PATH . 'admin/class-wpmoly-trailers-api-wrapper.php' );
		require_once( WPMOLYTR_PATH . 'admin/class-wpmoly-trailers-allocine.php' );
	}
}
else {
	add_action( 'init', 'wpmolytr_l10n' );
	add_action( 'admin_notices', 'wpmolytr_requirements_error' );
}
