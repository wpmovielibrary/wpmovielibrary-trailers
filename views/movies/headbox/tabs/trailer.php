<?php
/**
 * Movie Headbox Trailer Tab Template view
 * 
 * Showing a movie's headbox trailer tab.
 * 
 * @since    2.0
 * 
 * @uses    $trailer
 */
?>

<?php if ( '' != $trailer ) : ?>
				<iframe src="<?php echo $trailer ?>" frameborder="0"></iframe>
<?php else : ?>
				<p><?php _e( 'No trailer added for this movie.', 'wpmovielibrary-trailers' ) ?></p>
<?php endif; ?>
