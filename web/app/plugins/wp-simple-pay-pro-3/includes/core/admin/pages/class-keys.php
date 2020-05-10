<?php
/**
 * Admin pages: Stripe Setup
 *
 * @package SimplePay\Core\Admin\Pages
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin\Pages;

use SimplePay\Core\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feeds settings.
 *
 * Handles form settings and outputs the settings page markup.
 *
 * @since 3.0.0
 */
class Keys extends Admin_Page {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->id           = 'keys';
		$this->option_group = 'settings';
		$this->label        = esc_html__( 'Stripe Setup', 'simple-pay' );
		$this->link_text    = esc_html__( 'Help docs for Stripe Keys Settings', 'simple-pay' );
		$this->link_slug    = ''; // TODO: Fill in slug, not in use currently (issue #301)
		$this->ga_content   = 'general-settings';

		$this->sections = $this->add_sections();
		$this->fields   = $this->add_fields();
	}

	/**
	 * Add sections.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function add_sections() {

		return apply_filters(
			'simpay_add_' . $this->option_group . '_' . $this->id . '_sections',
			array(
				'connect'   => array(
					'title' => '',
				),
				'test_keys' => array(
					'title' => '',
				),
				'live_keys' => array(
					'title' => '',
				),
				'mode'      => array(
					'title' => '',
				),
				'country'   => array(
					'title' => '',
				),
				'locale'    => array(
					'title' => '',
				),
			)
		);
	}

	/**
	 * Add fields.
	 *
	 * @since  3.0.0
	 *
	 * @todo An extreme amount of markup soup is happening here.
	 * Settings API needs to be utilized better with custom fields being managed elsewhere.
	 *
	 * @return array
	 */
	public function add_fields() {

		$fields       = array();
		$this->values = get_option( 'simpay_' . $this->option_group . '_' . $this->id );

		if ( ! empty( $this->sections ) && is_array( $this->sections ) ) {
			foreach ( $this->sections as $section => $a ) {

				$section = sanitize_key( $section );

				if ( 'connect' == $section ) {
					$html = '';
					$mode = simpay_is_test_mode() ? __( 'test', 'simple-pay' ) : __( 'live', 'simple-pay' );

					// Need some sort of key (from a Connect account or manual) to check status.
					if ( simpay_check_keys_exist() ) {
						$html .= '<div id="simpay-stripe-account-info" class="simpay-stripe-account-info" data-account-id="' . simpay_get_account_id() . '" data-nonce="' . wp_create_nonce( 'simpay-stripe-connect-information' ) . '"><p><span class="spinner is-active"></span> <em>' . esc_html__( 'Retrieving account information...', 'simple-pay' ) . '</em></p></div>';
					}

					if ( false === simpay_get_account_id() || ! simpay_check_keys_exist() ) {
						$html .= '<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '" class="wpsp-stripe-connect"><span>' . __( 'Connect with Stripe', 'simple-pay' ) . '</span></a>';
					} else {
						$html .= '<p id="simpay-stripe-auth-error-account-actions" style="display: none;">' . sprintf(
							/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
							__( '%2$sReconnect in %1$s mode%4$s, or %3$sdisconnect this account%4$s.', 'simple-pay' ),
							'<strong>' . $mode . '</strong>',
							'<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '">',
							'<a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">',
							'</a>'
						) . '</p>';

						$html .= '<p id="simpay-stripe-activated-account-actions" style="display: none;">' . sprintf(
							/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
							__( 'Your Stripe account is connected in %1$s mode. %2$sReconnect in %1$s mode%4$s, or %3$sdisconnect this account%4$s.', 'simple-pay' ),
							'<strong>' . $mode . '</strong>',
							'<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '">',
							'<a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">',
							'</a>'
						) . '</p>';

						$html .= '<p id="simpay-stripe-unactivated-account-actions" style="display: none;">' . sprintf(
							/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for disconnecting Stripe, do not translate. %3$s Closing anchor tag, do not translate. */
							__( 'Your unsaved account is connected in %1$s mode. %2$sConnect to another account%3$s', 'simple-pay' ),
							'<strong>' . $mode . '</strong>',
							'<a href="' . esc_url( simpay_get_stripe_disconnect_url() ) . '">',
							'</a>'
						) . '</p>';
					}

					$html .= '<p class="simpay-stripe-connect-help description">';
					$html .= '<span class="dashicons dashicons-editor-help"></span><span>';
					$html .= sprintf(
						/* translators: %1$s Opening anchor tag for Stripe Connect documentation, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'Have questions about connecting with Stripe? See the %1$sdocumentation%2$s.', 'simple-pay' ),
						'<a href="' . simpay_docs_link( '', 'stripe-setup', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					);
					$html .= '</span></p>';

					// Only show buttons if we are managing keys, but none exist.
					// Otherwise the fields are auto shown.
					if ( simpay_can_site_manage_stripe_keys() ) {
						$html .= '<p id="wpsp-api-keys-row-reveal"><button type="button" class="button-link"><small>' . __( 'Manage API keys manually', 'simple-pay' ) . '</small></button></p>';
						$html .= '<p id="wpsp-api-keys-row-hide"><button type="button" class="button-link"><small>' . __( 'Hide API keys', 'simple-pay' ) . '</small></button></p>';
					}

					$fields[ $section ] = array(
						'test_mode' => array(
							'title' => esc_html__( 'Connection Status', 'simple-pay' ),
							'type'  => 'custom-html',
							'html'  => $html,
							'name'  => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][test_mode]',
							'id'    => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-test-mode',
						),
					);
				} elseif ( 'mode' == $section ) {
					$dashboard_message = sprintf(
						/* translators: %1$s Opening anchor tag to Stripe Dashboard, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'While in test mode no live payments are processed. Make sure Test mode is enabled in your %1$sStripe dashboard%2$s to view your test transactions.', 'simple-pay' ),
						'<a href="https://dashboard.stripe.com" target="_blank" rel="noopener noreferrer">',
						'</a>'
					);

					$toggle_notice = sprintf(
						'<p>%1$s</p>',
						esc_html__( 'You just toggled payment modes. You may be required to reconnect to Stripe when your settings are saved.', 'simple-pay' )
					);

					/**
					 * Filter the notice to be displayed when switching payment mode from Live to Test (or opposite).
					 *
					 * @since 3.5.0
					 *
					 * @param string $toggle_notice Toggle notice inner HTML.
					 */
					$toggle_notice = apply_filters( 'simpay_payment_mode_toggle_notice', $toggle_notice );

					$fields[ $section ] = array(
						'test_mode'        => array(
							'title'       => esc_html__( 'Test Mode', 'simple-pay' ),
							'default'     => 'enabled',
							'type'        => 'radio',
							'options'     => array(
								'enabled'  => esc_html__( 'Enabled', 'simple-pay' ),
								'disabled' => esc_html__( 'Disabled', 'simple-pay' ),
							),
							'value'       => $this->get_option_value( $section, 'test_mode' ),
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][test_mode]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-test-mode',
							'inline'      => 'inline',
							'description' => $dashboard_message,
						),
						'test_mode_toggle' => array(
							'title' => '',
							'id'    => 'simpay-test-mode-toggle',
							'type'  => 'custom-html',
							'html'  => '<div id="simpay-test-mode-toggle-notice" style="display: none;">' . $toggle_notice . '</div>',
						),
					);
				} elseif ( 'test_keys' == $section ) {

					$fields[ $section ] = array(
						'publishable_key' => array(
							'title'   => esc_html__( 'Test Publishable Key', 'simple-pay' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][publishable_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-publishable-key',
							'value'   => trim( $this->get_option_value( $section, 'publishable_key' ) ),
							'class'   => array(
								'regular-text',
							),
						),
						'secret_key'      => array(
							'title'   => esc_html__( 'Test Secret Key', 'simple-pay' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][secret_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-secret-key',
							'value'   => trim( $this->get_option_value( $section, 'secret_key' ) ),
							'class'   => array(
								'regular-text',
							),
						),
					);
				} elseif ( 'live_keys' == $section ) {

					$fields[ $section ] = array(
						'publishable_key' => array(
							'title'   => esc_html__( 'Live Publishable Key', 'simple-pay' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][publishable_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-publishable-key',
							'value'   => trim( $this->get_option_value( $section, 'publishable_key' ) ),
							'class'   => array(
								'regular-text',
							),
						),
						'secret_key'      => array(
							'title'   => esc_html__( 'Live Secret Key', 'simple-pay' ),
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][secret_key]',
							'id'      => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-secret-key',
							'value'   => trim( $this->get_option_value( $section, 'secret_key' ) ),
							'class'   => array(
								'regular-text',
							),
						),
					);
				} elseif ( 'country' == $section ) {

					$fields[ $section ] = array(
						'country' => array(
							'title'       => esc_html__( 'Account Country', 'simple-pay' ),
							'type'        => 'select',
							'options'     => simpay_get_country_list(),
							'name'        => 'simpay_' . $this->option_group . '_' . $this->id . '[' . $section . '][country]',
							'id'          => 'simpay-' . $this->option_group . '-' . $this->id . '-' . $section . '-country',
							'value'       => $this->get_option_value( $section, 'country' ),
							'description' => esc_html__( 'The country associated with the connected Stripe account.', 'simple-pay' ),
						),
					);

				} elseif ( 'locale' === $section ) {
					$value    = $this->get_option_value( $section, 'locale' );
					$fallback = get_option( 'simpay_settings_general' );
					$fallback = isset( $fallback['general']['locale'] ) ? $fallback['general']['locale'] : '';

					$fields[ $section ] = array(
						'locale' => array(
							'title'       => esc_html__( 'Stripe Checkout Locale', 'simple-pay' ),
							'type'        => 'select',
							'options'     => simpay_get_stripe_checkout_locales(),
							'name'        => 'simpay_' . $this->option_group . '_keys[locale][locale]',
							'id'          => 'simpay-' . $this->option_group . '-keys-locale-locale',
							'value'       => '' !== $value ? $value : $fallback,
							'description' => esc_html__( 'Specify "Auto-detect" to display Stripe Checkout in the user\'s preferred language, if available.', 'simple-pay' ),
						),
					);
				}
			}
		}

		return apply_filters( 'simpay_add_' . $this->option_group . '_' . $this->id . '_fields', $fields );
	}

}
