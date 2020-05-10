<?php
/**
 * Payment confirmation template tags
 *
 * @package SimplePay\Pro\Payments\Payment_Confirmation\Template_Tags
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Pro\Payments\Payment_Confirmation\Template_Tags;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Payments\Payment_Confirmation\Template_Tags as Core_Template_Tags;

use function SimplePay\Core\SimplePay;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds additional confirmation template tags.
 *
 * @param array $tags Template tags.
 * @return array
 */
function add_template_tags( $tags ) {
	return array_merge(
		$tags,
		array(
			'tax-amount',
			'recurring-amount',
			'max-charges',
			'trial-end-date',
			'payment',
			'subscription',
			'customer',
		)
	);
}
add_filter( 'simpay_payment_details_template_tags', __NAMESPACE__ . '\\add_template_tags' );

/**
 * Replaces {charge-id} with the Customer's Subscription ID.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function charge_id( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	return esc_html( $subscription->id );
}
add_filter( 'simpay_payment_confirmation_template_tag_charge-id', __NAMESPACE__ . '\\charge_id', 10, 3 );

/**
 * Replaces {charge-date} with the PaymentIntent's first Charge date.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function charge_date( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	// Localize format.
	$value = date_i18n( get_option( 'date_format' ), $subscription->created );

	/**
	 * @deprecated 3.6.0
	 */
	$value = apply_filters_deprecated(
		'simpay_details_order_date',
		array( $value ),
		'3.6.0',
		'simpay_payment_confirmation_template_tag_charge-date'
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_charge-date', __NAMESPACE__ . '\\charge_date', 10, 3 );

/**
 * Replaces {total-amount} with the PaymentIntent's first Charge amount.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function charge_amount( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	$value = simpay_format_currency(
		simpay_convert_amount_to_dollars( $subscription->latest_invoice->amount_paid ),
		$subscription->plan->currency
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_total-amount', __NAMESPACE__ . '\\charge_amount', 10, 3 );

/**
 * Replaces {tax-amount} template tag.
 *
 * @since 3.6.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function tax_amount( $value, $payment_confirmation_data ) {
	if ( ! empty( $payment_confirmation_data['subscriptions'] ) ) {
		$object   = current( $payment_confirmation_data['subscriptions'] );
		$currency = $object->plan->currency;
	} else {
		$object   = current( $payment_confirmation_data['paymentintents'] );
		$currency = $object->currency;
	}

	if ( ! isset( $object->metadata->simpay_tax_amount ) ) {
		$amount = simpay_format_currency( 0, $currency );
	} else {
		$amount = $object->metadata->simpay_tax_amount;
	}

	return esc_html( $amount );
}
add_filter( 'simpay_payment_confirmation_template_tag_tax-amount', __NAMESPACE__ . '\\tax_amount', 10, 2 );

/**
 * Replaces {recurring-amount} template tag.
 *
 * @since 3.6.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function recurring_amount( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription     = current( $payment_confirmation_data['subscriptions'] );
	$upcoming_invoice = Stripe_API::request(
		'Invoice',
		'upcoming',
		array(
			'customer' => $payment_confirmation_data['customer']->id,
		)
	);

	$amount   = $upcoming_invoice->amount_due;
	$currency = $upcoming_invoice->currency;

	$recurring_amount = simpay_format_currency(
		simpay_convert_amount_to_dollars( $amount ),
		$currency
	);
	$interval_count   = $subscription->plan->interval_count;
	$interval         = $subscription->plan->interval;

	return esc_html(
		sprintf(
			_n(
				'%1$s/%3$s',
				'%1$s every %2$d %3$ss',
				$interval_count,
				'simple-pay'
			),
			$recurring_amount,
			$interval_count,
			$interval
		)
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_recurring-amount', __NAMESPACE__ . '\\recurring_amount', 10, 2 );

/**
 * Replaces {max-charges} template tag.
 *
 * @since 3.6.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function max_charges( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	$value = isset( $subscription->metadata->simpay_charge_max ) ? $subscription->metadata->simpay_charge_max : $value;

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_max-charges', __NAMESPACE__ . '\\max_charges', 10, 2 );

/**
 * Replaces {trial-end-date} template tag.
 *
 * @since 3.6.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function trial_end_date( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	$value = date_i18n( get_option( 'date_format' ), $subscription->trial_end );

	return $value;
}
add_filter( 'simpay_payment_confirmation_template_tag_trial-end-date', __NAMESPACE__ . '\\trial_end_date', 10, 2 );

/**
 * Replaces {payment}, {subscription}, or {customer} template tag.
 *
 * Tags can be used in the following way:
 *
 *  {payment:metadata:simpay_form_id}
 *  {payment:currency}
 *  {subscription:metadata:simpay_form_id}
 *  {subscription:id}
 *
 * To access object properties.
 *
 * @link https://stripe.com/docs/api/payment_intents
 * @link https://stripe.com/docs/api/subscriptions
 * @link https://stripe.com/docs/api/customers
 *
 * @since 3.7.0
 *
 * @param string $value Template tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @param string $tag Payment confirmation template tag name, excluding curly braces.
 * @param array  $tags_with_keys Payment confirmation template tags including keys, excluding curly braces.
 * @return string
 */
function stripe_object_with_keys( $value, $payment_confirmation_data, $tag, $tag_with_keys ) {
	switch ( $tag ) {
		case 'payment':
			// Use first PaymentIntent.
			$object = current( $payment_confirmation_data['paymentintents'] );
			break;
		case 'subscription':
			// Use first Subscription.
			$object = current( $payment_confirmation_data['subscriptions'] );
			break;
		case 'customer':
			$object = $payment_confirmation_data['customer'];
			break;
	}

	$tag_keys = Core_Template_Tags\get_tag_keys( $tag_with_keys );
	$value    = Core_Template_tags\get_object_property_deep( $tag_keys, $object );

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_payment', __NAMESPACE__ . '\\stripe_object_with_keys', 10, 4 );
add_filter( 'simpay_payment_confirmation_template_tag_subscription', __NAMESPACE__ . '\\stripe_object_with_keys', 10, 4 );
add_filter( 'simpay_payment_confirmation_template_tag_customer', __NAMESPACE__ . '\\stripe_object_with_keys', 10, 4 );
