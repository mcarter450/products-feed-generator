<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.kahoycrafts.com
 * @since             1.0.0
 * @package           Products_Feed_Generator
 *
 * @wordpress-plugin
 * Plugin Name:       Products Feed Generator
 * Plugin URI:        https://www.kahoycrafts.com/products-feed-generator
 * Description:       Generates an XML Products Feed for Google Merchant Center in RSS 2.0 format.
 * Version:           1.0.7
 * Author:            Mike Carter
 * Author URI:        https://www.kahoycrafts.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       products-feed-generator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRODUCTS_FEED_GENERATOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-products-feed-generator-activator.php
 */
function activate_products_feed_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-products-feed-generator-activator.php';
	Products_Feed_Generator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-products-feed-generator-deactivator.php
 */
function deactivate_products_feed_generator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-products-feed-generator-deactivator.php';
	Products_Feed_Generator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_products_feed_generator' );
register_deactivation_hook( __FILE__, 'deactivate_products_feed_generator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-products-feed-generator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_products_feed_generator() {

	$plugin = new Products_Feed_Generator();
	$plugin->run();

}
run_products_feed_generator();
