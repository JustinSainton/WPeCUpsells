<?php

/**
 *
 * 1) Go to "Upsells" page.
 * 2) Create Upsell.  For example, "Gift wrap".
 * 3) Optionally, create "child" upsells.  This will show, conditionally, when the parent upsell is chosen. A great example for this would be "Gift Wrap" as a parent, and "Ribbon/Bow/Card" as children.
 * 4) Optionally, set prices for each upsell
 * 5) Optionally, set conditions for each upsell (IN, NOT IN, see coupons for example.)
 * 6) SHOOTING FOR THE MOON - allow for upsells to be per order, or per product.  Needs some UX thought and theme- * work - may not be possible, in which case, think probably per order to start.
 *
 **/

/**
 * This class handles all the generic functionality, back and front-end.
 *
 *  -- Creates hierarchical custom taxonomy called upsells.
 *  -- Add custom JS to allow for quick editing conditions, prices, etc.
 *  -- (TODO) Add upsell information to purchase log.
 *  -- (TODO) Don't show on product quick edit.
 *
 * @package    WPEC_Upsells
 * @subpackage WPEC_Upsells_Admin
 *
 */

defined( 'WPINC' ) or die;

class WPEC_Upsells_Admin {

	/**
	 * The active object instance
	 *
	 * @var boolean
	 * @access private
	 * @static
	 *
	 */
	private static $instance = false;

	/**
	 * The fields available in the quick edit field.  Defaults to price (if any) and conditions.
	 *
	 * @var array
	 * @access private
	 * @static
	 *
	 */
	private static $_quick_edit_fields = array();

	/**
	 * Get active object instance
	 *
	 * @since 1.0
	 * @access public
	 * @static
	 *
	 * @return object
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new WPEC_Upsells_Admin();

		return self::$instance;
	}

	/**
	 * Private constructor, to avoid direct instantiation.
	 *
	 * @since 1.0
	 * @access private
	 *
	 * @return void
	 */
	private function __construct() {

		add_action( 'admin_init'                       , array( $this, 'activate' ) );
		add_action( 'quick_edit_custom_box'            , array( $this, 'quick_edit_taxonomy' ), 10, 3 );
		add_action( 'edited_wpsc-upsells'              , array( $this, 'save_term_taxonomy' ) , 10, 2 );
		add_filter( 'manage_edit-wpsc-upsells_columns' , array( $this, 'add_columns' )        , 10 );
		add_filter( 'manage_wpsc-upsells_custom_column', array( $this, 'column_data' )        , 10, 3 );
		add_action( 'admin_enqueue_scripts'            , array( $this, 'add_wpsc_admin_js' )  , 25 );
		add_action( 'wpsc_additional_sales_amount_info', array( $this, 'add_upsell_to_purchase_logs' ) );
		add_action( 'admin_enqueue_scripts'            , 'wpsc_admin_include_coupon_js'       , 25 );
		/* hacky hack */
		add_filter( 'wpsc-upsells_row_actions'        , array( $this, 'get_tt_ids' )         , 10, 2 );

		do_action( 'wpec_upsells_admin_loaded' );

		/* TODO: Add "Show On: " field, to hook into different areas of the site.  Checkout page, single, etc. */
		/* TODO: Add Custom Label - what to show as the input label. */
		self::$_quick_edit_fields = apply_filters( 'wpsc_upsells_quick_edit_fields', array(
			'upsell_price' => __( 'Price: '     , 'wpsc-upsells' ),
			'conditions'   => __( 'Conditions: ', 'wpsc-upsells' ),
		) );
	}

	public function add_wpsc_admin_js( $hook ) {
		$screen = get_current_screen();

		if ( 'edit-wpsc-upsells' === $screen->id ) {

			wp_enqueue_script( 'wp-e-commerce-admin', WPSC_URL . '/wpsc-admin/js/admin.js' );
			// Localize scripts
			wp_localize_script( 'wp-e-commerce-admin', 'wpsc_adminL10n', array(
				'dragndrop_set'             => ( get_option( 'wpsc_sort_by' ) == 'dragndrop' ? 'true' : 'false' ),
				'save_product_order_nonce'  => _wpsc_create_ajax_nonce( 'save_product_order' ),
				'l10n_print_after'          => 'try{convertEntities(wpsc_adminL10n);}catch(e){};',
				'empty_coupon'              => esc_html__( 'Please enter a coupon code.', 'wpsc' ),
				'bulk_edit_no_vars'         => esc_html__( 'Quick Edit options are limited when editing products that have variations. You will need to edit the variations themselves.', 'wpsc' ),
				'wpsc_core_images_url'      => WPSC_CORE_IMAGES_URL,
				'variation_parent_swap'     => esc_html_x( 'New Variation Set', 'Variation taxonomy parent', 'wpsc' ),
				/* translators              : This string is prepended to the 'New Variation Set' string */
				'variation_helper_text'     => esc_html_x( 'Choose the Variation Set you want to add variants to. If you\'re creating a new variation set then select', 'Variation helper text', 'wpsc' ),
				'variations_tutorial'       => esc_html__( 'Variations allow you to create options for your products. For example, if you\'re selling T-Shirts, they will generally have a "Size" option. Size will be the Variation Set name, and it will be a "New Variant Set". You will then create variants (small, medium, large) which will have the "Variation Set" of Size. Once you have made your set you can use the table on the right to manage them (edit, delete). You will be able to order your variants by dragging and dropping them within their Variation Set.', 'wpsc' ),
				/* translators              : These strings are dynamically inserted as a drop-down for the Coupon comparison conditions */
				'coupons_compare_or'        => esc_html_x( 'OR'  , 'Coupon comparison logic', 'wpsc' ),
				'coupons_compare_and'       => esc_html_x( 'AND' , 'Coupon comparison logic', 'wpsc' ),
				'upsells_add_conditions'    => esc_html_x( 'Add Conditions'    , 'Coupon comparison logic', 'wpsc-upsells' ),
				'upsells_remove_conditions' => esc_html_x( 'Remove Conditions' , 'Coupon comparison logic', 'wpsc-upsells' ),
			) );

			wp_enqueue_script( 'wpsc-upsells'      , WPEC_UPSELLS_URL . '/inc/js/upsells.js' );
			wp_enqueue_style( 'wp-e-commerce-admin', WPEC_UPSELLS_URL . '/inc/css/upsells.css' );
		}
	}

	public function add_columns( $columns ) {

		foreach ( self::$_quick_edit_fields as $name => $label ) {
			$columns[ $name ] = rtrim( $label, ': ' );
		}

		return $columns;
	}

	public function column_data( $data, $name, $term_id ) {

		$meta  = self::get_term_meta( $term_id, $name );
		$value = is_array( $meta ) ? json_encode( $meta ) : esc_attr( $meta );
		$name  = 'input_' . esc_attr( $name );

		return '<input type="hidden" name="' . $name . '" class="' . $name . '" value=\'' . $value . '\' />';
	}

	private static function display_conditions( $term_id = 0 ) {
		$conditions = self::get_term_meta( $tt_id, 'conditions' );

		if ( empty( $conditions ) ) {
			$conditions = array(
							array(
								'property' => '',
								'logic'    => '',
								'value'    => '',
							)
						);
		}

		foreach ( $conditions as $key => $condition ) :
	?>
		<div class='coupon-condition'>
		<?php
			if ( isset( $condition["operator"] ) && ! empty( $condition["operator"] ) ) :
		?>
			<select name="rules[operator][]">
				<option value="and"<?php selected( 'and', $condition["operator"] ); ?>><?php _ex( 'AND', 'Coupon comparison logic', 'wpsc' );?></option>
				<option value="or"<?php  selected( 'or' , $condition["operator"] ); ?>><?php _ex( 'OR' , 'Coupon comparison logic', 'wpsc' );?></option>
			</select>
		<?php endif; ?>
			<select class="ruleprops" name="rules[property][]">
				<option value="item_name"<?php selected( 'item_name', $condition['property'] ); ?> rel="order"><?php _e( 'Item name', 'wpsc' ); ?></option>
				<option value="item_quantity"<?php selected( 'item_quantity', $condition['property'] ); ?> rel="order"><?php _e( 'Item quantity', 'wpsc' ); ?></option>
				<option value="total_quantity"<?php selected( 'total_quantity', $condition['property'] ); ?> rel="order"><?php _e( 'Total quantity', 'wpsc' ); ?></option>
				<option value="subtotal_amount"<?php selected( 'subtotal_amount', $condition['property'] ); ?> rel="order"><?php _e( 'Subtotal amount', 'wpsc' ); ?></option>
				<?php do_action( 'wpsc_coupon_rule_property_options' ); ?>
			</select>

			<select name="rules[logic][]">
				<option value="equal"<?php selected( 'equal', $condition['logic'] ); ?>><?php _e( 'Is equal to', 'wpsc' ); ?></option>
				<option value="greater"<?php selected( 'greater', $condition['logic'] ); ?>><?php _e( 'Is greater than', 'wpsc' ); ?></option>
				<option value="less"<?php selected( 'less', $condition['logic'] ); ?>><?php _e( 'Is less than', 'wpsc' ); ?></option>
				<option value="contains"<?php selected( 'contains', $condition['logic'] ); ?>><?php _e( 'Contains', 'wpsc' ); ?></option>
				<option value="not_contain"<?php selected( 'not_contain', $condition['logic'] ); ?>><?php _e( 'Does not contain', 'wpsc' ); ?></option>
				<option value="begins"<?php selected( 'begins', $condition['logic'] ); ?>><?php _e( 'Begins with', 'wpsc' ); ?></option>
				<option value="ends"<?php selected( 'ends', $condition['logic'] ); ?>><?php _e( 'Ends with', 'wpsc' ); ?></option>
				<option value="category"<?php selected( 'category', $condition['logic'] ); ?>><?php _e( 'In Category', 'wpsc' ); ?></option>
			</select>
			<input type="text" name="rules[value][]" value="<?php esc_attr_e( $condition['value'] ); ?>" style="width: 150px;"/>
			<a title="<?php esc_attr_e( 'Delete condition', 'wpsc' ); ?>" class="button-secondary wpsc-button-round wpsc-button-minus" href="#"><?php echo _x( '&ndash;', 'delete item', 'wpsc' ); ?></a>
			<a title="<?php esc_attr_e( 'Add condition', 'wpsc' ); ?>" class="button-secondary wpsc-button-round wpsc-button-plus" href="#"><?php echo _x( '+', 'add item', 'wpsc' ); ?></a>
		</div>
		<?php endforeach;

	}

	public function get_tt_ids( $actions, $tag ) {
		global $wpec_upsell_posts;

		$wpec_upsell_posts[] = $tag;

		return $actions;
	}

	public function quick_edit_taxonomy( $column, $screen, $name ) {

		if ( 'upsell_price' !== $column || 'edit-tags' !== $screen ) {
			return;
		}

		global $wpec_upsell_posts, $wpec_upsells_iterator;

		$tt_id = 0;

		if ( ! isset( $wpec_upsells_iterator ) ) {
			$wpec_upsells_iterator = 0;
		}

		if ( isset( $wpec_upsell_posts[ $wpec_upsells_iterator ] ) ) {
			$term_id = $wpec_upsell_posts[ $wpec_upsells_iterator ]->term_id;
			++$wpec_upsells_iterator;

		}

		foreach ( self::$_quick_edit_fields as $name => $label ) : ?>

			<fieldset>
				<div class="upsell-meta inline-edit-col">
					<label>
						<span class="title">
							<?php echo esc_html( $label ); ?>
						</span>
						<span class="input-text-wrap">
						<?php if ( 'conditions' === $name ) {
							echo '<div class="coupon-conditions">';
								echo '<a href="#" class="add-upsell-conditions"> '. __( 'Add Conditions' ) . '</a>';
								echo '<div class="upsell-conditions">';
								echo "<div class='coupon-condition'>";
								echo '<input type="hidden" name="rules[operator][]" value="" />';
								self::display_conditions( $term_id );
							} else {
								$value = self::get_term_meta( $term_id, $name );
								$value = ! empty( $value ) ? $value : '';

								echo apply_filters( 'wpec_upsell_admin_input_' . $name , '<input type="search" name="' . esc_attr( $name ) . '" placeholder="5.00" class="ptitle" value="' . esc_attr( $value ) . '">' );
							}
						?>
						</span>
						</label>
					</div>
				</fieldset>
		<?php endforeach;
	}

	/**
	 * Adds price and conditions, if they exist, to term meta data store, using the options table.
	 * Not as optimal as a term_meta table, but alas, we don't have one.
	 *
	 * @param  int   $term_id Term ID
	 * @param  int   $tt_id   Taxonomy Term ID
	 *
	 * @return void
	 */
	public function save_term_taxonomy( $term_id, $tt_id ) {

		$price      = floatval( $_POST['upsell_price'] );
		$rules      = $_POST['rules'];
		$conditions = array();

		/* Two foreach loops stolen from WPeC.  Pretty lame. */
		foreach ( $rules as $key => $rule ) {
			foreach ( $rule as $k => $r ) {
				$conditions[ $k ][ $key ] = sanitize_text_field( $r );
			}
		}

		foreach ( $conditions as $key => $rule ) {
			if ( empty( $rule['value'] ) ) {
				unset( $conditions[ $key ] );
			}
		}

		$term_meta = array( 'upsell_price' => $price, 'conditions' => $conditions );

		$this->save_term_meta( $term_meta, $term_id );
	}

	/**
	 * Saves term meta to option data store.
	 *
	 * @param  array   $data  Array of conditions and price data.
	 * @param  integer $tt_id Term Taxonomy ID
	 *
	 * @return boolean Whether or not option was updated.
	 */
	private static function save_term_meta( $data = array(), $term_id = 0 ) {

		$term_meta_store             = get_option( 'wpsc_upsells_term_meta', array() );
		$term_meta_store[ $term_id ] = $data;

		return update_option( 'wpsc_upsells_term_meta', $term_meta_store );
	}

	/**
	 * Retrieves term meta values.
	 * Can retrieve the entire array, or just one of the values.
	 *
	 * @param  int     $term_id Term ID
	 * @param  boolean $value Whether or not to return the entire array, or just the provided value.
	 *
	 * @return mixed          Array, either empty or array of values.  If $value is set, returns value, or empty string.
	 */
	public static function get_term_meta( $term_id, $value = false ) {
		$term_meta_store = get_option( 'wpsc_upsells_term_meta', array() );

		if ( ! isset( $term_meta_store[ $term_id ] ) ) {
			return array();
		} else {
			if ( $value ) {
				return isset( $term_meta_store[ $term_id ][ $value ] ) ? $term_meta_store[ $term_id ][ $value ] : '';
			} else {
				return $term_meta_store[ $term_id ];
			}
		}
	}

	public function add_upsell_to_purchase_logs( $cart_item_id ) {
		$meta  = wpsc_get_cart_item_meta( $cart_item_id, '', true );
		$_keys = WPEC_Upsells_Public::get_upsell_fields();
		$keys  = array_map( 'sanitize_title_with_dashes', array_keys( $_keys ) );

		$return = array();

		foreach ( $meta as $meta_key => $applied ) {
			if ( in_array( $meta_key, $keys ) ) {
				$return[] = get_term_by( 'slug', $meta_key, 'wpsc-upsells' )->name;
			}
		}

		if ( ! empty( $return ) ) {
			echo "<br /><small>( " . implode( ', ', $return ) . " )</small>";
		}
	}

	/**
	 * Checks for existence of WP-eCommerce.
	 * Shows notice if not active.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! class_exists( 'WP_eCommerce' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
		}
	}

	/**
	 * Shows dependency check notice.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return void
	 */
	public static function admin_notice() {
		$notice = sprintf( __( 'This plugin requires <a href="%s">WP e-Commerce</a> to work.', 'wpec-upsells' ), esc_url( 'http://wordpress.org/plugins/wp-e-commerce/' ) );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		?>
		<div class="error">
			<p><?php echo $notice; ?></p>
		</div>
		<?php
	}

}

add_action( 'plugins_loaded', array( 'WPEC_Upsells_Admin', 'get_instance' ) );