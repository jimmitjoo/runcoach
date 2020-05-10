<?php
/**
 * Webhooks
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Webhooks\Database;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attempt to find a Webhook based on an incoming Event ID.
 *
 * @since 3.5.0
 *
 * @param string $event_id Event ID.
 * @return bool False if no webhook is found.
 */
function get_recorded_event( $event_id ) {
	$db = new Database\Query();

	$webhook = $db->query(
		array(
			'event_id' => $event_id,
			'limit'    => 1,
		)
	);

	return ! empty( $webhook );
}

/**
 * Record a webhook's event in the database for future tracking.
 *
 * @since 3.5.0
 *
 * @param string $event_id Event ID.
 * @return bool False if no webhook is recorded.
 */
function record_event( $event ) {
	if ( empty( $event->id ) ) {
		return false;
	}

	$query = new Database\Query();

	return $query->add_item(
		array(
			'event_id'     => $event->id,
			'event_type'   => $event->type,
			'livemode'     => $event->livemode,
			'date_created' => $event->created,
		)
	);
}

/**
 * Handle a webhook.
 *
 * @since 3.5.0
 *
 * @see SimplePay\Pro\Webhooks\get_event_whitelist()
 *
 * @param object $event Stripe event.
 * @throws SimplePay\Pro\Webhooks\Exception\Invalid_Event_Type If the event type is not registered.
 * @throws SimplePay\Pro\Webhooks\Exception\Invalid_Event_Handler If the event type has no callable handler class.
 * @throws SimplePay\Pro\Webhooks\Exception\Duplicate_Attempt If the webhook has already been processed.
 */
function process_event( $event ) {
	$webhooks = get_event_whitelist();

	// Event isn't whitelisted.
	if ( ! isset( $webhooks[ $event->type ] ) ) {
		throw new Exception\Invalid_Event_Type( esc_html__( 'Event type not registered. No processing was done.', 'simple-pay' ) );
	}

	// Event can't be handled.
	if ( ! class_exists( $webhooks[ $event->type ] ) ) {
		throw new Exception\Invalid_Event_Handler( esc_html__( 'Event handler not found. No processing was done.', 'simple-pay' ) );
	}

	$record = get_recorded_event( $event->id );

	// Webhook has already been recorded.
	if ( false !== $record ) {
		throw new Exception\Duplicate_Attempt( esc_html__( 'Webhook has been previously received. No further processing was done.', 'simple-pay' ) );
	}

	$handler = new $webhooks[ $event->type ]( $event );
	$handler->handle();

	// Record a successful event.
	record_event( $event );
}
// Run a little late so custom code can run before processing by default.
add_action( 'simpay_webhook_event', __NAMESPACE__ . '\\process_event', 20 );

/**
 * Legacy Webhook URL handling.
 *
 * https://stripe.com/docs/recipes/installment-plan
 * https://stripe.com/docs/webhooks
 *
 * @since unkown
 */
function process_legacy_listener() {
	if ( ! ( isset( $_GET['simple-pay-listener'] ) && $_GET['simple-pay-listener'] == 'stripe' ) ) {
		return;
	}

	try {
		$event = verify_webhook( @file_get_contents( 'php://input' ) );

		/* This action is documented in includes/pro/rest-api/v1/class-webhooks-controller.php. */
		do_action( 'simpay_webhook_event', $event );

		status_header( 200 );
	} catch ( Exception\Invalid_Event_Type $e ) {
		// We can't find this event type, tell Stripe everything is good.
		status_header( 200 );

	} catch ( Exception\Invalid_Event_Handler $e ) {
		// We can't find anything to do with this event, tell Stripe everything is good.
		status_header( 200 );

	} catch ( Exception\Duplicate_Attempt $e ) {
		// Processing for this webhook has already happened, tell Stripe everything is good.
		status_header( 200 );

	} catch ( \Exception $e ) {
		// Something went wrong running the event type callback, tell Stripe to try again.
		status_header( 400 );
	}

	exit();
}
// Run after the REST API handler.
add_action( 'init', __NAMESPACE__ . '\\process_legacy_listener', 30 );

/**
 * Get a list of webhook events we want to handle.
 *
 * @since 3.5.0
 *
 * @return array
 */
function get_event_whitelist() {
	// Event Type => Handler Class
	$webhooks = array(
		'invoice.payment_succeeded'  => '\\SimplePay\\Pro\\Webhooks\\Webhook_Invoice_Payment_Succeeded',
		'invoice.upcoming'           => '\\SimplePay\\Pro\\Webhooks\\Webhook_Invoice_Upcoming',
		'payment_intent.succeeded'   => '\\SimplePay\\Pro\\Webhooks\\Webhook_Payment_Intent_Succeeded',
		'checkout.session.completed' => '\\SimplePay\\Pro\\Webhooks\\Webhook_Checkout_Session_Completed',
	);

	/**
	 * Filter the webhooks to handle.
	 *
	 * @since 3.5.0
	 *
	 * @param array $webhooks Webhooks to handle.
	 */
	return apply_filters( 'simpay_webhooks_get_event_whitelist', $webhooks );
}

/**
 * Retrieve the endpoint secret for the current mode.
 *
 * @since 3.5.0
 *
 * @return string
 */
function get_endpoint_secret() {
	$settings = get_option( 'simpay_settings_keys' );

	if ( simpay_is_test_mode() ) {
		$endpoint_secret = isset( $settings['test_keys']['endpoint_secret'] ) ? $settings['test_keys']['endpoint_secret'] : '';
	} else {
		$endpoint_secret = isset( $settings['live_keys']['endpoint_secret'] ) ? $settings['live_keys']['endpoint_secret'] : '';
	}

	return $endpoint_secret;
}

/**
 * Determine if a webhook can be verified.
 *
 * @since 3.5.0
 *
 * @return bool
 */
function can_verify_webhook_endpoint() {
	return '' !== get_endpoint_secret();
}

/**
 * Verify a Webhook.
 *
 * If a Webhook Endpoint secret exists verify the signature.
 * If no secret exists retrieve the data again from Stripe and use that object.
 *
 * @link https://stripe.com/docs/webhooks/signatures
 *
 * @since 3.5.0
 *
 * @param object $payload Event payload.
 * @throws \Stripe\Exception\ApiErrorException If the something goes wrong with Stripe verifying the Webhook with a secret.
 * @throws \Exception If the webhook cannot be verified by retrieving it with Stripe.
 * @return \Stripe\Event $event Stripe Event.
 */
function verify_webhook( $payload ) {
	$event = false;

	// We do not have the necessary information for endpoint verification.
	// Instead try getting the event from the Stripe API.
	if ( ! can_verify_webhook_endpoint() ) {
		$payload = json_decode( $payload );

		if ( ! $payload ) {
			return $event;
		}

		$event = Stripe_API::request( 'Event', 'retrieve', $payload->id );
	} else {
		$endpoint_secret = get_endpoint_secret();
		$sig_header      = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : false;

		if ( ! $sig_header ) {
			return $event;
		}

		// Stripe API wrapper can't do multiple arguments.
		Stripe_API::set_app_info();
		Stripe_API::set_api_key();

		$event = \Stripe\Webhook::constructEvent(
			$payload,
			$sig_header,
			$endpoint_secret
		);
	}

	return $event;
}
