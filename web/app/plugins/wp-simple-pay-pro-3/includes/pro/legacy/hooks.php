<?php
/**
 * Legacy: Hooks
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Pro\Legacy\Hooks;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Payments\Payment;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle legacy `simpay_subscription_created` hook.
 *
 * @since 3.6.0
 *
 * @param array                         $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_values Values of named fields in the payment form.
 */
function simpay_subscription_created( $payment_confirmation_data, $form, $form_values ) {
	if ( ! has_action( 'simpay_subscription_created' ) ) {
		return;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );
	$customer     = $payment_confirmation_data['customer'];

	if ( $subscription->latest_invoice && $subscription->latest_invoice->payment_intent ) {
		$charge = current( $subscription->latest_invoice->payment_intent->charges->data );
	} else {
		$charge = new \stdClass();
	}

	// Save old $_POST.
	$post_vars = $_POST;

	// Shim $_POST so existing actions have access to the same values.
	$_POST = $form_values;

	// Make form available.
	global $simpay_form;
	$simpay_form = $form;

	/**
	 * Allow further processing after a subscription has been created.
	 *
	 * @since unknown
	 *
	 * @param \Stripe\Subscription $subscription Stripe Subscription.
	 * @param \Stripe\Customer     $customer Stripe Customer.
	 * @param \Stripe\Charge       $charge Stripe Charge.
	 */
	do_action( 'simpay_subscription_created', $subscription, $customer, $charge );

	// Reset.
	$_POST = $post_vars;
	unset( $simpay_form );
	unset( $post_vars );
}

/**
 * Accesses a payment confirmation's data to run the legacy `simpay_subscription_created` hook.
 *
 * New implementations should use Webhooks to verify that action is only taken
 * when an object reaches the proper status.
 *
 * @since 3.6.0
 *
 * @param array                         $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_values Values of named fields in the payment form.
 */
function _transform_payment_confirmation_for_legacy_subscription( $payment_confirmation_data, $form, $form_values ) {
	if ( ! has_action( 'simpay_subscription_created' ) ) {
		return;
	}

	if ( ! isset( $payment_confirmation_data['subscriptions'] ) ) {
		return;
	}

	if ( ! $form->is_subscription() ) {
		return;
	}

	if ( isset( $payment_confirmation_data['subscriptions'] ) ) {
		simpay_subscription_created( $payment_confirmation_data, $form, $form_values );
	}
}
add_action( '_simpay_payment_confirmation', __NAMESPACE__ . '\\_transform_payment_confirmation_for_legacy_subscription', 10, 3 );
