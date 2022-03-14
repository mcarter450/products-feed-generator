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
	 * @since    1.0.0
	 * @var      string
	 */
	protected $product_identifiers;

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
		$this->attributes_map = get_option('pfg_product_attributes_map', array());
		$this->product_identifiers = get_option('pfg_product_identifiers', 'no');
		
	}

	/**
	 * Add settings link to plugins page
	 *
	 * @since    1.0.0
	 * @param array $links
	 * @return array 	Links
	 */
	public function pfg_settings_link( $links ) {

		$link = '<a href="' .
			admin_url( 'admin.php?page=wc-settings&tab=products&section=pfg' ) .
			'">' . __('Settings') . '</a>';

		array_unshift($links, $link);

		return $links;

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

		$view_feed_label = __( 'View feed →', 'products-feed-generator' );

		?>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>">
					<?php echo esc_html( $value['title'] ); ?>
					<?php echo wp_kses_post( $description['tooltip_html'] ); ?>
				</label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( $value['type'] ); ?>">
				<div id="feed_management">
					<button
						name ="<?php echo esc_attr( $name ); ?>"
						id   ="<?php echo esc_attr( $value['id'] ); ?>"
						type="button"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						value="<?php echo esc_attr( $name ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
					><?php echo esc_html($name); ?></button>
					<span class="load-icon"></span>
					<span class="view-feed">
						<?php if ( file_exists($feed_file) ): ?>
							<a id="view_feed_url" class="view-url" target="_blank" href="<?php echo esc_url($feed_url); ?>"><?php echo esc_html( $view_feed_label ); ?></a>
						<?php else: ?>
							<a id="view_feed_url" class="view-url" style="display:none;" target="_blank" href="<?php echo esc_url( $feed_url ); ?>"><?php echo esc_html( $view_feed_label ); ?></a>
						<?php endif; ?>
					</span>
				</div>
				<div id="feed_management_error"></div>
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
			'desc'   => __( 'The following options are used to configure a feed for Google Shopping', 'products-feed-generator' ), 
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
			'id'     => 'pfg_product_identifiers',
			'type'   => 'checkbox',
			'desc'  => __( 'Include unique identifiers (GTIN, mpn) on product edit page', 'products-feed-generator' ), 
		);
		$settings[] = array(
			'name'   => __( 'Product Details', 'products-feed-generator' ),
			'id'     => 'pfg_product_details_section',
			'type'   => 'checkbox',
			'desc'  => __( 'Show list of attributes in product detail section', 'products-feed-generator' ),
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
		);
		$settings[] = array(
			'name'   => __( 'Default Material', 'products-feed-generator' ),
			'id'     => 'pfg_product_material',
			'type'   => 'text',
			'placeholder' => 'plastic',
			'desc' => __( 'Default material to include with mapped attribute', 'products-feed-generator' ),
			'desc_tip' => __( 'Is the "Google: Material" field mapped to an attribute?', 'products-feed-generator' ),
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
			'desc' => __( 'Generate or view XML feed, be sure to save changes first', 'products-feed-generator'),
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
	public function woo_custom_fields() {

		echo '<fieldset class="product_feed_settings">';

		echo '<legend>'. __('Product Feed Generator Settings', 'products-feed-generator') .'</legend>';

		woocommerce_wp_text_input(
			array(
				'id' => '_product_brand',
				'label' => __('Brand', 'products-feed-generator'),
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
				'description' => __('Define a custom thumbnail image URL for this product.', 'products-feed-generator'),
			)
		);

		if ( $this->product_identifiers == 'yes' ) {

			woocommerce_wp_text_input(
				array(
					'id' => '_product_gtin',
					'label' => __('GTIN', 'products-feed-generator'),
					'desc_tip' => true,
					'description' => __('Define GTIN (Global Trade Item Number) for this product.', 'products-feed-generator'),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id' => '_product_mpn',
					'label' => __('MPN', 'products-feed-generator'),
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
				'description' => __('Define ID number for google product category', 'products-feed-generator'),
			)
		);

		echo '</fieldset>';

	}

	/**
	 * Render custom fields for variations
	 *
	 * @since    1.0.0
	 */
	public function woo_custom_fields_to_variations( $loop, $variation_data, $variation ) {

		if ( $this->product_identifiers == 'yes' ) {

			echo '<div class="product_identifiers">';

			woocommerce_wp_text_input( 
				array(
					'id' => 'variable_product_gtin[' . $loop . ']',
					'class' => 'short',
					'label' => __( 'GTIN', 'products-feed-generator' ),
					'value' => get_post_meta( $variation->ID, 'variable_product_gtin', true )
				) 
			);

			woocommerce_wp_text_input( 
				array(
					'id' => 'variable_product_mpn[' . $loop . ']',
					'class' => 'short',
					'label' => __( 'MPN', 'products-feed-generator' ),
					'value' => get_post_meta( $variation->ID, 'variable_product_mpn', true )
				) 
			);

			echo '</div>';

		}

	}

	/**
	 * Save free shipping badge setting
	 *
	 * @since    1.0.0
	 * @param integer $post_id
	 */
	public function woo_custom_fields_save( $post_id ) {

		if ( isset( $_POST['_product_brand']) ) {

			update_post_meta( $post_id, '_product_brand', sanitize_text_field( $_POST['_product_brand'] ) );
		}
		if ( isset( $_POST['_product_image_thumbnail'] ) ) {

			update_post_meta( $post_id, '_product_image_thumbnail', sanitize_url( $_POST['_product_image_thumbnail'] ) );
		}
		if ( isset( $_POST['_product_gtin'] ) ) {

			update_post_meta( $post_id, '_product_gtin', sanitize_text_field( $_POST['_product_gtin'] ) );
		}
		if ( isset( $_POST['_product_mpn'] ) ) {

			update_post_meta( $post_id, '_product_mpn', sanitize_text_field( $_POST['_product_mpn'] ) );
		}
		if ( isset( $_POST['_product_google_category'] ) ) {

			$product_category = '';

			if (! empty($_POST['_product_google_category']) ) {
				$product_category = intval( $_POST['_product_google_category'] );
			}

			update_post_meta( $post_id, '_product_google_category', $product_category );
		}
		if ( isset( $_POST['_product_condition'] ) ) {

			update_post_meta( $post_id, '_product_condition', sanitize_key( $_POST['_product_condition'] ) );
		}

	}

	/**
	 * Save free shipping badge setting
	 *
	 * @since    1.0.0
	 * @param integer $post_id
	 */
	public function woo_custom_fields_to_variations_save( $variation_id, $i ) {

   		if ( isset( $_POST['variable_product_gtin'][$i] ) ) {

   			update_post_meta( $variation_id, 'variable_product_gtin', sanitize_text_field( $_POST['variable_product_gtin'][$i] ) );
   		}

   		if ( isset( $_POST['variable_product_mpn'][$i] ) ) {

   			update_post_meta( $variation_id, 'variable_product_mpn', sanitize_text_field( $_POST['variable_product_mpn'][$i] ) );
   		}

	}

	/**
	 * Save custom settings
	 *
	 * @since    1.0.0
	 */
	public function woo_save_settings() {

		$attributes_map = array();

		$section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : '';
		if ($section == 'pfg') {
			foreach ($_POST as $key => $value) {
				if (stripos($key, 'attrib_map_') !== false) {
					$attribkey = substr( $key, strlen('attrib_map_') );
					$attribval = sanitize_text_field($value);

					$attributes_map[$attribkey] = $attribval;
				}
			}
			if ( $count = sizeof($attributes_map) ) {
				$attributes_map = array(
					'count' => $count,
					'forward' => $attributes_map,
					'reverse' => array_flip($attributes_map),
				);
			}

			$this->attributes_map = $attributes_map;

			update_option('pfg_product_attributes_map', $attributes_map);
		}

		$cron_schedule = isset($_POST['pfg_cron_schedule']) ? sanitize_key($_POST['pfg_cron_schedule']) : null;

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

		} elseif ( $cron_schedule ) {
			// Schedule new cron task
			if ($this->debug_log == 'yes') {
				wc_get_logger()->info('Create new scheduled cron task', array( 'source' => 'products-feed-generator' ) );
			}
			wp_schedule_event(time(), $cron_schedule, 'generate_google_products_feed');
		}
		
	}

	/**
	 * After deleting an attribute.
	 *
	 * @since    1.0.0
	 * @param int    $id       Attribute ID.
	 * @param string $name     Attribute name.
	 * @param string $taxonomy Attribute taxonomy name.
	 */
	public function woo_attribute_deleted( $id,  $name,  $taxonomy ) {

		$attributes_map = get_option('pfg_product_attributes_map', array());
		if ( empty($attributes_map) ) return;

		$attributes_map_rev = $attributes_map['reverse'];

		$dirty = false;
		foreach ($attributes_map_rev as $key => $attrval) {
			if ($key == $name) {
				unset($attributes_map['reverse'][$key]);
				unset($attributes_map['forward'][$attrval]);
				if ( $attributes_map['count'] > 0 ) {
					$attributes_map['count']--;
				}
				$dirty = true;
			}
		}

		if ($dirty) {
			update_option('pfg_product_attributes_map', $attributes_map);
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

			$feed_name = get_option('pfg_product_feed_name');

			if (empty($feed_name)) {
				$feed_name = 'google_products_feed.xml';
			} 

			$feed_file = $feed_dir .'/'. $feed_name;
		} 
		else {
			$error = new WP_Error( '001', 'No upload dir found.' );

			wp_send_json_error($error);
		}

		$product_variants = get_option('pfg_product_variants', 'parent_only');

		try {
			$xml_writer = new Products_Feed_Generator_Google_Shopping_XML_Writer($feed_file, $product_variants);
		}
		catch (Exception $e) {
			$error = new WP_Error( '002', $e->getMessage() );

			wp_send_json_error($error);

			return;
		}

		$total_records = 100;

		$params = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $total_records,
		];

		$query = new WP_Query();

		if (! $posts = $query->query($params) ) {
			$error = new WP_Error( '003', 'No products found.' );

			wp_send_json_error($error);
		}

		// get parent products first
		foreach ($posts as $post) {

			$product = $parent_product = wc_get_product($post->ID);
			$visibility = $product->get_catalog_visibility('view');

			if ($visibility == 'hidden') continue;

			$children = $product->get_children();
			$post_title = $post->post_title;

			$xml_writer->add_canonical_url($product->get_id(), $product->get_permalink());

			if ( $product_variants == 'parent_only' or 
				 $product_variants == 'parent_and_variants' ) {

				$xml_writer->write_product_data( $product );
			}

			// Get all child variants
			if ( $product_variants == 'variants_only' or 
				 $product_variants == 'parent_and_variants' ) {

				foreach ($children as $variant_id) {
					$product = wc_get_product($variant_id);

					$xml_writer->write_product_data( $product, $parent_product );
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
	 * Register the stylesheets for the woocommmerce product editor.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_woo_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-styles.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/products-feed-generator-admin.js', array( 'jquery' ), $this->version, false );

		$attributes = wc_get_attribute_taxonomy_labels();

		$local_vars = array( 
			'pluginUrl' => ( plugins_url() .'/'. $this->plugin_name ),
			'attributes' => $attributes,
			'attributes_map' => $this->attributes_map,
		);

		wp_localize_script( $this->plugin_name, 'jsVars', $local_vars );

	}

}
