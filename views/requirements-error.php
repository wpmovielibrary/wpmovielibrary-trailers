<div class="error">
	<p><?php _e( 'WPMovieLibrary-Trailers error: your environment does not meet all of the system requirements listed below.', 'wpml-trailers' ); ?></p>

<?php if ( is_wpml_active() ) : ?>
	<ul class="ul-disc">
		<li><strong style="color:<?php echo $wp; ?>;"><?php _e( 'WPMovieLibrary is required', 'wpml-trailers' ); ?></strong>.<br /><em><?php _e( 'WPMovieLibrary-Trailers extends the WPMovieLibrary plugin and therefore requires it to be installed and activated.', 'wpml-trailers' ); ?></em></li>
	</ul>

<?php else : ?>
	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo WPMLTR_REQUIRED_PHP_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpml-trailers' ), PHP_VERSION ); ?></em>
		</li>
		<li>
			<strong>WordPress <?php echo WPMLTR_REQUIRED_WP_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpml-trailers' ), esc_html( $wp_version ) ); ?></em>
		</li>
		<li>
			<strong>WPMovieLibrary <?php echo WPMLTR_REQUIRED_WPML_VERSION; ?>+</strong>
			<em><?php printf( __( '(You\'re running version %s)', 'wpml-trailers' ), WPML_VERSION ); ?></em>
		</li>
	</ul>

	<p><?php _e( 'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.', 'wpml-trailers' ); ?></p>
<?php endif; ?>

</div>