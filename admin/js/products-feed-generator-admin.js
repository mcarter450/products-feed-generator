(function( $ ) {
	'use strict';

	var added_options = [];

	function get_attribute_map_template(options) {
		var html =`
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label>Attribute map</label>
			</th>
			<td class="forminp forminp-text">
				<select name="pfg_product_attributes_key" id="pfg_product_attributes_key" style="width:200px;">
					<option value="g:size">Google: Size</option>
					<option value="g:color">Google: Color</option>
					<option value="g:material">Google: Material</option>
					<option value="g:pattern">Google: Pattern</option>
				</select>
				<select name="pfg_product_attributes_val" id="pfg_product_attributes_val" style="width:200px;">
					${options}
				</select><br>
				<button id="add_attribute_mapping">Add mapping</button>
				<!--p class="description">Google field</p-->
				<div id="attrib_map"></div>
			</td>
		</tr>`;

		return html;
	}

	function click_del_attribute_mapping(e) {

		let parent = $(this).parent();

		let key = parent.data('key');

		if (added_options.indexOf(key) != -1) {
			delete added_options[key];
		}

		$('#pfg_product_attributes_key > option').each(function(i) {
			let option = $(this);
			if (key == option.val()) {
				$(this).prop('disabled', false);
			}
		});

		parent.fadeOut(300, function() { $(this).remove(); });

		return false;

	}

	function click_add_attribute_mapping(e) {

		let key = $('#pfg_product_attributes_key').val();

		if (key === null) {
			return false;
		}

		if (added_options.indexOf(key) == -1) {
			added_options.push(key);
		}

		$('#pfg_product_attributes_key > :selected').prop('disabled', true);

		let val = $('#pfg_product_attributes_val').val();

		let $btn_del = $('<button id="del_attribute_mapping">Delete</button>');

		$btn_del.on('click', click_del_attribute_mapping);

		let $attribs = $(`<div id="${key}-${val}" data-key="${key}"><span class="field">${key}</span> -> <span class="attrib">${val}</span></div>`);

		$attribs.append($btn_del);

		$('#attrib_map').append($attribs);

		return false;

	}

	function click_generate_feed(e) {

		let data = {
			'action': 'generate_google_products_feed'
			//'whatever': 1234
		};

		let pluginUrl = jsVars.pluginUrl;

		let $spinner = $('<img src="'+ pluginUrl +'/admin/images/spinner-3.gif">');

		let $load_icon = $(this).next('.load-icon').append($spinner);
		let $view_url = $load_icon.next('.view-url');

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
				let error = response.data.shift()
				$load_icon.text( 'Error: '+ error.message );
			}
		});

	}

	jQuery(document).ready(function($) {

		let options = '';

		for (let key in jsVars.attributes) {
			options += `<option value="${key}">${jsVars.attributes[key]}</option>`;
		}

		$('#pfg_product_material').closest('tr').after(get_attribute_map_template(options));

		$('#add_attribute_mapping').on('click', click_add_attribute_mapping);
		$('#generate_feed').on('click', click_generate_feed);
		
	});

})( jQuery );
