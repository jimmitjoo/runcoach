<?php
/**
 * Webhooks: Checkout Session Completed
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Checkout_Session_Completed class.
 *
 * @since 3.7.0
 */
class Webhook_Checkout_Session_Completed extends Webhook_Base implements Webhook_Interface {

	/**
	 * Customer.
	 *
	 * @since 3.7.0
	 * @type \Stripe\Customer
	 */
	public $customer = null;

	/**
	 * Payment Intent.
	 *
	 * @since 3.7.0
	 * @type \Stripe\PaymentIntent
	 */
	public $payment_intent = null;

	/**
	 * Subscription.
	 *
	 * @since 3.7.0
	 * @type \Stripe\Subscription
	 */
	public $subscription = null;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.7.0
	 */
	public function handle() {
		$object = $this->event->data->object;

		if ( null !== $object->customer ) {
			$this->customer = Stripe_API::request(
				'Customer',
				'retrieve',
				$object->customer
			);
		}

		if ( null !== $object->payment_intent ) {
			$this->payment_intent = Stripe_API::request(
				'PaymentIntent',
				'retrieve',
				$object->payment_intent
			);
		}

		if ( null !== $object->subscription ) {
			$this->subscription = Stripe_API::request(
				'Subscription',
				'retrieve',
				$object->subscription
			);
		}

		/**
		 * Allows processing after a Checkout Session is completed.
		 *
		 * @since 3.7.0
		 *
		 * @param \Stripe\Event              $event Stripe webhook event.
		 * @param null|\Stripe\Customer      $customer Stripe Customer.
		 * @param null|\Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
		 * @param null|\Stripe\Subscription  $subscription Stripe Subscription.
		 */
		do_action(
			'simpay_webhook_checkout_session_completed',
			$this->event,
			$this->customer,
			$this->payment_intent,
			$this->subscription
		);
	}
}
