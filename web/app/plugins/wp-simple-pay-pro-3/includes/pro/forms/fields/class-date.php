<?php
/**
 * Forms field: Date
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date class.
 *
 * @since 3.0.0
 */
class Date extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style(
			'simpay-jquery-ui-cupertino',
			SIMPLE_PAY_INC_URL . 'pro/assets/css/vendor/jquery-ui/jquery-ui-cupertino.min.css',
			array(),
			SIMPLE_PAY_VERSION
		);

		$html = '';

		$id          = isset( $settings['id'] ) ? $settings['id'] : '';
		$meta_name   = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] ) ? $settings['metadata'] : $id;
		$label       = isset( $settings['label'] ) ? $settings['label'] : '';
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] ) ? 'required=""' : '';
		$default     = self::get_default_value();
		$name        = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$id = simpay_dashify( $id );

		$label = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		$field = '<input type="text" class="simpay-date-input" name="' . $name . '" id="' . esc_attr( $id ) . '" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( $default ) . '"' . esc_attr( $required ) . '" />';

		$html .= '<div class="simpay-form-control simpay-date-container">';
		$html .= '<div class="simpay-date-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</div>';
		$html .= '<div class="simpay-date-wrap simpay-field-wrap">';
		$html .= $field;
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

}
