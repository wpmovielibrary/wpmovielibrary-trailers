
wpml = wpml || {};

var wpml_trailers

	wpml.trailers = wpml_trailers = {

		_search: '#wpml-search-trailers',
		_source: '#wpml-trailer-source',
		_tmdb_id: '#tmdb_data_tmdb_id',
		_post_id: '#post_ID',
		_title: '#tmdb_data_title',
		_list: '#wpml-trailers-list'
	};

		wpml.trailers.init = function() {

			$( wpml_trailers._search ).on( 'click', function( e ) {
				e.preventDefault();
				wpml_trailers.search();
			} );
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
					beforeSend: function() {},
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
					complete: function() {},
					error: function() {}
				});
			};

			wpml.trailers.allocine.load_page = function( movie_id ) {

				wpml._get({
					data: {
						action: 'wpml_load_allocine_page',
						nonce: wpml.get_nonce( 'search-trailer' ),
						movie_id: movie_id
					},
					error: function( response ) {
						wpml_state.clear();
						$.each( response.responseJSON.errors, function() {
							wpml_state.set( this, 'error' );
						});
					},
					success: function( response ) {
						$( wpml_trailers._list ).text( response.data[0] );
					}
				});
			};

			wpml.trailers.allocine.select = function( movies ) {

				if ( ! movies.length )
					return false;

				$list = $( '<div/>', { id: 'wpml-trailers-movies-select' } );
				$( wpml_trailers._list ).append( $list );

				$.each( movies, function() {
					$list.append( '<div class="tmdb_select_movie"><a id="allocine_' + this.id + '" href="#" onclick="wpml_trailers_allocine.load_page( ' + this.id + ' ); return false;"><img src="' + this.thumbnail + '" /><em>' + this.title1 + '</em></a></div>' );
				} );
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
					error: function( response ) {
						wpml_state.clear();
						$.each( response.responseJSON.errors, function() {
							wpml_state.set( this, 'error' );
						});
					},
					success: function( response ) {
						console.log( response );
					}
				});
			};

	wpml_trailers.init();