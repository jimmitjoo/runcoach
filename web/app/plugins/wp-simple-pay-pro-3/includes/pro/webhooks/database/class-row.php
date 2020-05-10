<?php
/**
 * Webhooks: Database Row
 *
 * @package SimplePay\Pro\Webhooks\Database
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks\Database;

use SimplePay\Core\Database\Engine\Row as Row_Base;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Row class.
 *
 * @since 3.5.0
 */
final class Row extends Row_Base {

	/**
	 * Webhook record ID.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var int
	 */
	public $id;

	/**
	 * Webhook event ID.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var string
	 */
	public $event_id;

	/**
	 * Webhook event type.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var string
	 */
	public $event_type;

	/**
	 * Webhook processing mode.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var bool
	 */
	public $livemode;

	/**
	 * Webhook creation date.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var \DateTime
	 */
	public $date_created;

}
