<?php
/**
 * WPMovieLibrary-LightBox
 *
 * @package   WPMovieLibrary-LightBox
 * @author    Charlie MERLAND <charlie@caercam.org>
 * @license   GPL-3.0
 * @link      http://www.caercam.org/
 * @copyright 2014 Charlie MERLAND
 */

if ( ! class_exists( 'WPMovieLibrary_LightBox' ) ) :

	/**
	* Plugin class
	*
	* @package WPMovieLibrary-LightBox
	* @author  Charlie MERLAND <charlie@caercam.org>
	*/
	class WPMovieLibrary_LightBox {

		/**
		 * Initialize the plugin by setting localization and loading public scripts
		 * and styles.
		 *
		 * @since     1.0
		 */
		public function __construct() {

			$this->register_hook_callbacks();
		}

		/**
		 * Register callbacks for actions and filters
		 * 
		 * @since    1.0
		 */
		public function register_hook_callbacks() {

			// Enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			//add_filters( '', array( $this, 'add_pposter_lightbox' ) );
		}

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                     Plugin  Activate/Deactivate
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Fired when the plugin is activated.
		 * 
		 * Restore previously converted contents. If WPML was previously
		 * deactivated or uninstalled using the 'convert' option, Movies and
		 * Custom Taxonomies should still be in the database. If they are, we
		 * convert them back to WPML contents.
		 * 
		 * Call Movie Custom Post Type and Collections, Genres and Actors custom
		 * Taxonomies' registering functions and flush rewrite rules to update
		 * the permalinks.
		 *
		 * @since    1.0
		 *
		 * @param    boolean    $network_wide    True if WPMU superadmin uses
		 *                                       "Network Activate" action, false if
		 *                                       WPMU is disabled or plugin is
		 *                                       activated on an individual blog.
		 */
		public function activate( $network_wide ) {

			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( $network_wide ) {
					$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

					foreach ( $blogs as $blog ) {
						switch_to_blog( $blog );
						$this->single_activate( $network_wide );
					}

					restore_current_blog();
				} else {
					$this->single_activate( $network_wide );
				}
			} else {
				$this->single_activate( $network_wide );
			}

		}

		/**
		 * Fired when the plugin is deactivated.
		 * 
		 * When deactivatin/uninstalling WPML, adopt different behaviors depending
		 * on user options. Movies and Taxonomies can be kept as they are,
		 * converted to WordPress standars or removed. Default is conserve on
		 * deactivation, convert on uninstall.
		 *
		 * @since    1.0
		 */
		public function deactivate() {
		}

		/**
		 * Runs activation code on a new WPMS site when it's created
		 *
		 * @since    1.0
		 *
		 * @param    int    $blog_id
		 */
		public function activate_new_site( $blog_id ) {
			switch_to_blog( $blog_id );
			$this->single_activate( true );
			restore_current_blog();
		}

		/**
		 * Prepares a single blog to use the plugin
		 *
		 * @since    1.0
		 *
		 * @param    bool    $network_wide
		 */
		protected function single_activate( $network_wide ) {
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0
		 */
		public function enqueue_styles() {

			wp_enqueue_style( WPMLLB_SLUG . '-lightbox', WPMLLB_URL . '/vendor/css/lightbox.min.css', array(), WPMLLB_VERSION );
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0
		 */
		public function enqueue_scripts() {

			wp_enqueue_script( WPMLLB_SLUG . '-lightbox', WPMLLB_URL . '/vendor/js/lightbox.min.js', array(), WPMLLB_VERSION, true );
			//wp_enqueue_script( WPMLLB_SLUG . '-js', WPMLLB_URL . '/assets/js/wpml-lightbox.js', array( WPMLLB_SLUG . '-lightbox' ), WPMLLB_VERSION, true );
		}

		/**
		 * Initializes variables
		 *
		 * @since    1.0
		 */
		public function init() {
		}

	}
endif;