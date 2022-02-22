(function( $ ) {
	'use strict';

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
		'pattern': 'Google: Pattern',
		'age_group': 'Google: Age Group',
		'gender': 'Google: Gender'
	};

	/**
	 * @since 1.0.0
	 * @param {string} options
	 * @return {string} The html with template vars applied
	 */
	function get_attribute_map_template(google_options, attrib_options, attrib_map) {

		let disabled = (google_options && attrib_options) ? '' : 'disabled="disabled"';

		let html =`
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label>Attribute Map</label>
			</th>
			<td class="forminp forminp-text">
				<div id="attrib_widget">
					<select name="pfg_product_attributes_key" id="pfg_product_attributes_key" ${disabled}>
						${google_options}
					</select>
					<select name="pfg_product_attributes_val" id="pfg_product_attributes_val" ${disabled}>
						${attrib_options}
					</select>
					<button id="add_attribute_mapping" class="button-secondary btn-add-mapping" ${disabled}>Add mapping</button>
				</div>
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

		if (! jsVars.attributes[val] ) {
			return '';
		}

		let label = jsVars.attributes[val];

		let html = `
		<div data-key="${key}" data-val="${val}" class="attrib-set">
			<input type="hidden" name="attrib_map_${key}" value="${val}">
			<span class="field">${google_fields[key]}</span> \u2192 <span class="attrib">${label}</span>
			<button id="del_attribute_mapping_${key}">Delete</button>
		</div>`;

		return html;

	}

	/**
	 * @since 1.0.0
	 * @param {string} key
	 * @param {string} val
	 */
	function add_options(key, val) {

		let $attrib_keys = $('#pfg_product_attributes_key');
		let $attrib_vals = $('#pfg_product_attributes_val');

		let label = jsVars.attributes[val];

		$attrib_keys.append(`<option value="${key}">${google_fields[key]}</option>`);
		$attrib_vals.append(`<option value="${val}">${label}</option>`);

		if ( $attrib_keys.children('option').length > 0 ) {
			$attrib_keys.prop('disabled', false);
			$attrib_vals.prop('disabled', false);
			$('#add_attribute_mapping').prop('disabled', false);
		}

	}

	/**
	 * @since 1.0.0
	 * @param {object} e 	jQuery event object
	 * @return {boolean} False to prevent event from bubbling
	 */
	function click_del_attribute_mapping(e) {

		let parent = $(this).parent();

		let key = parent.data('key');
		let val = parent.data('val');

		add_options(key, val);

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

		let $key_option = $('#pfg_product_attributes_key > :selected');
		let $val_option = $('#pfg_product_attributes_val > :selected');

		let key = $key_option.val();
		let val = $val_option.val();

		if (key === null) {
			return false;
		}

		$key_option.remove();
		$val_option.remove();

		let $attrib_keys = $('#pfg_product_attributes_key');
		let $attrib_vals = $('#pfg_product_attributes_val');

		if ( $attrib_keys.children('option').length == 0 || $attrib_vals.children('option').length == 0 ) {
			$attrib_keys.prop('disabled', true);
			$attrib_vals.prop('disabled', true);
			$('#add_attribute_mapping').prop('disabled', true);
		}

		let $attrib = $( get_attribute_template(key, val) );

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

		var $btn_el = $(this).prop('disabled', true);

		let $spinner = $('<img src="'+ pluginUrl +'/admin/images/spinner-3.gif">');

		let $load_icon = $btn_el.next('.load-icon').append($spinner);

		let $feed_error = $('#feed_management_error');

		$feed_error.text(''); // Clear any error messages
		$feed_error.css('padding', '0px');

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {

			$spinner.remove();
			$btn_el.blur().prop('disabled', false);

			if (response.success) {
				$('#view_feed_url').show();
			}
			else {

				let error = response.data.shift();
				$feed_error.css('padding', '15px 0px');
				$feed_error.text( 'Error: '+ error.message );
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

		let attributes_map = {};
		let attributes_map_rev = {};

		if (jsVars.attributes_map && jsVars.attributes_map.count) {
			attributes_map = jsVars.attributes_map.forward;
			attributes_map_rev = jsVars.attributes_map.reverse;
		}

		for (let key in google_fields) {
			if ( attributes_map[key] ) {
				attrib_map += get_attribute_template(key, attributes_map[key]);
			} else {
				google_options += `<option value="${key}">${google_fields[key]}</option>`;
			}
		}

		for (let key in jsVars.attributes) {
			if (! attributes_map_rev[key] ) {
				attrib_options += `<option value="${key}">${jsVars.attributes[key]}</option>`;
			}
		}

		let $attribute_map_template = $( get_attribute_map_template(google_options, attrib_options, attrib_map) );

		$attribute_map_template.find('#add_attribute_mapping').on('click', click_add_attribute_mapping);
		$attribute_map_template.find('[id^=del_attribute_mapping_]').on('click', click_del_attribute_mapping);

		$('#pfg_product_material').closest('tr').after($attribute_map_template);
		$('#view_feed_url').on('click', function(e) {
			$(this).blur();
		});
		$('#generate_feed').on('click', click_generate_feed);
		
	});

})( jQuery );
