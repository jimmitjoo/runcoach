<?php
/**
 * Coupons
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate a coupon based on Stripe settings.
 *
 * @since 3.5.0
 *
 * @param object $coupon Stripe coupon.
 * @return bool
 */
function simpay_is_coupon_valid( $coupon ) {
	$valid = true;

	// If coupon is not found then exit now.
	if ( false === $coupon ) {
		$valid = false;
	}

	// Generally invalid.
	if ( ! $coupon->valid ) {
		$valid = false;
	}

	// Used too many times.
	if ( $coupon->max_redemptions && ( $coupon->times_redeemed === $coupon->max_redemptions ) ) {
		$valid = false;
	}

	// Expired.
	if ( $coupon->redeem_by && ( time() > $coupon->redeem_by ) ) {
		$valid = false;
	}

	/**
	 * Filter coupon validity.
	 *
	 * @since 3.5.0
	 *
	 * @param bool $valid If the coupon is valid or not.
	 * @param object $coupon Stripe coupon.
	 */
	return apply_filters( 'simpay_is_coupon_valid', $valid, $coupon );
}
