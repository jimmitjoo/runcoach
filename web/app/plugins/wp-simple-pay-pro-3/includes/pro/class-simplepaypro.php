<?php
/**
 * SimplePay: Pro
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro;

use SimplePay\Pro\Admin;
use SimplePay\Pro\Admin\Metaboxes\Settings;
use SimplePay\Pro\Forms\Ajax;
use SimplePay\Pro\Webhooks\Database\Table as Webhooks_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro SimplePay Class
 */
final class SimplePayPro {

	/**
	 * The single instance of this class
	 */
	protected static $_instance = null;

	/**
	 * Main Simple Pay instance
	 *
	 * Ensures only one instance of Simple Pay is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'simple-pay' ), '3.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'simple-pay' ), '3.0' );
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->load();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Filter common URLs so they run through core's get_url function as well.
		add_filter( 'simpay_get_url', array( $this, 'get_url' ), 10, 2 );
	}

	/**
	 * Load the plugin.
	 */
	public function load() {
		// Load pro shared back-end & front-end functions.
		require_once( SIMPLE_PAY_INC . 'pro/functions/shared.php' );
		require_once( SIMPLE_PAY_INC . 'pro/functions/coupons.php' );
		require_once( SIMPLE_PAY_INC . 'pro/functions/recaptcha.php' );

		// Webhooks.
		new Webhooks_Table();
		require_once( SIMPLE_PAY_INC . 'pro/webhooks/template-tags.php' );
		require_once( SIMPLE_PAY_INC . 'pro/webhooks/functions.php' );

		// REST API.
		require_once( SIMPLE_PAY_INC . 'pro/rest-api/functions.php' );

		// Payments/Purchase Flow.
		require_once( SIMPLE_PAY_INC . 'pro/payments/shared.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/plan.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/subscription.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/payment-confirmation-template-tags.php' );

		// Stripe Checkout.
		require_once( SIMPLE_PAY_INC . 'pro/payments/stripe-checkout/plan.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/stripe-checkout/subscription.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/stripe-checkout/customer.php' );

		// Legacy.
		require_once( SIMPLE_PAY_INC . 'pro/legacy/hooks.php' );

		// Load Lite helper class to update various differences between Lite and Pro.
		new Lite_Helper();
		new Objects();
		new Assets();

		// Load frontend ajax
		new Ajax();

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->load_admin();
		}
	}

	/**
	 * Load the plugin admin.
	 */
	public function load_admin() {
		require_once( SIMPLE_PAY_INC . 'pro/functions/admin.php' );
		require_once( SIMPLE_PAY_INC . 'pro/admin/metaboxes/stripe-checkout.php' );
		require_once( SIMPLE_PAY_INC . 'pro/admin/apple-pay.php' );
		require_once( SIMPLE_PAY_INC . 'pro/admin/settings.php' );
		require_once( SIMPLE_PAY_INC . 'pro/admin/license-management.php' );

		// Usage tracking functionality.
		require_once( SIMPLE_PAY_INC . 'pro/admin/usage-tracking/functions.php' );

		new Admin\Upgrades();
		new Admin\Menus();
		new Settings();
		new Admin\Pages();
		new Admin\Assets();

		// Admin ajax callbacks
		new Admin\Ajax();
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'simple-pay', false, plugin_basename( dirname( SIMPLE_PAY_MAIN_FILE ) ) . '/languages' );
	}

	/**
	 * Get common URLs (in addition to those in core).
	 */
	public function get_url( $url, $case ) {

		switch ( $case ) {
			case 'my-account':
				$url = 'https://wpsimplepay.com/my-account/';
				break;
		}

		return $url;
	}
}

/**
 * Start WP Simple Pay Pro.
 */
function SimplePayPro() {
	return SimplePayPro::instance();
}

SimplePayPro();
