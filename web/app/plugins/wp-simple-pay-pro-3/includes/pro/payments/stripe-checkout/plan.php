<?php
/**
 * Stripe Checkout: Plan
 *
 * Pro-only functionality adjustments for Stripe Checkout Plans.
 *
 * @package SimplePay\Pro\Payments\Stripe_Checkout\Plan
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Payments\Stripe_Checkout\Plan;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Payments\Plan;

/**
 * Creates a custom Plan based on a Plan previously created in the Stripe Dashboard.
 *
 * @since 3.7.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @param int                           $customer_id Stripe Customer ID.
 */
function create_custom_plan_from_template( $plan_template, $form, $form_data, $form_values, $customer_id, $product ) {
	if ( empty( $customer_id ) ) {
		$generated_suffix = esc_html__( ' - generated by WP Simple Pay', 'simple-pay' );
	} else {
		/** translators: %1$s Stripe Customer ID */
		$generated_suffix = sprintf( esc_html__( ' - generated by WP Simple Pay for %1$s', 'simple-pay' ), $customer_id );
	}

	/**
	 * Filters the suffix of generated Plan name.
	 *
	 * @since 3.6.6
	 *
	 * @param string $generated_suffix Suffix to append to generated Product and Plan names.
	 * @param SimplePay\Core\Abstracts\Form $form Form instance.
	 * @param array                         $form_data Form data generated by the client.
	 * @param array                         $form_values Values of named fields in the payment form.
	 * @param int                           $customer_id Stripe Customer ID.
	 * @param \Stripe\Plan|null             $product Attached Plan product if using a template, or null.
	 * @return string
	 */
	$generated_suffix = apply_filters(
		'simpay_stripe_checkout_generated_plan_suffix',
		$generated_suffix,
		$form,
		$form_data,
		$form_values,
		$customer_id,
		$product
	);

	$currency = $plan_template->currency;

	$custom_plan_args = array(
		'id'             => sanitize_title_with_dashes( $plan_template->id . '_' . uniqid() ),
		'currency'       => $currency,
		'interval'       => $plan_template->interval,
		'interval_count' => $plan_template->interval_count,
		'nickname'       => $plan_template->nickname . $generated_suffix,
		'metadata'       => array(
			'simpay_is_generated_plan' => 1,
			'simpay_plan_template_id'  => $plan_template->id,
		)
	);

	// Add amount, tax adjusted if needed.
	$amount = $plan_template->amount;

	if ( $form->tax_percent ) {
		$amount += simpay_convert_amount_to_cents(
			simpay_calculate_tax_amount( simpay_convert_amount_to_dollars( $amount ), $form->tax_percent )
		);
	}

	$custom_plan_args['amount'] = $amount;

	// Create arguments for a Product that the Plan can be attached to.
	if ( null === $product ) {
		$product_name = ! empty( $form->company_name )
			? $form->company_name
			: get_bloginfo( 'name' );

		/**
		 * Filters the Subscription Product name used in Stripe Checkout.
		 *
		 * This is the value output on the Stripe.com hosted Checkout page.
		 *
		 * @since 3.6.6
		 *
		 * @since 3.6.0 Uses the form's Item Description as default, falling back to a
		 *              generated "$10 every 3 months" name.
		 * @since 3.6.6 Uses the form's Company Name as default, falling back to the site name.
		 *              This mirrors the behavior of one-time payments.
		 *
		 * @param string $product_name Subscription product name.
		 * @param SimplePay\Core\Abstracts\Form $form Form instance.
		 * @param array                         $form_data Form data generated by the client.
		 * @param array                         $form_values Values of named fields in the payment form.
		 * @param int                           $customer_id Stripe Customer ID.
		 * @param \Stripe\Plan|null             $product Attached Plan product if using a template, or null.
		 */
		$product_name = apply_filters(
			'simpay_stripe_checkout_subscription_product_name',
			$product_name,
			$form,
			$form_data,
			$form_values,
			$customer_id,
			$product
		);

		$custom_plan_args['product'] = array(
			'name'     => $product_name,
			'metadata' => array(
				'simpay_is_generated_product' => 1,
			),
		);
	} else {
		$custom_plan_args['product'] = $product;
	}

	/**
	 * Filter the arguments used to create the custom Plan for a Subscription.
	 *
	 * @since 3.6.0
	 *
	 * @param array                         $custom_plan_args Arguments used to create the Plan.
	 * @param SimplePay\Core\Abstracts\Form $form Form instance.
	 * @param array                         $form_data Form data generated by the client.
	 * @param array                         $form_values Values of named fields in the payment form.
	 * @param int                           $customer_id Stripe Customer ID.
	 */
	$custom_plan_args = apply_filters(
		'simpay_get_custom_plan_args_from_payment_form_request',
		$custom_plan_args, $form, $form_data, $form_values, $customer_id
	);

	return Plan\create( $custom_plan_args );
}

/**
 * Finds all Plan created in the last 24 hours and removes any that have been
 * generated but not assigned to a completed purchase.
 *
 * @since 3.7.0
 */
function cleanup_generated_records() {
	$remove_generated_plans = true;

	/** This filter is documented in includes/pro/webhooks/class-webhook-invoice-payment-succeeded.php */
	$remove_generated_plans = apply_filters( 'simpay_remove_generated_plans', $remove_generated_plans );

	if ( true !== $remove_generated_plans ) {
		return;
	}

	try {
		$start = time() - DAY_IN_SECONDS;
		$end   = time();

		/** This filter is documented in includes/pro/payments/stripe-checkout/customer.php */
		$start = apply_filters( 'simpay_remove_generated_items_start', $start );

		$plans = Stripe_API::request(
			'Plan',
			'all',
			array(
				'created' => array(
					'gte' => $start,
					'lte' => $end,
				),
			)
		);

		foreach ( $plans->autoPagingIterator() as $plan ) {
			Plan\delete_generated( $plan );
		}
	} catch ( \Exception $e ) {
	}
}
add_action( 'simpay_webhook_checkout_session_completed', __NAMESPACE__ . '\\cleanup_generated_records', 20 );
