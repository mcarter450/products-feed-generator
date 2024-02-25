<?php

/**
 * Write XML for google shopping feed.
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes/xml-writers
 */

/**
 * Represents the data model for given product feed.
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes/xml-writers
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Google_Shopping_XML_Writer {

	/**
	 * @var XMLWriter
	 */
	protected $writer;

	/**
	 * Hash of canonical urls
	 *
	 * @since 	1.0.0
	 * @var array 
	 */	
	protected $canonical_urls = array();

	/**
	 * Map of product attributes 
	 *
	 * @since 	1.0.0
	 * @var array 	
	 */	
	protected $attributes_map = array();

	/**
	 * Map of shipping classes 
	 *
	 * @since 	1.0.0
	 * @var array 
	 */	
	protected $shipping_class_map = array();

	/**
	 * @since 	1.0.0
	 * @var string
	 */	
	protected $default_brand;

	/**
	 * @since 	1.0.0
	 * @var string
	 */	
	protected $default_material;

	/**
	 * @since 	1.0.0
	 * @var string
	 */	
	protected $woo_currency;

	/**
	 * @since 	1.0.0
	 * @var array 	Map of product attributes
	 */	
	protected $woo_weight_unit;

	/**
	 * Identifier exists (yes|no) 
	 *
	 * @since 	1.0.0
	 * @var string
	 */	
	protected $identifier_exists;

	/**
	 * Show product variants (yes|no)
	 *
	 * @since 	1.0.0
	 * @var string
	 */	
	protected $product_variants;

	/**
	 * Show product details (yes|no)
	 *
	 * @since 	1.0.0
	 * @var string
	 */	
	protected $product_details_section;

	/**
	 * Constructor
	 *
	 * @param string $feed_file
	 * @param string $product_variants
	 * @param return Products_Feed_Generator_XML_Writer
	 */
	public function __construct($feed_file, $product_variants) {

		$bloginfo = array();

		$bloginfo['name'] = get_bloginfo('name');
		$bloginfo['url'] = get_bloginfo('url');
		$bloginfo['description'] = get_bloginfo('description');

		$this->default_brand = get_option('pfg_product_brand', '');
		$this->default_material = get_option('pfg_product_material', '');
		$this->woo_currency = get_option('woocommerce_currency', '');
		$this->woo_weight_unit = get_option('woocommerce_weight_unit', '');
		$this->identifier_exists = get_option('pfg_product_identifiers', 'no');
		$this->product_variants = $product_variants = get_option('pfg_product_variants', 'parent_only');
		$this->product_details_section =  get_option('pfg_product_details_section', 'no');

		$attributes_map = get_option('pfg_product_attributes_map');
		$this->attributes_map = $attributes_map['reverse'] ?? array();
		$this->build_shipping_class_map();

		$this->writer = $writer = new XMLWriter(); 
		if (! $writer->openURI($feed_file) ) {
			error_log($feed_file);
			throw new Exception('File could not be created.');
		}  
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(4);
		$writer->startElement('rss');
		$writer->writeAttribute('version', '2.0');
		$writer->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
		$writer->startElement('channel');
		$writer->writeElement('title', $bloginfo['name']);
		$writer->writeElement('link', $bloginfo['url']);
		$writer->writeElement('description', $bloginfo['description']);

	}

	/**
	 * @return void
	 */
	protected function build_shipping_class_map() {

		$WC_Shipping = new WC_Shipping();
		$shipping_classes = $WC_Shipping->get_shipping_classes();

		foreach ($shipping_classes as $key => $value) {
			$this->shipping_class_map[$value->slug] = get_option("pfg_product_shipping_class_{$key}", $value->slug);
		}

	}

	/**
	 * @param integer $product_id
	 * @param string $permalink
	 */
	public function add_canonical_url($product_id, $permalink) {
		$this->canonical_urls[$product_id] = $permalink;
	}

	/**
	 * Write XML data and close file
	 *
	 * @return integer Number of bytes written
	 */
	public function close() {

		$writer = $this->writer;
		$writer->endElement(); // end channel
		$writer->endElement(); // end rss
		$writer->endDocument();
		return $writer->flush();

	}

	/**
	 * Write XML for product details section
	 *
	 * @param string $attribute
	 * @param string $value
	 * @return void
	 */
	protected function write_product_details( $attribute, $value ) {

		$writer = $this->writer;

		$writer->startElement('g:product_detail');

		if ($value) {
			$writer->writeElement('g:section_name', 'Additional information');
			$writer->writeElement('g:attribute_name', $attribute);
			$writer->writeElement('g:attribute_value', $value);
		}

		$writer->endElement(); // end product_detail

	}

	protected function get_variation_title( $product, $title, &$attributes ) {

		foreach ($attributes as $key => $value) {
			$attribute = $value;
			if ( is_scalar($value) && !empty($value) ) {
				if ( stripos($key, 'pa_') !== false ) {
					$attribute = $product->get_attribute($key);
					$attributes[$key] = $attribute;
				}
				$title .= " - {$attribute}"; 
			}
		}

		return $title;

	}

	/**
	 * Write XML for product data
	 *
	 * @param XMLWriter $writer
	 * @param WC_Product|WC_Product_Variation $product
	 * @param WC_Product $parent_product
	 * @return void
	 */
	public function write_product_data( $product, $parent_product = null ) {

		$writer = $this->writer;

		$id = $product->get_sku() ?: $product->get_id();
		
		$link = $product->get_permalink();

		$description = $product->get_description();

		$price = '';
		$sale_price = '';
		$woo_currency = $this->woo_currency;
		$identifier_exists = $this->identifier_exists;

		if ( $reg_price = $product->get_price() ) {
			$price = "$reg_price $woo_currency";
		}
		if ( $sale_price = $product->get_sale_price() ) {
			$reg_price = $product->get_regular_price();

			$price = "$reg_price $woo_currency";
			$sale_price = "$sale_price $woo_currency";
		}
		
		$availability = 'out_of_stock';
		if ( $product->get_stock_status() == 'instock' || $product->get_type() == 'external' ) {
			$availability = 'in_stock';
		}

		$google_category = $product->get_meta('_product_google_category') ?: '';

		$brand = $product->get_meta('_product_brand') ?: $this->default_brand;

		if ( $identifier_exists == 'yes' ) {
			$gtin = $product->get_meta('_product_gtin');
			$mpn = $product->get_meta('_product_mpn');
			$condition = $product->get_meta('_product_condition');
			if ( isset($parent_product) ) {
				$gtin = $product->get_meta('variable_product_gtin');
				$mpn = $product->get_meta('variable_product_mpn');
			}
		}

		// Obtain custom meta data from parent product
		if ( $this->product_variants == 'variants_only' and isset($parent_product) ) {
			$description = $parent_product->get_description();
			$google_category = $parent_product->get_meta('_product_google_category') ?: '';
			$brand = $parent_product->get_meta('_product_brand') ?: $this->default_brand;
			if ( $identifier_exists == 'yes' ) {
				$condition = $parent_product->get_meta('_product_condition');
			}
		}

		$shipping_weight = '';
		if ( $weight = $product->get_weight() ) {
			$shipping_weight = $weight .' '. $this->woo_weight_unit;
		}

		$shipping_label = '';
		if ( $shipping_class = $product->get_shipping_class() ) {
			$shipping_label = $this->shipping_class_map[$shipping_class];
		}

		// Returns collection of WC_Product_Attribute objects
		$attributes = $product->get_attributes();

		$title = $product->get_title();

		// $attributes is amended by reference
		$title = $this->get_variation_title($product, $title, $attributes);

		// Get Google product thumbnail override or default to featured image 
		$image_link = $product->get_meta('_product_image_thumbnail') ?: wp_get_attachment_url( $product->get_image_id() );

		$writer->startElement('item');

		$writer->writeElement('g:id', $id);
		$writer->writeElement('title', $title);
		$writer->writeElement('link', $link);
		
		if ( is_a($product, 'WC_Product_Variation') ) {

			$parent_id = $product->get_parent_id();
			$parent_data = $product->get_parent_data();
			$item_group_id = $parent_data['sku'] ?: $parent_id;

			$writer->writeElement('g:item_group_id', $item_group_id);
			$writer->writeElement('g:canonical_link', $this->canonical_urls[$parent_id]);

		} elseif ( $this->product_variants == 'parent_and_variants' ) {

			$writer->writeElement('g:item_group_id', $id);
			$writer->writeElement('g:canonical_link', $link);

		}

		$writer->writeElement('description', $description);
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
				$writer->writeElement('g:additional_image_link', $image_link);
			}
		}

		$materials_written = false;

		// Map any custom attributes
		foreach ($attributes as $key => $attribute) {

			$global_key = '';
			if ( stripos($key, 'pa_') !== false ) {
				$global_key = substr($key, strlen('pa_'));
			}

			if ( is_object($attribute) and is_a($product, 'WC_Product') ) {

				$label = $attr_name = $attribute->get_name();
				$options = $attribute->get_options();

				// Handle global attributes or taxonomies 
				if ( stripos($attr_name, 'pa_') !== false ) {
					$label = wc_attribute_label($attr_name);
					$options = $product->get_attribute($attr_name);

					$global_key = substr($attr_name, strlen('pa_'));

					if ( isset($this->attributes_map[$global_key]) && $this->attributes_map[$global_key] == 'material') {
						if ($this->default_material) {
							$options = $this->default_material . ", {$options}";
						}
						$materials = preg_replace('/[ ]*,[ ]*/i', '/', $options);

						$materials_written = $writer->writeElement('g:material', $materials);
					} 
					elseif ($this->default_material) {
						$materials_written = $writer->writeElement('g:material', $this->default_material);
					}

				} 
				// Handle product level only (unmapped) attributes
				elseif ( is_array($options) ) {
					$options = implode( ', ', $options );
				}

				// Writes both mapped and unmapped attributes to product details section
				if ( $this->product_details_section == 'yes' ) {
					$this->write_product_details($label, $options);
				}

			} 
			elseif ( isset($this->attributes_map[$global_key]) ) {
				// Variation for global product attribute
				$gfield = $this->attributes_map[$global_key];

				if ($gfield == 'material') {
					$material = $this->default_material ?  "{$this->default_material}/$attribute" : $attribute;
					$materials_written = $writer->writeElement("g:{$gfield}", $material);
				} 
				else {
					$writer->writeElement("g:{$gfield}", $attribute);
				}
			}

		}

		// Write default material if not handled in attributes loop
		if (! $materials_written && $this->default_material ) {
			$materials_written = $writer->writeElement('g:material', $this->default_material);
		}

		$writer->endElement(); // end item

	}

}
