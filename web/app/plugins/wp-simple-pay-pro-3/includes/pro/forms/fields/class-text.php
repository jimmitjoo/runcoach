<?php
/**
 * Forms field: Text
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
 * Text class.
 *
 * @since 3.0.0
 */
class Text extends Custom_Field {

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
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] ) ? 'required=""' : '';
		$default     = self::get_default_value();
		$multiline   = isset( $settings['multiline'] ) ? true : false;
		$rows        = isset( $settings['rows'] ) && ! empty( $settings['rows'] ) ? intval( $settings['rows'] ) : 5;
		$name        = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$id    = simpay_dashify( $id );
		$label = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';

		if ( ! $multiline ) {
			$field = '<input type="text" name="' . $name . '" id="' . esc_attr( $id ) . '" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( $default ) . '" ' . $required . ' maxlength="500" /> ';
		} else {
			$field = '<textarea maxlength="500" placeholder="' . esc_attr( $placeholder ) . '" name="' . $name . '" id="' . esc_attr( $id ) . '" rows="' . esc_attr( $rows ) . '" ' . esc_attr( $required ) . '>' . esc_html( $default ) . '</textarea>';
		}

		$html .= '<div class="simpay-form-control simpay-text-container">';
		$html .= '<div class="simpay-text-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</div>';
		$html .= '<div class="simpay-text-wrap simpay-field-wrap">';
		$html .= $field;
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

}
