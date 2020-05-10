<?php

namespace SimplePay\Pro\Admin\Pages;

use SimplePay\Core\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class License extends Admin_Page {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->id           = 'license';
		$this->option_group = 'settings';
		$this->label        = esc_html__( 'License', 'simple-pay' );
		$this->link_text    = esc_html__( 'Help docs for License Settings', 'simple-pay' );
		$this->link_slug    = ''; // TODO: Fill in slug, not in use currently (issue #301)
		$this->ga_content   = 'general-settings';

		$this->sections = $this->add_sections();
		$this->fields   = $this->add_fields();
	}

	/**
	 * Add sections.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_sections() {

		return apply_filters(
			'simpay_add_' . $this->option_group . '_' . $this->id . '_sections',
			array(
				'key' => array(
					'title' => '',
				),
			)
		);
	}

	/**
	 * Add fields.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_fields() {

		$fields       = array();
		$this->values = get_option( 'simpay_' . $this->option_group . '_' . $this->id );

		if ( ! empty( $this->sections ) && is_array( $this->sections ) ) {
			foreach ( $this->sections as $section => $a ) {

				$section = sanitize_key( $section );

				if ( 'key' == $section ) {

					$fields[ $section ] = array(
						'license_key' => array(
							'title' => esc_html__( 'License Key', 'simple-pay' ),
							'type'  => 'license',
							'name'  => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][license_key]',
							'id'    => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-license-key',
							'value' => get_option( 'simpay_license_key', '' ),
							'class' => array(
								'regular-text',
							),
						),
					);
				}
			}
		}

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_fields', $fields );
	}

}
