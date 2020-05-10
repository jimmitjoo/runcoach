<?php
/**
 * Admin: Pages
 *
 * @package SimplePay\Pro\Admin
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pages {

	public $values = array();

	public function __construct() {
		add_filter( 'simpay_add_settings_display_fields', array( $this, 'subscription_confirmation_messages' ) );

		add_filter( 'pre_update_option_simpay_settings_keys', array( $this, 'set_admin_notice_options' ), 10, 2 );

		// Add Beta version opt-in Setting to General > Misc area.
		add_filter( 'simpay_add_settings_general_fields', array( $this, 'add_beta_opt_in_setting' ) );

		// Remove the Save Changes button from the license page
		add_filter( 'simpay_admin_page_settings_license_submit', '__return_false' );
	}

	/**
	 * Set the database options for aadmin notices
	 *
	 * @param $new_value
	 * @param $old_value
	 *
	 * @return mixed
	 */
	function set_admin_notice_options( $new_value, $old_value ) {
		update_option( 'simpay_test_mode_changed', $new_value['mode']['test_mode'] );
		update_option( 'simpay_test_mode_changed_prev', $old_value['mode']['test_mode'] );

		// Check live keys
		if ( isset( $new_value['live_keys']['secret_key'] ) && $new_value['live_keys']['secret_key'] !== $old_value['live_keys']['secret_key'] || $new_value['live_keys']['publishable_key'] !== $old_value['live_keys']['publishable_key'] ) {

			update_option( 'simpay_live_keys_changed', true );
		}

		// Check test keys
		if ( isset( $new_value['test_keys']['secret_key'] ) && $new_value['test_keys']['secret_key'] !== $old_value['test_keys']['secret_key'] || $new_value['test_keys']['publishable_key'] !== $old_value['test_keys']['publishable_key'] ) {

			update_option( 'simpay_test_keys_changed', true );
		}

		return $new_value;
	}

	public function subscription_confirmation_messages( $fields ) {

		$section      = 'payment_confirmation_messages';
		$option_group = 'settings';
		$id           = 'display';

		$this->values = get_option( 'simpay_' . $option_group . '_' . $id );

		// Default template for subscriptions details
		$subscription_details_template = simpay_get_editor_default( 'subscription' );
		$subscription_details_value    = $this->get_option_value( $section, 'subscription_details' );

		$trial_details_template = simpay_get_editor_default( 'trial' );
		$trial_details_value    = $this->get_option_value( $section, 'trial_details' );

		// Add subscription payment & free trial sign up editor fields.
		if ( simpay_subscriptions_enabled() ) {

			$fields[ $section ] = array_merge(
				$fields[ $section ],
				array(
					'subscription_details' => array(
						'title'       => esc_html__( 'Subscription without Trial', 'simple-pay' ),
						'type'        => 'editor',
						'name'        => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][subscription_details]',
						'id'          => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-subscription-details',
						'value'       => isset( $subscription_details_value ) && ! empty( $subscription_details_value ) ? $subscription_details_value : $subscription_details_template,
						'escaping'    => array( $this, 'escape_editor' ),
						'description' => $this->subscription_details_description(),
					),
					'trial_details'        => array(
						'title'       => esc_html__( 'Subscription with Free Trial', 'simple-pay' ),
						'type'        => 'editor',
						'name'        => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][trial_details]',
						'id'          => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-trial-details',
						'value'       => isset( $trial_details_value ) && ! empty( $trial_details_value ) ? $trial_details_value : $trial_details_template,
						'escaping'    => array( $this, 'escape_editor' ),
						'description' => $this->trial_details_description(),
					),
				)
			);
		}

		return $fields;
	}


	// TODO: This is in the core files too. Need to find a way to call the parent so we don't have a duplicate here
	public function escape_editor( $value ) {
		return wp_kses_post( $value );
	}

	/**
	 * Default Subscription details template
	 *
	 * @return string'
	 */
	public function subscription_details_description() {

		$html  = '<div class="simpay-payment-details-description">';
		$html .= '<p class="description">' . esc_html__( 'Enter what your customers will see after a successful subscription plan payment.', 'simple-pay' ) . '</p>';
		$html .= '<p><strong>' . esc_html__( 'Available template tags:', 'simple-pay' ) . '</strong></p>';
		$html .= '<p><code>{recurring-amount}</code> - ' . esc_html__( 'The recurring amount to be charged each period of the subscription plan.', 'simple-pay' ) . '</p>';
		$html .= '<p><code>{max-charges}</code> - ' . esc_html__( 'The total number of max charges set for an installment plan.', 'simple-pay' ) . '</p>';

		$html .= '</div>';

		$html .= '<p class="simpay-stripe-connect-help description">';
		$html .= '<span class="dashicons dashicons-editor-help"></span><span>';
		$html .= sprintf(
			/* translators: %1$s Opening anchor tag for template tag documentation, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'For additional template tags, see the %1$sdocumentation%2$s.', 'simple-pay' ),
			'<a href="' . simpay_docs_link( '', 'configuring-payment-confirmation-display', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);
		$html .= '</span></p>';

		return $html;
	}

	/**
	 * Default Trial subscription details template
	 *
	 * @return string
	 */
	public function trial_details_description() {

		$html  = '<div class="simpay-payment-details-description">';
		$html .= '<p class="description">' . esc_html__( 'Enter what your customers will see after a successful subscription trial sign up.', 'simple-pay' ) . '</p>';
		$html .= '<p><strong>' . esc_html__( 'Available template tags:', 'simple-pay' ) . '</strong></p>';
		$html .= '<p><code>{trial-end-date}</code> - ' . esc_html__( "The day the plan's free trial ends.", 'simple-pay' ) . '</p>';

		$html .= '</div>';

		$html .= '<p class="simpay-stripe-connect-help description">';
		$html .= '<span class="dashicons dashicons-editor-help"></span><span>';
		$html .= sprintf(
			/* translators: %1$s Opening anchor tag for template tag documentation, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'For additional template tags, see the %1$sdocumentation%2$s.', 'simple-pay' ),
			'<a href="' . simpay_docs_link( '', 'configuring-payment-confirmation-display', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);
		$html .= '</span></p>';

		return $html;
	}

	/**
	 * Get option value.
	 *
	 * @since  3.0.0
	 * @access protected
	 *
	 * @param  string $section
	 * @param  string $setting
	 *
	 * @return string
	 */
	// TODO PRO: THis is in both lite and pro. find a way to avoid this.
	protected function get_option_value( $section, $setting ) {

		$option = $this->values;

		if ( ! empty( $option ) && is_array( $option ) ) {
			return isset( $option[ $section ][ $setting ] ) ? $option[ $section ][ $setting ] : '';
		}

		return '';
	}

	/**
	 * Add Beta version opt-in setting.
	 *
	 * @since 3.4.0
	 *
	 * @param array $fields Setting fields.
	 * @return array
	 */
	public function add_beta_opt_in_setting( $fields ) {
		$group   = 'general';
		$id      = 'settings';
		$section = 'general_misc';

		$fields[ $section ]['beta_opt_in'] = array(
			'title'       => esc_html__( 'Beta Versions', 'simple-pay' ),
			'type'        => 'checkbox',
			'name'        => 'simpay_' . $id . '_' . $group . '[' . $section . '][beta_opt_in]',
			'id'          => 'simpay-' . $id . '-' . $group . '-' . $section . '-beta-opt-in',
			'value'       => simpay_get_global_setting( 'beta_opt_in' ),
			'default'     => 'no',
			'description' => sprintf( esc_html__( 'Check this box to opt into update notifications for beta releases. This will allow you to use pre-release versions of WP Simple Pay.', 'simple-pay' ), SIMPLE_PAY_PLUGIN_NAME ),
		);

		return $fields;
	}

}
