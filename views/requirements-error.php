<div class="error">
	<p><?php _e( 'WPMovieLibrary-Trailers error: your environment does not meet all of the system requirements listed below.', 'wpmovielibrary-trailers' ); ?></p>

<?php if ( is_wpml_active() ) : ?>
	<ul class="ul-disc">
		<li><strong style="color:<?php echo $wp; ?>;"><?php _e( 'WPMovieLibrary is required', 'wpmovielibrary-trailers' ); ?></strong>.<br /><em><?php _e( 'WPMovieLibrary-Trailers extends the WPMovieLibrary plugin and therefore requires it to be installed and activated.', 'wpmovielibrary-trailers' ); ?></em></li>
	</ul>

<?php else : ?>
	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo WPMLTR_REQUIRED_PHP_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpmovielibrary-trailers' ), PHP_VERSION ); ?></em>
		</li>
		<li>
			<strong>WordPress <?php echo WPMLTR_REQUIRED_WP_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpmovielibrary-trailers' ), esc_html( $wp_version ) ); ?></em>
		</li>
		<li>
			<strong>WPMovieLibrary <?php echo WPMLTR_REQUIRED_WPML_VERSION; ?>+</strong>
			<em><?php if ( is_wpml_active() ) printf( __( '(You\'re running version %s)', 'wpmovielibrary-trailers' ), WPML_VERSION ); ?></em>
		</li>
	</ul>

	<p><?php _e( 'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.', 'wpmovielibrary-trailers' ); ?></p>

	<p><?php _e( 'If you tried activating WPMovieLibrary-Trailers without activating WPMovieLibrary first, you will need to deactivate and reactivate WPMovieLibrary-Trailers for this notice to disapear. <a href="http://wpmovielibrary.com/wpmovielibrary-trailers/documentation/installation/#requirements">Learn why</a>.', 'wpmovielibrary-trailers' ); ?></p>
<?php endif; ?>

</div>