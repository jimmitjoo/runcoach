<?php
/**
 * Webhooks: Invoice Upcoming
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
 * Webhook_Invoice_Upcoming class.
 *
 * @since 3.7.0
 */
class Webhook_Invoice_Upcoming extends Webhook_Base implements Webhook_Interface {

	/**
	 * @since 3.7.0
	 * @type \Stripe\Invoice
	 */
	public $invoice;

	/**
	 * @since 3.7.0
	 * @type \Stripe\Subscription
	 */
	public $subscription;

	/**
	 * Handles the Webhook's data.
	 *
	 * @since 3.7.0
	 */
	public function handle() {
		$this->invoice = $this->event->data->object;

		if ( ! $this->invoice->subscription ) {
			throw new \Exception( esc_html__( 'Subscription not found.', 'simple-pay' ) );
		}

		$this->subscription = Stripe_API::request(
			'Subscription',
			'retrieve',
			array(
				'id'     => $this->invoice->subscription,
				'expand' => array(
					'customer',
				),
			)
		);

		/**
		 * Allows additional actions to be performed inside the `invoice.upcoming` event processing.
		 *
		 * @since 3.7.0
		 *
		 * @param \Stripe\Event        $this->event Stripe Event object.
		 * @param \Stripe\Invoice      $invoice Stripe Invoice object.
		 * @param \Stripe\Subscription $subscription Stripe Subscription object.
		 */
		do_action(
			'simpay_webhook_invoice_upcoming',
			$this->event,
			$this->invoice,
			$this->subscription
		);

		// Send "Upcoming Invoice" email.
		//
		// Not attached to the action so the method can stay private
		// until a full email system needs to be added.
		$this->_send_upcoming_invoice_email();
	}

	/**
	 * Sends an email to the Subscription's Customer reminding them that an Invoice is
	 * about to be paid and provides a URL to update the payment method.
	 *
	 * Note: This method is private and called directly as it will likely be abstracted
	 * out in to a more robust email system in the future.
	 *
	 * @since 3.7.0
	 */
	private function _send_upcoming_invoice_email() {
		// Do nothing if Subscription was created before 3.7.0, or is missing a key.
		if ( ! isset( $this->subscription->metadata->simpay_subscription_key ) ) {
			return;
		}

		$account_name = ! empty( $this->invoice->account_name )
			? $this->invoice->account_name
			: $this->invoice->customer_email;

		$to = $this->invoice->customer_email;

		$subject = sprintf(
			/* translators: %s Stripe Account Name */
			esc_html__( '%s - Your subscription will renew soon', 'simple-pay' ),
			$account_name
		);

		// Find the form so the correct confirmation URL is used.
		$form_id = isset( $this->subscription->metadata->simpay_form_id )
			? $this->subscription->metadata->simpay_form_id
			: null;

		if ( $form_id ) {
			/** This filter is documented in includes/core/shortcodes.php */
			$form = apply_filters( 'simpay_form_view', '', $form_id );

			if ( empty( $form ) ) {
				$form = new Default_Form( $form_id );
			}

			$success_url = $form->payment_success_page;
		} else {
			$success_url = get_permalink( simpay_get_global_setting( 'success_page' ) );
		}

		$update_url = esc_url_raw(
			add_query_arg(
				array(
					'customer_id'      => $this->subscription->customer->id,
					'subscription_key' => $this->subscription->metadata->simpay_subscription_key,
				),
				$success_url
			)
		);

		$message = sprintf(
			/* translators: %1$s Stripe Account name. %2$s Renewal date. %3$s Update payment method URL. */
			esc_html__( "This is a friendly reminder that your %1\$s subscription will automatically renew on %2\$s\n\nYour payment method on file will be charged at that time. If your billing information has changed, you can update your payment details below:\n\n%3\$s", 'simple-pay' ),
			$account_name,
			date_i18n(
				get_option( 'date_format' ),
				$this->invoice->period_end
			),
			$update_url
		);

		$blogname  = wp_specialchars_decode( get_site_option( 'blogname' ), ENT_QUOTES );
		$blogemail = get_site_option( 'admin_email' );

		$header = 'From: ' . $blogname . ' <' . $blogemail . ">\r\n";

		wp_mail( $to, $subject, $message, $header );
	}
}
