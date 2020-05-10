<?php
/**
 * Forms field: Email
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
 * Email class.
 *
 * @since 3.0.0
 */
class Email extends Custom_Field {

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
		$label       = isset( $settings['label'] ) ? $settings['label'] : '';
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$default     = self::get_default_value();

		$id    = simpay_dashify( $id );
		$label = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		$field = '<input type="email" name="simpay_email" id="' . esc_attr( $id ) . '" class="simpay-email" value="' . esc_attr( $default ) . '" placeholder="' . esc_attr( $placeholder ) . '" required="" /> ';

		$html .= '<div class="simpay-form-control simpay-email-container">';
		$html .= '<div class="simpay-email-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</div>';
		$html .= '<div class="simpay-email-wrap simpay-field-wrap">';
		$html .= $field;
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

}
