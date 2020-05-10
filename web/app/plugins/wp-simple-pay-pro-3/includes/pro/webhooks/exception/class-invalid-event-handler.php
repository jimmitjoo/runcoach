<?php
/**
 * Webhook Exception: Invalid Event Handler
 *
 * @package SimplePay\Pro\Webhooks\Exception
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks\Exception;

use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Invalid_Event_Handler class.
 *
 * @since 3.5.0
 */
class Invalid_Event_Handler extends Exception {

}
