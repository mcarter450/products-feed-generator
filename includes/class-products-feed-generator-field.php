<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 */

/**
 * Represents a data field in a given product feed.
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Field {

	/**
	 * Name of field.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $name
	 */
	public $name;

	/**
	 * Required or optional attribute.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool    $required
	 */
	public $required;

	/**
	 * Name of value.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      mixed    $value
	 */
	public $value;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 * @param string $name
	 * @param bool $required
	 */
	public function __construct($name, $required = FALSE) {

		$this->name = $name;
		$this->required = $required;

	}

}
