<?php
/**
 * Admin: AJAX
 *
 * @package SimplePay\Pro\Admin
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Admin;

use SimplePay\Pro\Admin\Metaboxes\Custom_fields;
use SimplePay\Pro\License_Management;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin ajax.
 *
 * @since 3.0.0
 */
class Ajax {

	/**
	 * Set up ajax hooks.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_simpay_add_field', array( __CLASS__, 'add_field' ) );

		add_action( 'wp_ajax_simpay_add_plan', array( __CLASS__, 'add_plan' ) );

		// License key activation/deactivation.
		add_action( 'wp_ajax_simpay_activate_license', array( __CLASS__, 'activate_license' ) );
		add_action( 'wp_ajax_simpay_deactivate_license', array( __CLASS__, 'deactivate_license' ) );
	}

	/**
	 * Add a new metabox for custom fields settings
	 */
	public static function add_field() {

		// Check the nonce first
		check_ajax_referer( 'simpay_custom_fields_nonce', 'addFieldNonce' );

		ob_start();

		$type = isset( $_POST['fieldType'] ) ? sanitize_key( strtolower( $_POST['fieldType'] ) ) : '';

		$counter = isset( $_POST['counter'] ) ? intval( $_POST['counter'] ) : 0;
		$uid     = $counter;

		// Load new metabox depending on what type was selected
		if ( ! empty( $type ) ) {

			try {
				Custom_Fields::print_custom_field( $type, '', $counter, $uid );
			} catch ( \Exception $e ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $e,
					)
				);
			}
		} else {
			wp_send_json_error( array( 'success' => false ) );
		}

		ob_end_flush();

		die();
	}

	/**
	 * Add new plan for Subscription multi-plans section
	 */
	public function add_plan() {

		check_ajax_referer( 'simpay_add_plan_nonce', 'addPlanNonce' );

		ob_start();

		// Plan counter & order are used in tab-multi-subs file that's included.
		$plan_counter      = isset( $_POST['counter'] ) ? intval( $_POST['counter'] ) : 0;
		$plan_order        = isset( $_POST['counter'] ) ? intval( $_POST['counter'] ) : 0;
		$active_plans_list = simpay_get_plan_list();

		include( 'metaboxes/views/tabs/tab-multi-subs.php' );

		ob_end_flush();

		die();

	}

	/**
	 * Activate a plugin license.
	 *
	 * @since 3.5.0
	 */
	public static function activate_license() {
		$unknown_error = array(
			'message' => esc_html__( 'An unknown error has occured. Please try again.', 'simple-pay' ),
		);

		if ( ! wp_verify_nonce( $_POST['nonce'], 'simpay-manage-license' ) ) {
			return wp_send_json_error( $unknown_error );
		}

		$license      = sanitize_text_field( $_POST['license'] );
		$license_data = License_Management\activate_license( $license );

		// Error talking to the API.
		if ( ! $license_data ) {
			return wp_send_json_error( $unknown_error );
		}

		$feedback = License_Management\get_license_feedback( $license_data->license );
		$message  = License_Management\maybe_add_expiration_to_feedback( $license_data, $feedback );

		if ( 'valid' === $license_data->license ) {
			return wp_send_json_success(
				array(
					'message'      => $message,
					'license_data' => $license_data,
				)
			);
		} else {
			return wp_send_json_error(
				array(
					'message'      => $message,
					'license_data' => $license_data,
				)
			);
		}
	}

	/**
	 * Deactivate a plugin license.
	 *
	 * @since 3.5.0
	 */
	public static function deactivate_license() {
		$unknown_error = array(
			'message' => esc_html__( 'An unknown error has occured. Please try again.', 'simple-pay' ),
		);

		if ( ! wp_verify_nonce( $_POST['nonce'], 'simpay-manage-license' ) ) {
			return wp_send_json_error( $unknown_error );
		}

		$license      = sanitize_text_field( $_POST['license'] );
		$license_data = License_Management\deactivate_license( $license );

		// Error talking to the API.
		if ( ! $license_data ) {
			return wp_send_json_error( $unknown_error );
		}

		$feedback = License_Management\get_license_feedback( $license_data->license );
		$message  = License_Management\maybe_add_expiration_to_feedback( $license_data, $feedback );

		if ( 'deactivated' === $license_data->license ) {
			return wp_send_json_success(
				array(
					'message'      => $message,
					'license_data' => $license_data,
				)
			);
		} else {
			return wp_send_json_error(
				array(
					'message'      => $message,
					'license_data' => $license_data,
				)
			);
		}
	}

}
