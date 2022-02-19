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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		if ($this->debug_log == 'yes') {
			wc_get_logger()->info('Remove scheduled cron task', array( 'source' => 'products-feed-generator' ) );
		}
		wp_clear_scheduled_hook('generate_google_products_feed');

		if ( $upload_dir = wp_upload_dir() ) {
			$feed_dir = $upload_dir['basedir'] . '/woo-products-feed-generator';
			if ( file_exists($feed_dir) ) {
				rmdir($feed_dir);
			}
		}

		delete_opton('pfg_description'); // remove later

		//delete_post_meta($post_id, $key);
	}

}
