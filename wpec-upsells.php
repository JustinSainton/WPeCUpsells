<?php
/**
 * Plugin Name: WP e-Commerce Upsells
 * Plugin URI: http://zao.is
 * Description: Adds framework for upsells to WP e-Commerce.
 * Version: 1.0
 * Author: Zao
 * Author URI: http://zao.is/
 **/

/**
 *
 * Hello, I am WPeC Upsells Framework.  Pleased to make your acquaintance.
 *
 * Upsells Framework
 *
 * Creates a custom hierarchical taxnomy in WPeC, with no metabox by default.
 * The actual taxonomy is for Upsells.
 *
 * A user would use it like so:
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
 * @package WPEC_Upsells
 */

defined( 'WPINC' ) or die;

class WPEC_Upsells {

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
			self::$instance = new WPEC_Upsells();

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

		self::constants();
		self::includes();

		do_action( 'wpec_upsells_loaded' );
	}

	/**
	 * Define filesystem constants.
	 *
	 * @since 1.0
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function constants() {

		// Set the core file path
		define( 'WPEC_UPSELLS_FILE_PATH', dirname( __FILE__ ) );

		// Define the path to the plugin folder
		define( 'WPEC_UPSELLS_DIR_NAME', basename( WPEC_UPSELLS_FILE_PATH ) );

		// Define the URL to the plugin folder
		define( 'WPEC_UPSELLS_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
		define( 'WPEC_UPSELLS_URL'    , plugins_url( '', __FILE__ ) );

		do_action( 'wpec_upsells_constants' );
	}

	/**
	 * Include the three separate functionalities as indivudual classes
	 *
	 * @since 1.0
	 * @access public
	 * @static
	 *
	 * @return void
	 */
	public static function includes() {

		require_once( WPEC_UPSELLS_FILE_PATH . '/inc/class-wpec-upsells-admin.php' );
		require_once( WPEC_UPSELLS_FILE_PATH . '/inc/class-wpec-upsells-public.php' );

		do_action( 'wpec_upsells_includes' );
	}
}

WPEC_Upsells::get_instance();