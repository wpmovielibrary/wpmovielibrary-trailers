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
	 * Find Allociné Trailers
	 * 
	 * @since    1.0
	 */
	class WPMLTR_Allocine {

		/**
		 * Default constructor
		 */
		public function __construct() {

			if ( ! is_admin() )
				return false;

		}

		/**
		 * Get movie trailers
		 * 
		 * Use HTTP Api to get the movie's basic page and extract media
		 * ids for the trailers.
		 * 
		 * @since    1.0
		 * 
		 * @param    int      $movie_id Movie ID
		 * 
		 * @return   array    TMDb result
		 */
		public static function get_trailers( $movie_id ) {

			$trailers = array();

			$default = 'http://fr.web.img4.acsta.net/c_240_160/commons/emptymedia/entities/empty_videoportal.jpg';
			//$url     = "http://www.allocine.fr/film/fichefilm_gen_cfilm={$movie_id}.html";
			$url     = "http://www.allocine.fr/videos/fichefilm-{$movie_id}/toutes/";
			$page = wp_remote_get( $url );

			if ( is_wp_error( $page ) )
				return $page;

			preg_match_all( '#(<a href="/video/player_gen_cmedia=(.*?)(&|&amp;)cfilm=(.*?)\.html">(.*?)</a>)#si', $page['body'], $matches );

			for ( $i = 0; $i < count( $matches[ 0 ] ); $i++ ) {

				$media_id = $matches[ 2 ][ $i ];
				$movie_id = $matches[ 4 ][ $i ];
				$title    = str_replace( "\n", '', trim( strip_tags( $matches[ 5 ][ $i ] ) ) );

				$trailers[ $media_id ] = array(
					'id'        => $media_id,
					'site'      => 'allocine',
					'title'     => $title,
					'thumbnail' => $default,
					'movie_id'  => $movie_id
				);

				preg_match( '#http://(.*?)/c_195_110/videothumbnails/(.*?)' . $media_id . '(.*?).jpg#i', $page['body'], $match );
				if ( ! empty( $match ) ) {
					$thumbnail = str_replace( '195_110', '240_160', $match[ 0 ] );
					$trailers[ $media_id ]['thumbnail'] = $thumbnail;
				}
			}

			$trailers = array_reverse( $trailers, true );

			return $trailers;
		}

	}

endif;