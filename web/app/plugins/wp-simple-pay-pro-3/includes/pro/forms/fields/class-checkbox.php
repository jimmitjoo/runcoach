<?php
/**
 * Forms field: Checkbox
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
 * Checkbox class.
 *
 * @since 3.0.0
 */
class Checkbox extends Custom_Field {

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

		$id        = isset( $settings['id'] ) ? $settings['id'] : '';
		$meta_name = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] ) ? $settings['metadata'] : $id;
		$label     = isset( $settings['label'] ) ? $settings['label'] : '';
		$required  = isset( $settings['required'] ) ? 'required=""' : '';
		$default   = self::get_default_value( 'default', false );
		$name      = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$id = simpay_dashify( $id );

		$label = '<label for="' . esc_attr( simpay_dashify( $id ) ) . '">' . $label . '</label>';
		$field = '<input type="checkbox" name="' . $name . '" id="' . esc_attr( $id ) . '" class="simpay-checkbox" ' . $required . ' ' . checked( false !== $default, true, false ) . ' />';

		$html .= '<div class="simpay-form-control simpay-checkbox-container">';
		$html .= '<div class="simpay-checkbox-wrap simpay-field-wrap">';
		$html .= $field . ' ' . $label;
		$html .= '</div>';
		$html .= '</div>';

		return $html;

	}

}
