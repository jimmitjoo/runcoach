<?php
/**
 * Webhooks: Database Schema
 *
 * @package SimplePay\Pro\Webhooks\Database
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks\Database;

use SimplePay\Core\Database\Engine\Schema as Schema_Base;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema class.
 *
 * @since 3.5.0
 */
final class Schema extends Schema_Base {

	/**
	 * Array of database column objects.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),

		// event_id
		array(
			'name'     => 'event_id',
			'type'     => 'varchar',
			'length'   => '255',
			'default'  => '',
			'sortable' => true,
		),

		// event_type
		array(
			'name'     => 'event_type',
			'type'     => 'varchar',
			'length'   => '255',
			'default'  => '',
			'sortable' => true,
		),

		// livemode
		array(
			'name'     => 'livemode',
			'type'     => 'tinyint',
			'length'   => '1',
			'default'  => '',
			'sortable' => true,
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

	);
}
