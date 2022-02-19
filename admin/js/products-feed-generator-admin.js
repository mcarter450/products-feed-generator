(function( $ ) {
	'use strict';

	/**
	 * Array of all added options
	 *
	 * @since 1.0.0
	 * @type {object}
	 */
	var added_options = [];

	/**
	 * Array of google custom fields
	 *
	 * @since 1.0.0
	 * @type {object}
	 */
	var google_fields = {
		'size': 'Google: Size',
		'color': 'Google: Color',
		'material': 'Google: Material',
		'pattern': 'Google: Pattern'
	};

	/**
	 * @since 1.0.0
	 * @param {string} options
	 * @return {string} The html with template vars applied
	 */
	function get_attribute_map_template(google_options, attrib_options, attrib_map) {

		let html =`
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label>Attribute map</label>
			</th>
			<td class="forminp forminp-text">
				<select name="pfg_product_attributes_key" id="pfg_product_attributes_key" style="width:200px;">
					${google_options}
				</select>
				<select name="pfg_product_attributes_val" id="pfg_product_attributes_val" style="width:200px;">
					${attrib_options}
				</select><br>
				<button id="add_attribute_mapping" class="button-secondary btn-add-mapping">Add mapping</button>
				<!--p class="description">Google field</p-->
				<div id="attrib_map">
					${attrib_map}
				</div>
			</td>
		</tr>`;

		return html;

	}

	/**
	 * @since 1.0.0
	 * @param {string} key
	 * @param {string} val
	 * @return {string} The html with template vars applied
	 */
	function get_attribute_template(key, val) {

		let label = jsVars.attributes[val];

		let html = `
		<div id="${key}-${val}" data-key="${key}" class="attrib-set">
			<input type="hidden" name="attrib_map_${key}" name="attrib_map_${key}" value="${val}">
			<span class="field">${google_fields[key]}</span> \u2192 <span class="attrib">${label}</span>
			<button id="del_attribute_mapping_${key}">Delete</button>
		</div>`;

		return html;

	}

	/**
	 * @since 1.0.0
	 * @param {object} e 	jQuery event object
	 * @return {boolean} False to prevent event from bubbling
	 */
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

	/**
	 * @since 1.0.0
	 * @param {object} e 	jQuery event object
	 * @return {boolean} False to prevent event from bubbling
	 */
	function click_add_attribute_mapping(e) {

		$(this).blur();

		let key = $('#pfg_product_attributes_key').val();
		let val = $('#pfg_product_attributes_val').val();

		if (key === null) {
			return false;
		}

		if (added_options.indexOf(key) == -1) {
			added_options.push(key);
		}

		$('#pfg_product_attributes_key > :selected').prop('disabled', true);

		let $attrib = $(get_attribute_template(key, val));

		$attrib.find(`#del_attribute_mapping_${key}`).on('click', click_del_attribute_mapping);

		$('#attrib_map').append($attrib);

		return false;

	}

	/**
	 * @since 1.0.0
	 * @param {object} e 	jQuery event object
	 */
	function click_generate_feed(e) {

		let data = {
			'action': 'generate_google_products_feed'
			//'whatever': 1234
		};

		let pluginUrl = jsVars.pluginUrl;

		let $spinner = $('<img src="'+ pluginUrl +'/admin/images/spinner-3.gif">');

		var $btn_el = $(this).prop('disabled', true);

		let $load_icon = $btn_el.next('.load-icon').append($spinner);
		let $view_url = $load_icon.next('.view-url');

		$load_icon.text(''); // Clear any error messages
		$load_icon.append($spinner);

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {

			$spinner.remove();
			$btn_el.blur().prop('disabled', false);
			if (response.success) {
				$view_url.show();
			}
			else {
				let error = response.data.shift()
				$load_icon.text( 'Error: '+ error.message );
			}
			
		});

	}

	/**
	 * Handle dom ready event
	 *
	 * @since 1.0.0
	 */
	jQuery(document).ready(function($) {

		let attrib_options = '';
		let google_options = '';
		let attrib_map = '';

		var attributes_map = jsVars.attributes_map;

		for (let key in google_fields) {
			if (attributes_map[key]) {
				attrib_map += get_attribute_template(key, attributes_map[key]);

				google_options += `<option value="${key}" disabled="disabled">${google_fields[key]}</option>`;
			} else {
				google_options += `<option value="${key}">${google_fields[key]}</option>`;
			}
		}

		for (let key in jsVars.attributes) {
			attrib_options += `<option value="${key}">${jsVars.attributes[key]}</option>`;
		}

		let $attribute_map_template = $(get_attribute_map_template(google_options, attrib_options, attrib_map));

		$attribute_map_template.find('#add_attribute_mapping').on('click', click_add_attribute_mapping);
		$attribute_map_template.find('[id^=del_attribute_mapping_]').on('click', click_del_attribute_mapping);

		$('#pfg_product_material').closest('tr').after($attribute_map_template);
		$('#view_feed_url').on('click', function(e) {
			o
			$(this).blur();
		});
		$('#generate_feed').on('click', click_generate_feed);
		
	});

})( jQuery );
