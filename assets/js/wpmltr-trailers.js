
wpml = wpml || {};

var wpml_trailers

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
		_trailers: '#wpml_data_trailers',
		_spinner: '#wpml-trailers .spinner',
		_frame: '#wpml-trailer-frame'
	};

		wpml.trailers.search = function() {

			if ( 'allocine' == $( wpml_trailers._source ).val() )
				wpml_trailers.allocine.search();
			else
				wpml_trailers.tmdb.search();
		};

		wpml.trailers.allocine = wpml_trailers_allocine = {};

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
						
						$.each( response, function() {
							if ( this.title1 == title || this.title2 == title )
								movies.push( this );
						} );

						if ( movies.length > 1 ) {
							wpml_trailers_allocine.select( movies );
							return false;
						}

						wpml_trailers_allocine.load_page( movies[0].id );
						
					},
					complete: function() {
						$( wpml_trailers._spinner ).removeClass( 'visible' );
					},
					error: function() {}
				});
			};

			wpml.trailers.allocine.select = function( movies ) {

				if ( ! movies.length )
					return false;

				$( wpml_trailers._select ).empty();

				$.each( movies, function() {

					if ( undefined == this.thumbnail )
						this.thumbnail = 'http://fr.web.img1.acsta.net/c_160_240/commons/emptymedia/empty_photo.jpg';
					else
						this.thumbnail = this.thumbnail.replace( '75_100', '160_240' );

					$( wpml_trailers._select ).append( '<div class="wpml-select-movie"><a id="allocine_' + this.id + '" href="#" onclick="wpml_trailers_allocine.load_page( ' + this.id + ' ); return false;"><img src="' + this.thumbnail + '" /><em>' + this.title1 + '</em></a></div>' );
				} );
			};

			wpml.trailers.allocine.load_page = function( movie_id ) {

				$( wpml_trailers._select ).empty();
				$( wpml_trailers._list ).empty();

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
						featured = true;
						$.each( response.data, function( i, item ) {
							if ( featured ) {
								$( wpml_trailers._trailer ).val( this.media_id );
								wpml_trailers_allocine.load_player( this.media_id, this.movie_id );
								featured = false;
							}
							$( wpml_trailers._list ).append( '<div class="wpml-select-trailer"><a href="#" onclick="wpml_trailers_allocine.load_player( ' + this.media_id + ', ' + this.movie_id + ' ); return false;"><img src="' + this.thumbnail + '" alt="' + this.title + '" /> <span>' + this.title + '</span></a>' );
						});
						$( wpml_trailers._trailers ).val( JSON.stringify( response.data ) );
					},
					complete: function() {
						$( wpml_trailers._spinner ).removeClass( 'visible' );
					}
				});
			};

			wpml.trailers.allocine.load_player = function( media_id, movie_id ) {

				var url = 'http://www.allocine.fr/_video/iblogvision.aspx?cmedia=' + media_id,
				   link = 'http://www.allocine.fr/video/player_gen_cmedia=' + media_id + '&cfilm=' + movie_id + '.html',
				   code = '<iframe src="' + url + '" width="640" height="320px" frameborder="0"></iframe>';
				$( wpml_trailers._frame ).find( 'iframe' ).prop( 'src', url );
				$( wpml_trailers._frame ).find( 'iframe' ).attr( 'src', url );
				$( wpml_trailers._frame ).find( '#wpml_trailer_url' ).val( url );
				$( wpml_trailers._frame ).find( '#wpml_trailer_page' ).val( link );
				$( wpml_trailers._frame ).find( '#wpml_trailer_code' ).val( code );
				$( wpml_trailers._frame ).show();
			};

		wpml.trailers.tmdb = wpml_trailers_tmdb = {};

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
						console.log( response );
					},
					complete: function() {
						$( wpml_trailers._spinner ).removeClass( 'visible' );
					}
				});
			};

		wpml.trailers.empty = function() {

			$( wpml_trailers._select ).empty();
			$( wpml_trailers._list ).empty();
			$( wpml_trailers._trailer ).val( '' );
			$( wpml_trailers._trailers ).val( '' );
		};
