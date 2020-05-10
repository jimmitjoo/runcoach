<?php
/**
 * Plugin Name: WP Simple Pay Pro
 * Plugin URI: https://wpsimplepay.com
 * Description: Add high conversion Stripe payment forms to your WordPress site in minutes.
 * Author: Sandhills Development, LLC
 * Author URI: https://sandhillsdev.com
 * Version: 3.7.1
 * Text Domain: simple-pay
 * Domain Path: /languages
 *
 * @package SimplePay
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright 2014-2019 Sandhills Development, LLC. All rights reserved.
 */

namespace SimplePay;

use SimplePay\Core\Bootstrap\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//
// Shared.
//
if ( ! defined( 'SIMPLE_PAY_STORE_URL' ) ) {
	define( 'SIMPLE_PAY_STORE_URL', 'https://wpsimplepay.com/' );
}

//
// Lite/Pro-specific.
//
if ( ! defined( 'SIMPLE_PAY_VERSION' ) ) {
	define( 'SIMPLE_PAY_VERSION', '3.7.1' );
}

if ( ! defined( 'SIMPLE_PAY_PLUGIN_NAME' ) ) {
	define( 'SIMPLE_PAY_PLUGIN_NAME', 'WP Simple Pay Pro' );
}

if ( ! defined( 'SIMPLE_PAY_ITEM_NAME' ) ) {
	define( 'SIMPLE_PAY_ITEM_NAME', 'WP Simple Pay Pro 3' );
}

//
// Stripe.
//
if ( ! defined( 'SIMPLE_PAY_STRIPE_API_VERSION' ) ) {
	define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2019-12-03' );
}

if ( ! defined( 'SIMPLE_PAY_STRIPE_PARTNER_ID' ) ) {
	define( 'SIMPLE_PAY_STRIPE_PARTNER_ID', 'pp_partner_DKkf27LbiCjOYt' );
}

//
// Helpers.
//
if ( ! defined( 'SIMPLE_PAY_MAIN_FILE' ) ) {
	define( 'SIMPLE_PAY_MAIN_FILE', __FILE__ );
}

if ( ! defined( 'SIMPLE_PAY_URL' ) ) {
	define( 'SIMPLE_PAY_URL', plugin_dir_url( SIMPLE_PAY_MAIN_FILE ) );
}

if ( ! defined( 'SIMPLE_PAY_DIR' ) ) {
	define( 'SIMPLE_PAY_DIR', plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) );
}

if ( ! defined( 'SIMPLE_PAY_INC' ) ) {
	define( 'SIMPLE_PAY_INC', plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) . 'includes/' );
}

if ( ! defined( 'SIMPLE_PAY_INC_URL' ) ) {
	define( 'SIMPLE_PAY_INC_URL', plugin_dir_url( SIMPLE_PAY_MAIN_FILE ) . 'includes/' );
}

//
// Pro-only.
//
if ( ! defined( 'SIMPLE_PAY_ITEM_ID' ) ) {
	define( 'SIMPLE_PAY_ITEM_ID', 177993 );
}

/**
 * Show warning if Lite version is active.
 *
 * @since unknown
 */
function simpay_deactivate_lite_notice() {
	?>

<div class="error">
	<p>
		<?php printf( __( 'You must <a href="%1$s">deactivate WP Simple Pay Lite</a> in order to use %2$s.', 'simple-pay' ), wp_nonce_url( 'plugins.php?action=deactivate&plugin=stripe%2Fstripe-checkout.php&plugin_status=all&paged=1&s=', 'deactivate-plugin_stripe/stripe-checkout.php' ), SIMPLE_PAY_ITEM_NAME ); ?>
	</p>
</div>

	<?php
}

// Stop any further checks if Lite is already loaded.
if ( class_exists( 'SimplePay\Core\SimplePay' ) ) {
	add_action( 'admin_notices', __NAMESPACE__ . '\\simpay_deactivate_lite_notice' );
	return;
}

// Compatibility files.
require_once( SIMPLE_PAY_DIR . 'includes/core/bootstrap/compatibility.php' );

if ( Compatibility\server_requirements_met() ) {
	// Autoloader.
	require_once( SIMPLE_PAY_DIR . 'vendor/autoload.php' );
	require_once( SIMPLE_PAY_DIR . 'includes/core/bootstrap/autoload.php' );

	// Core & Pro main plugin files.
	require_once( SIMPLE_PAY_DIR . 'includes/core/class-simplepay.php' );
	require_once( SIMPLE_PAY_DIR . 'includes/pro/class-simplepaypro.php' );
} else {
	Compatibility\show_admin_notices();
}
