<?php
/**
 * Apple Pay
 *
 * @link https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay
 *
 * @package SimplePay\Pro\Admin\Apple_Pay
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.4.0
 */

namespace SimplePay\Pro\Admin\Apple_Pay;

use SimplePay\Core\Admin\Notice_Manager;
use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return information associated with the name/location of the domain verification file.
 *
 * @since 3.4.0
 *
 * @return array Domain verification file information.
 */
function get_fileinfo() {
	$path = untrailingslashit( $_SERVER['DOCUMENT_ROOT'] );
	$dir  = '.well-known';
	$file = 'apple-developer-merchantid-domain-association';

	return array(
		'path'     => $path,
		'dir'      => $dir,
		'file'     => $file,
		'fullpath' => $path . '/' . $dir . '/' . $file,
	);
}

/**
 * Determines if the current website is setup to use Apple Pay.
 *
 * @since 3.6.0
 *
 * @return bool True if the domain has been verified and the association file exists.
 */
function can_use_apple_pay() {
	return has_verification_file() && has_domain_verification();
}

/**
 * Determines if the domain verification file already exists.
 *
 * @since 3.4.0
 *
 * @return bool If the domain verification file exists.
 */
function has_verification_file() {
	$fileinfo = get_fileinfo();

	if ( ! @file_exists( $fileinfo['fullpath'] ) ) {
		return false;
	}

	return true;
}

/**
 * Determines if the currently verified domain matches the current site.
 *
 * @since 3.6.0
 *
 * @return bool True if the logged verified domain matches the current site.
 */
function has_domain_verification() {
	$settings = get_option( 'simpay_settings_keys' );

	return (
		isset( $settings['apple_pay_domain_verification'] ) &&
		( $_SERVER['HTTP_HOST'] === $settings['apple_pay_domain_verification'] )
	);
}

/**
 * Attempt to create a directory in the server root and copy the domain verification file.
 *
 * @since 3.4.0
 *
 * @throws \Exception If the directory or file cannot be created.
 */
function create_directory_and_move_file() {
	$fileinfo = get_fileinfo();

	// Create directory if it does not exist.
	if ( ! file_exists( trailingslashit( $fileinfo['path'] ) . $fileinfo['dir'] ) ) {
		if ( ! @mkdir( trailingslashit( $fileinfo['path'] ) . $fileinfo['dir'], 0755 ) ) { // @codingStandardsIgnoreLine
			throw new \Exception( __( 'Unable to create domain association folder in domain root.', 'simple-pay' ) );
		}
	}

	// Move file if needed.
	if ( ! has_verification_file() ) {
		if ( ! @copy( trailingslashit( SIMPLE_PAY_DIR ) . $fileinfo['file'], $fileinfo['fullpath'] ) ) { // @codingStandardsIgnoreLine
			throw new \Exception( __( 'Unable to copy domain association file to domain .well-known directory.', 'simple-pay' ) );
		}
	}
}

/**
 * Verify domain with Stripe.
 *
 * Must be a valid domain, and a domain that is publicly accessible.
 *
 * @since 3.4.0
 *
 * @throws \Exception If the API request fails.
 */
function verify_domain_with_stripe() {
	$create = Stripe_API::request(
		'ApplePayDomain',
		'create',
		array(
			'domain_name' => $_SERVER['HTTP_HOST'],
		)
	);

	if ( ! $create ) {
		throw new \Exception( __( 'Unable to automatically add domain to Apple Pay settings in Stripe.', 'simple-pay' ) );
	}

	return $create;
}

/**
 * Add Apple Pay verification.
 *
 * Moves the domain association file and attempts to add domain to Stripe.
 *
 * @since 3.6.0
 */
function verify_domain() {
	$settings = get_option( 'simpay_settings_keys' );

	try {
		// Create directory and move file if needed.
		create_directory_and_move_file();

		// Verify domain with Stripe
		$verification = verify_domain_with_stripe();

		// Set domain verification to current domain name.
		$settings['apple_pay_domain_verification'] = $verification['domain_name'];
	} catch ( \Exception $e ) {
		// Set error if something went wrong.
		$settings['apple_pay_domain_verification'] = $e->getMessage();

		// Force the notice to appear again.
		// @todo This should likely be managed elsewhere.
		Notice_Manager::undismiss_notice( 'apple-pay-domain-verification-' . $_SERVER['HTTP_HOST'] );
	}

	update_option( 'simpay_settings_keys', $settings );
}

/**
 * If an error was caught trying to generate the domain verification, show it.
 *
 * @since 3.6.0
 */
function get_verification_error() {
	$settings = get_option( 'simpay_settings_keys' );
	$error    = (
		isset( $settings['apple_pay_domain_verification'] ) &&
		$settings['apple_pay_domain_verification'] !== $_SERVER['HTTP_HOST']
	);

	ob_start();
	?>

<p>
	<strong><?php echo wp_kses_post( __( 'Apple Pay domain verification error', 'simple-pay' ) ); ?></strong><?php echo $error ? wp_kses_post( ': ' . $settings['apple_pay_domain_verification'] ) : null; ?>
</p>
<p>
	<a href="https://dashboard.stripe.com/account/apple_pay" target="_blank" rel="noopener noreferrer">
		<?php echo esc_html__( 'Manually verify your domain', 'simple-pay' ); ?>
	</a>
</p>

	<?php
	return ob_get_clean();
}

/**
 * Display verification error for current domain.
 *
 * @since 3.6.0
 */
function show_verification_error() {
	// No setting exists to track Apple Pay, so do nothing.
	$settings = get_option( 'simpay_settings_keys' );

	if ( ! isset( $settings['apple_pay_domain_verification'] ) || can_use_apple_pay() ) {
		return;
	}

	Notice_Manager::add_notice(
		'apple-pay-domain-verification-' . $_SERVER['HTTP_HOST'],
		array(
			'dismissible' => true,
			'type'        => 'error',
			'callback'    => __NAMESPACE__ . '\\get_verification_error',
		)
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\show_verification_error', 20 );

/**
 * Determine if we should add the domain to the connected Stripe Account's
 * Apple Pay domain verification.
 *
 * Uses admin_init so notices can be shown in time.
 *
 * @since 3.6.0
 */
function verify_on_form_save() {
	$fields  = isset( $_POST['_simpay_custom_field'] ) ? $_POST['_simpay_custom_field'] : array();
	$has_prb = false;

	// Not saving a form, bail.
	if ( empty( $fields ) ) {
		return;
	}

	// No keys exist, or in test mode, we can't do anything.
	if ( simpay_is_test_mode() || ! simpay_check_keys_exist() ) {
		return;
	}

	foreach ( $fields as $type => $field ) {
		if ( 'payment_request_button' === $type ) {
			$has_prb = true;
			break;
		}
	}

	// Payment Request Button field does not exist, do nothing.
	if ( ! $has_prb ) {
		return;
	}

	verify_domain();
}
add_action( 'admin_init', __NAMESPACE__ . '\\verify_on_form_save' );

/**
 * Attempts to reverify domain when the Stripe Account payment mode changes.
 *
 * @since 3.6.0
 */
function verify_on_stripe_connect() {
	// No keys exist, or in test mode, we can't do anything.
	if ( simpay_is_test_mode() || ! simpay_check_keys_exist() ) {
		return;
	}

	// No setting exists to track Apple Pay, so do nothing.
	$settings = get_option( 'simpay_settings_keys' );

	if ( ! isset( $settings['apple_pay_domain_verification'] ) ) {
		return;
	}

	// Force show any relevant messages when reconnecting.
	verify_domain();
}
add_action( 'simpay_stripe_account_connected', __NAMESPACE__ . '\\verify_on_stripe_connect' );
