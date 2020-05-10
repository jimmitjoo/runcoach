<?php
/**
 * Assets
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class.
 *
 * @since 3.0.0
 */
class Assets {

	public function __construct() {

		add_filter( 'simpay_before_register_public_scripts', array( $this, 'add_public_scripts' ) );

		add_filter( 'simpay_before_register_public_styles', array( $this, 'add_public_styles' ), 10, 2 );
	}

	/**
	 * Register public assets.
	 *
	 * @since unknown
	 *
	 * @param array $scripts Scripts to register.
	 * @return array
	 */
	public function add_public_scripts( $scripts ) {
		$scripts['simpay-public-pro'] = array(
			'src'    => SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-public-pro.min.js',
			'deps'   => array(
				'jquery',
				'simpay-polyfill',
				'simpay-accounting',
				'simpay-shared',
				'simpay-public',
			),
			'ver'    => SIMPLE_PAY_VERSION,
			'footer' => true,
		);

		return $scripts;
	}

	public function add_public_styles( $styles, $min ) {
		$styles['simpay-jquery-ui-cupertino'] = array(
			'src'   => SIMPLE_PAY_INC_URL . 'pro/assets/css/vendor/jquery-ui/jquery-ui-cupertino.min.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		$styles['simpay-public-pro'] = array(
			'src'   => SIMPLE_PAY_INC_URL . 'pro/assets/css/simpay-public-pro.min.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		return $styles;
	}
}
