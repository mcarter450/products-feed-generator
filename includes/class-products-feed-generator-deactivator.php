<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Deactivator {

	/**
	 * Cleanup files and variables left by plugin
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		$debug_log = get_option('pfg_product_debug_log');
		if ( $debug_log == 'yes' ) {
			wc_get_logger()->info('Remove scheduled cron task', array( 'source' => 'products-feed-generator' ) );
		}
		wp_clear_scheduled_hook('generate_google_products_feed');

		// Should user xml files be deleted?
		// if ( $upload_dir = wp_upload_dir() ) {
		// 	$feed_dir = $upload_dir['basedir'] . '/woo-products-feed-generator';
		// 	if ( file_exists($feed_dir) ) {
		// 		foreach (glob("{$feed_dir}/*.xml") as $filename) {
		// 		   unlink($filename);
		// 		}
		// 		rmdir($feed_dir);
		// 	}
		// }

		delete_option('pfg_product_brand');
		delete_option('pfg_product_identifiers');
		delete_option('pfg_product_details_section');
		delete_option('pfg_product_variants');
		delete_option('pfg_product_material');
		delete_option('pfg_product_attributes_map');

		$WC_Shipping = new WC_Shipping();
		$shipping_classes = $WC_Shipping->get_shipping_classes();

		foreach ($shipping_classes as $key => $value) {
			delete_option("pfg_product_shipping_class_{$key}");
		}

		delete_option('pfg_product_feed_name');
		delete_option('pfg_cron_schedule');
		delete_option('pfg_product_debug_log');

	}

}
