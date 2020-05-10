<?php
/**
 * Admin: Assets
 *
 * @package SimplePay
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public function __construct() {

		add_filter( 'simpay_before_register_admin_scripts', array( $this, 'add_admin_scripts' ) );

		add_filter( 'simpay_before_register_admin_styles', array( $this, 'add_admin_styles' ), 10, 2 );
	}

	/**
	 * Register public assets.
	 *
	 * @since 3.0.0
	 *
	 * @param array $scripts Scripts to register.
	 * @return arrya
	 */
	public function add_admin_scripts( $scripts ) {

		$scripts['simpay-admin-pro'] = array(
			'src'    => SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-admin-pro.min.js',
			'deps'   => array( 'jquery', 'jquery-ui-datepicker', 'simpay-admin', 'wp-api', 'wp-util', 'underscore' ),
			'ver'    => SIMPLE_PAY_VERSION,
			'footer' => false,
		);

		$scripts['simpay-admin-subs'] = array(
			'src'    => SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-admin-subcription-settings.min.js',
			'deps'   => array( 'simpay-admin' ),
			'ver'    => SIMPLE_PAY_VERSION,
			'footer' => false,
		);

		return $scripts;
	}

	public function add_admin_styles( $styles, $min ) {

		$styles['simpay-jquery-ui-cupertino'] = array(
			'src'   => SIMPLE_PAY_INC_URL . 'pro/assets/css/vendor/jquery-ui/jquery-ui-cupertino.min.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		$styles['simpay-admin-pro'] = array(
			'src'   => SIMPLE_PAY_INC_URL . 'pro/assets/css/simpay-admin-pro.min.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		return $styles;
	}
}
