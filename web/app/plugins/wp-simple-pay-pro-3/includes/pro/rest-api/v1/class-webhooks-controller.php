<?php
/**
 * REST API: v1 Base Controller
 *
 * @package SimplePay\Pro\REST_API\v1
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\REST_API\v1;

use SimplePay\Core\REST_API\Controller;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Webhooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhooks_Controller class.
 *
 * @since 3.5.0
 */
class Webhooks_Controller extends Controller {

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
	protected $rest_base = 'webhooks';

	/**
	 * List of Webhooks.
	 *
	 * @var string
	 */
	protected $webhooks = false;

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
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE ),
				),
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
	 * Handle an incoming webhook.
	 *
	 * @since 3.5.0
	 *
	 * @param WP_REST_Request Request data.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * List webhooks and connection data.
	 *
	 * @since 3.5.0
	 *
	 * @param WP_REST_Request Request data.
	 * @return WP_Error|WP_REST_Reponse
	 */
	public function get_items( $request ) {
		$response = array(
			'webhooks'          => $this->get_webhooks(),
			'can_handle_events' => $this->can_handle_events(),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Verify permission for creating a webhook.
	 *
	 * @since 3.5.0
	 *
	 * @param WP_REST_Request Request data.
	 * @return bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Create a webhook for all registered events.
	 *
	 * @since 3.5.0
	 *
	 * @param WP_REST_Request Request data.
	 * @return WP_Error|WP_REST_Reponse
	 */
	public function create_item( $request ) {
		$webhook = Stripe_API::request(
			'WebhookEndpoint',
			'create',
			array(
				'url'            => simpay_get_webhook_url(),
				'enabled_events' => array( '*' ),
				'connect'        => true,
			)
		);

		if ( ! $webhook ) {
			return rest_ensure_response(
				array(
					/* Transltators: %1$s Anchor opening tag, do not translate. %2$s Closing anchor tag, do not translate. */
					'message' => sprintf(
						__( 'Unable to create webhook. Please configure manually in your %1$sStripe dashboard%2$s.', 'simple-pay' ),
						'<a href="https://dashboard.stripe.com/account/webhooks">',
						'</a>'
					),
				)
			);
		}

		// Store the created ID for easier updating later.
		$settings = get_option( 'simpay_settings_keys' );

		if ( simpay_is_test_mode() ) {
			$settings['test_keys']['endpoint_secret'] = $webhook->secret;
		} else {
			$settings['live_keys']['endpoint_secret'] = $webhook->secret;
		}

		return rest_ensure_response(
			array(
				'secret'  => $webhook->secret,
				'message' => __( 'Webhook configured!', 'simple-pay' ),
			)
		);
	}

	/**
	 * List Webhooks created in Stripe for the current payment mode.
	 *
	 * @since 3.5.0
	 *
	 * @return array
	 */
	protected function get_webhooks() {
		if ( $this->webhooks ) {
			return $this->webhooks;
		}

		$testmode = simpay_is_test_mode();

		$webhooks = Stripe_API::request(
			'WebhookEndpoint',
			'all',
			array(
				'limit' => 100,
			)
		);

		// API request failed, but API doesn't handle this well, so normalize a response.
		if ( ! $webhooks ) {
			$webhooks       = new \stdClass();
			$webhooks->data = array();
		}

		$data = $webhooks->data;

		array_reduce(
			$data,
			function( $webhook ) use ( $testmode ) {
				return $testmode === $webhook['livemode'];
			}
		);

		$this->webhooks = $data;

		return $this->webhooks;
	}

	/**
	 * Determine if the connected account can handle the necessary
	 * webhook event types.
	 *
	 * @since 3.5.0
	 *
	 * @return bool
	 */
	protected function can_handle_events() {
		$webhooks = $this->get_webhooks();
		$events   = array_keys( Webhooks\get_event_whitelist() );
		$url      = simpay_get_webhook_url();
		$can      = false;

		foreach ( $webhooks as $webhook ) {
			if ( 'enabled' !== $webhook->status ) {
				continue;
			}

			if ( untrailingslashit( $webhook->url ) !== untrailingslashit( $url ) ) {
				continue;
			}

			// URL matches and all events are handled, all good.
			if ( '*' === current( $webhook->enabled_events ) ) {
				$can = true;
				break;
			}

			// URL matches and registered events are in the enabled event list.
			if ( ! array_diff( $events, $webhook->enabled_events ) ) {
				$can = true;
				break;
			}
		}

		return $can;
	}
}
