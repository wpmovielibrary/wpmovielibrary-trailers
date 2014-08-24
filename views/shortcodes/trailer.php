<?php
/**
 * Movie Trailer Shortcode view Template
 * 
 * Showing a movie's trailer.
 * 
 * @since    1.1
 * 
 * @uses    $title
 * @uses    $url
 * @uses    $width
 * @uses    $height
 */
?>

	<div class="wpml-shortcode-div  wpml-movie-trailer-shortcode">
<?php if ( $title ) : ?>
		<div class="wpml-movie-trailer-title"><h3><?php echo $title ?></h3></div>

<?php endif; ?>
		<div class="wpml-movie-trailer-video">
			<iframe src="<?php echo $url ?>" width="<?php echo $width ?>" height="<?php echo $height ?>" frameborder="0" allowfullscreen></iframe>
		</div>
	</div>
