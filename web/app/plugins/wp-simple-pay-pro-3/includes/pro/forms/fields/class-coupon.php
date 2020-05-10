<?php
/**
 * Forms field: Coupon
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
 * Coupon class.
 *
 * @since 3.0.0
 */
class Coupon extends Custom_Field {

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
		$label       = isset( $settings['label'] ) && ! empty( $settings['label'] ) ? $settings['label'] : '';
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$default     = self::get_default_value();

		$style = isset( $settings['style'] ) ? $settings['style'] : simpay_get_global_setting( 'apply_button_style' );

		// Get form ID from field ID.
		$form_id = null;
		if ( $id ) {
			$form_id_list = explode( '_', $id );
			$form_id      = $form_id_list[1];
		}

		$form_display_type = get_post_meta( $form_id, '_form_display_type', true );
		$loading_image     = esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/loading.gif' );

		$field = '<input type="text" name="simpay_field[coupon]" class="simpay-coupon-field" value="' . esc_attr( $default ) . '" placeholder="' . esc_attr( $placeholder ) . '" />';

		$button = '<button type="button" class="simpay-apply-coupon simpay-btn ' . ( 'stripe' === $style ? 'stripe-button-el' : '' ) . '"><span>' . esc_html__( 'Apply', 'simple-pay' ) . '</span></button>';

		$html .= '<div class="simpay-form-control simpay-coupon-container">';

		// Label
		$html .= '<div class="simpay-coupon-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</div>';

		// Coupon field and apply button
		$html .= '<div class="simpay-coupon-wrap simpay-field-wrap">';
		$html .= $field . $button;
		$html .= '</div>';

		// Coupon message to show AJAX response message when a coupon is entered
		$html .= '<span class="simpay-coupon-loading" style="display: none;"><img src="' . esc_attr( $loading_image ) . '" /></span>'; // Loading image
		$html .= '<span class="simpay-coupon-message" style="display: none;"></span>'; // Message container
		$html .= '<span class="simpay-remove-coupon" style="display: none;"> (<a href="#">' . esc_html__( 'remove', 'simple-pay' ) . '</a>)</span>';
		$html .= '<input type="hidden" name="simpay_coupon" class="simpay-coupon" />';

		$html .= wp_nonce_field( 'simpay_coupon_nonce', 'simpay_coupon_nonce', true, false );

		$html .= '</div>';

		return $html;
	}

}
