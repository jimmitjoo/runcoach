<?php
/**
 * Forms field: Recurring Amount Toggle
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
 * Recurring_Amount_Toggle class.
 *
 * @since 3.0.0
 */
class Recurring_Amount_Toggle extends Custom_Field {

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

		$id    = isset( $settings['id'] ) ? $settings['id'] : '';
		$label = isset( $settings['label'] ) && ! empty( $settings['label'] ) ? $settings['label'] : esc_html__( 'Make this a recurring amount', 'simple-pay' );
		$name  = 'recurring_amount_toggle';

		$id = simpay_dashify( $id );

		$label = '<label for="' . esc_attr( simpay_dashify( $id ) ) . '">' . esc_html( $label ) . '</label>';
		$field = '<input type="checkbox" name="' . $name . '" id="' . esc_attr( $id ) . '" />';

		$html .= '<div class="simpay-form-control simpay-recurring-amount-toggle-container">';
		$html .= '<div class="simpay-checkbox-wrap simpay-field-wrap">';
		$html .= $field . ' ' . $label;
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

}
