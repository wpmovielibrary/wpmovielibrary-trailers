
wpmoly = wpmoly || {};

var wpmoly_trailers

	/**
	 * Get Trailers for your movies!
	 * 
	 * @since    1.0
	 */
	wpmoly.trailers = wpmoly_trailers = {

		_search: '#wpmoly-search-trailers',
		_empty: '#wpmoly-empty-trailers',
		_source: '#wpmoly-trailer-source',
		_tmdb_id: '#meta_data_tmdb_id',
		_post_id: '#post_ID',
		_title: '#meta_data_title',
		_list: '#wpmoly-trailers-list',
		_select: '#wpmoly-trailers-movies-select',
		_trailer: '#wpmoly_data_trailer',
		_trailer_data: '#wpmoly_data_trailer_data',
		_trailers: '#wpmoly_data_trailers',
		_spinner: '#wpmoly-trailers .spinner',
		_frame: '#wpmoly-trailer-frame',

		search: function() {},
		empty: function() {},
	};

		/**
		 * Get Trailers from... Where?
		 * 
		 * @since    1.0
		 */
		wpmoly.trailers.search = function() {

			if ( 'allocine' == $( wpmoly_trailers._source ).val() )
				wpmoly_trailers.allocine.search();
			else
				wpmoly_trailers.tmdb.search();
		};

		/**
		 * Get Trailers from Allociné
		 * 
		 * @since    1.0
		 */
		wpmoly.trailers.allocine = wpmoly_trailers_allocine = {

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
			wpmoly.trailers.allocine.search = function() {

				var title = $( wpmoly_trailers._title ).val(),
				   movies = [];

				$.ajax({
					type: 'GET',
					url: 'http://essearch.allocine.net/fr/autocomplete?q=' + title,
					beforeSend: function() {
						$( wpmoly_trailers._spinner ).addClass( 'visible' );
					},
					success: function( response ) {
						if ( ! response.length )
							return false;

						movies = response;
						if ( movies.length > 1 ) {
							wpmoly_trailers_allocine.select( movies );
							return false;
						}

						wpmoly_trailers_allocine.get_trailers( movies[0].id );
						
					},
					complete: function() {
						$( wpmoly_trailers._spinner ).removeClass( 'visible' );
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
			wpmoly.trailers.allocine.select = function( movies ) {

				if ( ! movies.length )
					return false;

				$( wpmoly_trailers._select ).empty();

				$.each( movies, function() {

					if ( undefined == this.thumbnail )
						this.thumbnail = 'http://fr.web.img1.acsta.net/c_160_240/commons/emptymedia/empty_photo.jpg';
					else
						this.thumbnail = this.thumbnail.replace( '75_100', '160_240' );

					if ( undefined == this.title1 )
						this.title1 = this.title2;

					$( wpmoly_trailers._select ).append( '<div class="wpmoly-select-movie"><a id="allocine_' + this.id + '" href="#" onclick="wpmoly_trailers_allocine.get_trailers( ' + this.id + ' ); return false;"><img src="' + this.thumbnail + '" /><em>' + this.title1 + '</em></a></div>' );
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
			wpmoly.trailers.allocine.get_trailers = function( movie_id ) {

				$( wpmoly_trailers._select ).empty();

				wpmoly._get({
					data: {
						action: 'wpmoly_load_allocine_page',
						nonce: wpmoly.get_nonce( 'search-trailer' ),
						movie_id: movie_id
					},
					beforeSend: function() {
						$( wpmoly_trailers._spinner ).addClass( 'visible' );
					},
					error: function( response ) {
						wpmoly_state.clear();
						$.each( response.responseJSON.errors, function() {
							wpmoly_state.set( this, 'error' );
						});
					},
					success: function( response ) {
						featured = ( '' == $( wpmoly_trailers._frame ).find( 'iframe' ).attr( 'src' ) );
						$.each( response.data, function( i, item ) {
							var data = JSON.stringify( this );
							    data = data.replace( "'", "\\u0027" );
							$( wpmoly_trailers._list ).append( '<div class="wpmoly-select-trailer"><a href="#" onclick="wpmoly_trailers_allocine.load_trailer( ' + this.id + ', ' + this.movie_id + ' ); return false;"><img src="' + this.thumbnail + '" alt="' + this.title + '" /> <span>' + this.title + '</span></a><input type="hidden" id="trailer_data_' + this.id + '" value=\'\' /></div>' );
							$( '#trailer_data_' + this.id ).val( data );

							if ( featured ) {
								wpmoly_trailers_allocine.load_trailer( this.id, this.movie_id );
								featured = false;
							}
						});
					},
					complete: function() {
						$( wpmoly_trailers._spinner ).removeClass( 'visible' );
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
			wpmoly.trailers.allocine.load_trailer = function( media_id, movie_id ) {

				var    url = 'http://www.allocine.fr/_video/iblogvision.aspx?cmedia=' + media_id,
				      link = 'http://www.allocine.fr/video/player_gen_cmedia=' + media_id + '&cfilm=' + movie_id + '.html',
				      code = '<iframe src="' + url + '" width="640" height="320px" frameborder="0" allowfullscreen></iframe>',
				 shortcode = '[movie_trailer id="' + $( wpmoly_trailers._post_id ).val() + '"]',
				      data = $( '#trailer_data_' + media_id ).val();
				$( wpmoly_trailers._frame ).find( 'iframe' ).prop( 'src', url );
				$( wpmoly_trailers._frame ).find( 'iframe' ).attr( 'src', url );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_url' ).val( url );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_page' ).val( link );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_code' ).val( code );
				$( wpmoly_trailers._trailer ).val( media_id );
				$( wpmoly_trailers._trailer_data ).val( data );
				$( wpmoly_trailers._frame ).show();
			};

		/**
		 * Default Trailers source: TMDb API.
		 * 
		 * @since    1.0
		 */
		wpmoly.trailers.tmdb = wpmoly_trailers_tmdb = {

			search: function() {},
		};

			/**
			 * Search for Trailers using TMDb API.
			 * 
			 * @since    1.0
			 */
			wpmoly.trailers.tmdb.search = function() {

				wpmoly._get({
					data: {
						action: 'wpmoly_search_trailer',
						nonce: wpmoly.get_nonce( 'search-trailer' ),
						source: $( wpmoly_trailers._source ).val(),
						tmdb_id: $( wpmoly_trailers._tmdb_id ).val(),
						post_id: $( wpmoly_trailers._post_id ).val()
					},
					beforeSend: function() {
						$( wpmoly_trailers._spinner ).addClass( 'visible' );
					},
					error: function( response ) {
						wpmoly_state.clear();
						$.each( response.responseJSON.errors, function() {
							wpmoly_state.set( this, 'error' );
						});
					},
					success: function( response ) {
						$.each( response.data, function() {
							var data = JSON.stringify( this );
							    data = data.replace( "'", "\\u0027" );
							this.thumbnail = 'http://img.youtube.com/vi/' + this.id + '/mqdefault.jpg';
							$( wpmoly_trailers._list ).append( '<div class="wpmoly-select-trailer"><a href="#" onclick="wpmoly_trailers_tmdb.load_trailer( \'' + this.id + '\' ); return false;"><img src="' + this.thumbnail + '" alt="' + this.title + '" /> <span>' + this.title + '</span></a><input type="hidden" id="trailer_data_' + this.id + '" value=\'\' /></div>' );
							$( '#trailer_data_' + this.id ).val( data );

							featured = ( '' == $( wpmoly_trailers._frame ).find( 'iframe' ).attr( 'src' ) );
							if ( featured ) {
								wpmoly_trailers_tmdb.load_trailer( this.id );
							}
						});
					},
					complete: function() {
						$( wpmoly_trailers._spinner ).removeClass( 'visible' );
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
			wpmoly.trailers.tmdb.load_trailer = function( id ) {

				var    url = 'https://www.youtube.com/embed/' + id,
				      link = 'https://www.youtube.com/watch?v=' + id,
				      code = '<iframe src="' + url + '" width="640" height="320px" frameborder="0" allowfullscreen></iframe>',
				 shortcode = '[movie_trailer id="' + $( wpmoly_trailers._post_id ).val() + '"]',
				      data = $( '#trailer_data_' + id ).val();
				$( wpmoly_trailers._frame ).find( 'iframe' ).prop( 'src', url );
				$( wpmoly_trailers._frame ).find( 'iframe' ).attr( 'src', url );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_url' ).val( url );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_page' ).val( link );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_code' ).val( code );
				$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_shortcode' ).val( shortcode );
				$( wpmoly_trailers._trailer ).val( id );
				$( wpmoly_trailers._trailer_data ).val( data );
				$( wpmoly_trailers._frame ).show();
			};

		/**
		 * Clean up the Trailers Metabox.
		 * 
		 * @since    1.0
		 */
		wpmoly.trailers.empty = function() {

			$( wpmoly_trailers._select ).empty();
			$( wpmoly_trailers._list ).empty();
			$( wpmoly_trailers._trailer ).val( '' );
			$( wpmoly_trailers._trailer_data ).val( '' );
			$( wpmoly_trailers._trailers ).val( '' );
		};

		/**
		 * Remove current Trailer.
		 * 
		 * @since    1.1
		 */
		wpmoly.trailers.remove = function() {

			wpmoly._post({
				data: {
					action: 'wpmoly_remove_trailer',
					nonce: wpmoly.get_nonce( 'remove-trailer' ),
					post_id: $( wpmoly_trailers._post_id ).val()
				},
				beforeSend: function() {
					$( wpmoly_trailers._spinner ).addClass( 'visible' );
				},
				error: function( response ) {
					wpmoly_state.clear();
					$.each( response.responseJSON.errors, function() {
						wpmoly_state.set( this, 'error' );
					});
				},
				success: function( response ) {
					wpmoly_trailers.empty();
					$( wpmoly_trailers._frame ).find( 'iframe' ).prop( 'src', '' );
					$( wpmoly_trailers._frame ).find( 'iframe' ).attr( 'src', '' );
					$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_url' ).val( '' );
					$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_page' ).val( '' );
					$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_code' ).val( '' );
					$( wpmoly_trailers._frame ).find( '#wpmoly_trailer_shortcode' ).val( '' );
					$( wpmoly_trailers._trailer ).val( '' );
					$( wpmoly_trailers._trailer_data ).val( '' );
					$( wpmoly_trailers._frame ).hide();
				},
				complete: function() {
					$( wpmoly_trailers._spinner ).removeClass( 'visible' );
				}
			});
		};
