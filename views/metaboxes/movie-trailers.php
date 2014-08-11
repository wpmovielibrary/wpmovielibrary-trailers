
		<div id="wpml-trailers" class="wpml-trailers">

			<p><strong><?php _e( 'Find a trailer', 'wpmovielibrary' ); ?></strong></p>

			<div>
				<?php WPML_Utils::_nonce_field( 'search-trailer' ) ?>
				<select id="wpml-trailer-source">
					<option value="youtube"><?php _e( 'YouTube', 'wpmovielibrary' ); ?></option>
					<option value="allocine"><?php _e( 'Allociné', 'wpmovielibrary' ); ?></option>
				</select>
				<a id="wpml-search-trailers" class="button" href="#"><?php _e( 'Search', 'wpmovielibrary' ); ?></a>
				<span class="spinner"></span>

				<div id="wpml-trailers-list"></div>

			</div>

		</div>
