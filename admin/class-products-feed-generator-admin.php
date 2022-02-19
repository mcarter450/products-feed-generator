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

	/**
	 * @since    1.0.0
	 * @var      string
	 */
	protected $debug_log;

	/**
	 * @since    1.0.0
	 * @var      array
	 */
	protected $attributes_map;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string    $plugin_name       The name of this plugin.
	 * @param string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->debug_log = get_option('pfg_product_debug_log', 'no');
		$this->attributes_map = get_option('pfg_product_attributes_map');
		
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

	/**
	 * @since    1.0.0
	 * @param array $value
	 */
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

		$name = __( 'Generate feed', 'products-feed-generator' );

		?>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $description['tooltip_html']; ?>
			</th>

			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<button
					name ="<?php echo $name; ?>"
					id   ="<?php echo esc_attr( $value['id'] ); ?>"
					type="button"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo $name; ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
				><?php echo $name ?></button><span class="load-icon"></span>
				<?php if ( file_exists($feed_file) ): ?>
					<a id="view_feed_url" class="view-url" target="_blank" href="<?php echo $feed_url; ?>">View feed →</a>
				<?php else: ?>
					<a id="view_feed_url" class="view-url" style="display:none;" target="_blank" href="<?php echo $feed_url; ?>">View feed →</a>
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

		$WC_Shipping = new WC_Shipping();
		$shipping_classes = $WC_Shipping->get_shipping_classes();

		foreach ($shipping_classes as $key => $value) {
			$settings[] = array(
				'name'   => "&quot;{$value->name}&quot; class",
				'id'     => "pfg_product_shipping_class_{$key}",
				'type'   => 'text',
				'desc_tip'  => __( 'Google shipping label value', 'products-feed-generator' ), 
				'placeholder' => $value->slug,
			);
		}

		$settings[] = array(
			'name'   => __( 'Feed Name', 'products-feed-generator' ),
			'id'     => 'pfg_product_feed_name',
			'type'   => 'text',
			'desc_tip'  => __( 'File name of products feed', 'products-feed-generator' ),
			'placeholder' => 'google_products_feed.xml',
		);
		$settings[] = array(
			'name'   => __( 'Cron Schedule', 'products-feed-generator' ),
			'id'     => 'pfg_cron_schedule',
			'type'   => 'select',
			'desc_tip'  => __( 'The cron schedule is based on the current system time. For example you select the "Daily" schedule and save changes at 2:00 pm, the task would be scheduled to run at 2:00 pm the following day.', 'products-feed-generator' ),
			'options' => array(
				'daily' => __( 'Daily', 'products-feed-generator' ),
				'twicedaily' => __( 'Twice Daily', 'products-feed-generator' ),
				'hourly' => __( 'Hourly', 'products-feed-generator' ),
				'weekly' => __( 'Weekly', 'products-feed-generator' ),
			),
		);
		
		$settings[] = array(
			'name' => __( 'Feed Actions', 'products-feed-generator' ),
			'type' => 'button',
			'desc' => __( 'Generate or view XML feed', 'products-feed-generator'),
			'desc_tip' => true,
			'class' => 'button-secondary btn-gen-feed',
			'id'	=> 'generate_feed',
		);
		$settings[] = array(
			'name'   => __( 'Debug Logging', 'products-feed-generator' ),
			'id'     => 'pfg_product_debug_log',
			'type'   => 'checkbox',
			'desc'  => __( 'Log debug and info messages to WC status log', 'products-feed-generator' ), 
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
	 * Save custom settings
	 *
	 * @since    1.0.0
	 */
	public function woo_save_settings() {

		$attributes_map = array();

		$section = sanitize_key( $_GET['section'] );
		if ($section == 'pfg') {
			foreach ($_POST as $key => $value) {
				if (stripos($key, 'attrib_map_') !== false) {
					$attribkey = substr( $key, strlen('attrib_map_') );
					$attribval = sanitize_key($value);

					$attributes_map[$attribkey] = $attribval;
				}
			}
		}

		$this->attributes_map = $attributes_map;

		update_option('pfg_product_attributes_map', $attributes_map);

		$cron_schedule = sanitize_key($_POST['pfg_cron_schedule']);

		if ( $cron_schedule and wp_next_scheduled('generate_google_products_feed') ) {

			$cron_event = wp_get_scheduled_event('generate_google_products_feed');
			if ($cron_schedule != $cron_event->schedule) {
				// Schedule new cron task
				if ($this->debug_log == 'yes') {
					wc_get_logger()->info('Update scheduled cron task', array( 'source' => 'products-feed-generator' ) );
				}
				wp_clear_scheduled_hook('generate_google_products_feed');
				wp_schedule_event(time(), $cron_schedule, 'generate_google_products_feed');
			}

		} else {
			if ($this->debug_log == 'yes') {
				wc_get_logger()->info('Create new scheduled cron task', array( 'source' => 'products-feed-generator' ) );
			}
			wp_schedule_event(time(), $cron_schedule, 'generate_google_products_feed');
		}
		
	}

	/**
	 * Write XML product feed to disk
	 *
	 * @since    1.0.0
	 */
	public function generate_google_products_feed() {

		// Load the WooCommerce logger
		if ($this->debug_log == 'yes') {
			wc_get_logger()->info('Generate google shopping feed', array( 'source' => 'products-feed-generator' ) );
		}

		$feed_dir = '';
		if ( $upload_dir = wp_upload_dir() ) {
			$feed_dir = $upload_dir['basedir'] . '/woo-products-feed-generator';

			$feed_name = get_option('pfg_product_feed_name', 'google_products_feed.xml');

			$feed_file = $feed_dir .'/'. $feed_name;
		} else {
			$error = new WP_Error( '001', 'No upload dir found.' );

			wp_send_json_error($error);
		}

		$product_variants = get_option('pfg_product_variants', 'parent_only');

		$xml_writer = new Products_Feed_Generator_Google_Shopping_XML_Writer($feed_file, $product_variants);

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

		$parent_products = array();

		// get parent products first
		foreach ($posts as $post) {

			$product = wc_get_product($post->ID);
			$parent_desc = $product->get_description();
			$children = $product->get_children();
			$post_title = $post->post_title;

			$xml_writer->add_canonical_url($product->get_id(), $product->get_permalink());

			if ( $product_variants == 'parent_only' or 
				 $product_variants == 'parent_and_variants' ) {

				$xml_writer->write_product_data( $product, $parent_desc );

			}

			// Get all child variants
			if ( $product_variants == 'variants_only' or 
				 $product_variants == 'parent_and_variants' ) {

				foreach ($children as $variant_id) {
					$product = wc_get_product($variant_id);

					$xml_writer->write_product_data( $product, $parent_desc );
				}

			}

		}

		$xml_writer->close(); // Close and write document to file

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
			'attributes_map' => $this->attributes_map,
		);

		wp_localize_script( $this->plugin_name, 'jsVars', $local_vars );

	}

}
