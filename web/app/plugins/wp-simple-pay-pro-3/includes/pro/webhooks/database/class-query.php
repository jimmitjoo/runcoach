<?php
/**
 * Webhooks: Database Query
 *
 * @package SimplePay\Pro\Webhooks\Database
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks\Database;

use SimplePay\Core\Database\Engine\Query as Query_Base;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query class.
 *
 * @since 3.5.0
 */
final class Query extends Query_Base {

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.5.0
	 * @var   string
	 */
	protected $table_name = 'webhooks';

	/**
	 * Name of the database table alias.
	 *
	 * @since 3.5.0
	 * @var   string
	 */
	protected $table_alias = 'wh';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since 3.5.0
	 * @var   string
	 */
	protected $table_schema = '\\SimplePay\\Pro\\Webhooks\\Database\\Schema';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * Use underscores between words. I.E. "order_item"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @since 3.5.0
	 * @var   string
	 */
	protected $item_name = 'webhook';

	/**
	 * Plural version for a group of items.
	 *
	 * Use underscores between words. I.E. "order_item"
	 *
	 * This is used to automatically generate action hooks.
	 *
	 * @since 3.5.0
	 * @var   string
	 */
	protected $item_name_plural = 'webhooks';

	/**
	 * Name of class used to turn IDs into first-class objects.
	 *
	 * I.E. `\\Sugar_Calendar\\Database\\Row` or `\\Sugar_Calendar\\Database\\Rows\\Customer`
	 *
	 * This is used when looping through return values to guarantee their shape.
	 *
	 * @since 3.5.0
	 * @var   mixed
	 */
	protected $item_shape = '\\SimplePay\\Pro\\Webhooks\\Database\\Webhook';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since 3.5.0
	 * @access protected
	 * @var string
	 */
	protected $cache_group = 'webhooks';

}
