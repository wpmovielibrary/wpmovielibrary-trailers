
		<div id="wpmoly-trailers" class="wpmoly-trailers">

			<p><strong><?php _e( 'Find a trailer', 'wpmovielibrary-trailers' ); ?></strong></p>

			<div>
				<select id="wpmoly-trailer-source">
					<option value="youtube"><?php _e( 'YouTube', 'wpmovielibrary-trailers' ); ?></option>
					<option value="allocine"><?php _e( 'Allociné', 'wpmovielibrary-trailers' ); ?></option>
				</select>
				<a id="wpmoly-search-trailers" href="#" class="button button-secondary button-icon" title="<?php _e( 'Search', 'wpmovielibrary-trailers' ); ?>" onclick="wpmoly_trailers.search(); return false;"><span class="wpmolicon icon-search"></a>
				<a id="wpmoly-empty-trailers" href="#" class="button button-secondary button-icon" title="<?php _e( 'Empty', 'wpmovielibrary-trailers' ); ?>" onclick="wpmoly_trailers.empty(); return false;"><span class="wpmolicon icon-no"></a>
				<span class="spinner"></span>

				<a id="wpmoly-remove-trailer" href="#" class="button button-secondary button-icon" title="<?php _e( 'Remove', 'wpmovielibrary-trailers' ); ?>" onclick="wpmoly_trailers.remove(); return false;" style="float:right"><span class="wpmolicon icon-erase"></a>
				
				<div id="wpmoly-trailers-movies-select"></div>

				<div id="wpmoly-trailer-frame"<?php echo $style ?>>
					<iframe src="<?php echo $url ?>" frameborder="0"></iframe>
					<p><?php _e( 'The above video will be used as the default trailer for this movie; to use an alternative trailer make your choice among the other videos below.', 'wpmovielibrary-trailers' ); ?> <a href="#" onclick="$('#wpmoly-trailer-details').slideToggle( 250 ); return false;"><?php _e( 'Show more &raquo;', 'wpmovielibrary-trailers' ); ?></a></p>
					<div id="wpmoly-trailer-details">
						<label for="wpmoly_trailer_url"><?php _e( 'Video URI', 'wpmovielibrary-trailers' ); ?> <input type="text" id="wpmoly_trailer_url" size="40" value="<?php echo $url ?>" /></label>
						<label for="wpmoly_trailer_page"><?php _e( 'Trailer Page', 'wpmovielibrary-trailers' ); ?> <input type="text" id="wpmoly_trailer_page" size="40" value="<?php echo $link ?>" /></label>
						<label for="wpmoly_trailer_code"><?php _e( 'Embed Code', 'wpmovielibrary-trailers' ); ?><br /><textarea rows="3" cols="40" id="wpmoly_trailer_code"><?php echo $code ?></textarea></label>
						<label for="wpmoly_trailer_shortcode"><?php _e( 'Shortcode', 'wpmovielibrary-trailers' ); ?><br /><input type="text" id="wpmoly_trailer_shortcode" size="40" value='<?php echo $shortcode ?>' /></label><br />
						<a href="http://wpmovielibrary.com/extensions/wpmovielibrary-trailers/movie_trailer/"><?php _e( 'Learn more about Shortcode', 'wpmovielibrary-trailers' ); ?></a>
					</div>
				</div>

				<div id="wpmoly-trailers-list"></div>

				<?php wpmoly_nonce_field( 'remove-trailer' ) ?>
				<?php wpmoly_nonce_field( 'search-trailer' ) ?>
				<input type="hidden" id="wpmoly_data_trailer" name="wpmoly_data[trailer]" value="<?php echo $trailer ?>" />
				<input type="hidden" id="wpmoly_data_trailer_data" name="wpmoly_data[trailer_data]" value='<?php echo $trailer_data_ ?>' />

				<div style="clear:both;"></div>

			</div>

		</div>
