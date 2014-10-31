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

if ( class_exists( 'WPMOLY_TMDb' ) && ! class_exists( 'WPMOLYTR_TMDb' ) ) :

	/**
	 * Extends Class for WPML Api Wrapper Class
	 * 
	 * Adds a get_trailers() method to the default WPMovieLibrary Api Wrapper
	 * for TMDb Api Class.
	 * 
	 * @since    1.0
	 */
	class WPMOLYTR_TMDb extends WPMOLY_TMDb {

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
		public static function get_trailers( $id, $lang = null ) {

			$api = new WPMOLYTR_Api();
			$trailers = $api->getTrailers( $id, $lang );
			$trailers = self::filter_data( $trailers );

			return $trailers;
		}

		/**
		 * Get movie's videos
		 * 
		 * @since    1.0
		 * 
		 * @param    int       $id Movie TMDb ID
		 * @param    string    $lang Filter the result with a language
		 * 
		 * @return   array     TMDb result
		 */
		public static function get_videos( $id, $lang = null ) {

			$api = new WPMOLYTR_Api();
			$videos = $api->getVideos( $id, $lang );
			$videos = self::filter_data( $videos );

			return $videos;
		}

		private static function filter_data( $data ) {

			$_data = array();

			if ( ! isset( $data['results'] ) || empty( $data['results'] ) )
				return $_data;

			foreach ( $data['results'] as $d )
				$_data[] = array(
					'id'        => $d['key'],
					'site'      => 'youtube',
					'title'     => $d['name'],
					'thumbnail' => null,
					'movie_id'  => null
				);

			return $_data;
		}

	}

endif;