<?php
/**
 * Forms: AJAX
 *
 * @package SimplePay\Pro\Forms
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Core\Payments\Stripe_API;

/**
 * Ajax class.
 *
 * @since 3.0.0
 */
class Ajax {

	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_simpay_get_coupon', array( $this, 'simpay_get_coupon' ) );
		add_action( 'wp_ajax_nopriv_simpay_get_coupon', array( $this, 'simpay_get_coupon' ) );
	}

	/**
	 * Check for a coupon and return the discounted amount.
	 */
	public function simpay_get_coupon() {

		// Check nonce first
		if ( false === check_ajax_referer( 'simpay_coupon_nonce', 'couponNonce', false ) ) {
			echo esc_html__( 'Coupon security check failed.', 'simple-pay' );
			wp_die();
		}

		$json = array();
		$code = sanitize_text_field( $_POST['coupon'] );
		// Amount is already unformatted.
		$amount   = floatval( $_POST['amount'] );
		$discount = 0;

		$json['coupon']['code'] = $code;
		$json['amount']         = $amount;

		try {
			$coupon = Stripe_API::request( 'Coupon', 'retrieve', $code );

			// Invalid coupon.
			if ( ! simpay_is_coupon_valid( $coupon ) ) {
				return wp_send_json_error(
					array(
						'error' => esc_html__( 'Coupon is invalid.', 'simple-pay' ),
					)
				);
			}

			// TODO Map JSON coupon object to Stripe coupon object for consistency.
			// percent_off, amount_off, duration, etc.
			// https://stripe.com/docs/api/php#coupons

			// Check coupon type
			if ( ! empty( $coupon->percent_off ) ) {

				// Coupon is percent off so handle that

				$json['coupon']['amountOff'] = $coupon->percent_off;
				$json['coupon']['type']      = 'percent';

				if ( $coupon->percent_off == 100 ) {
					$discount = $amount;
				} else {
					$discount_pct = ( 100 - $coupon->percent_off ) / 100;
					$discount     = $amount - round( $amount * $discount_pct, simpay_get_decimal_places() );
				}
			} elseif ( ! empty( $coupon->amount_off ) ) {

				// Coupon is a set amount off (e,g, $3.00 off)
				if ( simpay_is_zero_decimal() ) {
					$amountOff = $coupon->amount_off;
				} else {
					$amountOff = $coupon->amount_off / 100;
				}

				$json['coupon']['amountOff'] = $amountOff;
				$json['coupon']['type']      = 'amount';

				$discount = simpay_convert_amount_to_cents( $amount - ( $amount - $amountOff ) );

				if ( $discount < 0 ) {
					$discount = 0;
				}
			}

			// Check if the coupon puts the total below the minimum amount
			if ( ( $amount - $discount ) < simpay_global_minimum_amount() ) {
				echo esc_html__( 'Coupon entered puts the total below the required minimum amount.', 'simple-pay' );
				wp_die();
			} else {

				$json['success'] = true;

				// We want to send the correct amount back to the JS
				$json['discount'] = $discount;

				// Send back full Stripe Coupon object.
				// @todo This should be all that is needed for the client
				// but the rest needs to stay for backwards compatibility.
				$json['stripeCoupon'] = $coupon;
			}

			// Return coupon duration for recurring amount label.
			if ( ! empty( $coupon->duration ) ) {
				$json['coupon']['duration'] = $coupon->duration;
			}

			// Return as JSON
			wp_send_json( $json );
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
		}
	}
}
