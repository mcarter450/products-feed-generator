<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/includes
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Products_Feed_Generator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PRODUCTS_FEED_GENERATOR_VERSION' ) ) {
			$this->version = PRODUCTS_FEED_GENERATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'products-feed-generator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Products_Feed_Generator_Loader. Orchestrates the hooks of the plugin.
	 * - Products_Feed_Generator_i18n. Defines internationalization functionality.
	 * - Products_Feed_Generator_Admin. Defines all hooks for the admin area.
	 * - Products_Feed_Generator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-products-feed-generator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-products-feed-generator-i18n.php';

		/**
		 * The class responsible for writing the google product feed data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/xml-writers/class-products-feed-generator-google-shopping-xml-writer.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-products-feed-generator-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-products-feed-generator-public.php';

		$this->loader = new Products_Feed_Generator_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Products_Feed_Generator_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Products_Feed_Generator_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Products_Feed_Generator_Admin( $this->get_plugin_name(), $this->get_version() );

		if ( is_admin() ) {
			
			$tab =  isset($_GET['tab']) ? sanitize_key( $_GET['tab'] ) : '';
			$page = isset($_GET['page']) ? sanitize_key( $_GET['page'] ) : '';
			$section = isset($_GET['section']) ? sanitize_key( $_GET['section'] ) : '';

			if ($page == 'wc-settings' and $tab == 'products' and $section == 'pfg') {
				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			} else {
				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_woo_styles' );
			}

			$this->loader->add_filter( 'woocommerce_get_sections_products', $plugin_admin, 'woo_add_section', 10, 1 );
			$this->loader->add_filter( 'woocommerce_get_settings_products', $plugin_admin, 'woo_all_settings', 10, 2 );
			$this->loader->add_action( 'woocommerce_admin_field_button', $plugin_admin, 'woo_add_admin_field_button' );
			$this->loader->add_action( 'woocommerce_settings_save_products', $plugin_admin, 'woo_save_settings' );
			$this->loader->add_action( 'woocommerce_product_options_general_product_data', $plugin_admin, 'woo_custom_fields', 10, 0);
			$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'woo_custom_fields_save', 10, 1 );

			$this->loader->add_action( 'woocommerce_variation_options_pricing', $plugin_admin, 'woo_custom_fields_to_variations', 10, 3 );
			$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'woo_custom_fields_to_variations_save', 10, 2 );

			$this->loader->add_action( 'woocommerce_attribute_deleted', $plugin_admin, 'woo_attribute_deleted', 10, 3 );
			
			$this->loader->add_action( 'wp_ajax_generate_google_products_feed', $plugin_admin, 'generate_google_products_feed' );
			$this->loader->add_filter( 'plugin_action_links_'. $this->plugin_name .'/'. $this->plugin_name .'.php', $plugin_admin, 'pfg_settings_link' );
			
		}

		// Products feed generation hook
		$this->loader->add_action( 'generate_google_products_feed', $plugin_admin, 'generate_google_products_feed' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Products_Feed_Generator_Public( $this->get_plugin_name(), $this->get_version() );

		// No public functionaliy yet
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Products_Feed_Generator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
