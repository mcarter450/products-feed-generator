<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/admin
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	protected $shipping_class_map = array();

	protected $bloginfo = array();

	protected $feed_url;

	protected $canonical_urls = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->bloginfo['name'] = get_bloginfo('name');
		$this->bloginfo['url'] = get_bloginfo('url');
		$this->bloginfo['description'] = get_bloginfo('description');

	}

	/**
	 * Add section to WooCommerce shipping settings
	 *
	 * @since    1.0.0
	 * @param array Sections
	 * @return array Sections
	 */
	public function woo_add_section( $sections ) {

		$sections['pfg'] = __( 'Products Feed Generator', 'products-feed-generator' );
		return $sections;
		
	}

	function woo_add_admin_field_button( $value ) {
		$option_value = (array) WC_Admin_Settings::get_option( $value['id'] );
		$description = WC_Admin_Settings::get_field_description( $value );

		$feed_url = '';
		if ( $upload_dir = wp_upload_dir() ) {
			$feed_base_dir = $upload_dir['basedir'] . '/woo-products-feed-generator';
			$feed_base_url = $upload_dir['baseurl'] . '/woo-products-feed-generator';

			$feed_name = get_option('pfg_product_feed_name', 'google_products_feed.xml') ?: 'google_products_feed.xml';
			$feed_url = $feed_base_url .'/'. $feed_name;
			$feed_file = $feed_base_dir .'/'. $feed_name;
		}

		?>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $description['tooltip_html']; ?>
			</th>

			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">

				<button
				name ="<?php echo esc_attr( $value['name'] ); ?>"
				id   ="<?php echo esc_attr( $value['id'] ); ?>"
				type="button"
				style="<?php echo esc_attr( $value['css'] ); ?>"
				value="<?php echo esc_attr( $value['name'] ); ?>"
				class="<?php echo esc_attr( $value['class'] ); ?>"
				><?php echo esc_attr( $value['name'] ); ?></button><span class="load-icon"></span>
				<?php if ( file_exists($feed_file) ): ?>
					<a class="view-url" target="_blank" href="<?php echo $feed_url; ?>">View feed</a>
				<?php else: ?>
					<a class="view-url" style="display:none;" target="_blank" href="<?php echo $feed_url; ?>">View feed</a>
				<?php endif; ?>
				<?php echo $description['description']; ?>

			</td>
		</tr>

		<?php       
	}

	/**
	 * Add section to WooCommerce shipping settings
	 *
	 * @since    1.0.0
	 * @param array $settings
	 * @param string $current_section
	 * @return array Settings
	 */
	public function woo_all_settings( $settings, $current_section ) {
		/**
		 * Check the current section is what we want
		 **/
		if ( $current_section != 'pfg' ) {
			return $settings; // If not, return the standard settings
		}

		$settings = array();
		// Add Title to the Settings
		$settings[] = array( 
			'name'   => __( 'Products Feed Generator Settings', 'products-feed-generator' ), 
			'type'   => 'title', 
			'desc'   => __( 'The following options are used to configure the products feed and all optional settings', 'products-feed-generator' ), 
			'id'     => 'pfg' 
		);
		$settings[] = array(
			'name'   => __( 'Default Brand', 'products-feed-generator' ),
			'id'     => 'pfg_product_brand',
			'type'   => 'text',
			'placeholder' => 'Initech',
			'desc' => __( 'Default when brand is not assigned at product level', 'products-feed-generator' ),
		);
		$settings[] = array(
			'name'   => __( 'Unique Identifiers', 'products-feed-generator' ),
			'id'     => 'pfg_product_identifieres',
			'type'   => 'checkbox',
			'desc'  => __( 'Include unique identifiers (GTIN, mpn) on product edit page', 'products-feed-generator' ), 
		);
		$settings[] = array(
			'name'   => __( 'Product Variants', 'products-feed-generator' ),
			'id'     => 'pfg_product_variants',
			'type'   => 'radio',
			'options' => array(
				'parent_only' => __( 'Parent products only', 'products-feed-generator' ),
				'parent_and_variants' => __( 'Parent products + Variations', 'products-feed-generator' ),
				'variants_only' => __( 'Variations only', 'products-feed-generator' ),
			),
			//'desc'  => __( 'Include product variations in feeds', 'products-feed-generator' ), 
		);
		$settings[] = array(
			'name'   => __( 'Default Material', 'products-feed-generator' ),
			'id'     => 'pfg_product_material',
			'type'   => 'text',
			'placeholder' => 'wood',
			'desc' => __( 'Default material to include with each product', 'products-feed-generator' ),
		);
		// $settings[] = array(
		// 	'name'   => __( 'Attribute map', 'products-feed-generator' ),
		// 	'id'     => 'pfg_product_attributes_map',
		// 	'type'   => 'textarea',
		// 	'placeholder' => "attribute-slug: google_field\nlength: size\nmaterial: material",
		// 	'style' => 'height:100px;',
		// 	'desc' => __( 'Map product attribute to Google field, use YAML syntax', 'products-feed-generator' ),
		// );
		$settings[] = array(
			'name'   => __( 'Attribute map', 'products-feed-generator' ),
			'id'     => 'pfg_product_attributes_map',
			'type'   => 'hidden',
			'style' => 'height:100px;',
			//'desc' => __( 'Map product attribute to Google field, use YAML syntax', 'products-feed-generator' ),
		);

		$WC_Shipping = new WC_Shipping();
		$shipping_classes = $WC_Shipping->get_shipping_classes();

		foreach ($shipping_classes as $key => $value) {
			$settings[] = array(
				'name'   => "&quot;{$value->name}&quot; class",
				'id'     => "pfg_product_shipping_class_{$key}",
				'type'   => 'text',
				'desc'  => __( 'Google shipping label value', 'products-feed-generator' ), 
				'placeholder' => $value->slug,
			);
		}

		$settings[] = array(
			'name'   => __( 'XML Feed Name', 'products-feed-generator' ),
			'id'     => 'pfg_product_feed_name',
			'type'   => 'text',
			'placeholder' => 'google_products_feed.xml',
		);
		$settings[] = array(
			'name'   => __( 'Cron Schedule', 'products-feed-generator' ),
			'id'     => 'pfg_cron_schedule',
			'type'   => 'select',
			'options' => array(
				'daily' => __( 'Daily', 'products-feed-generator' ),
				'twicedaily' => __( 'Twice Daily', 'products-feed-generator' ),
				'hourly' => __( 'Hourly', 'products-feed-generator' ),
				'weekly' => __( 'Weekly', 'products-feed-generator' ),
			),
		);
		$settings[] = array(
			'name' => __( 'Generate Feed' ),
			'type' => 'button',
			//'desc' => __( 'Generate Feed'),
			'desc_tip' => true,
			'class' => 'button-secondary',
			'id'	=> 'generate_feed',
		);

		$settings[] = array( 'type' => 'sectionend', 'id' => 'pfg' );

		return $settings;

	}

	/**
	 * Render custom product fields
	 *
	 * @since    1.0.0
	 */
	public function woocommerce_product_custom_fields() {

		global $woocommerce, $post;

		$product_identifieres = get_option('pfg_product_identifieres', 'no');

		echo '<div class="product_feed_settings">';

		woocommerce_wp_text_input(
			array(
				'id' => '_product_brand',
				'label' => __('Google brand', 'products-feed-generator'),
				'desc_tip' => true,
				'description' => __('Define brand for this product.', 'products-feed-generator'),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id' => '_product_image_thumbnail',
				'label' => __('Google thumbnail', 'products-feed-generator'),
				'data_type' => 'url',
				'desc_tip' => true,
				'description' => __('Define a thumbnail image URL for this product.', 'products-feed-generator'),
			)
		);

		if ( $product_identifieres == 'yes' ) {

			woocommerce_wp_text_input(
				array(
					'id' => '_product_gtin',
					'label' => __('Google GTIN', 'products-feed-generator'),
					'desc_tip' => true,
					'description' => __('Define GTIN (Global Trade Item Number) for this product.', 'products-feed-generator'),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id' => '_product_mpn',
					'label' => __('Google MPN', 'products-feed-generator'),
					'desc_tip' => true,
					'description' => __('Define MPN (Manufacturer Part Number) only if GTIN does not exist.', 'products-feed-generator'),
				)
			);

			woocommerce_wp_radio(
				array(
					'id' => '_product_condition',
					'label' => __('Condition', 'products-feed-generator'),
					//'desc_tip' => true,
					'options' => array(
						'new' =>  __('New', 'products-feed-generator'),
						'used' => __('Used', 'products-feed-generator'),
						'refurbished' => __('Refurbished', 'products-feed-generator'),
					),
					//'description' => __('Define condition of this product.', 'products-feed-generator'),
				)
			);

		}

		woocommerce_wp_text_input(
			array(
				'id' => '_product_google_category',
				'label' => __('Google category', 'products-feed-generator'),
				'desc_tip' => true,
				'style' => 'width:150px;',
				'description' => __('Define ID number for Google product category', 'products-feed-generator'),
			)
		);

		echo '</div>';

	}

	/**
	 * Save free shipping badge setting
	 *
	 * @since    1.0.0
	 * @param integer $post_id
	 */
	public function woocommerce_product_custom_fields_save( $post_id ) {

		$product_brand = sanitize_text_field( $_POST['_product_brand'] );
		$product_thumb = sanitize_url( $_POST['_product_image_thumbnail'] );
		$product_gtin = sanitize_key( $_POST['_product_gtin'] );
		$product_mpn = sanitize_key( $_POST['_product_mpn'] );
		$product_google_category = intval( $_POST['_product_google_category'] );
		$product_condition = sanitize_key( $_POST['_product_condition'] );

		update_post_meta( $post_id, '_product_brand', $product_brand );
		update_post_meta( $post_id, '_product_image_thumbnail', $product_thumb );
		update_post_meta( $post_id, '_product_gtin', $product_gtin );
		update_post_meta( $post_id, '_product_mpn', $product_mpn );
		update_post_meta( $post_id, '_product_google_category', $product_google_category );
		update_post_meta( $post_id, '_product_condition', $product_condition );

	}

	/**
	 * Write XML for product details section
	 */
	public function write_product_details( $writer, $attribute, $value ) {

		$writer->startElement('g:product_detail');

		if ($value) {
			$writer->writeElement('g:section_name', 'Additional information');
			$writer->writeElement('g:attribute_name', $attribute);
			$writer->writeElement('g:attribute_value', $value);
		}

		$writer->endElement(); // end product_detail

	}

	/**
	 * Write XML for product data
	 */
	public function write_product_data( $writer, $product, $parent_desc, $default_brand, $woo_currency, $woo_weight_unit, $identifier_exists, $product_variants ) {

		$id = $product->get_sku() ?: $product->get_id();
		//$title = htmlspecialchars($post->post_title, ENT_XML1, 'UTF-8');
		
		$link = $product->get_permalink();

		$description = $product->get_description() ?: $parent_desc;
        //$model->description = htmlspecialchars($description, ENT_XML1, 'UTF-8');

        $price = '';
        $sale_price = '';

		if ( $reg_price = $product->get_price() ) {
			$price = "$reg_price $woo_currency";
		}
		if ( $sale_price = $product->get_sale_price() ) {
			$reg_price = $product->get_regular_price();

			$price = "$reg_price $woo_currency";
			$sale_price = "$sale_price $woo_currency";
		}
		
		$availability = 'out_of_stock';
		if ( $product->get_stock_quantity() > 0 ) {
			$availability = 'in_stock';
		}

		$google_category = $product->get_meta('_product_google_category');

		$brand = $product->get_meta('_product_brand') ?: $default_brand;

		if ( $identifier_exists == 'yes' ) {
			$gtin = $product->get_meta('_product_gtin');
			$mpn = $product->get_meta('_product_mpn');
			$condition = $product->get_meta('_product_condition');
		}

		$shipping_weight = '';
		if ( $weight = $product->get_weight() ) {
			$shipping_weight = $weight .' '. $woo_weight_unit;
		}

		$shipping_label = '';
		if ( $shipping_class = $product->get_shipping_class() ) {
			$shipping_label = $this->shipping_class_map[$shipping_class];
		}

		$materials = 'wood';
		// if ($material = $product->get_attribute('Material')) {
		// 	if ( stripos($material, '|') !== false ) {
		// 		$materials .= '/'. str_replace(' | ', '/', strtolower($material));
		// 	} else {
		// 		$materials .= '/'. strtolower($material);
		// 	}
		// }

		$attributes = $product->get_attributes();

		$title = $product->get_title();

		foreach ($attributes as $key => $value) {
			if ( is_scalar($value) ) {
				$title .= " - {$value}"; 
			}
		}

		// Get Google product thumbnail override or default to featured image 
		$image_link = $product->get_meta('_product_image_thumbnail') ?: wp_get_attachment_url( $product->get_image_id() );

		$writer->startElement('item');

		$writer->writeElement('g:id', $id);
		$writer->writeElement('g:title', $title);
		$writer->writeElement('g:link', $link);
		
		if ( is_a($product, 'WC_Product_Variation') ) {

			$parent_id = $product->get_parent_id();
			$parent_data = $product->get_parent_data();
			$item_group_id = $parent_data['sku'] ?: $parent_id;

			$writer->writeElement('g:item_group_id', $item_group_id);
			$writer->writeElement('g:canonical_link', $this->canonical_urls[$parent_id]);

		} elseif ( $product_variants == 'parent_and_variants' ) {

			$writer->writeElement('g:item_group_id', $id);
			$writer->writeElement('g:canonical_link', $link);

		}

		$writer->writeElement('g:description', $description);
		$writer->writeElement('g:price', $price);
		$writer->writeElement('g:sale_price', $sale_price);
		$writer->writeElement('g:availability', $availability);
		$writer->writeElement('g:brand', $brand);
		$writer->writeElement('g:google_product_category', $google_category);
		$writer->writeElement('g:identifier_exists', $identifier_exists);
		if ( $identifier_exists == 'yes' ) {
			$writer->writeElement('g:gtin', $gtin);
			$writer->writeElement('g:mpn', $mpn);
			$writer->writeElement('g:condition', $condition);
		}
		$writer->writeElement('g:shipping_weight', $shipping_weight);
		$writer->writeElement('g:shipping_label', $shipping_label);
		$writer->writeElement('g:image_link', $image_link);

		// Only available for parent product and not variations
		if ($attachment_images = $product->get_gallery_image_ids()) {
			$attachment_images = array_slice($attachment_images, 0, 10);
			foreach ($attachment_images as $attachment_id) {
				$image_link = wp_get_attachment_image_src($attachment_id, 'woocommerce_single')[0];
				//$image_links[] = htmlspecialchars($image_link);
				$writer->writeElement('g:additional_image_link', $image_link);
			}
		}

		if ( $materials ) {
			//$writer->writeElement('g:material', $materials);
		}
		// if ( $lengths ) {
		// 	$this->write_product_details($writer, 'Length', $lengths);
		// }
		// if ( $finish ) {
		// 	$this->write_product_details($writer, 'Finish', $finish);
		// }

		$attrib_map = $this->get_attrib_map( get_option('pfg_product_attributes_map') );

		//error_log( print_r($attrib_map, 1) );

		foreach ($attributes as $key => $value) {
			if ( is_object($value) ) {
				$options = implode( ', ', $value->get_options() );

				$this->write_product_details($writer, $value->get_name(), $options);
			} elseif ( array_key_exists($key, $attrib_map) ) {

				//error_log($key);
				//error_log($value);
				$gfield = trim($attrib_map[$key]);
				//error_log("g:". $gfield);
				$writer->writeElement("g:{$gfield}", $value);
			} else {
				$this->write_product_details($writer, $key, $value);
			}
		}

		$writer->endElement(); // end item

	}

	private function get_attrib_map($text) {

		$separator = "\r\n";
		$line = strtok($text, $separator);

		$map = array();

		while ($line !== false) {
		    # do something with $line
		    $attribs = explode(':', $line);
		    //$map[$attribs[0]] = $attribs[1];
		    $map[$attribs[0]] = $attribs[1];
		    $line = strtok( $separator );
		}

		return $map;

	}

	/**
	 * Write XML product feed to disk
	 */
	public function generate_google_products_feed() {

		error_log('Generate google products feed');

		$feed_dir = '';
		if ( $upload_dir = wp_upload_dir() ) {
			$feed_dir = $upload_dir['basedir'] . '/woo-products-feed-generator';

			$feed_name = get_option('pfg_product_feed_name', 'google_products_feed.xml') ?: 'google_products_feed.xml';

			$feed_file = $feed_dir .'/'. $feed_name;
		} else {
			$error = new WP_Error( '001', 'No upload dir found.' );

			wp_send_json_error($error);
		}

		$WC_Shipping = new WC_Shipping();
		$shipping_classes = $WC_Shipping->get_shipping_classes();
		//$shipping_classes = get_terms( array('taxonomy' => 'product_shipping_class', 'hide_empty' => false ) );

		foreach ($shipping_classes as $key => $value) {
			$this->shipping_class_map[$value->slug] = get_option("pfg_product_shipping_class_{$key}", $value->slug);
		}

		//error_log( print_r($shipping_classes, 1) );

		$writer = new XMLWriter();  
		$writer->openURI($feed_file);   
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(4);
		$writer->startElement('rss');
		$writer->writeAttribute('version', '2.0');
		$writer->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
		$writer->startElement('channel');
		$writer->writeElement('title', $this->bloginfo['name']);
		$writer->writeElement('link', $this->bloginfo['url']);
		$writer->writeElement('description', $this->bloginfo['description']);

		$total_records = 100;

		$params = [
			'post_type' => 'product',
			'posts_per_page' => $total_records,
		];

		$query = new WP_Query();

		if (! $posts = $query->query($params) ) {
			$error = new WP_Error( '002', 'No products found.' );

			wp_send_json_error($error);
		}

		$default_brand = get_option('pfg_product_brand', '');
		$woo_currency = get_option('woocommerce_currency');
		$woo_weight_unit = get_option('woocommerce_weight_unit');
		$identifier_exists = get_option('pfg_product_identifieres', 'no');
		$product_variants = get_option('pfg_product_variants', 'parent_only');

		$parent_products = array();

		// get parent products first
		foreach ($posts as $post) {

			$product = wc_get_product($post->ID);
			$parent_desc = $product->get_description();
			$children = $product->get_children();
			$post_title = $post->post_title;

			$this->canonical_urls[$product->get_id()] = $product->get_permalink();

			if ( $product_variants == 'parent_only' or 
				 $product_variants == 'parent_and_variants' or
				 count($children) == 0 ) {

				$this->write_product_data( 
					$writer, 
					$product, 
					$parent_desc,
					$default_brand, 
					$woo_currency, 
					$woo_weight_unit, 
					$identifier_exists, 
					$product_variants 
				);

			}

			// Get all child variants
			if ( $product_variants == 'variants_only' or 
				 $product_variants == 'parent_and_variants' ) {

				foreach ($children as $variant_id) {

					$product = wc_get_product($variant_id);

					$this->write_product_data( 
						$writer, 
						$product, 
						$parent_desc,
						$default_brand, 
						$woo_currency, 
						$woo_weight_unit, 
						$identifier_exists, 
						$product_variants
					);
				}
			}

		}

		// if ( $product_variants == 'variants_only' or $product_variants == 'parent_and_variants' ) {

		// 	$params['post_type'] = 'product_variation';
		// 	$params['posts_per_page'] = $total_records;

		// 	if ( $posts = $query->query($params) ) {

		// 		foreach ($posts as $post) {

		// 			$product = wc_get_product($post->ID);
		// 			$post_title = $post->post_title;

		// 			if ( is_a($product, 'WC_Product_Variation') ) {
		// 				$this->write_product_data( 
		// 					$writer, 
		// 					$product, 
		// 					$post_itle,
		// 					$default_brand, 
		// 					$woo_currency, 
		// 					$woo_weight_unit, 
		// 					$identifier_exists, 
		// 					'yes'
		// 				);
		// 			}

		// 		}

		// 	}

		// }

		$writer->endElement(); // end channel
		$writer->endElement(); // end rss
		$writer->endDocument();
		$writer->flush();

		wp_send_json_success(array(
	        'ready' => true
	    ));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/products-feed-generator-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $wpdb;

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/products-feed-generator-admin.js', array( 'jquery' ), $this->version, false );

		if ( $filtered_attributes = $_COOKIE['pfg_attribute_map'] ) {
			$filtered_attributes = json_decode(stripslashes($filtered_attributes));
		}
		else {

			$results = $wpdb->get_results( "SELECT meta_value FROM {$wpdb->prefix}postmeta where meta_key = '_product_attributes' LIMIT 100", OBJECT );

			$filtered_attributes = array();

			foreach ($results as $row) {
				$attributes = unserialize($row->meta_value);
				if ( is_array($attributes) ) {
					foreach ($attributes as $key => $value) {
						if ($value['is_visible'] and $value['is_variation']) {
							$filtered_attributes[$key] = $value['name'];
						}
					}
				}
			}

			$json = json_encode($filtered_attributes);

			// Only set cookie cache if less than 4096 bytes
			if ( mb_strlen($json, '8bit') < 4096 ) {
				setcookie("pfg_attribute_map", $json, time()+3600); // Expires in 1 hour
			}

		} 

		$local_vars = array( 
			'pluginUrl' => ( plugins_url() .'/'. $this->plugin_name ),
			'attributes' => $filtered_attributes,
		);

		wp_localize_script( $this->plugin_name, 'jsVars', $local_vars );

	}

}
