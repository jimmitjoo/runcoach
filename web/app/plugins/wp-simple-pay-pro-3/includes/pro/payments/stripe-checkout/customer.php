<?php
/**
 * Stripe Checkout: Customer
 *
 * Pro-only functionality adjustments for Stripe Checkout Customers.
 *
 * @package SimplePay\Pro\Payments\Stripe_Checkout\Customer
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Payments\Stripe_Checkout\Customer;

use SimplePay\Core\Payments\Stripe_API;

/**
 * Adds `simpay_is_generated_customer` metadata to Stripe Customer records that are
 * generated for Stipe Checkout.
 *
 * This allows cleanup of these records if the Stripe Checkout Session is not completed.
 *
 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/964
 * @see \SimplePay\Pro\Payments\Stripe_Checkout\Subscription\create_custom_plan_from_template()
 *
 * @since 3.7.0
 *
 * @param \Stripe\Customer              $customer Stripe Customer.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 */
function add_generated_record_metadata( $customer_args, $form ) {
	if ( 'stripe_checkout' !== $form->get_display_type() ) {
		return $customer_args;
	}

	$customer_args['metadata']['simpay_is_generated_customer'] = 1;

	return $customer_args;
}
add_filter( 'simpay_get_customer_args_from_payment_form_request', __NAMESPACE__ . '\\add_generated_record_metadata', 10, 2 );

/**
 * Removes `simpay_is_generated_customer` metadata from a Stripe Customer record
 * when a Stripe Checkout Session has been completed.
 *
 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/964
 *
 * @since 3.7.0
 *
 * @param \Stripe\Event         $event Stripe webhook event.
 * @param null|\Stripe\Customer $customer Stripe Customer.
 */
function remove_generated_record_metadata( $event, $customer ) {
	if ( null === $customer ) {
		return;
	}

	$metadata = $customer->metadata->toArray();

	if ( isset( $metadata['simpay_is_generated_customer'] ) ) {
		$metadata['simpay_is_generated_customer'] = '';
	}

	Stripe_API::request(
		'Customer',
		'update',
		$customer->id,
		array(
			'metadata' => $metadata,
		)
	);
}
add_action( 'simpay_webhook_checkout_session_completed', __NAMESPACE__ . '\\remove_generated_record_metadata', 10, 2 );

/**
 * Finds all Customers created in the last 24 hours and removes any that have been
 * generated but not assigned to a completed purchase.
 *
 * @since 3.7.0
 */
function cleanup_generated_records() {
	$remove_generated_customers = true;

	/**
	 * Filters if generated Customers should be removed.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $remove_generated_customers Determines if generated Customers should be removed.
	 */
	$remove_generated_customers = apply_filters( 'simpay_remove_generated_customers', $remove_generated_customers );

	if ( true !== $remove_generated_customers ) {
		return;
	}

	try {
		$start = time() - DAY_IN_SECONDS;
		$end   = time();

		/**
		 * Filters timestamp is used as the starting point of the time
		 * range, ending at the current time.
		 *
		 * @since 3.7.0
		 *
		 * @param bool $start Starting timestamp for query time range. Default 1 day ago.
		 */
		$start = apply_filters( 'simpay_remove_generated_items_start', $start );

		$customers = Stripe_API::request(
			'Customer',
			'all',
			array(
				'created' => array(
					'gte' => $start,
					'lte' => $end,
				),
			)
		);

		foreach ( $customers->autoPagingIterator() as $customer ) {
			if ( ! isset( $customer->metadata->simpay_is_generated_customer ) ) {
				continue;
			}

			$customer->delete();
		}
	} catch ( \Exception $e ) {
	}
}
add_action( 'simpay_webhook_checkout_session_completed', __NAMESPACE__ . '\\cleanup_generated_records', 20 );
