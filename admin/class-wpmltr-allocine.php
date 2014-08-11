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

if ( ! class_exists( 'WPMLTR_Allocine' ) ) :

	/**
	 * 
	 * 
	 * @since    1.0
	 */
	class WPMLTR_Allocine {

		const autocomplete = 'http://essearch.allocine.net/fr/autocomplete';

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
		 * @param    string    $title Movie title to search
		 * 
		 * @return   array     TMDb result
		 */
		public static function get_trailers( $title ) {

			$pages = self::get_movie_page( $title );

			return $pages;
		}

		private static function get_movie_page( $movie_id ) {

			$trailers = array();

			$default = 'http://fr.web.img4.acsta.net/c_195_110/commons/emptymedia/entities/empty_videoportal.jpg';
			$url = "http://www.allocine.fr/film/fichefilm_gen_cfilm={$movie_id}.html";
			$page = wp_remote_get( $url );

			if ( is_wp_error( $page ) )
				return $page;

			preg_match_all( '#(<a href="/video/player_gen_cmedia=(.*?)(&|&amp;)cfilm=(.*?)\.html">(.*?)</a>)#si', $page['body'], $matches );

			for ( $i = 0; $i < count( $matches ); $i++ ) {

				$media_id = $matches[ 2 ][ $i ];
				$movie_id = $matches[ 4 ][ $i ];
				$title    = str_replace( "\n", '', trim( strip_tags( $matches[ 5 ][ $i ] ) ) );

				$trailers[ $media_id ] = array(
					'media_id'  => $media_id,
					'movie_id'  => $movie_id,
					'title'     => $title,
					'thumbnail' => $default
				);

				preg_match( '#<img src=("|\')http://(.*?)/c_195_110/videothumbnails/(.*?)' . $matches[ 2 ][ $i ] . '(.*?).jpg("|\') />#i', $page['body'], $match );
				if ( ! empty( $match ) )
					$trailers[ $media_id ][''] = str_replace( array( '<img src=\'', '<img src="', '\' >', '" />' ), '', $match[ 0 ] );
			}

			return $trailers;
		}

	}

endif;