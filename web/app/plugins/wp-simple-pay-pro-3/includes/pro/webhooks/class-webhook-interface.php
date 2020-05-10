<?php
/**
 * Webhooks: Interface
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Interface class.
 *
 * @since 3.5.0
 */
interface Webhook_Interface {

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.5.0
	 */
	public function handle();

}
