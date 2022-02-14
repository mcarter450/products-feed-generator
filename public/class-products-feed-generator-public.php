<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.kahoycrafts.com
 * @since      1.0.0
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Products_Feed_Generator
 * @subpackage Products_Feed_Generator/public
 * @author     Mike Carter <mike@kahoycrafts.com>
 */
class Products_Feed_Generator_Public {

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

	// protected $product_images = [
	// 	84  => '/wp-content/uploads/2021/12/futuristwhiteoakpull_google.jpg',
	// 	86  => '/wp-content/uploads/2021/11/walnutplanterboxbookends-google-thumb.jpg',
	// 	87  => '/wp-content/uploads/2021/11/cityscapebookends-google-thumb.jpg',
	// 	89  => '/wp-content/uploads/2021/11/t-shapedoakdrawerpull-google-thumb.jpg',
	// 	90  => '/wp-content/uploads/2021/11/squarebarcabinetpull-google-thumb.jpg',
	// 	92  => '/wp-content/uploads/2021/11/l-shapedoakpull-google-thumb.jpg',
	// 	94  => '/wp-content/uploads/2021/11/squarecabinetknob-google-thumb.jpg',
	// 	96  => '/wp-content/uploads/2021/11/uturnaokdrawerpull-google-thumb.jpg',
	// 	98  => '/wp-content/uploads/2021/11/grocerylistholder-google-thumb.jpg',
	// 	99  => '/wp-content/uploads/2021/11/uprightcoasterholder-google-thumb.jpg',
	// 	100 => '/wp-content/uploads/2021/11/flatcoasterholder-google-thumb.jpg',
	// 	102 => '/wp-content/uploads/2021/11/farmhousecrate-google-thumb.jpg',
	// 	103 => '/wp-content/uploads/2021/11/squareplaterack-google-thumb.jpg',
	// 	739 => '/wp-content/uploads/2021/11/halfmoondrawerpull_google_thumb.jpg',
	// ];

	// protected $product_categories = [
	// 	84  => 4700,
	// 	90  => 4700,
	// 	96  => 4700,
	// 	98  => 638,
	// 	99  => 7238,
	// 	100 => 7238,
	// 	739 => 4700,
	// ];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Products_Feed_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Products_Feed_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/products-feed-generator-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Products_Feed_Generator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Products_Feed_Generator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/products-feed-generator-public.js', array( 'jquery' ), $this->version, false );

	}

}
