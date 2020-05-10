<?php
/**
 * Payment confirmation
 *
 * @package SimplePay\Pro\Payments\Payment_Confirmation
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Pro\Payments\Payment_Confirmation;

use SimplePay\Core\Payments\Stripe_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Finds the Payment Confirmation Form ID from the Subscription metadata.
 *
 * @since 3.6.2
 *
 * @param int   $form_id Form ID.
 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
 * @return int
 */
function get_form_id( $form_id, $payment_confirmation_data ) {
	if ( ! isset( $payment_confirmation_data['subscriptions'] ) ) {
		return $form_id;
	}

	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $form_id;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	if ( ! $subscription->metadata->simpay_form_id ) {
		return $form_id;
	}

	return $subscription->metadata->simpay_form_id;
}
add_filter( 'simpay_payment_confirmation_form_id', __NAMESPACE__ . '\\get_form_id', 10, 2 );

/**
 * Adds Customer Subscriptions to available payment confirmatino data.
 *
 * @since 3.6.0
 *
 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
 * @return array $payment_confirmation_data
 */
function add_subscriptions_data( $payment_confirmation_data ) {
	$subscriptions = Stripe_API::request(
		'Subscription',
		'all',
		array(
			'customer' => $payment_confirmation_data['customer']->id,
			'limit'    => 1,
			'expand'   => array(
				'data.latest_invoice',
				'data.latest_invoice.payment_intent.charges',
			),
		)
	);

	$payment_confirmation_data['subscriptions'] = $subscriptions->data;

	return $payment_confirmation_data;
}
add_filter( 'simpay_payment_confirmation_data', __NAMESPACE__ . '\\add_subscriptions_data' );

/**
 * Change the base confirmation message depending on the form type.
 *
 * @since 3.6.0
 *
 * @param string $content Payment confirmation content.
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
function get_content( $content, $payment_confirmation_data ) {
	$display_options = get_option( 'simpay_settings_display' );

	// No custom content settings available.
	if ( ! $display_options ) {
		return $content;
	}

	// Not a subscription.
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $content;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	$type = $subscription->trial_end ? 'trial' : 'subscription';

	$default = simpay_get_editor_default( $type );
	$content = isset( $display_options['payment_confirmation_messages'][ $type . '_details' ] ) ?
		$display_options['payment_confirmation_messages'][ $type . '_details' ] :
		$default;

	return $content;
}
add_filter( 'simpay_payment_confirmation_content', __NAMESPACE__ . '\\get_content', 10, 2 );

/**
 * Appdends the "Update Payment Method" form to the confirmation content.
 *
 * @since 3.7.0
 *
 * @param string $content Payment confirmation shortcode content.
 * @param array  $payment_confirmation_data Array of data to send to the Payment Confirmation template tags.
 */
function update_payment_method_form( $content, $payment_confirmation_data ) {
	if ( ! isset( $_GET['subscription_key'] ) ) {
		return $content;
	}

	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $content;
	}

	$subscription     = current( $payment_confirmation_data['subscriptions'] );
	$subscription_key = esc_attr( $_GET['subscription_key'] );

	try {
		$settings = get_option( 'simpay_settings_keys' );

		wp_enqueue_script(
			'simpay-update-payment-method',
			SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-public-pro-update-payment-method.min.js',
			array(
				'jquery',
				'simpay-polyfill',
				'simpay-shared',
				'simpay-public',
				'simpay-stripe-js-v3',
			),
			SIMPLE_PAY_VERSION
		);

		wp_localize_script(
			'simpay-update-payment-method',
			'simpayUpdatePaymentMethod',
			array(
				'stripe' => array(
					'key' => $settings[ ( simpay_is_test_mode() ? 'test' : 'live' ) . '_keys' ]['publishable_key'],
				),
				'i18n'   => array(
					'submit'  => esc_html__( 'Update Payment Method', 'simple-pay' ),
					'loading' => esc_html__( 'Please Wait...', 'simple-pay' ),
				),
			)
		);

		// Match stored meta key to passed subscription key.
		if ( $subscription_key !== $subscription->metadata->simpay_subscription_key ) {
			throw new \Exception( esc_html__( 'Unable to match Customer records to allow payment method updates.', 'simple-pay' ) );
		}

		$customer_id = $payment_confirmation_data['customer']->id;

		// Retrieve the Upcoming Invoice.
		$upcoming_invoice = Stripe_API::request(
			'Invoice',
			'upcoming',
			array(
				'customer' => $customer_id,
			)
		);

		$payment_method_id = $payment_confirmation_data['customer']->default_source;

		// If a default method is not attached to the Customer (Stripe Checkout), look in the Subscription.
		if ( ! $payment_method_id ) {
			$payment_method_id = $subscription->default_payment_method;
		}

		// Find the most recent card.
		$payment_method = Stripe_API::request( 'PaymentMethod', 'retrieve', $payment_method_id );

		$amount_due = $upcoming_invoice->amount_due;
		$currency   = $upcoming_invoice->currency;

		$amount_due = html_entity_decode(
			simpay_format_currency( simpay_convert_amount_to_dollars( $amount_due ), $currency )
		);

		$classes = array(
			'simpay-update-payment-method',
			'simpay-checkout-form simpay-checkout-form--embedded',
		);

		if ( 'disabled' !== simpay_get_global_setting( 'default_plugin_styles' ) ) {
			$classes[] = 'simpay-styled';
		}

		ob_start();
		?>

<form
	action=""
	method="POST"
	id="simpay-form-update-payment-method"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
>

	<h3><?php esc_html_e( 'Update Payment Method', 'simple-pay' ); ?></h3>

	<p>
		<?php
		printf(
			__( 'The next invoice for %1$s will automatically charge the %2$s ending in %3$s on %4$s.', 'simple-pay' ),
			esc_html( $amount_due ),
			'<strong>' . esc_html( ucwords( $payment_method->card->brand ) ) . '</strong>',
			'<strong>' . esc_html( $payment_method->card->last4 ) . '</strong>',
			date_i18n(
				get_option( 'date_format' ),
				$subscription->current_period_end
			)
		);
		?>
			
	</p>

	<div class="simpay-form-control simpay-form-control--card simpay-card-container">
		<div class="simpay-card-label simpay-label-wrap">
			<label for="simpay-update-payment-method"><?php esc_html_e( 'Card Details', 'simple-pay' ); ?></label>
		</div>

		<div id="simpay-card-element-update-payment-method" class="simpay-card-wrap simpay-field-wrap"></div>
	</div>

	<div class="simpay-form-control simpay-checkout-btn-container">
		<button class="simpay-btn" type="submit"><span>
			<?php esc_html_e( 'Update Payment Method', 'simple-pay' ); ?>
		</span></button>
	</div>

	<div id="simpay-card-element-update-payment-method-errors" class="simpay-errors"></div>

		<?php wp_nonce_field( 'simpay_payment_form' ); ?>
	<input type="hidden" name="customer_id" value="<?php echo esc_html( $customer_id ); ?>" />
	<input type="hidden" name="subscription_id" value="<?php echo esc_html( $subscription->id ); ?>" />
	<input type="hidden" name="subscription_key" value="<?php echo esc_html( $subscription_key ); ?>" />

</form>

		<?php
		$content .= trim( ob_get_clean() );
	} catch ( \Exception $e ) {
		if ( current_user_can( 'manage_options' ) ) {
			$content .= $e->getMessage();
		}
	}

	return $content;
}
add_filter( 'simpay_after_payment_details', __NAMESPACE__ . '\\update_payment_method_form', 20, 2 );
