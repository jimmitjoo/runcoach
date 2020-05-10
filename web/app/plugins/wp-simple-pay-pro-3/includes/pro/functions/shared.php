<?php
/**
 * Functions
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the max length for metadata fields
 */
function simpay_metadata_title_length() {
	return 40;
}

/**
 * Get the max length for the metadata description
 *
 * @return int
 */
function simpay_metadata_description_length() {
	return 500;
}

/**
 * Handle metadata truncation using calls to other DRY functions
 *
 * @param $type
 * @param $value
 *
 * @return bool|string
 */
function simpay_truncate_metadata( $type, $value ) {

	switch ( $type ) {
		case 'title':
			return substr( $value, 0, simpay_metadata_title_length() );
		case 'description':
			return substr( $value, 0, simpay_metadata_description_length() );
		default:
			return $value;
	}
}

/**
 * Calculate the tax of an amount when passed in the percentage value. Defaults to the form amount and tax_percent.
 *
 * @todo This should have required arguments and not rely on the $simpay_form global.
 *
 * @param string $amount
 * @param string $tax_percent
 *
 * @return float
 */
function simpay_calculate_tax_amount( $amount = '', $tax_percent = '' ) {

	global $simpay_form;

	// If the global does not exist and one of the parameters wasn't passed in then we leave now
	if ( ! isset( $simpay_form ) && ( empty( $amount ) || empty( $tax_percent ) ) ) {
		return 0;
	}

	if ( empty( $amount ) ) {
		$amount = simpay_unformat_currency( $simpay_form->amount );
	}

	if ( empty( $tax_percent ) ) {
		$tax_percent = floatval( $simpay_form->tax_percent );
	}

	$retval = round( $amount * ( $tax_percent / 100 ), simpay_get_decimal_places() );

	return $retval;
}

/**
 * Get the separator to use for fields that list multiple values
 * Affected Custom Fields: Dropdown values/amounts/quantities, radio values/amounts/quantities
 */
function simpay_list_separator() {
	return apply_filters( 'simpay_list_separator', ',' );
}

/**
 * Get the stored date format for the datepicker
 *
 * @return string
 */
function simpay_get_date_format() {

	$date_format = simpay_get_setting( 'date_format' );
	$date_format = ! empty( $date_format ) ? $date_format : 'mm/dd/yy';

	return $date_format;
}

/**
 * Determine if the base country supports Payment Request Button
 *
 * @since 3.5.0
 *
 * @return bool
 */
function simpay_can_use_payment_request_button() {
	$country = strtoupper( simpay_get_global_setting( 'country' ) );

	if ( ! $country ) {
		$country = 'US';
	}

	$countries = array(
		'AT',
		'AU',
		'BE',
		'BR',
		'CA',
		'CH',
		'DE',
		'DK',
		'EE',
		'ES',
		'FI',
		'FR',
		'GB',
		'GR',
		'HK',
		'IE',
		'IN',
		'IT',
		'JP',
		'LT',
		'LU',
		'LV',
		'MX',
		'MY',
		'NL',
		'NO',
		'NZ',
		'PH',
		'PL',
		'PT',
		'RO',
		'SE',
		'SG',
		'SK',
		'US',
	);

	$can_use = in_array( $country, $countries, true );

	/**
	 * Filter Payment Request Button availibility.
	 *
	 * @since 3.5.1
	 *
	 * @param bool $can_use   Can the button be used?
	 * @param string $country Current country.
	 */
	$can_use = apply_filters( 'simpay_can_use_payment_request_button', $can_use, $country );

	return $can_use;
}
