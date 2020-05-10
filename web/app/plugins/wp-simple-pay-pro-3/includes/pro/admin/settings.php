<?php
/**
 * Admin: Settings
 *
 * @package SimplePay\Pro\Admin\Settings
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Admin\Settings;

use SimplePay\Pro\reCAPTCHA;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pro users can always manage keys.
add_filter( 'simpay_can_site_manage_stripe_keys', '__return_true' );

/**
 * When the "General" settings tab is updated, and the "Beta Opt-in" setting
 * has been checked, flush the plugin update cache.
 *
 * This will allow for instance notices of released updates if people toggle
 * the setting on after receiving an update email.
 *
 * @since 3.5.0
 *
 * @param mixed $new_value New setting value.
 * @param mixed $old_value Old setting value.
 * @return mixed
 */
function flush_plugin_update_cache( $new_value, $old_value ) {
	if ( ! isset( $new_value['general_misc']['beta_opt_in'] ) || isset( $old_value['general_misc']['beta_opt_in'] ) ) {
		return $new_value;
	}

	set_site_transient( 'update_plugins', null );

	return $new_value;
}
add_filter( 'pre_update_option_simpay_settings_general', __NAMESPACE__ . '\\flush_plugin_update_cache', 10, 2 );

/**
 * Add additional information about what may need to be updated when toggling
 * between Test/Live modes in Pro.
 *
 * @since 3.5.0
 *
 * @param string $toggle_notice Toggle notice inner HTML.
 * @return string
 */
function payment_mode_toggle_notice( $toggle_notice ) {
	$add = sprintf(
		/* translators: %1$s Stripe account mode. %2$s Link to Stripe dashboard. */
		__( 'Please also ensure you have the correct subscription, coupon, and webhook settings in your %1$s %2$s.', 'simple-pay' ),
		'<span id="simpay-toggle-notice-status" data-live="' . esc_attr( _x( 'live', 'Stripe account status', 'simple-pay' ) ) . '" data-test="' . esc_attr( _x( 'test', 'Stripe account status', 'simple-pay' ) ) . '"></span>',
		'<a id="simpay-toggle-notice-status-link" data-live="https://dashboard.stripe.com/live/dashboard" data-test="https://dashboard.stripe.com/test/dashboard" target="_blank" rel="noopener noreferrer">' . __( 'Stripe account', 'simple-pay' ) . '</a>'
	);

	return sprintf( '%1$s<p>%2$s</p>', $toggle_notice, $add );
}
add_filter( 'simpay_payment_mode_toggle_notice', __NAMESPACE__ . '\\payment_mode_toggle_notice' );

/**
 * Generate custom HTML to output under Webhooks title.
 *
 * @since 3.5.0
 */
function webhooks_setting_description() {
	ob_start();
	?>

	<p><?php esc_html_e( 'Stripe can send webhook events that notify your application any time an event happens on your account.', 'simple-pay' ); ?></p>

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'To allow your site to receive webhook notifications, add an endpoint in your %1$sStripe dashboard%2$s with the following URL:', 'simple-pay' ),
			'<a href="https://dashboard.stripe.com/account/webhooks" target="_blank" rel="noopener noreferrer">',
			'</a>'
		)
	);
	?>
	</p>

	<br />

	<p><code><?php echo esc_url( simpay_get_webhook_url() ); ?></code></p>

	<br />

	<p><?php echo wp_kses_post( sprintf( __( 'For more information view our %1$shelp docs for webhooks%2$s.', 'simple-pay' ), '<a href="' . simpay_docs_link( '', 'webhooks', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">', '</a>' ) ); ?></p>

	<?php if ( ! simpay_get_account_id() && simpay_check_keys_exist() ) : ?>
	<br />
	<div id="simpay-webhook-error" style="display: none;">
		<p>
			<?php esc_html_e( 'Your webhook configuration for the entered API keys is missing or incorrect.', 'simple-pay' ); ?>
		</p>

		<p>
			<button type="button" id="simpay-webhook-create" class="button"><?php esc_html_e( 'Create Webhooks Automatically', 'simple-pay' ); ?></button>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s Anchor opening tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'or %1$smanually in Stripe%2$s', 'simple-pay' ),
					'<a href="https://dashboard.stripe.com/account/webhooks">',
					'</a>'
				)
			);
			?>
		</p>
	</div>
	<?php endif; ?>

	<?php
	return ob_get_clean();
}

/**
 * Add "Webhook" section to Stripe Setup tab.
 *
 * @since 3.5.0
 *
 * @param array $sections Settings sections.
 * @return array
 */
function add_webhook_settings_section( $sections ) {
	$sections['webhooks'] = array(
		'title' => __( 'Webhooks', 'simple-pay' ),
	);

	return $sections;
}
add_filter( 'simpay_add_settings_keys_sections', __NAMESPACE__ . '\\add_webhook_settings_section' );

/**
 * Add "Stripe Elements Locale" fields to Stripe Setup tab.
 *
 * @since 3.6.0
 *
 * @param array $fields Settings fields.
 * @return array
 */
function add_elements_locale_settings_fields( $fields ) {
	$settings = get_option( 'simpay_settings_keys' );

	$fields['locale']['elements-locale'] = array(
		'title'       => esc_html__( 'Card Field Locale', 'simple-pay' ),
		'type'        => 'select',
		'options'     => simpay_get_stripe_elements_locales(),
		'name'        => 'simpay_settings_keys[locale][elements_locale]',
		'id'          => 'simpay-settings-keys-elements-locale',
		'value'       => isset( $settings['locale']['elements_locale'] ) ? $settings['locale']['elements_locale'] : 'auto',
		'description' => esc_html__( 'Specify "Auto-detect" to display the Credit Card field placeholders and validation messages in the user\'s preferred language, if available.', 'simple-pay' ) . '<br />' . esc_html__( 'This setting applies to the Embedded and Overlay form displays only.', 'simple-pay' ),
	);

	return $fields;
}
add_filter( 'simpay_add_settings_keys_fields', __NAMESPACE__ . '\\add_elements_locale_settings_fields' );

/**
 * Update Stripe Checkout Locale setting description to specify form type.
 *
 * @since 3.6.0
 *
 * @param array $fields Settings fields.
 * @return array
 */
function update_local_setting_field( $fields ) {
	if ( ! isset( $fields['locale']['locale'] ) ) {
		return $fields;
	}

	$description = $fields['locale']['locale']['description'];

	$fields['locale']['locale']['description'] = $description . '<br />' . esc_html__( 'This setting applies to the Stripe Checkout form displays only.', 'simple-pay' );

	return $fields;
}
add_filter( 'simpay_add_settings_keys_fields', __NAMESPACE__ . '\\update_local_setting_field' );

/**
 * Add "Webhook" fields to Stripe Setup tab.
 *
 * @since 3.5.0
 *
 * @param array $fields Settings fields.
 * @return array
 */
function add_webhook_settings_fields( $fields ) {
	$id       = 'settings';
	$group    = 'keys';
	$section  = 'webhooks';
	$settings = get_option( 'simpay_settings_keys' );

	$fields[ $section ]['setup'] = array(
		'title' => esc_html__( 'Setup', 'simple-pay' ),
		'type'  => 'custom-html',
		'html'  => webhooks_setting_description(),
		'name'  => 'simpay_' . $id . '_' . $group . '[' . $section . '][setup]',
		'id'    => 'simpay-' . $id . '-' . $group . '-' . $section . '-setup',
	);

	$args = array(
		'type'        => 'standard',
		'subtype'     => 'password',
		'default'     => '',
		'description' => wp_kses_post(
			sprintf(
				/* translators: %1$s opening anchor tag and URL, do not translate. %2$s closing anchor tag, do not translate */
				__( 'Stripe can optionally sign the webhook events it sends to your endpoints for added security. To do so, retrieve your endpoint&#39;s secret from your %1$sDashboard&#39;s webhooks settings%2$s. Select an endpoint for which you want to obtain the secret, then select the <em>Click to reveal</em> button.', 'simple-pay' ),
				'<a href="https://dashboard.stripe.com/account/webhooks" target="_blank" rel="noopener noreferrer">',
				'</a>'
			)
		),
		'class'       => array(
			'regular-text',
		),
	);

	$fields[ $section ]['test_endpoint_secret'] = wp_parse_args(
		$args,
		array(
			'title' => esc_html__( 'Test Endpoint Secret', 'simple-pay' ),
			'name'  => 'simpay_' . $id . '_' . $group . '[test_keys][endpoint_secret]',
			'id'    => 'simpay-' . $id . '-' . $group . '-test-keys-endpoint-secret',
			'value' => isset( $settings['test_keys']['endpoint_secret'] ) ? $settings['test_keys']['endpoint_secret'] : '',
		)
	);

	$fields[ $section ]['live_endpoint_secret'] = wp_parse_args(
		$args,
		array(
			'title' => esc_html__( 'Live Endpoint Secret', 'simple-pay' ),
			'name'  => 'simpay_' . $id . '_' . $group . '[live_keys][endpoint_secret]',
			'id'    => 'simpay-' . $id . '-' . $group . '-live-keys-endpoint-secret',
			'value' => isset( $settings['live_keys']['endpoint_secret'] ) ? $settings['live_keys']['endpoint_secret'] : '',
		)
	);

	return $fields;
}
add_filter( 'simpay_add_settings_keys_fields', __NAMESPACE__ . '\\add_webhook_settings_fields' );
