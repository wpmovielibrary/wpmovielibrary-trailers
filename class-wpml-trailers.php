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

			if ( ! $this->wpml_requirements_met() ) {
				add_action( 'init', 'wpmltr_l10n' );
				add_action( 'admin_notices', 'wpmltr_requirements_error' );
				return false;
			}

			$this->register_hook_callbacks();

			$this->register_shortcodes();
		}

		/**
		 * Make sure WPMovieLibrary is active and compatible.
		 *
		 * @since    1.0
		 * 
		 * @return   boolean    Requirements met or not?
		 */
		private function wpml_requirements_met() {

			$wpml_active  = is_wpml_active();
			$wpml_version = ( is_wpml_active() && version_compare( WPML_VERSION, WPMLTR_REQUIRED_WPML_VERSION, '>=' ) );

			if ( ! $wpml_active || ! $wpml_version )
				return false;

			return true;
		}

		/**
		 * Register callbacks for actions and filters
		 * 
		 * @since    1.0
		 */
		public function register_hook_callbacks() {

			add_action( 'plugins_loaded', 'wpmltr_l10n' );

			add_action( 'activated_plugin', __CLASS__ . '::require_wpml_first' );

			// Enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			add_action( 'save_post', array( $this, 'save_trailers' ), 10, 3 );

			add_filter( 'wpml_filter_metaboxes', __CLASS__ . '::add_meta_box' );
			add_filter( 'wpml_filter_shortcodes', __CLASS__ . '::add_movie_trailer_shortcode' );

			add_action( 'wp_ajax_wpml_search_trailer', __CLASS__ . '::search_trailer_callback' );
			add_action( 'wp_ajax_wpml_load_allocine_page', __CLASS__ . '::load_allocine_page_callback' );
			add_action( 'wp_ajax_wpml_remove_trailer', __CLASS__ . '::remove_trailer_callback' );
		}

		/**
		 * Register all shortcodes.
		 *
		 * @since    1.0
		 */
		public function register_shortcodes() {

			add_shortcode( 'movie_trailer', __CLASS__ . '::movie_trailer_shortcode' );
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
		public function enqueue_styles() {

			wp_enqueue_style( WPMLTR_SLUG . '-css', WPMLTR_URL . '/assets/css/public.css', array(), WPMLTR_VERSION );
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0
		 */
		public function admin_enqueue_styles() {

			wp_enqueue_style( WPMLTR_SLUG . '-admin-css', WPMLTR_URL . '/assets/css/admin.css', array(), WPMLTR_VERSION );
		}

		/**
		 * Register and enqueue public-facing style sheet.
		 *
		 * @since    1.0
		 */
		public function admin_enqueue_scripts() {

			wp_enqueue_script( WPMLTR_SLUG . 'admin-js', WPMLTR_URL . '/assets/js/wpmltr-trailers.js', array( WPML_SLUG ), WPMLTR_VERSION, true );
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
				return new WP_Error( 'missing_id', __( 'Required TMDb ID not provided or invalid.', 'wpmovielibrary-trailers' ) );

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
				return new WP_Error( 'missing_id', __( 'Required Allociné Movie ID not provided or invalid.', 'wpmovielibrary-trailers' ) );

			$response = WPMLTR_Allocine::get_trailers( $movie_id );

			WPML_Utils::ajax_response( $response, array(), WPML_Utils::create_nonce( 'search-trailer' ) );
		}

		/**
		 * AJAX Callback to remove Movie's current Trailer
		 * 
		 * @since    1.1
		 */
		public static function remove_trailer_callback() {

			WPML_Utils::check_ajax_referer( 'remove-trailer' );

			$post_id = ( isset( $_POST['post_id'] ) && '' != $_POST['post_id'] ? intval( $_POST['post_id'] ) : null );

			if ( is_null( $post_id ) )
				return new WP_Error( 'missing_id', __( 'Required Post ID not provided or invalid.', 'wpmovielibrary-trailers' ) );

			$response = self::remove_trailer( $post_id );

			WPML_Utils::ajax_response( $response, array(), WPML_Utils::create_nonce( 'remove-trailer' ) );
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
						'title'         => __( 'WPMovieLibrary − Trailers', 'wpmovielibrary-trailers' ),
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
			$movie_id = ( isset( $trailer_data['movie_id'] ) ? $trailer_data['movie_id'] : null ); 

			if ( isset( $trailer_data['site'] ) && '' != $trailer_data['site'] ) {
				$url  = call_user_func( __CLASS__ . "::get_{$trailer_data['site']}_trailer_url", $trailer );
				$link = call_user_func( __CLASS__ . "::get_{$trailer_data['site']}_trailer_link", $trailer, $movie_id );
				$code = htmlentities( $url );
			}
			else {
				$url  = '';
				$link = '';
				$code = '';
			}

			$attributes = array(
				'style'         => ( ! $url ? '' : ' class="visible"' ),
				'trailer'       => $trailer,
				'trailer_data'  => $trailer_data,
				'trailer_data_' => str_replace( "'", "\u0027", json_encode( $trailer_data ) ),
				'url'           => $url,
				'link'          => $link,
				'code'          => $code,
				'shortcode'     => '[movie_trailer id="' . $post->ID . '"]'
			);

			echo self::render_template( 'metaboxes/movie-trailers.php', $attributes );
		}

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                              Trailers
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Get a movie's trailer.
		 *
		 * @since    1.0
		 *
		 * @param    int      $post_id Post ID.
		 * 
		 * @return   bool|array    Trailer data or false
		 */
		public static function get_movie_trailer( $post_id ) {

			$trailer = get_post_meta( $post_id, '_wpml_movie_trailer_data', true );
			return $trailer;
		}

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
				return new WP_Error( __( 'You are not allowed to edit posts.', 'wpmovielibrary-trailers' ) );

			if ( ! $post = get_post( $post_ID ) || 'movie' != get_post_type( $post ) )
				return new WP_Error( sprintf( __( 'Posts with #%s is invalid or is not a movie.', 'wpmovielibrary-trailers' ), $post_ID ) );

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
					$errors->add( 'trailer', __( 'An error occurred while saving the trailer.', 'wpmovielibrary-trailers' ) );
			}

			return ( ! empty( $errors->errors ) ? $errors : $post_ID );
		}

		/**
		 * Remove a Trailer.
		 *
		 * @since    1.1
		 *
		 * @param    int        $post_ID Post ID.
		 * 
		 * @return   int|WP_Error    Post ID if trailer was removed successfully, WP_Error if an error occurred.
		 */
		private static function remove_trailer( $post_ID ) {

			if ( ! $post = get_post( $post_ID ) || 'movie' != get_post_type( $post ) )
				return new WP_Error( sprintf( __( 'Posts with #%s is invalid or is not a movie.', 'wpmovielibrary-trailers' ), $post_ID ) );

			$errors = new WP_Error();

			$trailer = delete_post_meta( $post_ID, '_wpml_movie_trailer' );
			$trailer_data = delete_post_meta( $post_ID, '_wpml_movie_trailer_data' );

			if ( ! $trailer || ! $trailer_data )
				$errors->add( 'trailer', __( 'An error occurred while removing the trailer.', 'wpmovielibrary-trailers' ) );

			return ( ! empty( $errors->errors ) ? $errors : $post_ID );
		}

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                             Shortcodes
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Add Movie Trailer shortcode to the list of WPML Shortcodes.
		 *
		 * @since    1.0
		 * 
		 * @param    array    WPML Shortcodes list
		 * 
		 * @return   array    Updated Shortcodes list
		 */
		public static function add_movie_trailer_shortcode( $shortcodes ) {

			$new_shortcode = array(
				'movie_trailer' => array(
					'atts' => array(
						'id' => array( 'default' => null, 'values' => null, 'filter' => 'esc_attr' ),
						'title' => array( 'default' => null, 'values' => null, 'filter' => 'esc_attr' ),
						'height' => array( 'default' => 360, 'values' => null, 'filter' => 'esc_attr' ),
						'width' => array( 'default' => 640, 'values' => null, 'filter' => 'esc_attr' ),
						'label' => array( 'default' => false, 'values' => 'boolean', 'filter' => 'esc_attr' )
					),
					'callback' => __CLASS__ . '::movie_trailer_shortcode',
				)
			);

			$shortcodes = array_merge( $shortcodes, $new_shortcode );

			return $shortcodes;
		}

		/**
		 * Movie Trailer shortcode.
		 *
		 * @since    1.0
		 * 
		 * @param    array     Shortcode attributes
		 * @param    string    Shortcode content
		 * 
		 * @return   string    Shortcode display
		 */
		public static function movie_trailer_shortcode( $atts, $content ) {

			$atts = apply_filters( 'wpml_filter_shortcode_atts', 'movie_trailer', $atts );

			// Caching
			$name = apply_filters( 'wpml_cache_name', 'movie_trailer_shortcode', $atts );
			$content = WPML_Cache::output( $name, function() use ( $atts, $content ) {

				extract( $atts );

				$movie_id = WPML_Shortcodes::find_movie_id( $id, $title );
				if ( is_null( $movie_id ) )
					return $content;

				$trailer = self::get_movie_trailer( $movie_id );
				if ( '' == $trailer )
					return $content;

				if ( ! in_array( $trailer['site'], array( 'youtube', 'allocine' ) ) )
					return $content;

				$atts['title'] = ( $label ? __( 'Trailer', 'wpmovielibrary-trailers' ) : false );
				$atts['url']   = call_user_func( __CLASS__ . "::get_{$trailer['site']}_trailer_url", $trailer['id'] );

				$content = self::render_template( 'shortcodes/trailer.php', $atts, $require = 'always' );

				return $content;

			}, $echo = false );

			return $content;
		}

		/** * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 *
		 *                               Utils
		 * 
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

		/**
		 * Return trailer's video URL.
		 * 
		 * @since    1.0
		 * 
		 * @param    int      $media_id Trailers's media ID
		 * 
		 * @return   string    Trailer URL
		 */
		private static function get_allocine_trailer_url( $media_id ) {

			return "http://www.allocine.fr/_video/iblogvision.aspx?cmedia={$media_id}";
		}

		/**
		 * Return trailer's page URL.
		 * 
		 * @since    1.0
		 * 
		 * @param    int      $media_id Trailers's media ID
		 * @param    int      $movie_id Trailers's Movie ID
		 * 
		 * @return   string    Trailer's page URL
		 */
		private static function get_allocine_trailer_link( $media_id, $movie_id ) {

			return "http://www.allocine.fr/video/player_gen_cmedia={$media_id}&amp;cfilm={$movie_id}.html";
		}

		/**
		 * Return trailer's video URL.
		 * 
		 * @since    1.0
		 * 
		 * @param    int      $id Trailers's ID
		 * 
		 * @return   string    Trailer URL
		 */
		private static function get_youtube_trailer_url( $id ) {

			return "https://www.youtube.com/embed/{$id}";
		}

		/**
		 * Return trailer's page URL.
		 * 
		 * @since    1.0
		 * 
		 * @param    int      $id Trailers's ID
		 * 
		 * @return   string    Trailer's page URL
		 */
		private static function get_youtube_trailer_link( $id ) {

			return "https://www.youtube.com/watch?v={$id}";
		}

		/**
		 * Prepare Trailers data.
		 *
		 * @since    1.0
		 *
		 * @param    array    Trailers data
		 *
		 * @return   array    Filtered data
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