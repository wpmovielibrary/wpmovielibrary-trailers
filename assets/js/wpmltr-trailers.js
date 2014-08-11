
wpml = wpml || {};

var wpml_trailers

	wpml.trailers = wpml_trailers = {

		_search: '#wpml-search-trailers',
		_source: '#wpml-trailer-source',
		_tmdb_id: '#tmdb_data_tmdb_id'
	};

		wpml.trailers.init = function() {

			$( wpml_trailers._search ).on( 'click', function( e ) {
				e.preventDefault();
				wpml_trailers.search();
			} );
		};

		wpml.trailers.search = function() {

			wpml._get({
				data: {
					action: 'wpml_search_trailer',
					nonce: wpml.get_nonce( 'search-trailer' ),
					source: $( wpml_trailers._source ).val(),
					tmdb_id: $( wpml_trailers._tmdb_id ).val()
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