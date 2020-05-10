<?php
/**
 * Forms field: Card
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
 * Card class.
 *
 * @since 3.0.0
 */
class Card extends Custom_Field {

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
		$postal_code = isset( $settings['postal_code'] ) ? 'no' : 'yes';

		$id    = simpay_dashify( $id );
		$label = '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		$field = '<div id="simpay-card-element-' . esc_attr( $id ) . '" class="simpay-card-wrap simpay-field-wrap" data-show-postal-code="' . esc_attr( $postal_code ) . '"></div>';

		$html .= '<div class="simpay-form-control simpay-form-control--card simpay-card-container">';
		$html .= '<div class="simpay-card-label simpay-label-wrap">';
		$html .= $label;
		$html .= '</div>';
		$html .= $field;

		$html .= '</div>';

		return $html;
	}

}
