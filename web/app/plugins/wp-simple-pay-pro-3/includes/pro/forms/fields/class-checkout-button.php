<?php
/**
 * Forms field: Checkout Button
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
 * Checkout_Button class.
 *
 * @since 3.0.0
 */
class Checkout_Button extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {

		$id          = isset( $settings['id'] ) ? $settings['id'] : '';
		$button_text = self::print_button_text( $settings );
		$style       = isset( $settings['style'] ) ? $settings['style'] : 'none';

		$button_classes = array(
			'simpay-btn',
			'simpay-checkout-btn',
		);

		if ( 'stripe' === $style ) {
			$button_classes[] = 'stripe-button-el';
		}

		$id = simpay_dashify( $id );

		$html  = '<div class="simpay-form-control simpay-checkout-btn-container">';
		$html .= '<button id="' . esc_attr( $id ) . '" class="' . esc_attr( implode( ' ', $button_classes ) ) . '" type="submit"><span>' . $button_text . '</span></button>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * HTML for the button text including total amount.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_button_text( $settings ) {

		// TODO Handle trials -- "Start your free trial" text.

		$raw_amount       = simpay_get_form_setting( 'total_amount' );
		$formatted_amount = simpay_format_currency( $raw_amount, simpay_get_setting( 'currency' ) );

		$text = isset( $settings['text'] ) && ! empty( $settings['text'] ) ? $settings['text'] : esc_html__( 'Pay {{amount}}', 'simple-pay' );
		$text = str_replace( '{{amount}}', '<em class="simpay-total-amount-value">' . $formatted_amount . '</em>', $text );

		return $text;
	}

}
