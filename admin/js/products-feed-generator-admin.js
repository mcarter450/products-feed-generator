(function( $ ) {
	'use strict';

	jQuery(document).ready(function($) {

		$('#generate_feed').on('click', function(e) {
			//console.log('generate feed');
			var data = {
				'action': 'generate_google_products_feed'
				//'whatever': 1234
			};

			var pluginUrl = jsVars.pluginUrl;

			var $spinner = $('<img src="'+ pluginUrl +'/admin/images/spinner-3.gif">');

			var $load_icon = $(this).next('.load-icon').append($spinner);
			var $view_url = $load_icon.next('.view-url');
			$load_icon.text(''); // Clear any error messages
			$load_icon.append($spinner);

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(response) {
				//console.log(response);
				$spinner.remove();
				if ( response.success ) {
					$view_url.css('display', 'inline');
				}
				else {
					var error = response.data.shift()
					$load_icon.text( 'Error: '+ error.message );
				}
			});
		});
		
	});

})( jQuery );
