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

			$this->init();
		}

		/**
		 * Initializes variables
		 *
		 * @since    1.0
		 */
		public function init() {

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

			add_action( 'save_post', array( $this, 'save_trailers' ), 10, 3 );

			add_filter( 'wpml_filter_metaboxes', __CLASS__ . '::add_meta_box' );

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

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                     Scripts/Styles and Utils
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

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

			wp_enqueue_script( WPMLTR_SLUG . '-js', WPMLTR_URL . '/assets/js/wpmltr-trailers.js', array( WPML_SLUG ), WPMLTR_VERSION, true );
		}

		/**
		 * Make sure the plugin is load after WPMovieLibrary and not
		 * before, which would result in errors and missing files.
		 *
		 * @since    1.0
		 */
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

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                               Callbacks
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * AJAX Callback to search Trailers through the API
		 * 
		 * @since    1.0
		 */
		public static function search_trailer_callback() {

			WPML_Utils::check_ajax_referer( 'search-trailer' );

			$tmdb_id = ( isset( $_GET['tmdb_id'] ) && '' != $_GET['tmdb_id'] ? intval( $_GET['tmdb_id'] ) : null );
			$post_id = ( isset( $_GET['post_id'] ) && '' != $_GET['post_id'] ? intval( $_GET['post_id'] ) : null );

			if ( is_null( $tmdb_id ) )
				return new WP_Error( 'missing_id', __( 'Required TMDb ID not provided or invalid.', 'wpml-trailers' ) );

			$response = self::get_trailers( $tmdb_id );

			WPML_Utils::ajax_response( $response, array(), WPML_Utils::create_nonce( 'search-trailer' ) );
		}

		/**
		 * AJAX Callback to find trailers from Allociné.
		 *
		 * @since    1.0
		 */
		public static function load_allocine_page_callback() {

			WPML_Utils::check_ajax_referer( 'search-trailer' );

			$movie_id = ( isset( $_GET['movie_id'] ) && '' != $_GET['movie_id'] ? intval( $_GET['movie_id'] ) : null );

			if ( is_null( $movie_id ) )
				return new WP_Error( 'missing_id', __( 'Required Allociné Movie ID not provided or invalid.', 'wpml-trailers' ) );

			$response = WPMLTR_Allocine::get_trailers( $movie_id );

			WPML_Utils::ajax_response( $response, array(), WPML_Utils::create_nonce( 'search-trailer' ) );
		}

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                             Metabox
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Register Trailers Metabox
		 *
		 * @since    1.0
		 * 
		 * @param    array    $metaboxes Existing Metaboxes
		 * 
		 * @return   array    Updated Metaboxes List
		 */
		public static function add_meta_box( $metaboxes ) {

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

			$trailer = get_post_meta( $post->ID, '_wpml_movie_trailer', true );
			$trailer_data = get_post_meta( $post->ID, '_wpml_movie_trailer_data', true );

			if ( 'youtube' == $trailer_data['site'] ) {
				$url  = WPMLTR_TMDb::get_trailer_url( $trailer );
				$link = WPMLTR_TMDb::get_trailer_link( $trailer );
				$code = '&lt;iframe src="' . $url . '" width="640" height="320px" frameborder="0"&gt;&lt;/iframe&gt;';
			}
			else if ( 'allocine' == $trailer_data['site'] ) {
				$url  = WPMLTR_Allocine::get_trailer_url( $trailer );
				$link = WPMLTR_Allocine::get_trailer_link( $trailer, $trailer_data['movie_id'] );
				$code = '&lt;iframe src="' . $url . '" width="640" height="320px" frameborder="0"&gt;&lt;/iframe&gt;';
			}
			else
				return false;

			$attributes = array(
				'style'         => ( ! $trailer ? '' : ' class="visible"' ),
				'trailer'       => $trailer,
				'trailer_data'  => $trailer_data,
				'trailer_data_' => json_encode( $trailer_data ),
				'url'           => $url,
				'link'          => $link,
				'code'          => $code,
			);

			echo self::render_template( 'metaboxes/movie-trailers.php', $attributes );
		}


		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                              Trailers
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Get Trailers from the API.
		 *
		 * @since    1.0
		 * 
		 * @param    int     $tmdb_id TMDb Movie ID.
		 * 
		 * @return   array   Found Trailers
		 */
		private static function get_trailers( $tmdb_id ) {

			$lang = WPML_Settings::tmdb__lang();
			$trailers_lang = WPMLTR_TMDb::get_videos( $tmdb_id, $lang );

			if ( 'en' != $lang ) {

				$trailers_gen  = WPMLTR_TMDb::get_videos( $tmdb_id, 'en' );

				if ( isset( $trailers_lang ) && isset( $trailers_gen ) )
					$trailers = array_merge( $trailers_lang, $trailers_gen );
				else if ( isset( $trailers_lang ) && ! isset( $trailers_gen ) )
					$trailers = $trailers_lang;
				else if ( ! isset( $trailers_lang ) && isset( $trailers_gen ) )
					$trailers = $trailers_gen;
			}
			else
				$trailers = $trailers_lang;

			return $trailers;
		}

		/**
		 * Save Trailers along with movie.
		 *
		 * @since    1.0
		 *
		 * @param    int        $post_ID Post ID.
		 * @param    WP_Post    $post Post object.
		 * @param    bool       $update Whether this is an existing post being updated or not.
		 * 
		 * @return   int|WP_Error    Post ID if trailers were saved successfully, WP_Error if an error occurred.
		 */
		public function save_trailers( $post_ID, $post, $update ) {

			if ( ! current_user_can( 'edit_post', $post_ID ) )
				return new WP_Error( __( 'You are not allowed to edit posts.', 'wpml-trailers' ) );

			if ( ! $post = get_post( $post_ID ) || 'movie' != get_post_type( $post ) )
				return new WP_Error( sprintf( __( 'Posts with #%s is invalid or is not a movie.', 'wpml-trailers' ), $post_ID ) );

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_ID;

			$errors = new WP_Error();

			if ( isset( $_POST['wpml_data'] ) && '' != $_POST['wpml_data'] ) {

				$data = $_POST['wpml_data'];

				$trailer  = ( isset( $data['trailer'] ) && '' != $data['trailer'] ? esc_attr( $data['trailer'] ) : null );
				$trailer_data = ( isset( $data['trailer_data'] ) && '' != $data['trailer_data'] ? $this->_json_decode( $data['trailer_data'] ) : null );

				if ( ! is_null( $trailer ) )
					$trailer = update_post_meta( $post_ID, '_wpml_movie_trailer', $trailer );
				if ( ! is_null( $trailer_data ) )
					$trailer_data = update_post_meta( $post_ID, '_wpml_movie_trailer_data', $trailer_data );

				if ( ! $trailer || ! $trailer_data )
					$errors->add( 'trailer', __( 'An error occurred while saving the trailer.', 'wpml-trailers' ) );
			}

			return ( ! empty( $errors->errors ) ? $errors : $post_ID );
		}

		/**
		 * Prepare Trailers data.
		 *
		 * @since 1.0
		 *
		 * @param array Trailers data
		 *
		 * @return array Filtered data
		 */
		private function filter_trailer( $trailer ) {

			return (array) $trailer;
		}

		/**
		 * Decode a stringified JSON.
		 * 
		 * All this stuff is somehow need to get a proper array.
		 * 
		 * @since    1.0
		 * 
		 * @param    string    JSON string
		 * 
		 * @return   array     Decoded data
		 */
		private function _json_decode( $json ) {

			$json = esc_attr( $json );
			$json = html_entity_decode( $json );
			$json = stripslashes( $json );
			$json = json_decode( $json );
			$json = $this->filter_trailer( $json );

			return $json;
		}

	}
endif;