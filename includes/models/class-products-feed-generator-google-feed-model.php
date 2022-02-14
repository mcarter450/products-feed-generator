<?php

/**
 * The google shopping feed data model.
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes/models
 */

/**
 * Represents the data model for given product feed.
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes/models
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Google_Feed_Model {

	/**
	 * Array of stored values
	 */
	protected $values = array();

	/**
	 * Array of defined fields
	 */
	protected $fields = array(

		'id', // SKU or product id if not set
		'title',
		'description',
		'link',
		'image_link',
		'availability',
		'price',

		'brand', //Required (For all new products, except movies, books, and musical recording brands)
		'GTIN', //Required (For all new products with a GTIN assigned by the manufacturer)
		'mpn', //Required (Only if your new product does not have a manufacturer assigned GTIN)
		'identifier_exists', // Use to indicate whether or not the unique product identifiers (UPIs) GTIN, MPN, and brand are available for your product.

		'availability_date', // required if availability=backorder|preorder,
		'additional_image_link', // Many
		'sale_price',
		'sale_price_effective_date',

		'google_product_category', // Map to WooCommerce Category
		'product_type', // Product category that you define for your product. Include the full category. For example, include Home > Women > Dresses > Maxi Dresses instead of just Dresses

		'condition', // Required if your product is used or refurbished
		'adult', // Required (If a product contains adult content)
		'mulitpack', // For grouped products - The number of identical products sold within a merchant-defined multipack
		'is_bundle', // For grouped products - yes|no
		'age_group', //Required (For all apparel products that are targeted to people in Brazil, France, Germany, Japan, the UK, and the US as well as all products with assigned age groups)
		'color', //Required (For all apparel products in feeds that are targeted to Brazil, France, Germany, Japan, the UK, and the US as well as all products available in different colors)
		'gender', // Required (Required for all apparel items in feeds that are targeted to people in Brazil, France, Germany, Japan, the UK, and the US as well as all gender-specific products)
		'material', // Required (if relevant for distinguishing different products in a set of variants)
		'pattern', // Required (if relevant for distinguishing different products in a set of variants)
		'size', // Apparel
		'size_type', // Apparel
		'size_system', // Apprel 
		'item_group_id', // Variants - SKU or product variant id if not set

		'product_length',
		'product_width',
		'product_height',
		'product_weight',
		'product_detail', // Custom attributes

		'shopping_ads_excluded_country]', // Global
		'shipping_weight',
		'shipping_length',
		'shipping_width',
		'shipping_height',
		'ships_from_country',
		'tax', // Your product’s sales tax rate in percent + shipping tax

	);

	public function __construct() {

	}

	public function __isset($key) {

		if ( in_array($key, $this->fields) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return mixed  Value if defined or null
	 */
	public function __set($key, $value) {

		if ( in_array($key, $this->fields) ) {
			$this->values[$key] = $value;
		}

	}

	/**
	 * @return mixed  Value if defined or null
	 */
	public function __get($key) {

		if (isset($this->values[$key])) {
			return $this->values[$key];
		}

		return null;

	}

}