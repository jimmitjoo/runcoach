<?php
/**
 * Webhook: Payment Intent Succeeded
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.3
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Payment_Intent_Succeeded class.
 *
 * @since 3.6.3
 */
class Webhook_Payment_Intent_Succeeded extends Webhook_Base implements Webhook_Interface {

	/**
	 * @var \Stripe\PaymentIntent
	 * @since 3.6.3
	 */
	public $payment_intent;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.6.3
	 */
	public function handle() {
		$payment_intent = $this->event->data->object;

		// Retreive again with Customer expanded.
		$this->payment_intent = Stripe_API::request(
			'PaymentIntent',
			'retrieve',
			array(
				'id'     => $payment_intent->id,
				'expand' => array(
					'customer',
				),
			)
		);

		// PaymentIntent is not created by an Invoice.
		if ( ! $payment_intent->invoice && 'succeeded' === $payment_intent->status ) {
			/**
			 * Allows processing after a single payment intent succeeds.
			 *
			 * @since 3.6.3
			 *
			 * @param \Stripe\Event         $event Stripe webhook event.
			 * @param \Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
			 */
			do_action( 'simpay_webhook_payment_intent_succeeded', $this->event, $this->payment_intent );
		}
	}

}
