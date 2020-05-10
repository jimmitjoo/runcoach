<?php
/**
 * "Lite Helper"
 *
 * @todo Move all of these things in to more organized areas.
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Forms\Pro_Form;
use SimplePay\Pro\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lite_Helper {

	public function __construct() {

		if ( is_admin() ) {
			// Remove the upgrade sidebar from settings pages
			add_filter( 'simpay_settings_sidebar_template', array( $this, 'remove_sidebar' ) );

			// Add a docs links
			add_action( 'simpay_admin_after_payment_options', array( $this, 'payment_options_docs_link' ), 999 );
			add_action( 'simpay_admin_after_stripe_checkout', array( $this, 'stripe_checkout_docs_link' ), 999 );

			// Add general settings fields
			add_filter( 'simpay_add_settings_general_fields', array( $this, 'general_settings' ) );

			// Add {tax-amount} description field back to payment details screen.
			add_filter( 'simpay_payment_details_tag_descriptions', array( $this, 'simpay_tax_amount_description' ) );

			add_filter( 'simpay_upgrade_link', array( $this, 'pro_upgrade_link' ) );

			// Add custom fields by default when a new payment form is first created.
			add_action( 'simpay_form_created', array( $this, 'create_missing_fields_new_form' ) );

			// Save Pro form settings.
			add_action( 'simpay_save_form_settings', array( $this, 'save_form_settings' ), 0, 2 );
		}

		// Change the payment handler to the Pro version
		add_filter( 'simpay_payment_handler', array( $this, 'pro_payment_handler' ), 10, 3 );

		// Add default editor templates for payment details
		add_filter( 'simpay_editor_template', array( $this, 'add_default_templates' ), 10, 2 );

		add_filter( 'simpay_utm_campaign', array( $this, 'pro_ga_campaign' ) );

		// Load the pro shared script variables
		add_filter( 'simpay_shared_script_variables', array( $this, 'pro_shared_script_variables' ), 11 );

		// We need to make our object factory use the Pro_Form and not the Default_Form for form objects.
		add_filter( 'simpay_form_object_type', array( $this, 'pro_form_object' ) );
		add_filter( 'simpay_form_namespace', array( $this, 'pro_object_namespace' ) );

		// Use Pro form instead of Default_Form
		add_filter( 'simpay_form_view', array( $this, 'load_pro_form' ), 10, 2 );
	}

	// TODO Remove $view param? Never used.

	public function load_pro_form( $view, $id ) {
		return new Pro_Form( $id );
	}

	public function pro_object_namespace() {
		return 'SimplePay\\Pro';
	}

	public function pro_form_object() {
		return 'pro-form';
	}

	public function pro_ga_campaign() {
		return 'pro-plugin';
	}

	public function pro_upgrade_link( $link ) {
		return simpay_ga_url( simpay_get_url( 'my-account' ), 'under-box-promo' );
	}

	public function add_default_templates( $template, $editor ) {

		switch ( $editor ) {
			case 'subscription':
				$html  = __( 'Thanks for your purchase. Here are the details of your payment:', 'simple-pay' ) . "\n\n";
				$html .= '<strong>' . esc_html__( 'Item:', 'simple-pay' ) . '</strong>' . ' {item-description}' . "\n";
				$html .= '<strong>' . esc_html__( 'Purchased From:', 'simple-pay' ) . '</strong>' . ' {company-name}' . "\n";
				$html .= '<strong>' . esc_html__( 'Payment Date:', 'simple-pay' ) . '</strong>' . ' {charge-date}' . "\n";
				$html .= '<strong>' . esc_html__( 'Initial Payment Amount:', 'simple-pay' ) . '</strong>' . ' {total-amount}' . "\n";
				$html .= '<strong>' . esc_html__( 'Recurring Payment Amount: ', 'simple-pay' ) . '</strong>' . '{recurring-amount}' . "\n";

				return $html;
			case 'trial':
				$html  = __( 'Thanks for subscribing. Your card will not be charged until your free trial ends.', 'simple-pay' ) . "\n\n";
				$html .= '<strong>' . esc_html__( 'Item:', 'simple-pay' ) . '</strong>' . ' {item-description}' . "\n";
				$html .= '<strong>' . esc_html__( 'Purchased From:', 'simple-pay' ) . '</strong>' . ' {company-name}' . "\n";
				$html .= '<strong>' . esc_html__( 'Trial End Date:', 'simple-pay' ) . '</strong>' . ' {trial-end-date}' . "\n";
				$html .= '<strong>' . esc_html__( 'Recurring Payment Amount: ', 'simple-pay' ) . '</strong>' . '{recurring-amount}' . "\n";

				return $html;
			default:
				return $template;
		}
	}

	public function simpay_tax_amount_description( $html ) {
		$html .= '<p><code>{tax-amount}</code> - ' . esc_html__( 'The calculated tax amount based on the total and the tax percent setting.', 'simple-pay' ) . '</p>';

		return $html;
	}

	public function pro_payment_handler( $old, $form, $action ) {
		return new Payments\Payment( $form, $action );
	}

	/**
	 * This function is used to insert a setting at a specific location based on the associative key.
	 *
	 * @deprecated 3.4.0 Use simpay_add_to_array_after()
	 *
	 * @param new_key  string The new key to use for                                      $fields[ $section ][ $new_key ]
	 * @param $value    array The array that holds the information for this settings array
	 * @param $needle   string The key to find in the current array of fields
	 * @param $haystack array The current array to search
	 *
	 * @return array
	 */
	private function insert_after_key( $new_key, $value, $needle, $haystack ) {
		return simpay_add_to_array_after( $new_key, $value, $needle, $haystack );
	}

	public function general_settings( $fields ) {

		$id           = 'general';
		$option_group = 'settings';
		$section      = 'general';
		$values       = get_option( 'simpay_' . $option_group . '_' . $id );

		// General settings
		$new = array(
			'title'       => esc_html__( 'Date Format', 'simple-pay' ),
			'type'        => 'standard',
			'subtype'     => 'text',
			'name'        => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][date_format]',
			'id'          => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-date-format',
			'value'       => $this->get_option_value( $values, $section, 'date_format' ),
			'description' => sprintf(
				wp_kses(
					__( '<a href="%s" target="_blank" rel="noopener noreferrer">Date format options</a> (uses jQuery UI Datepicker)', 'simple-pay' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					)
				),
				'http://api.jqueryui.com/datepicker/#utility-formatDate'
			),
			'class'       => array(
				'simpay-medium-text',
			),
			'default'     => 'mm/dd/yy',
			'placeholder' => 'mm/dd/yy',
		);

		$fields[ $section ] = $this->insert_after_key( 'date_format', $new, 'cancelled_page', $fields[ $section ] );

		// Currency options
		$section = 'general_currency';

		$new = array(
			'title'       => esc_html__( 'Tax Rate Percentage', 'simple-pay' ),
			'type'        => 'standard',
			'subtype'     => 'number',
			'name'        => 'simpay_' . $option_group . '_' . $id . '[' . $section . '][tax_percent]',
			'id'          => 'simpay-' . $option_group . '-' . $id . '-' . $section . '-tax-percent',
			'value'       => $this->get_option_value( $values, $section, 'tax_percent' ),
			'attributes'  => array(
				'min'  => 0,
				'max'  => 100,
				'step' => 'any',
			),
			'class'       => array(
				'simpay-small-text',
				'simpay-tax-percent-field',
			),
			'description' => esc_html__( 'Enter a tax rate as a percentage to add to the charged amount (i.e. for 7.5% tax enter 7.5).', 'simple-pay' ),
		);

		$fields[ $section ] = $this->insert_after_key( 'tax_percent', $new, 'currency_position', $fields[ $section ] );

		return $fields;
	}

	private function get_option_value( $values, $section, $setting ) {

		$option = $values;

		if ( ! empty( $option ) && is_array( $option ) ) {
			return isset( $option[ $section ][ $setting ] ) ? $option[ $section ][ $setting ] : '';
		}

		return '';
	}

	public function pro_shared_script_variables( $arr ) {

		$i18n['i18n'] = array_merge(
			isset( $arr['i18n'] ) ? $arr['i18n'] : array(),
			array(
				'limitSingleField'     => esc_html__( 'You may only add one of these fields per form.', 'simple-pay' ),
				'limitMaxFields'       => esc_html__( 'The maximum number of fields is 20.', 'simple-pay' ),
				'couponPercentOffText' => esc_html_x( '% off', 'This is for the coupon percent off text on the frontend. i.e. 10% off', 'simple-pay' ),
				'couponAmountOffText'  => esc_html_x( 'off', 'This is for coupon amount off on the frontend. i.e. $3.00 off', 'simple-pay' ),
			)
		);

		$integers['integers'] = array_merge(
			isset( $arr['integers'] ) ? $arr['integers'] : array(),
			array(
				'minAmount' => simpay_global_minimum_amount(),
			)
		);

		return array_merge( $arr, $i18n, $integers );
	}

	/**
	 * Add the docs link to the payment options form settings tab
	 */
	public function payment_options_docs_link() {
		echo simpay_docs_link( __( 'Help docs for Payment Options', 'simple-pay' ), 'payment-options', 'form-settings' );
	}

	/**
	 * Add the docs link to the checkout overlay display form settings tab
	 */
	public function stripe_checkout_docs_link() {
		echo simpay_docs_link( __( 'Help docs for Stripe Checkout Display', 'simple-pay' ), 'stripe-checkout-display-options', 'form-settings' );
	}

	public function remove_sidebar() {
		return '';
	}

	/**
	 * Validate and save the meta box fields.
	 *
	 * @since  3.0.0
	 *
	 * @param  int      $post_id
	 * @param  \WP_Post $post
	 *
	 * @return void
	 */
	public function save_form_settings( $post_id, $post ) {

		/** Payment Options */

		// Minimum Amount
		// TODO Rewrite. Hard to read.
		$minimum_amount = isset( $_POST['_minimum_amount'] ) ? sanitize_text_field( $_POST['_minimum_amount'] ) : ( false !== get_post_meta( $post_id, '_minimum_amount', true ) ? get_post_meta( $post_id, '_minimum_amount', true ) : simpay_global_minimum_amount() );

		update_post_meta( $post_id, '_minimum_amount', $minimum_amount );

		// Custom Amount Default
		$custom_amount_default = isset( $_POST['_custom_amount_default'] ) ? sanitize_text_field( $_POST['_custom_amount_default'] ) : '';
		update_post_meta( $post_id, '_custom_amount_default', $custom_amount_default );

		// Custom Amount Label
		$custom_amount_label = isset( $_POST['_custom_amount_label'] ) ? sanitize_text_field( $_POST['_custom_amount_label'] ) : '';
		update_post_meta( $post_id, '_custom_amount_label', $custom_amount_label );

		// Plan select Form Field Label
		$form_field_label = isset( $_POST['_plan_select_form_field_label'] ) ? sanitize_text_field( $_POST['_plan_select_form_field_label'] ) : '';
		update_post_meta( $post_id, '_plan_select_form_field_label', $form_field_label );

		/** Form Display Options */

		// Default form display type to Embedded.
		$form_display_type = isset( $_POST['_form_display_type'] ) ? esc_attr( $_POST['_form_display_type'] ) : 'embedded';
		update_post_meta( $post_id, '_form_display_type', $form_display_type );

		/** Checkout Overlay Display Options */

		// Enable Billing Address
		$enable_billing_address = isset( $_POST['_enable_billing_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_billing_address', $enable_billing_address );

		// Enable Shipping Address
		$enable_shipping_address = isset( $_POST['_enable_shipping_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_shipping_address', $enable_shipping_address );

		/** Subscription Options */

		// Subscription Plans
		$subscription_type = isset( $_POST['_subscription_type'] ) ? esc_attr( $_POST['_subscription_type'] ) : 'disabled';
		update_post_meta( $post_id, '_subscription_type', $subscription_type );

		// Select Plan (Single)
		$single_plan = isset( $_POST['_single_plan'] ) ? sanitize_text_field( $_POST['_single_plan'] ) : 'empty';

		if ( 'empty' !== $single_plan ) {

			// Save the entire plan object to it's own post_meta slot
			$single_plan_object = Stripe_API::request( 'Plan', 'retrieve', array( 'id' => $single_plan ) );

			if ( $single_plan_object ) {
				update_post_meta( $post_id, '_single_plan_object', $single_plan_object );
			}
		}
		update_post_meta( $post_id, '_single_plan', $single_plan );

		// Display Style
		$display_style = isset( $_POST['_multi_plan_display'] ) ? esc_attr( $_POST['_multi_plan_display'] ) : 'radio';
		update_post_meta( $post_id, '_multi_plan_display', $display_style );

		// Plan Setup Fee
		$setup_fee = isset( $_POST['_setup_fee'] ) ? sanitize_text_field( $_POST['_setup_fee'] ) : '';
		update_post_meta( $post_id, '_setup_fee', $setup_fee );

		// Max charges
		$max_charges = isset( $_POST['_max_charges'] ) ? absint( $_POST['_max_charges'] ) : 0;
		update_post_meta( $post_id, '_max_charges', $max_charges );

		// Custom Plan Label
		$custom_plan_label = isset( $_POST['_custom_plan_label'] ) ? sanitize_text_field( $_POST['_custom_plan_label'] ) : '';
		update_post_meta( $post_id, '_custom_plan_label', $custom_plan_label );

		// Show Recurring Total Label
		$enable_recurring_total = isset( $_POST['_enable_recurring_total'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_recurring_total', $enable_recurring_total );

		// Stripe Recurring Total Label
		$recurring_total_label = isset( $_POST['_recurring_total_label'] ) ? sanitize_text_field( $_POST['_recurring_total_label'] ) : '';
		update_post_meta( $post_id, '_recurring_total_label', $recurring_total_label );

		// Multi-plans
		$multi_plan = isset( $_POST['_multi_plan'] )
			? $this->clear_empty_plans( $_POST['_multi_plan'] )
			: array();
		$multi_plan = array_values( $multi_plan );

		// Cache full plan objects from Stripe.
		//
		// @todo This information is available because we had to call `simpay_get_plans()`,
		// so we should send the object through the form POST vs. making further API requests.
		foreach ( $multi_plan as $k => $plan ) {
			try {
				$plan_object                     = Stripe_API::request( 'Plan', 'retrieve', $plan['select_plan'] );
				$multi_plan[ $k ]['plan_object'] = $plan_object;
			} catch ( \Exception $e ) {
			}
		}

		update_post_meta( $post_id, '_multi_plan', $multi_plan );

		// Default radio button value for default plan
		$multi_plan_default_value = isset( $_POST['_multi_plan_default_value'] ) ? $_POST['_multi_plan_default_value'] : '';

		// Convert legacy "empty" string values to the first selected Plan for default.
		if ( 'empty' === $multi_plan_default_value || empty( $multi_plan_default_value ) ) {
			if ( ! empty( $multi_plan ) ) {
				$multi_plan_default_value = current( $multi_plan )['plan_object']->id;
			}
		}

		update_post_meta( $post_id, '_multi_plan_default_value', $multi_plan_default_value );

		// Custom Amount (multi-plan)
		$subscription_custom_amount = isset( $_POST['_subscription_custom_amount'] ) ? esc_attr( $_POST['_subscription_custom_amount'] ) : 'disabled';
		update_post_meta( $post_id, '_subscription_custom_amount', $subscription_custom_amount );

		// Minimum Amount
		$multi_plan_minimum_amount = isset( $_POST['_multi_plan_minimum_amount'] ) ? sanitize_text_field( $_POST['_multi_plan_minimum_amount'] ) : '';
		update_post_meta( $post_id, '_multi_plan_minimum_amount', $multi_plan_minimum_amount );

		// Default amount
		$multi_plan_default_amount = isset( $_POST['_multi_plan_default_amount'] ) ? sanitize_text_field( $_POST['_multi_plan_default_amount'] ) : '';
		update_post_meta( $post_id, '_multi_plan_default_amount', $multi_plan_default_amount );

		// Interval
		$plan_interval = isset( $_POST['_plan_interval'] ) ? intval( $_POST['_plan_interval'] ) : '';
		update_post_meta( $post_id, '_plan_interval', $plan_interval );

		// Frequency
		$plan_frequency = isset( $_POST['_plan_frequency'] ) ? esc_attr( $_POST['_plan_frequency'] ) : '';
		update_post_meta( $post_id, '_plan_frequency', $plan_frequency );

		// Save custom fields
		$fields = isset( $_POST['_simpay_custom_field'] ) ? $_POST['_simpay_custom_field'] : array();

		// Check & create required missing fields for this form display type.
		$fields = $this->create_missing_fields( $fields, $post_id );

		$fields = $this->update_ids( $fields, $post_id );

		// Re-index the array so if fields were removed we don't overwrite the index with a new field
		foreach ( $fields as $k => $v ) {
			$fields[ $k ] = array_values( $v );
		}

		update_post_meta( $post_id, '_custom_fields', $fields );
	}

	/**
	 * Clears out the empty plans and returns the reformed array.
	 *
	 * @since 3.0.0
	 *
	 * @param array $plans List of plans.
	 * @return array
	 */
	public function clear_empty_plans( $plans ) {
		if ( ! is_array( $plans ) || empty( $plans ) ) {
			return array();
		}

		foreach ( $plans as $k => $v ) {
			if ( 'empty' === $v['select_plan'] ) {
				unset( $plans[ $k ] );
			}
		}

		return $plans;
	}

	/**
	 * Converts the IDs for the fields before saving
	 */
	private function update_ids( $arr, $form_id ) {

		if ( ! empty( $arr ) && is_array( $arr ) ) {
			foreach ( $arr as $k => &$v ) {

				if ( ! empty( $v ) && is_array( $v ) ) {
					foreach ( $v as $k2 => &$v2 ) {

						if ( ! empty( $v2 ) && is_array( $v2 ) ) {
							foreach ( $v2 as $k3 => &$v3 ) {
								if ( empty( $v3 ) ) {
									if ( 'id' === $k3 ) {

										if ( 'payment_button' !== $k ) {
											$v3 = 'simpay_' . $form_id . '_' . $k . '_' . $v2['uid'];
										} else {
											$v3 = 'simpay_' . $form_id . '_' . $k;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $arr;
	}

	/**
	 * Fires on form save and checks for missing fields to keep things easy for users. Example: adds a payment button
	 * if one was not added.
	 * If this is a newly created form, possibly add more fields by default (i.e. Customer Name).
	 *
	 * @param $fields array
	 * @param $form_id int
	 * @param $new_form bool
	 *
	 * @return array
	 */
	private function create_missing_fields( $fields, $form_id, $new_form = false ) {

		$has_customer_name   = false;
		$has_email           = false;
		$has_card            = false;
		$has_custom_amount   = false;
		$has_sub_select      = false;
		$has_payment_button  = false;
		$has_checkout_button = false;

		$form_display_type  = get_post_meta( $form_id, '_form_display_type', true );
		$amount_type        = isset( $_POST['_amount_type'] ) ? sanitize_text_field( $_POST['_amount_type'] ) : '';
		$sub_type           = isset( $_POST['_subscription_type'] ) ? sanitize_text_field( $_POST['_subscription_type'] ) : '';
		$sub_custom_enabled = isset( $_POST['_subscription_custom_amount'] ) ? sanitize_text_field( $_POST['_subscription_custom_amount'] ) : '';

		$use_custom = ( 'one_time_custom' === $amount_type );

		// Add sub selection if user selects plan OR set single plan w/ custom amount.
		$use_sub_select = ( ( 'user' === $sub_type ) || ( 'single' === $sub_type && 'enabled' === $sub_custom_enabled ) );

		// TODO Not sure why we start with 1 here but it works.
		$total_count             = 1;
		$payment_button_position = 1;

		if ( ! empty( $fields ) && is_array( $fields ) ) {

			foreach ( $fields as $type => $values ) {

				$total_count += 1;

				if ( 'custom_amount' === $type ) {
					$has_custom_amount = true;
				}

				if ( 'plan_select' === $type ) {
					$has_sub_select = true;
				}

				if ( 'customer_name' === $type ) {
					$has_customer_name = true;
				}

				if ( 'email' === $type ) {
					$has_email = true;
				}

				if ( 'card' === $type ) {
					$has_card = true;
				}

				if ( 'checkout_button' === $type ) {
					$has_checkout_button = true;
				}

				if ( 'payment_button' === $type ) {
					$has_payment_button = true;
					// Determine payment button position.
					$payment_button_position = $total_count;
				}
			}
		}

		// TODO Rearrange these back to how they were.

		// Custom amount
		if ( ! $has_custom_amount && $use_custom ) {

			$position = ( $has_payment_button ) ? $payment_button_position - 3 : $total_count;

			$fields['custom_amount'][] = array(
				'order' => $position,
				'uid'   => $total_count,
				'id'    => 'simpay_' . $form_id . '_custom_amount_' . $total_count,
				'text'  => '',
			);

			$total_count ++;
		}

		// Sub plan select
		if ( ! $has_sub_select && $use_sub_select ) {

			$position = ( $has_payment_button ) ? $payment_button_position - 3 : $total_count;

			$fields['plan_select'][] = array(
				'order' => $position,
				'uid'   => $total_count,
				'id'    => 'simpay_' . $form_id . '_plan_select_' . $total_count,
				'text'  => '',
			);

			$total_count ++;
		}

		// Customer Name - Add only if new form.
		// Form display type not saved as embedded yet here, but that's what we'll assume for now.
		if ( true === $new_form && ! $has_customer_name ) {

			$position = ( $has_payment_button ) ? $payment_button_position - 3 : $total_count;

			$fields['customer_name'][] = array(
				'order'       => $position,
				'uid'         => $total_count,
				'id'          => 'simpay_' . $form_id . '_customer_name',
				'placeholder' => __( 'Full name', 'simple-pay' ),
				'required'    => 'yes',
			);

			$total_count ++;
		}

		// Email
		if ( ! $has_email && ( $form_display_type == 'embedded' || $form_display_type == 'overlay' ) ) {

			$position = ( $has_payment_button ) ? $payment_button_position - 2 : $total_count;

			$fields['email'][] = array(
				'order'       => $position,
				'uid'         => $total_count,
				'id'          => 'simpay_' . $form_id . '_email',
				'placeholder' => __( 'Email', 'simple-pay' ),
			);

			$total_count ++;
		}

		// Card
		if ( ! $has_card && ( $form_display_type == 'embedded' || $form_display_type == 'overlay' ) ) {

			$position = ( $has_payment_button ) ? $payment_button_position - 1 : $total_count;

			$fields['card'][] = array(
				'order'      => $position,
				'uid'        => $total_count,
				'id'         => 'simpay_' . $form_id . '_card',
				'verify_zip' => 'yes',
			);

			$total_count ++;
		}

		// Payment button
		if ( ! $has_payment_button && ( $form_display_type == 'stripe_checkout' || $form_display_type == 'overlay' ) ) {

			$fields['payment_button'][] = array(
				'order'           => $total_count,
				'uid'             => $total_count,
				'id'              => 'simpay_' . $form_id . '_payment_button',
				'text'            => '',
				'processing_text' => '',
			);

			$total_count ++;
		}

		// Checkout button
		if ( ! $has_checkout_button && ( $form_display_type == 'embedded' || $form_display_type == 'overlay' ) ) {

			$position = ( $has_payment_button ) ? $payment_button_position + 1 : $total_count;

			$fields['checkout_button'][] = array(
				'order'           => $position,
				'uid'             => $total_count,
				'id'              => 'simpay_' . $form_id . '_checkout_button',
				'text'            => '',
				'processing_text' => '',
			);
		}

		if ( $form_display_type == 'stripe_checkout' ) {

			// Remove any fields that can be duplicated in Stripe Checkout.
			unset( $fields['card'] );
			unset( $fields['checkout_button'] );
		}

		if ( $form_display_type == 'embedded' ) {

			// Remove payment button if embedded display.
			unset( $fields['payment_button'] );
		}

		if ( $use_sub_select || ( ! $use_sub_select && ! $use_custom ) ) {

			// Remove Custom Amount field if subscription is enabled.
			unset( $fields['custom_amount'] );
		}

		return $fields;
	}

	/**
	 * Add custom fields by default when a new payment form is first created.
	 */
	public function create_missing_fields_new_form( $form_id ) {
		$fields = $this->create_missing_fields( array(), $form_id, true );
		update_post_meta( $form_id, '_custom_fields', $fields );
	}
}
