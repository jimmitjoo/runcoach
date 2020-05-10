<?php
/**
 * Usage tracking.
 *
 * @since 3.6.0
 */

namespace SimplePay\Pro\Admin\Usage_Tracking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Never trigger optin notice when running Pro.
remove_action( 'simpay_day_after_install_scheduled_events', 'SimplePay\Core\Admin\Usage_Tracking\needs_optin_notice' );

/**
 * Enables usage tracking by default for new Pro installs.
 *
 * @since 3.6.0
 *
 * @param array $fields Setting fields.
 * @return array
 */
function update_setting_default( $fields ) {
	$fields['general_misc']['usage_tracking_opt_in']['default'] = 'yes';

	return $fields;
}
add_filter( 'simpay_add_settings_general_fields', __NAMESPACE__ . '\\update_setting_default' );

/**
 * Enables usage tracking for existing Pro installs if no setting has been saved.
 *
 * @since 3.6.0
 *
 * @param array $setting Existing setting value.
 * @return array Shimmed setting value.
 */
function shim_setting_default( $setting ) {
	if ( ! isset( $setting['general_misc']['usage_tracking_opt_in'] ) ) {
		$setting['general_misc']['usage_tracking_opt_in'] = 'yes';
	}

	return $setting;
}
add_filter( 'option_simpay_settings_general', __NAMESPACE__ . '\\shim_setting_default' );

/**
 * Saves an value for the usage tracking setting (unchecked checkboxes are not normally saved)
 * so we can determine if the value still needs to be shimmed or not.
 *
 * @see SimplePay\Pro\Admin\Usage_Tracking\shim_setting_default()
 *
 * @since 3.6.0
 *
 * @param array $setting Existing setting value.
 * @return array Shimmed setting value.
 */
function shim_setting_disabled( $setting ) {
	if ( ! isset( $setting['general_misc']['usage_tracking_opt_in'] ) ) {
		$setting['general_misc']['usage_tracking_opt_in'] = 'no';
	}

	return $setting;
}
add_filter( 'pre_update_option_simpay_settings_general', __NAMESPACE__ . '\\shim_setting_disabled' );
