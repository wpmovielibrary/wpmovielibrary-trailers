<?php
/**
 * WPML_TMDb Class extension.
 * 
 * @package   WPMovieLibrary-Trailers
 * @author    Charlie MERLAND <charlie.merland@gmail.com>
 * @license   GPL-3.0
 * @link      http://www.caercam.org/
 * @copyright 2014 CaerCam.org
 */

if ( ! class_exists( 'WPML_TMDb' ) )
	WPML_TMDb::get_instance();

if ( class_exists( 'WPML_TMDb' ) && ! class_exists( 'WPMLTR_TMDb' ) ) :

	/**
	 * Extends Class for WPML Api Wrapper Class
	 * 
	 * Adds a get_trailers() method to the default WPMovieLibrary Api Wrapper
	 * for TMDb Api Class.
	 * 
	 * @since    1.0
	 */
	class WPMLTR_TMDb extends WPML_TMDb {

		/**
		 * Default constructor
		 */
		public function __construct() {

			if ( ! is_admin() )
				return false;

		}

		/**
		 * Get movie's trailers
		 * 
		 * @since    1.0
		 * 
		 * @param    int       $id Movie TMDb ID
		 * @param    string    $lang Filter the result with a language
		 * 
		 * @return   array     TMDb result
		 */
		public static function get_trailers( $id, $lang ) {

			$api = new WPMLTR_Api();
			return $api->getTrailers( $id, $params );
		}

	}

endif;