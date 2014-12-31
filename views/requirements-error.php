<div class="error">
	<p><?php _e( 'WPMovieLibrary-Trailers error: your environment does not meet all of the system requirements listed below.', 'wpmovielibrary-trailers' ); ?></p>

	<ul>
<?php if ( version_compare( PHP_VERSION, WPMOLYTR_REQUIRED_PHP_VERSION, '<=' ) ) : ?>
		<li>
			<strong>PHP <?php echo WPMOLYTR_REQUIRED_PHP_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpmovielibrary-trailers' ), PHP_VERSION ); ?></em>
		</li>
<?php
endif;
if ( version_compare( $wp_version, WPMOLYTR_REQUIRED_WP_VERSION, '<=' ) ) :
?>
		<li>
			<strong>WordPress <?php echo WPMOLYTR_REQUIRED_WP_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpmovielibrary-trailers' ), esc_html( $wp_version ) ); ?></em>
		</li>
<?php endif; ?>
	</ul>

	<p><?php _e( 'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.', 'wpmovielibrary-trailers' ); ?></p>

	<p><?php _e( 'If you tried activating WPMovieLibrary-Trailers without activating WPMovieLibrary first, you will need to deactivate and reactivate WPMovieLibrary-Trailers for this notice to disapear. <a href="http://wpmovielibrary.com/wpmovielibrary-trailers/documentation/installation/#requirements">Learn why</a>.', 'wpmovielibrary-trailers' ); ?></p>

</div>