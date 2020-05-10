<?php
/**
 * Functions
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

use SimplePay\Core\Payments\Stripe_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Elements supported locales.
 *
 * @since 3.6.0
 *
 * @return array
 */
function simpay_get_stripe_elements_locales() {
	return array(
		'auto' => esc_html__( 'Auto-detect', 'simple-pay' ),
		'ar'   => esc_html__( 'Arabic (ar)', 'simple-pay' ),
		'zh'   => esc_html__( 'Chinese (Simplified) (zh)', 'simple-pay' ),
		'da'   => esc_html__( 'Danish (da)', 'simple-pay' ),
		'en'   => esc_html__( 'English (en)', 'simple-pay' ),
		'nl'   => esc_html__( 'Dutch (nl)', 'simple-pay' ),
		'fi'   => esc_html__( 'Finnish (fi)', 'simple-pay' ),
		'fr'   => esc_html__( 'French (fr)', 'simple-pay' ),
		'de'   => esc_html__( 'German (de)', 'simple-pay' ),
		'he'   => esc_html__( 'Hebrew (he)', 'simple-pay' ),
		'it'   => esc_html__( 'Italian (it)', 'simple-pay' ),
		'ja'   => esc_html__( 'Japanese (ja)', 'simple-pay' ),
		'no'   => esc_html__( 'Norwegian (no)', 'simple-pay' ),
		'pl'   => esc_html__( 'Polish (pl)', 'simple-pay' ),
		'ru'   => esc_html__( 'Russian (ru)', 'simple-pay' ),
		'es'   => esc_html__( 'Spanish (es)', 'simple-pay' ),
		'sv'   => esc_html__( 'Swedish (sv)', 'simple-pay' ),
	);
}

/**
 * Check the user's license to see if subscriptions are enabled or not
 *
 * @return bool
 */
function simpay_subscriptions_enabled() {

	$license_data = get_option( 'simpay_license_data' );

	if ( ! empty( $license_data ) && 'valid' === $license_data->license ) {
		$price_id = $license_data->price_id;

		if ( '1' !== $price_id ) {
			return true;
		}
	}

	return false;
}

/**
 * Retrive a list of Plans available in the connected account.
 *
 * @since 3.6.0
 *
 * @return array
 */
function simpay_get_plans() {
	try {
		$args = array(
			'limit' => 100,
		);

		// Retrieve the first 100 plans.
		$plans = Stripe_API::request( 'Plan', 'all', $args );

		if ( ! is_object( $plans ) ) {
			return false;
		}

		// Var to hold final list of plans (merged data property of each plan list call).
		$plan_data = $plans->data;

		// If there are more than 100 plans, iterate through them.
		while ( $plans->has_more ) {

			$last_plan_id = end( $plans->data )->id;

			// Add the `starting_after` parameter to reflect the last plan ID.
			$args = array(
				'limit'          => 100,
				'starting_after' => $last_plan_id,
			);

			$plans = Stripe_API::request( 'Plan', 'all', $args );

			// Merge next group of plans from another plan list call.
			$plan_data = array_merge( $plan_data, $plans->data );

		}

		return $plan_data;
	} catch ( \Exception $e ) {
		return array();
	}
}

/**
 * Get a list of all the Stripe plans
 */
function simpay_get_plan_list() {
	// Make sure the API keys exist before we try to load the plan list
	if ( ! simpay_check_keys_exist() ) {
		return array();
	}

	$skip_metered_plans = true;

	/**
	 * Filters whether or not the list of Plans should include
	 * "Metered usage" pricing options.
	 *
	 * @since 3.6.0
	 *
	 * @param bool $skip_metered_plans If the metered plans should be skipped.
	 * @return bool
	 */
	$skip_metered_plans = apply_filters( 'simpay_get_plan_list_skip_metered_plans', $skip_metered_plans );

	$plans = simpay_get_plans();

	if ( ! empty( $plans ) && is_array( $plans ) ) {

		$options = array();

		foreach ( $plans as $k => $v ) {

			// Skip generated plans.
			if ( isset( $v['metadata']['simpay_is_generated_plan'] ) ) {
				continue;
			}

			// Skip "Metered usage" pricing.
			if ( $skip_metered_plans && ( 'licensed' !== $v['usage_type'] ) ) {
				continue;
			}

			$nickname       = $v['nickname']; // New pricing plan name (as of Stripe API 2018-02-05)
			$legacy_name    = $v['name']; // Legacy plan name attribute (before Stripe API 2018-02-05)
			$id             = $v['id'];
			$currency       = $v['currency'];
			$amount         = $v['amount'];
			$interval       = $v['interval'];
			$interval_count = $v['interval_count'];
			$decimals       = 0;

			// Display "PlanName - $##/month". Omit product name & plan ID.
			// If no plan name (nickname attr), try (legacy) name attr, then finally plan id attr.

			// TODO Display "ProductName/PlanName - $##/month". Omit plan ID. ...at some point?
			// Would need to access Products in Stripe API.

			$plan_name = $nickname;

			if ( empty( $plan_name ) ) {
				if ( ! empty( $legacy_name ) ) {
					$plan_name = $legacy_name;
				} else {
					$plan_name = $id;
				}
			}

			if ( ! simpay_is_zero_decimal( $currency ) ) {
				$amount   = $amount / 100;
				$decimals = 2;
			}

			// Put currency symbol + amount in one string to make it easier
			$amount = simpay_get_currency_symbol( $currency ) . number_format( $amount, $decimals );

			$options[ $id ] = $plan_name . ' - ' . sprintf( _n( '%1$s/%3$s', '%1$s every %2$d %3$ss', $interval_count, 'simple-pay' ), $amount, $interval_count, $interval );
		}

		asort( $options );

		return $options;
	}

	return array();
}

/**
 * Get the description for Form Field Label
 */
function simpay_form_field_label_description() {
	return esc_html__( 'Label displayed above this field on the payment form. Leave blank if using only placeholders.', 'simple-pay' );
}

/**
 * Get the description for Placeholder
 */
function simpay_placeholder_description() {
	return esc_html__( 'i.e. inline label', 'simple-pay' );
}

/**
 * Get the description for Stripe Metadata Label
 */
function simpay_metadata_label_description() {
	return esc_html__( 'Used to identify this field within Stripe payment records. Not displayed on the payment form.', 'simple-pay' );
}

/**
 * My Account/License upgrade URL with GA campaign values.
 *
 * @param string $ga_content
 *
 * @return string
 */
function simpay_my_account_url( $ga_content ) {

	return simpay_ga_url( simpay_get_url( 'my-account' ), $ga_content, true );
}
