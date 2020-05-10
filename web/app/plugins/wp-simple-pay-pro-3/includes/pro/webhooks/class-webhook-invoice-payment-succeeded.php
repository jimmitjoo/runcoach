<?php
/**
 * Webhooks: Invoice Payment Succeeded
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Payments\Plan;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Invoice_Payment_Succeeded class.
 *
 * @since 3.5.0
 */
class Webhook_Invoice_Payment_Succeeded extends Webhook_Base implements Webhook_Interface {

	/**
	 * @type \Stripe\Subscription
	 * @since 3.6.3
	 */
	public $subscription;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.5.0
	 */
	public function handle() {
		$invoice = $this->event->data->object;

		if ( ! $invoice->subscription ) {
			throw new \Exception( esc_html__( 'Subscription not found.', 'simple-pay' ) );
		}

		$this->subscription = Stripe_API::request(
			'Subscription',
			'retrieve',
			array(
				'id'     => $invoice->subscription,
				'expand' => array(
					'customer',
				),
			)
		);

		// Initial invoice, Subscription is new.
		if ( 'subscription_create' === $invoice->billing_reason ) {
			/**
			 * Allows processing after a subscription's first payment has been completed.
			 *
			 * This is done here instead of the actual `customer.subscription.created` webhook
			 * to ensure it is only run after an invoice has been successfully paid.
			 *
			 * @since 3.6.3
			 *
			 * @param \Stripe\Event        $event Stripe webhook event.
			 * @param \Stripe\Subscription $subscription Stripe Subscription.
			 */
			do_action( 'simpay_webhook_subscription_created', $this->event, $this->subscription );

			// Remove generated Product + Plans.
			$this->maybe_remove_generated_plan();
		}

		$this->handle_installment_plan();
	}

	/**
	 * Removes unique Product + Plans generated for Subscriptions.
	 *
	 * @since 3.6.0
	 *
	 * @param \Stripe\Subscription $subscription Stripe Subscription.
	 * @return bool
	 */
	private function maybe_remove_generated_plan() {
		$remove_generated_plans = true;

		/**
		 * Filters if generated Plans + Products should be removed.
		 *
		 * @since 3.6.0
		 *
		 * @param bool $remove_generated_plans Determines if generated plans should be removed.
		 */
		$remove_generated_plans = apply_filters( 'simpay_remove_generated_plans', $remove_generated_plans );

		if ( true !== $remove_generated_plans ) {
			return;
		}

		$invoice = $this->event->data->object;
		$lines   = $invoice->lines->data;

		foreach ( $lines as $line ) {
			if ( ! isset( $line->plan ) ) {
				continue;
			}

			$plan = $line->plan;

			try {
				Plan\delete_generated( $plan );
			} catch ( \Exception $e ) {
				// Do nothing.
				// It's fine if these generated records can't be automatically deleted.
				//
				// It might not be able to be deleted due to:
				// - Previously removed through another Webhook (such as a successful Stripe Checkout Session).
				// - Previously removed through the Stripe Dashboard.
			}
		}
	}

	/**
	 * Tracks the number of invoices that have been charged, and cancels the subscription
	 * when the maximum charge count has been reached.
	 *
	 * @todo May be better to retrieve the actual Invoices from Stripe and count those.
	 *
	 * @since 3.6.0
	 *
	 * @see https://stripe.com/docs/recipes/installment-plan
	 */
	private function handle_installment_plan() {
		$invoice = $this->event->data->object;

		/**
		 * Allow additional actions to be performed inside the `invoice.payment_succeeded` event processing.
		 *
		 * @since 3.5.0
		 *
		 * @param \Stripe\Event        $this->event Stripe Event object.
		 * @param \Stripe\Invoice      $invoice Stripe Invoice object.
		 * @param \Stripe\Subscription $subscription Stripe Subscription object.
		 */
		do_action( 'simpay_webhook_invoice_payment_succeeded', $this->event, $invoice, $this->subscription );

		// No max charge is set, so do nothing.
		if ( ! isset( $this->subscription->metadata['simpay_charge_max'] ) ) {
			return;
		}

		$max_charges  = $this->subscription->metadata['simpay_charge_max'];
		$charge_count = $this->subscription->metadata['simpay_charge_count'];

		$charge_count++;

		// Update the total count metadata
		$this->subscription->metadata['simpay_charge_count'] = absint( $charge_count );
		$this->subscription->save();

		/**
		 * Allow additional actions to be performed before subscription metadata is updated.
		 *
		 * Since 3.5.0 this now actually happens *after* the subscription is updated in Stripe.
		 *
		 * @since 3.5.0
		 *
		 * @param \Stripe\Event        $this->event Stripe Event object.
		 * @param \Stripe\Invoice      $invoice Stripe Invoice object.
		 * @param \Stripe\Subscription $subscription Stripe Subscription object.
		 */
		do_action( 'simpay_webhook_after_installment_increase', $this->event, $invoice, $this->subscription );

		// Cancel subscription if the new charge count equals (or is somehow greater) than the max charges.
		if ( $charge_count >= $max_charges ) {
			$this->subscription->cancel();

			/**
			 * Allow additional actions to be performed after a subscription is cancelled.
			 *
			 * @since 3.5.0
			 *
			 * @param object $this->event Stripe Event object.
			 * @param object $invoice Stripe Invoice object.
			 * @param object $subscription Stripe Subscription object.
			 */
			do_action( 'simpay_webhook_after_subscription_cancel', $this->event, $invoice, $this->subscription );
		}
	}
}
