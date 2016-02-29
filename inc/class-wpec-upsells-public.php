<?php

/**
 * Handles all front-end, customer-facing functionalities of the Upsells Framework
 *
 *  1) Processes
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
 *  -- TODO: (labels) Show upsell labels, prices and boxes on front-end.
 *  -- Hook into purchase workflow to add purchase order meta.
 *  -- (TODO)Via JS, modify price of cart total.
 *  -- (TODO)Also modify item total when checking boxes on single item page.
 *  -- (TODO)Add minimum version checks
 *
 * @package    WPEC_Upsells
 * @subpackage WPEC_Upsells_Public
 */

defined( 'WPINC' ) or die;

class WPEC_Upsells_Public {

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
			self::$instance = new WPEC_Upsells_Public();

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

		add_action( 'init'                              , array( $this, 'register_taxonomy' ), 0 );
		add_action( 'wpsc_set_cart_item'                , array( $this, 'add_upsell_meta' ), 10, 4 );
		add_filter( 'wpsc_price'                        , array( $this, 'modify_price' ), 11, 3 );
		add_action( 'wpsc_after_checkout_cart_item_name', array( $this, 'show_options_in_cart' ) );

		add_filter( 'wpsc_purchase_log_notification_product_table_args', array( $this, 'show_options' ), 10, 2 );

		/* Eventually, add proper check here to run on init and add to the properly configured action. */
		/* For now, hardcoding to form_fields_begin.  Will eventually allow for shopping cart page. */
		add_action( 'wpsc_product_form_fields_begin', array( $this, 'show_upsell_fields' ) );

		do_action( 'wpec_upsells_public_loaded' );
	}

	public function register_taxonomy() {

		$labels = array(
			'name'              => _x( 'Upsells'        , 'taxonomy general name', 'wpsc-upsells' ),
			'singular_name'     => _x( 'Upsell'         , 'taxonomy singular name', 'wpsc-upsells' ),
			'search_items'      => __( 'Search Upsells' , 'wpsc-upsells' ),
			'all_items'         => __( 'All Upsells'    , 'wpsc-upsells' ),
			'parent_item'       => __( 'Parent Upsell'  , 'wpsc-upsells' ),
			'parent_item_colon' => __( 'Parent Upsell:' , 'wpsc-upsells' ),
			'edit_item'         => __( 'Edit Upsell'    , 'wpsc-upsells' ),
			'update_item'       => __( 'Update Upsell'  , 'wpsc-upsells' ),
			'add_new_item'      => __( 'Add New Upsell' , 'wpsc-upsells' ),
			'new_item_name'     => __( 'New Upsell Name', 'wpsc-upsells' ),
			'menu_name'         => __( 'Upsells'        , 'wpsc-upsells' ),
		);

		$args = array(
			'hierarchical' => true,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => false,
			'meta_box_cb'  => false /* Only 3.8 */
		);

		register_taxonomy( 'wpsc-upsells', 'wpsc-product', $args );
		register_taxonomy_for_object_type( 'wpsc-upsells', 'wpsc-product' );
	}

	/* TODO: Cache and invalidate */
	public static function get_upsell_fields() {

		$upsells = get_terms( 'wpsc-upsells', array( 'hide_empty' => false ) );
		$fields  = array();

		if ( empty( $upsells ) || is_wp_error( $upsells ) ) {
			return $fields;
		}

		/* TODO: Apply Conditions */
		foreach ( $upsells as $upsell ) {
			$fields[ $upsell->name ] = WPEC_Upsells_Admin::get_term_meta( $upsell->term_id, 'upsell_price' );
		}

		return apply_filters( 'wpsc_upsells_get_upsell_fields', $fields );

	}

	public function show_upsell_fields( $product_id ) {

		echo '<div class="wpsc-upsells">';
			/* TODO: Add custom labeling.  Object-noun agreement is tricky. */
			foreach ( self::get_upsell_fields() as $name => $price ) {
				echo '<label class="wpsc-upsell">
						<input type="checkbox" name="wpsc_upsell_' . sanitize_title_with_dashes( $name ) . '" value="" /> ' .
						sprintf( apply_filters( 'wpsc_upsells_upsell_field_label',
						__( 'Would you like to add %s to this product? (%s)', 'wpec-upsells' ), $name, $price ),
						strtolower( $name ),
						wpsc_currency_display( $price ) ) .
					'</label>';
			}
		echo '</div>';
	}

	public function add_upsell_meta( $product_id, $parameters, $cart, $cart_item ) {

		foreach ( $_POST as $key => $value ) {
			if ( false === strpos( $key, 'wpsc_upsell_' ) )
				continue;

			$meta_key = str_replace( 'wpsc_upsell_', '', $key );

			$cart_item->update_meta( $meta_key, 'applied' );
		}
	}

	public function show_options_in_cart() {
		global $wpsc_cart;

		$item = $wpsc_cart->cart_items[ wpsc_the_cart_item_key() ];
		$meta = $item->get_meta();

		$_keys = self::get_upsell_fields();
		$keys  = array_map( 'sanitize_title_with_dashes', array_keys( $_keys ) );

		$return = array();

		foreach ( $meta as $meta_key => $applied ) {
			if ( in_array( $meta_key, $keys ) ) {
				$return[] = get_term_by( 'slug', $meta_key, 'wpsc-upsells' )->name;
			}
		}

		if ( ! empty( $return ) ) {
			echo '<em class="wpsc_upsells_cart_note">( ' . implode( ', ', $return ) . ' )</em>';
		}

	}

	public function show_options( $args, $notification ) {
		$rows = $args['rows'];
		$log  = $notification->get_purchase_log();

		$cart_contents = $log->get_cart_contents();
		$_keys         = self::get_upsell_fields();
		$keys          = array_map( 'sanitize_title_with_dashes', array_keys( $_keys ) );

		foreach ( $rows as $key => $row ) {
			$name = $rows[ $key ][ 0 ];
			$meta = wpsc_get_cart_item_meta( $cart_contents[ $key ]->id, '', true );

			$return = array();

			foreach ( $meta as $meta_key => $applied ) {
				if ( in_array( $meta_key, $keys ) ) {
					$return[] = get_term_by( 'slug', $meta_key, 'wpsc-upsells' )->name;
				}
			}

			if ( ! empty( $return ) ) {
				$rows[ $key ][ 0 ] = $name . "\r\n( " . implode( ', ', $return ) . " )";
			}
		}

		$args['rows'] = $rows;

		return $args;
	}
	/*TODO: Abstract this with show_options_in_cart() */
	public function modify_price( $price, $product_id, $cart_item = '' ) {

		if ( empty( $cart_item ) )
			return $price;

		$meta = $cart_item->get_meta();



		$_keys = self::get_upsell_fields();
		$keys  = array_map( 'sanitize_title_with_dashes', array_keys( $_keys ) );

		foreach ( $_keys as $meta_name => $meta_price ) {
			if ( in_array( sanitize_title_with_dashes( $meta_name ), array_keys( $meta ) ) ) {
				$price += $meta_price;
			}
		}

		return $price;
	}
}
add_action( 'plugins_loaded', array( 'WPEC_Upsells_Public', 'get_instance' ) );