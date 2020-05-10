<?php
/**
 * REST API: v1 Webhook Receiver Controller
 *
 * @package SimplePay\Pro\REST_API\v1
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\REST_API\v1;

use SimplePay\Core\REST_API\Controller;
use SimplePay\Pro\Webhooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhooks_Receiver_Controller class.
 *
 * @since 3.5.0
 */
class Webhook_Receiver_Controller extends Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wpsp/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'webhook-receiver';

	/**
	 * Register the routes for Webhooks.
	 *
	 * @since 3.5.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Always allow POST requests to this endpoint.
	 *
	 * Webhook verification happens during the full callback in order to
	 * provide Stripe with more relevant responses, vs. WP REST API permission information.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request Request data.
	 * @return true
	 */
	public function create_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * Handle an incoming webhook.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request $request Request data.
	 * @return \WP_REST_Response
	 */
	public function create_item( $request ) {
		try {
			$event = Webhooks\verify_webhook( @file_get_contents( 'php://input' ) );

			/**
			 * Allow code to run when a valid webhook is found.
			 *
			 * @since 3.5.0
			 *
			 * @param \Stripe\Event $event Stripe event.
			 */
			do_action( 'simpay_webhook_event', $event );

			return new \WP_REST_Response( esc_html__( 'Webhook received.', 'simple-pay' ), 200 );
		} catch ( Webhooks\Exception\Invalid_Event_Type $e ) {
			// We can't find this event type, tell Stripe everything is good.
			return new \WP_REST_Response( $e->getMessage(), 200 );

		} catch ( Webhooks\Exception\Invalid_Event_Handler $e ) {
			// We can't find anything to do with this event, tell Stripe everything is good.
			return new \WP_REST_Response( $e->getMessage(), 200 );

		} catch ( Webhooks\Exception\Duplicate_Attempt $e ) {
			// Processing for this webhook has already happened, tell Stripe everything is good.
			return new \WP_REST_Response( $e->getMessage(), 200 );

		} catch ( \Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), 400 );
		}
	}

	/**
	 * Get the Webhook's schema.
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'webhook',
			'type'    => 'object',
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
