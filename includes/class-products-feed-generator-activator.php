<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Activator {

	/**
	 * Plugin activation
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		if ( class_exists('WC_product') ) {
			if (! wp_next_scheduled('generate_google_products_feed') ) {
				wp_schedule_event(time(), 'daily', 'generate_google_products_feed');
			}
		}
		if ( $upload_dir = wp_upload_dir() ) {
			$feed_dir = $upload_dir['basedir'] . '/woo-products-feed-generator';
			if (! file_exists($feed_dir) ) {
				mkdir($feed_dir);
			}
		}
		
	}

}
