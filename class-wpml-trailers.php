<?php
/**
 * WPMovieLibrary-Trailers
 *
 * @package   WPMovieLibrary-Trailers
 * @author    Charlie MERLAND <charlie@caercam.org>
 * @license   GPL-3.0
 * @link      http://www.caercam.org/
 * @copyright 2014 Charlie MERLAND
 */

if ( ! class_exists( 'WPMovieLibrary_Trailers' ) ) :

	/**
	* Plugin class
	*
	* @package WPMovieLibrary-Trailers
	* @author  Charlie MERLAND <charlie@caercam.org>
	*/
	class WPMovieLibrary_Trailers extends WPMLTR_Module {

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

			add_action( 'activated_plugin', __CLASS__ . '::require_wpml_first' );

			// Enqueue scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			add_filter( 'wpml_filter_metaboxes', array( $this, 'add_meta_box' ), 10 );

			add_action( 'wp_ajax_wpml_search_trailer', __CLASS__ . '::search_trailer_callback' );
			add_action( 'wp_ajax_wpml_load_allocine_page', __CLASS__ . '::load_allocine_page_callback' );
		}

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                     Plugin  Activate/Deactivate
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Fired when the plugin is activated.
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

			self::require_wpml_first();
		}

		public static function require_wpml_first() {

			$this_plugin_path = plugin_dir_path( __FILE__ );
			$this_plugin      = basename( $this_plugin_path ) . '/wpml-trailers.php';
			$active_plugins   = get_option( 'active_plugins' );
			$this_plugin_key  = array_search( $this_plugin, $active_plugins );
			$wpml_plugin_key  = array_search( 'wpmovielibrary/wpmovielibrary.php', $active_plugins );

			if ( $this_plugin_key < $wpml_plugin_key ) {

				unset( $active_plugins[ $this_plugin_key ] );
				$active_plugins = array_merge(
					array_slice( $active_plugins, 0, $wpml_plugin_key ),
					array( $this_plugin ),
					array_slice( $active_plugins, $wpml_plugin_key )
				);

				update_option( 'active_plugins', $active_plugins );
			}
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0
		 */
		public function admin_enqueue_styles() {

			wp_enqueue_style( WPMLTR_SLUG . '-css', WPMLTR_URL . '/assets/css/admin.css', array(), WPMLTR_VERSION );
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0
		 */
		public function admin_enqueue_scripts() {

			//wp_enqueue_script( WPMLTR_SLUG . '-lightbox', WPMLTR_URL . '/vendor/js/lightbox.min.js', array(), WPMLTR_VERSION, true );
			wp_enqueue_script( WPMLTR_SLUG . '-js', WPMLTR_URL . '/assets/js/wpmltr-trailers.js', array( WPML_SLUG ), WPMLTR_VERSION, true );
		}

		/**
		 * Register Trailers Metabox
		 *
		 * @since    1.0
		 */
		public function add_meta_box( $metaboxes ) {

			$metaboxes = array_merge(
				$metaboxes,
				array(
					array(
						'id'            => 'wpml_trailers',
						'title'         => __( 'WPMovieLibrary − Trailers', 'wpml-trailers' ),
						'callback'      => 'WPMovieLibrary_Trailers::metabox_content',
						'screen'        => 'movie',
						'context'       => 'normal',
						'priority'      => 'high',
						'callback_args' => null
					)
				)
			);

			return $metaboxes;
		}

		/**
		 * Trailers Metabox
		 * 
		 * @since    1.0
		 * 
		 * @param    object    Current Post object
		 * @param    null      $metabox null
		 */
		public static function metabox_content( $post, $metabox ) {

			echo self::render_template( 'metaboxes/movie-trailers.php' );
		}

		/**
		 * Trailers search callback
		 * 
		 * @since    1.0
		 */
		public static function search_trailer_callback() {

			WPML_Utils::check_ajax_referer( 'search-trailer' );

			$tmdb_id = ( isset( $_GET['tmdb_id'] ) && '' != $_GET['tmdb_id'] ? intval( $_GET['tmdb_id'] ) : null );
			$post_id = ( isset( $_GET['post_id'] ) && '' != $_GET['post_id'] ? intval( $_GET['post_id'] ) : null );

			if ( is_null( $tmdb_id ) )
				return new WP_Error( 'missing_id', __( 'Required TMDb ID not provided or invalid.', 'wpmovielibrary' ) );

			$response = self::get_trailers( $tmdb_id );

			WPML_Utils::ajax_response( $response, array(), WPML_Utils::create_nonce( 'search-trailer' ) );
		}

		public static function load_allocine_page_callback() {

			WPML_Utils::check_ajax_referer( 'search-trailer' );

			$movie_id = ( isset( $_GET['movie_id'] ) && '' != $_GET['movie_id'] ? intval( $_GET['movie_id'] ) : null );

			if ( is_null( $movie_id ) )
				return new WP_Error( 'missing_id', __( 'Required Allociné Movie ID not provided or invalid.', 'wpmovielibrary' ) );

			$response = WPMLTR_Allocine::get_trailers( $movie_id );

			WPML_Utils::ajax_response( $response, array(), WPML_Utils::create_nonce( 'search-trailer' ) );
		}

		public static function get_trailers( $tmdb_id ) {

			$trailers = WPMLTR_TMDb::get_trailers( $tmdb_id );
			return $trailers;
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