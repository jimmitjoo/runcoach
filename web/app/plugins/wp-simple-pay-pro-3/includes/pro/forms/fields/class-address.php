<?php
/**
 * Forms field: Address
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
 * Address class.
 *
 * @since 3.0.0
 */
class Address extends Custom_Field {

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

		$base_id  = isset( $settings['id'] ) ? $settings['id'] : '';
		$required = isset( $settings['required'] ) ? 'required=""' : '';

		// Billing Container Label
		$id_attr                       = simpay_dashify( $base_id ) . '-billing-street';
		$label_text                    = isset( $settings['billing-container-label'] ) ? $settings['billing-container-label'] : '';
		$billing_container_label_html  = '<legend class="simpay-address-billing-container-label simpay-label-wrap">';
		$billing_container_label_html .= esc_html( $label_text );
		$billing_container_label_html .= '</legend>';

		// Billing Street
		// $id_attr for street same as container label above.
		$label_text           = isset( $settings['label-street'] ) ? $settings['label-street'] : '';
		$placeholder_text     = isset( $settings['placeholder-street'] ) ? $settings['placeholder-street'] : '';
		$label_html           = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
		$field_html           = '<input type="text" name="simpay_billing_address_line1" id="' . esc_attr( $id_attr ) . '" class="simpay-address-street" placeholder="' . esc_attr( $placeholder_text ) . '" ' . $required . ' maxlength="500" /> ';
		$billing_street_html  = '<div class="simpay-address-street-container simpay-form-control">';
		$billing_street_html .= '<div class="simpay-address-street-label simpay-label-wrap">';
		$billing_street_html .= $label_html;
		$billing_street_html .= '</div>';
		$billing_street_html .= '<div class="simpay-address-street-wrap simpay-field-wrap">';
		$billing_street_html .= $field_html;
		$billing_street_html .= '</div>';
		$billing_street_html .= '</div>';

		// Billing City
		$id_attr            = simpay_dashify( $base_id ) . '-billing-city';
		$label_text         = isset( $settings['label-city'] ) ? $settings['label-city'] : '';
		$placeholder_text   = isset( $settings['placeholder-city'] ) ? $settings['placeholder-city'] : '';
		$label_html         = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
		$field_html         = '<input type="text" name="simpay_billing_address_city" id="' . esc_attr( $id_attr ) . '" class="simpay-address-city" placeholder="' . esc_attr( $placeholder_text ) . '" ' . $required . ' maxlength="500" /> ';
		$billing_city_html  = '<div class="simpay-address-city-container simpay-form-control">';
		$billing_city_html .= '<div class="simpay-address-city-label simpay-label-wrap">';
		$billing_city_html .= $label_html;
		$billing_city_html .= '</div>';
		$billing_city_html .= '<div class="simpay-address-city-wrap simpay-field-wrap">';
		$billing_city_html .= $field_html;
		$billing_city_html .= '</div>';
		$billing_city_html .= '</div>';

		// Billing State
		$id_attr             = simpay_dashify( $base_id ) . '-billing-state';
		$label_text          = isset( $settings['label-state'] ) ? $settings['label-state'] : '';
		$placeholder_text    = isset( $settings['placeholder-state'] ) ? $settings['placeholder-state'] : '';
		$label_html          = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
		$field_html          = '<input type="text" name="simpay_billing_address_state" id="' . esc_attr( $id_attr ) . '" class="simpay-address-state" placeholder="' . esc_attr( $placeholder_text ) . '" ' . $required . ' maxlength="500" /> ';
		$billing_state_html  = '<div class="simpay-address-state-container simpay-form-control">';
		$billing_state_html .= '<div class="simpay-address-state-label simpay-label-wrap">';
		$billing_state_html .= $label_html;
		$billing_state_html .= '</div>';
		$billing_state_html .= '<div class="simpay-address-state-wrap simpay-field-wrap">';
		$billing_state_html .= $field_html;
		$billing_state_html .= '</div>';
		$billing_state_html .= '</div>';

		// Billing Zip
		$id_attr           = simpay_dashify( $base_id ) . '-billing-zip';
		$label_text        = isset( $settings['label-zip'] ) ? $settings['label-zip'] : '';
		$placeholder_text  = isset( $settings['placeholder-zip'] ) ? $settings['placeholder-zip'] : '';
		$label_html        = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
		$field_html        = '<input type="text" name="simpay_billing_address_postal_code" id="' . esc_attr( $id_attr ) . '" class="simpay-address-zip" placeholder="' . esc_attr( $placeholder_text ) . '" ' . $required . ' maxlength="500" /> ';
		$billing_zip_html  = '<div class="simpay-address-zip-container simpay-form-control">';
		$billing_zip_html .= '<div class="simpay-address-zip-label simpay-label-wrap">';
		$billing_zip_html .= $label_html;
		$billing_zip_html .= '</div>';
		$billing_zip_html .= '<div class="simpay-address-zip-wrap simpay-field-wrap">';
		$billing_zip_html .= $field_html;
		$billing_zip_html .= '</div>';
		$billing_zip_html .= '</div>';

		// Billing Country
		$id_attr    = simpay_dashify( $base_id ) . '-billing-country';
		$label_text = isset( $settings['label-country'] ) ? $settings['label-country'] : '';
		$label_html = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
		$field_html = '<select name="simpay_billing_address_country" id="' . esc_attr( $id_attr ) . '" class="simpay-address-country" ' . $required . ' /> ';
		$countries  = simpay_get_country_list();
		// Get selected country from settings, defaulting to US.
		$selected_country = self::get_default_value( 'default-country', 'US' );
		foreach ( $countries as $country_code => $country ) {
			$field_html .= '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
		}
		$field_html .= '</select>';

		$billing_country_html  = '<div class="simpay-address-country-container simpay-form-control">';
		$billing_country_html .= '<div class="simpay-address-country-label simpay-label-wrap">';
		$billing_country_html .= $label_html;
		$billing_country_html .= '</div>';
		$billing_country_html .= '<div class="simpay-address-country-wrap simpay-field-wrap">';
		$billing_country_html .= $field_html;
		$billing_country_html .= '</div>';
		$billing_country_html .= '</div>';

		// Shipping Address
		// Using labels, placeholders, CSS classes, etc. from billing address.
		// Only the ID of each field differs from billing address fields.
		// Saving to metadata unlike billing address.
		// All are disabled since required, but hidden, on initial load.
		// Not setting to required as they're hidden by default and can't be focused on by HTML 5 validation.
		$shipping_address_html = '';

		if ( isset( $settings['collect-shipping'] ) && 'yes' === $settings['collect-shipping'] ) {

			// "Same billing & shipping info" checkbox (checked by default)
			$id_attr                   = simpay_dashify( $base_id ) . '-same-address-toggle';
			$label_html                = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html__( 'Same billing & shipping info', 'simple-pay' ) . '</label>';
			$field_html                = '<input type="checkbox" name="simpay_same_billing_shipping" id="' . esc_attr( $id_attr ) . '" class="simpay-same-address-toggle" checked="checked" />';
			$same_address_toggle_html  = '<div class="simpay-form-control simpay-same-address-toggle-container">';
			$same_address_toggle_html .= '<div class="simpay-same-address-toggle-wrap simpay-field-wrap">';
			$same_address_toggle_html .= $field_html . ' ' . $label_html;
			$same_address_toggle_html .= '</div>';
			$same_address_toggle_html .= '</div>';

			// Shipping Container Label
			$id_attr                        = simpay_dashify( $base_id ) . '-shipping-street';
			$label_text                     = isset( $settings['shipping-container-label'] ) ? $settings['shipping-container-label'] : '';
			$shipping_container_label_html  = '<legend class="simpay-address-shipping-container-label simpay-label-wrap">';
			$shipping_container_label_html .= esc_html( $label_text );
			$shipping_container_label_html .= '</legend>';

			// Shipping Street
			// $id_attr for street same as container label above.
			$label_text            = isset( $settings['label-street'] ) ? $settings['label-street'] : '';
			$placeholder_text      = isset( $settings['placeholder-street'] ) ? $settings['placeholder-street'] : '';
			$label_html            = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
			$field_html            = '<input type="text" name="simpay_shipping_address_line1" id="' . esc_attr( $id_attr ) . '" class="simpay-address-street" placeholder="' . esc_attr( $placeholder_text ) . '" disabled maxlength="500" /> ';
			$shipping_street_html  = '<div class="simpay-address-street-container simpay-form-control">';
			$shipping_street_html .= '<div class="simpay-address-street-label simpay-label-wrap">';
			$shipping_street_html .= $label_html;
			$shipping_street_html .= '</div>';
			$shipping_street_html .= '<div class="simpay-address-street-wrap simpay-field-wrap">';
			$shipping_street_html .= $field_html;
			$shipping_street_html .= '</div>';
			$shipping_street_html .= '</div>';

			// Shipping City
			$id_attr             = simpay_dashify( $base_id ) . '-shipping-city';
			$label_text          = isset( $settings['label-city'] ) ? $settings['label-city'] : '';
			$placeholder_text    = isset( $settings['placeholder-city'] ) ? $settings['placeholder-city'] : '';
			$label_html          = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
			$field_html          = '<input type="text" name="simpay_shipping_address_city" id="' . esc_attr( $id_attr ) . '" class="simpay-address-city" placeholder="' . esc_attr( $placeholder_text ) . '" disabled maxlength="500" /> ';
			$shipping_city_html  = '<div class="simpay-address-city-container simpay-form-control">';
			$shipping_city_html .= '<div class="simpay-address-city-label simpay-label-wrap">';
			$shipping_city_html .= $label_html;
			$shipping_city_html .= '</div>';
			$shipping_city_html .= '<div class="simpay-address-city-wrap simpay-field-wrap">';
			$shipping_city_html .= $field_html;
			$shipping_city_html .= '</div>';
			$shipping_city_html .= '</div>';

			// Shipping State
			$id_attr              = simpay_dashify( $base_id ) . '-shipping-state';
			$label_text           = isset( $settings['label-state'] ) ? $settings['label-state'] : '';
			$placeholder_text     = isset( $settings['placeholder-state'] ) ? $settings['placeholder-state'] : '';
			$label_html           = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
			$field_html           = '<input type="text" name="simpay_shipping_address_state" id="' . esc_attr( $id_attr ) . '" class="simpay-address-state" placeholder="' . esc_attr( $placeholder_text ) . '" disabled maxlength="500" /> ';
			$shipping_state_html  = '<div class="simpay-address-state-container simpay-form-control">';
			$shipping_state_html .= '<div class="simpay-address-state-label simpay-label-wrap">';
			$shipping_state_html .= $label_html;
			$shipping_state_html .= '</div>';
			$shipping_state_html .= '<div class="simpay-address-state-wrap simpay-field-wrap">';
			$shipping_state_html .= $field_html;
			$shipping_state_html .= '</div>';
			$shipping_state_html .= '</div>';

			// Shipping Zip
			$id_attr            = simpay_dashify( $base_id ) . '-shipping-zip';
			$label_text         = isset( $settings['label-zip'] ) ? $settings['label-zip'] : '';
			$placeholder_text   = isset( $settings['placeholder-zip'] ) ? $settings['placeholder-zip'] : '';
			$label_html         = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
			$field_html         = '<input type="text" name="simpay_shipping_address_postal_code" id="' . esc_attr( $id_attr ) . '" class="simpay-address-zip" placeholder="' . esc_attr( $placeholder_text ) . '" disabled maxlength="500" /> ';
			$shipping_zip_html  = '<div class="simpay-address-zip-container simpay-form-control">';
			$shipping_zip_html .= '<div class="simpay-address-zip-label simpay-label-wrap">';
			$shipping_zip_html .= $label_html;
			$shipping_zip_html .= '</div>';
			$shipping_zip_html .= '<div class="simpay-address-zip-wrap simpay-field-wrap">';
			$shipping_zip_html .= $field_html;
			$shipping_zip_html .= '</div>';
			$shipping_zip_html .= '</div>';

			// Shipping Country
			$id_attr    = simpay_dashify( $base_id ) . '-shipping-country';
			$label_text = isset( $settings['label-country'] ) ? $settings['label-country'] : '';
			$label_html = '<label for="' . esc_attr( $id_attr ) . '">' . esc_html( $label_text ) . '</label>';
			$field_html = '<select name="simpay_shipping_address_country"" id="' . esc_attr( $id_attr ) . '" class="simpay-address-country" disabled /> ';
			$countries  = simpay_get_country_list();
			// Get selected country from settings, defaulting to US.
			$selected_country = self::get_default_value( 'default-country', 'US' );
			foreach ( $countries as $country_code => $country ) {
				$field_html .= '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
			}
			$field_html .= '</select>';

			$shipping_country_html  = '<div class="simpay-address-country-container simpay-form-control">';
			$shipping_country_html .= '<div class="simpay-address-country-label simpay-label-wrap">';
			$shipping_country_html .= $label_html;
			$shipping_country_html .= '</div>';
			$shipping_country_html .= '<div class="simpay-address-country-wrap simpay-field-wrap">';
			$shipping_country_html .= $field_html;
			$shipping_country_html .= '</div>';
			$shipping_country_html .= '</div>';

			// Combined Shipping HTML
			$shipping_address_html  = $same_address_toggle_html;
			$shipping_address_html .= '<fieldset class="simpay-form-control simpay-address-container simpay-shipping-address-container" style="display: none;">';
			$shipping_address_html .= $shipping_container_label_html;
			$shipping_address_html .= $shipping_street_html;
			$shipping_address_html .= $shipping_city_html;
			$shipping_address_html .= $shipping_state_html;
			$shipping_address_html .= $shipping_zip_html;
			$shipping_address_html .= $shipping_country_html;
			$shipping_address_html .= '</fieldset>';
		}

		// Final HTML
		$html .= '<fieldset class="simpay-form-control simpay-address-container simpay-billing-address-container">';
		$html .= $billing_container_label_html;
		$html .= $billing_street_html;
		$html .= $billing_city_html;
		$html .= $billing_state_html;
		$html .= $billing_zip_html;
		$html .= $billing_country_html;
		$html .= '</fieldset>';

		$html .= $shipping_address_html;

		return $html;
	}
}
