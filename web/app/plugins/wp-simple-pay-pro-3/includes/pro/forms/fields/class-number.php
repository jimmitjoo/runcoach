<?php
/**
 * Forms field: Number
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
 * Number class.
 *
 * @since 3.0.0
 */
class Number extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$html = '';

		$id          = isset( $settings['id'] ) ? $settings['id'] : '';
		$meta_name   = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] ) ? $settings['metadata'] : $id;
		$label       = isset( $settings['label'] ) ? $settings['label'] : '';
		$placeholder = isset( $settings['placeholder'] ) ? intval( $settings['placeholder'] ) : '';
		$required    = isset( $settings['required'] ) ? 'required=""' : '';
		$default     = self::get_default_value();
		$minimum     = isset( $settings['minimum'] ) ? intval( $settings['minimum'] ) : '';
		$maximum     = isset( $settings['maximum'] ) ? intval( $settings['maximum'] ) : '';
		$quantity    = isset( $settings['quantity'] ) ? $settings['quantity'] : '';
		$name        = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$id = simpay_dashify( $id );

		$classes = '';

		if ( ! empty( $quantity ) ) {
			$classes .= 'simpay-quantity-input';
		}

		$label = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

		// Field always uses these
		$field = '<input type="number" name="' . $name . '" id="' . esc_attr( $id ) . '" class="' . $classes . '" ';

		// Add placeholder only if it is set in the settings
		if ( ! empty( $placeholder ) ) {
			$field .= 'placeholder="' . esc_attr( $placeholder ) . '" ';
		}

		// Add min attribute if set in the settings
		if ( ! empty( $minimum ) ) {
			$field .= 'min="' . esc_attr( $minimum ) . '" ';
		}

		// Add max attribute if set in the settings
		if ( ! empty( $maximum ) ) {
			$field .= 'max="' . esc_attr( $maximum ) . '" ';
		}

		// Add value attribute if default is set in the settings
		if ( ! empty( $default ) ) {
			$field .= 'value="' . esc_attr( $default ) . '" ';
		}

		$field .= $required;

		// Close field
		$field .= ' />';

		if ( ! empty( $quantity ) ) {
			$field .= '<input type="hidden" name="simpay_quantity" class="simpay-quantity" value="" />';
		}

		$html .= '<div class="simpay-form-control simpay-number-container">';
		$html .= '<div class="simpay-number-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</div>';
		$html .= '<div class="simpay-number-wrap simpay-field-wrap">';
		$html .= $field;
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

}
