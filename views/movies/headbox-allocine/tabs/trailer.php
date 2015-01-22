<?php
/**
 * Movie Headbox Images Tab Template view
 * 
 * Showing a movie's headbox images tab, Allociné style.
 * 
 * @since    2.1
 * 
 * @uses    $id
 * @uses    
 */
?>

					<div id="movie-headbox-<?php echo $id ?>-trailers" class="wpmoly headbox allocine movie section trailers">
						<h3 class="wpmoly headbox allocine movie meta sub-title"><?php _e( 'Trailer', 'wpmovielibrary' ); ?></h3>
<?php if ( '' != $trailer ) : ?>
						<iframe src="<?php echo $trailer ?>" frameborder="0"></iframe>
<?php else : ?>
						<p><?php _e( 'No trailer added for this movie.', 'wpmovielibrary-trailers' ) ?></p>
<?php endif; ?>
					</div>
					<hr />
