
wpml = wpml || {};

var wpml_trailers

	/**
	 * Get Trailers for your movies!
	 * 
	 * @since    1.0
	 */
	wpml.trailers = wpml_trailers = {

		_search: '#wpml-search-trailers',
		_empty: '#wpml-empty-trailers',
		_source: '#wpml-trailer-source',
		_tmdb_id: '#tmdb_data_tmdb_id',
		_post_id: '#post_ID',
		_title: '#tmdb_data_title',
		_list: '#wpml-trailers-list',
		_select: '#wpml-trailers-movies-select',
		_trailer: '#wpml_data_trailer',
		_trailer_data: '#wpml_data_trailer_data',
		_trailers: '#wpml_data_trailers',
		_spinner: '#wpml-trailers .spinner',
		_frame: '#wpml-trailer-frame',

		search: function() {},
		empty: function() {},
	};

		/**
		 * Get Trailers from... Where?
		 * 
		 * @since    1.0
		 */
		wpml.trailers.search = function() {

			if ( 'allocine' == $( wpml_trailers._source ).val() )
				wpml_trailers.allocine.search();
			else
				wpml_trailers.tmdb.search();
		};

		/**
		 * Get Trailers from Allociné
		 * 
		 * @since    1.0
		 */
		wpml.trailers.allocine = wpml_trailers_allocine = {

			search: function() {},
			select: function() {},
			get_trailers: function() {},
			load_trailer: function() {},
		};

			/**
			 * Search for the movie using Allociné's autocomplete URI.
			 * 
			 * @since    1.0
			 */
			wpml.trailers.allocine.search = function() {

				var title = $( wpml_trailers._title ).val(),
				   movies = [];

				$.ajax({
					type: 'GET',
					url: 'http://essearch.allocine.net/fr/autocomplete?q=' + title,
					beforeSend: function() {
						$( wpml_trailers._spinner ).addClass( 'visible' );
					},
					success: function( response ) {
						if ( ! response.length )
							return false;

						movies = response;
						if ( movies.length > 1 ) {
							wpml_trailers_allocine.select( movies );
							return false;
						}

						wpml_trailers_allocine.get_trailers( movies[0].id );
						
					},
					complete: function() {
						$( wpml_trailers._spinner ).removeClass( 'visible' );
					},
					error: function() {}
				});
			};

			/**
			 * Show a list of movies matching the current Movie.
			 * 
			 * Allociné autocomplete returns a few movies that can
			 * possibly match the movie we want, a user choice must
			 * be made. Display titles and posters just like the 
			 * select list in Metadata Metabox.
			 * 
			 * @since    1.0
			 * 
			 * @param    int    movies Available movies matching the current Movie
			 */
			wpml.trailers.allocine.select = function( movies ) {

				if ( ! movies.length )
					return false;

				$( wpml_trailers._select ).empty();

				$.each( movies, function() {

					if ( undefined == this.thumbnail )
						this.thumbnail = 'http://fr.web.img1.acsta.net/c_160_240/commons/emptymedia/empty_photo.jpg';
					else
						this.thumbnail = this.thumbnail.replace( '75_100', '160_240' );

					$( wpml_trailers._select ).append( '<div class="wpml-select-movie"><a id="allocine_' + this.id + '" href="#" onclick="wpml_trailers_allocine.get_trailers( ' + this.id + ' ); return false;"><img src="' + this.thumbnail + '" /><em>' + this.title1 + '</em></a></div>' );
				} );
			};

			/**
			 * Get the movie Trailers.
			 * 
			 * Movie page is downloaded using WordPress' HTTP Api and
			 * parsed to extract the Trailers data (media ID, title and
			 * thumbnail if any).
			 * 
			 * First result is set as the default trailer, other are
			 * add below. Form inputs are filled.
			 * 
			 * @param    int    movie_id The movie ID 
			 * 
			 * @since    1.0
			 */
			wpml.trailers.allocine.get_trailers = function( movie_id ) {

				$( wpml_trailers._select ).empty();

				wpml._get({
					data: {
						action: 'wpml_load_allocine_page',
						nonce: wpml.get_nonce( 'search-trailer' ),
						movie_id: movie_id
					},
					beforeSend: function() {
						$( wpml_trailers._spinner ).addClass( 'visible' );
					},
					error: function( response ) {
						wpml_state.clear();
						$.each( response.responseJSON.errors, function() {
							wpml_state.set( this, 'error' );
						});
					},
					success: function( response ) {
						featured = ( '' == $( wpml_trailers._frame ).find( 'iframe' ).attr( 'src' ) );
						$.each( response.data, function( i, item ) {
							var data = JSON.stringify( this );
							    data = data.replace( "'", "\\u0027" );
							$( wpml_trailers._list ).append( '<div class="wpml-select-trailer"><a href="#" onclick="wpml_trailers_allocine.load_trailer( ' + this.id + ', ' + this.movie_id + ' ); return false;"><img src="' + this.thumbnail + '" alt="' + this.title + '" /> <span>' + this.title + '</span></a><input type="hidden" id="trailer_data_' + this.id + '" value=\'\' /></div>' );
							$( '#trailer_data_' + this.id ).val( data );

							if ( featured ) {
								wpml_trailers_allocine.load_trailer( this.id, this.movie_id );
								featured = false;
							}
						});
					},
					complete: function() {
						$( wpml_trailers._spinner ).removeClass( 'visible' );
					}
				});
			};

			/**
			 * Update the Metabox content with select Trailer.
			 * 
			 * Fill the needed form inputs and user inputs.
			 * 
			 * @param    int    media_id The trailer media ID
			 * @param    int    movie_id The movie ID 
			 * 
			 * @since    1.0
			 */
			wpml.trailers.allocine.load_trailer = function( media_id, movie_id ) {

				var    url = 'http://www.allocine.fr/_video/iblogvision.aspx?cmedia=' + media_id,
				      link = 'http://www.allocine.fr/video/player_gen_cmedia=' + media_id + '&cfilm=' + movie_id + '.html',
				      code = '<iframe src="' + url + '" width="640" height="320px" frameborder="0" allowfullscreen></iframe>',
				 shortcode = '[movie_trailer id="' + $( wpml_trailers._post_id ).val() + '"]',
				      data = $( '#trailer_data_' + media_id ).val();
				$( wpml_trailers._frame ).find( 'iframe' ).prop( 'src', url );
				$( wpml_trailers._frame ).find( 'iframe' ).attr( 'src', url );
				$( wpml_trailers._frame ).find( '#wpml_trailer_url' ).val( url );
				$( wpml_trailers._frame ).find( '#wpml_trailer_page' ).val( link );
				$( wpml_trailers._frame ).find( '#wpml_trailer_code' ).val( code );
				$( wpml_trailers._trailer ).val( media_id );
				$( wpml_trailers._trailer_data ).val( data );
				$( wpml_trailers._frame ).show();
			};

		/**
		 * Default Trailers source: TMDb API.
		 * 
		 * @since    1.0
		 */
		wpml.trailers.tmdb = wpml_trailers_tmdb = {

			search: function() {},
		};

			/**
			 * Search for Trailers using TMDb API.
			 * 
			 * @since    1.0
			 */
			wpml.trailers.tmdb.search = function() {

				wpml._get({
					data: {
						action: 'wpml_search_trailer',
						nonce: wpml.get_nonce( 'search-trailer' ),
						source: $( wpml_trailers._source ).val(),
						tmdb_id: $( wpml_trailers._tmdb_id ).val(),
						post_id: $( wpml_trailers._post_id ).val()
					},
					beforeSend: function() {
						$( wpml_trailers._spinner ).addClass( 'visible' );
					},
					error: function( response ) {
						wpml_state.clear();
						$.each( response.responseJSON.errors, function() {
							wpml_state.set( this, 'error' );
						});
					},
					success: function( response ) {
						$.each( response.data, function() {
							var data = JSON.stringify( this );
							    data = data.replace( "'", "\\u0027" );
							this.thumbnail = 'http://img.youtube.com/vi/' + this.id + '/mqdefault.jpg';
							$( wpml_trailers._list ).append( '<div class="wpml-select-trailer"><a href="#" onclick="wpml_trailers_tmdb.load_trailer( \'' + this.id + '\' ); return false;"><img src="' + this.thumbnail + '" alt="' + this.title + '" /> <span>' + this.title + '</span></a><input type="hidden" id="trailer_data_' + this.id + '" value=\'\' /></div>' );
							$( '#trailer_data_' + this.id ).val( data );

							featured = ( '' == $( wpml_trailers._frame ).find( 'iframe' ).attr( 'src' ) );
							if ( featured ) {
								wpml_trailers_tmdb.load_trailer( this.id );
							}
						});
					},
					complete: function() {
						$( wpml_trailers._spinner ).removeClass( 'visible' );
					}
				});
			};

			/**
			 * Update the Metabox content with select Trailer.
			 * 
			 * Fill the needed form inputs and user inputs.
			 * 
			 * @param    int    media_id The trailer media ID
			 * 
			 * @since    1.0
			 */
			wpml.trailers.tmdb.load_trailer = function( id ) {

				var    url = 'https://www.youtube.com/embed/' + id,
				      link = 'https://www.youtube.com/watch?v=' + id,
				      code = '<iframe src="' + url + '" width="640" height="320px" frameborder="0" allowfullscreen></iframe>',
				 shortcode = '[movie_trailer id="' + $( wpml_trailers._post_id ).val() + '"]',
				      data = $( '#trailer_data_' + id ).val();
				$( wpml_trailers._frame ).find( 'iframe' ).prop( 'src', url );
				$( wpml_trailers._frame ).find( 'iframe' ).attr( 'src', url );
				$( wpml_trailers._frame ).find( '#wpml_trailer_url' ).val( url );
				$( wpml_trailers._frame ).find( '#wpml_trailer_page' ).val( link );
				$( wpml_trailers._frame ).find( '#wpml_trailer_code' ).val( code );
				$( wpml_trailers._frame ).find( '#wpml_trailer_shortcode' ).val( shortcode );
				$( wpml_trailers._trailer ).val( id );
				$( wpml_trailers._trailer_data ).val( data );
				$( wpml_trailers._frame ).show();
			};

		/**
		 * Clean up the Trailers Metabox.
		 * 
		 * @since    1.0
		 */
		wpml.trailers.empty = function() {

			$( wpml_trailers._select ).empty();
			$( wpml_trailers._list ).empty();
			$( wpml_trailers._trailer ).val( '' );
			$( wpml_trailers._trailer_data ).val( '' );
			$( wpml_trailers._trailers ).val( '' );
		};

		/**
		 * Remove current Trailer.
		 * 
		 * @since    1.1
		 */
		wpml.trailers.remove = function() {

			wpml._post({
				data: {
					action: 'wpml_remove_trailer',
					nonce: wpml.get_nonce( 'remove-trailer' ),
					post_id: $( wpml_trailers._post_id ).val()
				},
				beforeSend: function() {
					$( wpml_trailers._spinner ).addClass( 'visible' );
				},
				error: function( response ) {
					wpml_state.clear();
					$.each( response.responseJSON.errors, function() {
						wpml_state.set( this, 'error' );
					});
				},
				success: function( response ) {
					wpml_trailers.empty();
					$( wpml_trailers._frame ).find( 'iframe' ).prop( 'src', '' );
					$( wpml_trailers._frame ).find( 'iframe' ).attr( 'src', '' );
					$( wpml_trailers._frame ).find( '#wpml_trailer_url' ).val( '' );
					$( wpml_trailers._frame ).find( '#wpml_trailer_page' ).val( '' );
					$( wpml_trailers._frame ).find( '#wpml_trailer_code' ).val( '' );
					$( wpml_trailers._frame ).find( '#wpml_trailer_shortcode' ).val( '' );
					$( wpml_trailers._trailer ).val( '' );
					$( wpml_trailers._trailer_data ).val( '' );
					$( wpml_trailers._frame ).hide();
				},
				complete: function() {
					$( wpml_trailers._spinner ).removeClass( 'visible' );
				}
			});
		};
