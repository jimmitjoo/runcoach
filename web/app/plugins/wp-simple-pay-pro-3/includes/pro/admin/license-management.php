<?php
/**
 * License Management
 *
 * @package SimplePay\Pro\License_Management
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\License_Management;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the EDD SL Updater class if a key is saved
 *
 * @since unknown
 */
function load_updater() {
	// Load custom updater file.
	require_once trailingslashit( SIMPLE_PAY_INC ) . 'pro/class-edd-sl-plugin-updater.php';

	// Retrieve our license key from the DB.
	$key      = trim( get_option( 'simpay_license_key' ) );
	$settings = get_option( 'simpay_settings_general' );

	new \Simple_Pay_EDD_SL_Plugin_Updater(
		SIMPLE_PAY_STORE_URL,
		SIMPLE_PAY_MAIN_FILE,
		array(
			'version' => SIMPLE_PAY_VERSION,        // current version number
			'license' => $key,                      // license key (used get_option above to retrieve from DB)
			'item_id' => SIMPLE_PAY_ITEM_ID,        // Download ID of this plugin (using instead of Download Name)
			'author'  => 'Moonstone Media',          // author of this plugin,
			'beta'    => ! empty( $settings['general_misc']['beta_opt_in'] ),
		)
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\load_updater', 0 );

/**
 * Show a notice in the "Settings"  menu item if there is no license key.
 *
 * @since 3.5.0
 */
function settings_menu_name( $name ) {
	$license = get_option( 'simpay_license_key', false );

	if ( $license ) {
		return $name;
	}

	$bubble = ' <span id="simpay-settings-bubble" class="awaiting-mod count-1"><span class="pending-count">1</span></span>';

	return $name . $bubble;
}
add_filter( 'simpay_settings_menu_name', __NAMESPACE__ . '\\settings_menu_name' );

/**
 * Show a notice in the plugin list if there is no license key.
 *
 * @since 3.5.0
 *
 * @param string $file
 * @param array  $plugin
 * @return mixed
 */
function plugin_list_show_empty_license_notice( $file ) {
	$license_data = get_option( 'simpay_license_data', false );

	if ( $license_data && 'valid' === $license_data->license ) {
		return;
	}

	wp_add_inline_script(
		'updates',
		sprintf(
			implode(
				"\n",
				array(
					'( function() {',
					'  var row = document.querySelector( \'[data-slug="wp-simple-pay-pro"]\' );',
					'  if ( row ) {',
					'    row.classList.add( "update" );',
					'  }',
					'} )();',
				)
			)
		)
	);
	?>

<tr class="simpay-plugin-update-license plugin-update-tr active">
	<td colspan="3" class="plugin-update colspanchange">
		<div class="notice inline notice-warning notice-alt">
			<p>
				<strong><?php esc_html_e( 'A valid license key is required for access to automatic updates and support.', 'simple-pay' ); ?></strong>
			</p>

			<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. %3$s Opening anchor tag, do not translate. */
					__( 'Retrieve your license key from %1$syour WP Simple Pay account%3$s or purchase receipt email then %2$sactivate your website%3$s.', 'simple-pay' ),
					sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">', simpay_ga_url( simpay_get_url( 'my-account' ), 'settings-link' ) ),
					sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">', admin_url( 'admin.php?page=simpay_settings' ) ),
					'</a>'
				)
			);
			?>
			</p>
		</div>
	</td>
</tr>

	<?php
}
add_action(
	'after_plugin_row_' . plugin_basename( SIMPLE_PAY_MAIN_FILE ),
	__NAMESPACE__ . '\\plugin_list_show_empty_license_notice',
	5
);

/**
 * Check if a current license is still valid.
 * Run check once every 24 hours, or 2 hours if an error is encountered.
 *
 * @since unknown
 */
function check_license_still_valid() {
	$simpay_license_next_check = get_option( 'simpay_license_next_check', false );

	if ( is_numeric( $simpay_license_next_check ) && ( $simpay_license_next_check > current_time( 'timestamp' ) ) ) {
		return;
	}

	$key = trim( get_option( 'simpay_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license'    => $key,
		'item_id'    => SIMPLE_PAY_ITEM_ID,
		'url'        => home_url(),
	);

	// Call the custom API.
	$response = wp_remote_post(
		SIMPLE_PAY_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	if ( is_wp_error( $response ) ) {

		update_option( 'simpay_license_next_check', current_time( 'timestamp' ) + ( HOUR_IN_SECONDS * 2 ) );

		return;
	}

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// Update saved license data & timestamp.
	update_option( 'simpay_license_data', $license_data );
	update_option( 'simpay_license_next_check', current_time( 'timestamp' ) + DAY_IN_SECONDS );
}
add_action( 'admin_init', __NAMESPACE__ . '\\check_license_still_valid' );

/**
 * Activate a license key.
 *
 * @param string $license License key to activate.
 * @return bool|object False on failure, or license data.
 */
function activate_license( $license ) {
	// Retrieve license key from form field.
	$key = sanitize_key( trim( $license ) );

	// data to send in our API request
	$api_params = array(
		'edd_action' => 'activate_license',
		'license'    => $key,
		'item_id'    => SIMPLE_PAY_ITEM_ID,
		'url'        => home_url(),
	);

	// Call the custom API.
	$response = wp_remote_post(
		SIMPLE_PAY_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	// No error, so let's proceed.

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// Update saved license key & data.
	update_option( 'simpay_license_key', $key );
	update_option( 'simpay_license_data', $license_data );

	return $license_data;
}

/**
 * Deactivate a license key.
 *
 * @param string $license License key to deactivate.
 * @return bool|object False on failure, or license data.
 */
function deactivate_license( $license ) {
	$key = sanitize_key( trim( $license ) );

	// data to send in our API request
	$api_params = array(
		'edd_action' => 'deactivate_license',
		'license'    => $key,
		'item_id'    => SIMPLE_PAY_ITEM_ID,
		'url'        => home_url(),
	);

	// Call the custom API.
	$response = wp_remote_post(
		SIMPLE_PAY_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	// No error, so let's proceed.

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// $license_data->license will be either "deactivated" or "failed"
	if ( $license_data->license == 'deactivated' ) {

		// Remove saved license data, key & next check options.
		delete_option( 'simpay_license_data' );
		delete_option( 'simpay_license_key' );
		delete_option( 'simpay_license_next_check' );
	}

	return $license_data;
}

/**
 * Maybe add the expiration date to a valid license feedback message.
 *
 * @since 3.5.0
 *
 * @param object $license_data
 * @param string $feedback
 * @return string
 */
function maybe_add_expiration_to_feedback( $license_data, $feedback ) {
	// Default return value
	$retval = $feedback;

	// Bail if not a valid license
	if ( 'valid' !== $license_data->license ) {
		return $retval;
	}

	// Bail if no expiration
	if ( empty( $license_data->expires ) || 'lifetime' === $license_data->expires ) {
		$retval = get_license_feedback( 'valid-forever' );
	} else {
		$date    = date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires ) );
		$expires = '<time datetime="' . $license_data->expires . '">' . $date . '</time>';
		$retval  = sprintf( $feedback, $expires );
	}

	return $retval;
}

/**
 * Get license feedback, based on a specific status.
 *
 * @since 3.5.0
 *
 * @static var array $retval
 *
 * @param string $status Optional specific status to fetch.
 * @return array|string
 */
function get_license_feedback( $status = '' ) {
	static $retval = array();

	// Stash array in local static var to avoid thrashing the gettext API
	if ( empty( $retval ) ) {
		$retval = array(
			'empty'               => esc_html__( 'Please enter a valid license key.', 'simple-pay' ),
			/* translators: %1$s The date the license is valid until. */
			'valid'               => esc_html__( 'This license key is valid until %1$s.', 'simple-pay' ),
			// Stub fedback for when the license does not expired.
			'valid-forever'       => esc_html__( 'This license key is valid.', 'simple-pay' ),
			'expired'             => esc_html__( 'This license key is expired', 'simple-pay' ),
			'disabled'            => esc_html__( 'This license key is disabled.', 'simple-pay' ),
			'revoked'             => esc_html__( 'This license key is disabled.', 'simple-pay' ),
			'invalid'             => esc_html__( 'This license key is not valid.', 'simple-pay' ),
			'inactive'            => esc_html__( 'This license key is saved but has not been activated.', 'simple-pay' ),
			'deactivated'         => esc_html__( 'This license key is saved but has not been activated.', 'simple-pay' ),
			'failed'              => esc_html__( 'This license key could not be deactivated.', 'simple-pay' ),
			'site_inactive'       => esc_html__( 'This license key is saved but has not been activated.', 'simple-pay' ),
			'item_name_mismatch'  => esc_html__( 'This license key appears to be for another product.', 'simple-pay' ),
			'invalid_item_id'     => esc_html__( 'This license key appears to be for another product.', 'simple-pay' ),
			'no_activations_left' => esc_html__( 'This license key has reached its activation limit.', 'simple-pay' ),
		);
	}

	// Maybe pluck a specific status
	if ( isset( $retval[ $status ] ) ) {
		return $retval[ $status ];
	}

	// Return specific array, or all if not found
	return $retval;
}
