<?php
/**
 * Forms: Embed/Overlay
 *
 * @package SimplePay\Pro\Forms
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms;

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Admin\MetaBoxes\Custom_Fields;
use SimplePay\Core\Forms\Default_Form;
use SimplePay\Pro\Payments\Plan;
use SimplePay\Pro\Payments\Subscription;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro_Form class.
 *
 * @since 3.0.0
 */
class Pro_Form extends Default_Form {

	public $printed_subscriptions = false;
	public $printed_custom_amount = false;

	/**
	 * Form constructor.
	 *
	 * @param $id int
	 */
	public function __construct( $id ) {

		parent::__construct( $id );

		// TODO Need to set this property?
		// Set our form specific filter to apply to each setting
		$this->filter = 'simpay_form_' . $this->id;

		// Setup the global settings tied to this form
		$this->pro_set_global_settings();

		// Setup the post meta settings tied to this form
		$this->pro_set_post_meta_settings();

	}

	public function register_hooks() {
		parent::register_hooks();

		add_action( 'simpay_form_' . $this->id . '_before_payment_form', array( $this, 'before_payment_form' ) );
		add_action( 'simpay_form_' . $this->id . '_after_form_display', array( $this, 'after_form_display' ) );
		add_filter( 'simpay_form_' . $this->id . '_custom_fields', array( $this, 'get_custom_fields_html' ), 10, 3 );
		add_action( 'simpay_form_' . $this->id . '_before_form_bottom', array( $this, 'pro_html' ) );

		add_filter( 'simpay_form_' . $this->id . '_classes', array( $this, 'pro_form_classes' ) );
		add_filter( 'simpay_form_' . $this->id . '_script_variables', array( $this, 'pro_get_form_script_variables' ), 10, 2 );
		add_filter( 'simpay_payment_button_class', array( $this, 'payment_button_class' ) );
	}

	public function payment_button_class( $classes ) {

		$button_action = ( 'overlay' == $this->get_form_display_type() ) ? 'simpay-modal-btn' : 'simpay-payment-btn';

		if ( isset( $classes['simpay-payment-btn'] ) ) {
			unset( $classes['simpay-payment-btn'] );
		}

		$classes[] = $button_action;

		return $classes;
	}

	public function pro_form_classes( $classes ) {

		$classes[] = 'simpay-checkout-form--' . $this->get_form_display_type();

		// If Stripe Checkout is enabled, maybe add custom form styling.
		if ( 'stripe_checkout' === $this->get_form_display_type() ) {
			$styled = simpay_get_filtered(
				'stripe_enable_form_styles',
				simpay_get_saved_meta( $this->id, '_enable_stripe_checkout_form_styles', 'no' ),
				$this->id
			);

			if ( 'yes' === $styled ) {
				$classes[] = 'simpay-checkout-form--stripe_checkout-styled';

				// If the on-page fields should not be styled remove the `.simpay-styled` class.
			} else {
				$simpay_styled = array_search( 'simpay-styled', $classes, true );

				if ( false !== $simpay_styled ) {
					unset( $classes[ $simpay_styled ] );
				}
			}
		}

		return $classes;
	}

	// HTML to render before form output depending on form display type.
	public function before_payment_form() {

		$html              = '';
		$heading_html      = '';
		$form_display_type = $this->get_form_display_type();
		$form_title        = $this->company_name;
		$form_description  = $this->item_description;

		// Add title & description text for Embedded & Overlay form types if they exist.

		if ( 'embedded' === $form_display_type || 'overlay' === $form_display_type ) {

			if ( ! empty( $form_title ) ) {
				$heading_html .= '<h3 class="simpay-form-title">' . esc_html( $form_title ) . '</h3>';
			}

			if ( ! empty( $form_description ) ) {
				$heading_html .= '<p class="simpay-form-description">' . esc_html( $form_description ) . '</p>';
			}
		}

		if ( 'embedded' === $form_display_type ) {

			$html .= '<div class="simpay-embedded-heading simpay-styled">';
			$html .= $heading_html;
			$html .= '</div>';

		} elseif ( 'overlay' === $form_display_type ) {

			$html .= '<label for="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-control-open">' . $this->get_payment_button( $this->custom_fields ) . '</label>';
			$html .= '<input type="checkbox" id="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-control" data-form-id="' . esc_attr( $this->id ) . '">';

			$classes = array(
				'simpay-modal',
			);
		
			if ( 'disabled' !== simpay_get_global_setting( 'default_plugin_styles' ) ) {
				$classes[] = 'simpay-styled';
			}

			$html .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-form-id="' . esc_attr( $this->id ) . '">';
			$html .= '<div class="simpay-modal__body">';
			$html .= '<div class="simpay-modal__content">';
			$html .= $heading_html;
			$html .= '<label for="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-control-close">&#x2715;</label>';
		}

		echo $html;
	}

	// HTML to render after form output depending on form display type.
	public function after_form_display() {

		$html = '';

		if ( 'overlay' == $this->get_form_display_type() ) {
			$html .= '</div>';
			$html .= '</div>';
			$html .= '<label for="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-overlay-close" z-index="-1"></label>';
			$html .= '</div>';

			// Show a test mode badge here since the main one is only shown on the custom overlay.
			$html .= simpay_get_test_mode_badge();
		}

		echo $html;
	}

	// Helper function to get payment button out of the form
	private function get_payment_button( $fields ) {

		$html = '';

		foreach ( $fields as $k => $v ) {
			switch ( $v['type'] ) {
				case 'payment_button':
					$html .= \SimplePay\Core\Forms\Fields\Payment_Button::html( $v );
			}
		}

		return $html;
	}

	public function pro_html() {

		$html = '';

		// In case they have subscriptions but have not set the custom field for placement we will print it after the other custom fields.
		if ( ! $this->printed_subscriptions && $this->is_subscription() && 'user' === $this->subscription_type ) {
			$html .= $this->print_subscription_options( $this->has_subscription_custom_amount );
		}

		// Print custom amount field if this is not a subscription (subscription custom amount is handled in the print_subscription() function
		if ( ! $this->printed_custom_amount ) {
			if ( $this->is_one_time_custom_amount || $this->has_subscription_custom_amount ) {
				$html .= $this->print_custom_amount();
			}
		}

		if ( $this->is_subscription() ) {
			$html .= '<input type="hidden" name="simpay_multi_plan_id" value="" class="simpay-multi-plan-id" />';
			$html .= '<input type="hidden" name="simpay_multi_plan_setup_fee" value="" class="simpay-multi-plan-setup-fee" />';
			$html .= '<input type="hidden" name="simpay_max_charges" value="" class="simpay-max-charges" />';
		}

		// Add a hidden field to hold the tax value
		if ( $this->tax_percent > 0 ) {
			$html .= '<input type="hidden" name="simpay_tax_amount" value="" class="simpay-tax-amount" />';
		}

		echo $html;
	}

	/**
	 * Print the subscription options
	 *
	 * @param bool $custom_amount If a custom amount is found and should be printed
	 *
	 * @return string
	 */
	public function print_subscription_options( $custom_amount = false ) {

		$html              = '';
		$plan_select_label = simpay_get_saved_meta( $this->id, '_plan_select_form_field_label' );

		if ( 'single' === $this->subscription_type ) {

			if ( $custom_amount ) {
				$html .= $this->print_custom_amount();
			}
		} elseif ( 'user' === $this->subscription_type ) {

			$plans = $this->plans;

			if ( empty( $plans ) ) {
				$html = simpay_admin_error( '<div>' . esc_html__( 'You have not set any plans to choose from.', 'simple-pay' ) . '</div>' );

				$this->printed_subscriptions = true;

				return $html;
			}

			$html .= '<div class="simpay-plan-wrapper simpay-form-control">';

			// Add label
			if ( ! empty( $plan_select_label ) ) {
				$html .= '<div class="simpay-plan-select-label simpay-label-wrap"><label>' . esc_html( $plan_select_label ) . '</label></div>';
			}

			if ( 'radio' === $this->subscription_display_type ) {

				$html .= '<ul class="simpay-multi-plan-radio-group">';

				if ( ! empty( $plans ) && is_array( $plans ) ) {
					foreach ( $plans as $k => $v ) {

						// If $v is not an array skip this one
						if ( ! is_array( $v ) ) {
							continue;
						}

						if ( empty( $this->default_plan ) ) {
							$this->default_plan = $v['select_plan'];
						}

						if ( 'empty' === $v['select_plan'] ) {
							continue;
						}

						if ( isset( $v['plan_object'] ) ) {
							// Use the cached plan object that is set on the form save
							$plan = $v['plan_object'];
						} else {
							// If no cached object is found then revert to calling the Stripe API
							$plan = Stripe_API::request( 'Plan', 'retrieve', $v['select_plan'] );
						}

						if ( ! $plan ) {
							$html .= simpay_admin_error( '<li>' . sprintf( wp_kses( __( 'The plan <strong>%1$s</strong> does not exist.', 'simple-pay' ), array( 'strong' => array() ) ), $v['select_plan'] ) . '</li>' );
							continue;
						}

						// Our plan is good and we can process the rest
						$plan_name           = isset( $plan->nickname ) ? $plan->nickname . ' - ' : '';
						$plan_amount         = simpay_convert_amount_to_dollars( $plan->amount );
						$plan_interval       = $plan->interval;
						$plan_interval_count = $plan->interval_count;
						$is_trial            = $plan->trial_period_days > 0 ? true : false;
						$max_charges         = isset( $v['max_charges'] ) && ! empty( $v['max_charges'] ) ? $v['max_charges'] : 0;

						if ( ! empty( $v['custom_label'] ) ) {
							$label = $v['custom_label'];
						} else {
							$label = $plan_name . sprintf( _n( '%1$s/%3$s', '%1$s every %2$d %3$ss', $plan_interval_count, 'simple-pay' ), simpay_format_currency( $plan_amount, $plan->currency ), $plan_interval_count, $plan_interval );
						}

						$checked = $this->default_plan === $v['select_plan'] ? 'checked' : '';

						if ( 'checked' === $checked ) {
							$this->is_trial = $is_trial;
						}

						$html .= '<li><label><input class="simpay-multi-sub" type="radio" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" value="' . esc_attr( $v['select_plan'] ) . '" data-plan-id="' . esc_attr( $v['select_plan'] ) . '" data-plan-amount="' . floatval( $plan_amount ) . '" data-plan-setup-fee="' . esc_attr( $v['setup_fee'] ) . '" data-plan-interval="' . esc_attr( $plan_interval ) . '" ' . ( $is_trial ? ' data-plan-trial="true" ' : '' ) . ' data-plan-interval-count="' . esc_attr( $plan_interval_count ) . '" ' . $checked . ' data-plan-max-charges="' . absint( $max_charges ) . '" />' . esc_html( apply_filters( 'simpay_plan_name_label', $label, $plan ) ) . '</label></li>';
					}
				}

				if ( $custom_amount ) {

					$html .= '<li><label><input data-plan-setup-fee="0" type="radio" class="simpay-multi-sub simpay-custom-plan-option" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" data-plan-interval="' . esc_attr( $this->subscription_frequency ) . '" data-plan-interval-count="' . esc_attr( $this->subscription_interval ) . '" value="simpay_custom_plan" />' . esc_html( $this->subscription_custom_amount_label ) . '</label>';
					$html .= $this->print_custom_amount();
					$html .= '</li>';
				}

				$html .= '</ul>';

			} elseif ( 'dropdown' === $this->subscription_display_type ) {

				$html .= '<div class="simpay-form-control">';

				$html .= '<select>';

				if ( ! empty( $plans ) && is_array( $plans ) ) {
					foreach ( $plans as $k => $v ) {

						// If $v is not an array we need to skip it
						if ( ! is_array( $v ) ) {
							continue;
						}

						if ( empty( $this->default_plan ) ) {
							$this->default_plan = $v['select_plan'];
						}

						if ( 'empty' === $v['select_plan'] ) {
							continue;
						}

						if ( isset( $v['plan_object'] ) ) {
							// Use the cached plan object that is set on the form save
							$plan = $v['plan_object'];
						} else {
							// If no cached object is found then revert to calling the Stripe API
							$plan = Stripe_API::request( 'Plan', 'retrieve', $v['select_plan'] );
						}

						if ( false === $plan ) {
							$html .= simpay_admin_error( '<li>' . sprintf( wp_kses( __( 'The plan <strong>%1$s</strong> does not exist.', 'simple-pay' ), array( 'strong' => array() ) ), $v['select_plan'] ) . '</li>' );
							continue;
						}

						// Our plan is good and we can process the rest
						$plan_name           = isset( $plan->nickname ) ? $plan->nickname . ' - ' : '';
						$plan_amount         = simpay_convert_amount_to_dollars( $plan->amount );
						$plan_interval       = $plan->interval;
						$plan_interval_count = $plan->interval_count;
						$is_trial            = $plan->trial_period_days > 0 ? true : false;
						$max_charges         = isset( $v['max_charges'] ) && ! empty( $v['max_charges'] ) ? $v['max_charges'] : 0;

						if ( ! empty( $v['custom_label'] ) ) {
							$label = $v['custom_label'];
						} else {
							$label = $plan_name . sprintf( _n( '%1$s/%3$s', '%1$s every %2$d %3$ss', $plan_interval_count, 'simple-pay' ), simpay_format_currency( $plan_amount, $plan->currency ), $plan_interval_count, $plan_interval );
						}

						// This needs to check selected status for dropdown. Bit different than radio
						$selected = $this->default_plan === $v['select_plan'] ? 'selected' : '';

						if ( 'selected' === $selected ) {
							$this->is_trial = $is_trial;
						}

						$html .= '<option class="simpay-multi-sub" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" value="' . esc_attr( $v['select_plan'] ) . '" data-plan-id="' . esc_attr( $v['select_plan'] ) . '" data-plan-amount="' . floatval( $plan_amount ) . '" data-plan-setup-fee="' . esc_attr( $v['setup_fee'] ) . '" ' . ( $is_trial ? ' data-plan-trial="true" ' : '' ) . ' data-plan-interval="' . esc_attr( $plan_interval ) . '" ' . $selected . ' data-plan-max-charges="' . absint( $max_charges ) . '">' . esc_html( apply_filters( 'simpay_plan_name_label', $label, $plan ) ) . '</option>';
					}
				}

				if ( $custom_amount ) {
					$html .= '<option data-plan-setup-fee="0" name="simpay_multi_plan_' . esc_attr( $this->id ) . '" value="simpay_custom_plan" class="simpay-multi-sub simpay-custom-plan-option" data-plan-interval="' . esc_attr( $this->subscription_frequency ) . '" data-plan-interval-count="' . esc_attr( $this->subscription_interval ) . '">' . esc_html( $this->subscription_custom_amount_label ) . '</option>';
				}

				$html .= '</select>';

				$html .= '</div>';

				if ( $custom_amount ) {
					$html .= $this->print_custom_amount();
				}
			}

			$html .= '</div>';

			// Set flag to know we have printed these
			$this->printed_subscriptions = true;
		}

		return $html;

	}

	/**
	 * Print a custom amount field.
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Remove $print_wrapper parameter. Always print wrapper.
	 *
	 * @return string
	 */
	public function print_custom_amount() {

		$html = '';

		// Set default amount, input name, and label based on if this form is a subscription or not.
		if ( $this->is_subscription() ) {
			$min_amount     = $this->subscription_minimum_amount;
			$default_amount = $this->subscription_default_amount;
			$final_amount   = $this->subscription_amount;
			$input_name     = 'simpay_subscription_custom_amount';
			$label          = 'user' !== $this->subscription_type ? simpay_get_saved_meta( $this->id, '_plan_select_form_field_label' ) : '';
		} else {
			$min_amount     = $this->minimum_amount;
			$default_amount = $this->default_amount;
			$final_amount   = $this->amount;
			$input_name     = 'simpay_custom_amount';
			$label          = $this->custom_amount_label;
		}

		if ( $default_amount >= $min_amount ) {

			// Format custom amount input value with thousands & decimal separators, but not symbol.
			$custom_amount_input_value = simpay_format_currency( $final_amount, '', false );
		} else {
			// If default amount is less than minimum, then simply leave blank.
			$custom_amount_input_value = '';
		}

		// outer wrap div
		$html .= '<div class="simpay-form-control simpay-custom-amount-container">';

		$field_id = esc_attr( simpay_dashify( $input_name ) ) . '-' . $this->id;

		// Label
		$html .= '<div class="simpay-custom-amount-label simpay-label-wrap">';
		$html .= '<label for="' . esc_attr( $field_id ) . '">' . esc_html( $label ) . '</label>';
		$html .= '</div>';

		// Currency symbol placement & html
		$currency_symbol_placement = ( 'left' === $this->currency_position || 'left_space' === $this->currency_position ) ? 'left' : 'right';
		$currency_symbol_html      = '<span class="simpay-currency-symbol simpay-currency-symbol-' . $currency_symbol_placement . '">' . simpay_get_currency_symbol( $this->currency ) . '</span>';

		// Field output
		$html .= '<div class="simpay-custom-amount-wrap simpay-field-wrap">';

		if ( 'left' === $currency_symbol_placement ) {
			$html .= $currency_symbol_html;
		}

		// TODO Test custom input on mobile

		// Filter to allow changing to "number" input type.
		// "tel" input type brings up number pad but does not allow decimal entry on mobile browsers.
		$custom_amount_input_type = apply_filters( 'simpay_custom_amount_field_type', 'tel' );
		$custom_amount_input_type = ( $custom_amount_input_type !== 'tel' && $custom_amount_input_type !== 'number' ) ? 'tel' : $custom_amount_input_type;

		// Can add additional form tag attributes here using a filter.
		// If type="number", automatically add step="0.01" attribute.
		$custom_amount_input_atts = '';

		if ( $custom_amount_input_type === 'number' ) {
			$custom_amount_input_atts = 'step="0.01"';
		}

		$custom_amount_input_atts = apply_filters( 'simpay_custom_amount_input_attributes', $custom_amount_input_atts );

		$html .= '<input id="' . $field_id . '" name="' . esc_attr( $input_name ) . '" class="simpay-amount-input simpay-custom-amount-input simpay-custom-amount-input-symbol-' . $currency_symbol_placement . '" type="' . esc_attr( $custom_amount_input_type ) . '" value="' . esc_attr( $custom_amount_input_value ) . '" ' . $custom_amount_input_atts . ' />';

		// If this is a subscription then add a field we can keep track of the custom amount selection
		if ( $this->is_subscription() ) {
			$html .= '<input type="hidden" name="simpay_has_custom_plan" class="simpay-has-custom-plan" value="' . ( 'single' === $this->subscription_type ? 'true' : '' ) . '" />';
		}

		if ( 'right' === $currency_symbol_placement ) {
			$html .= $currency_symbol_html;
		}

		$html .= '</div>';

		$html .= '</div>';

		// Set flag so we know this was already printed
		$this->printed_custom_amount = true;

		return $html;

	}

	/**
	 * Print out the custom fields.
	 *
	 * @return string
	 */
	public function get_custom_fields_html( $html, $form ) {

		if ( ! empty( $form->custom_fields ) && is_array( $form->custom_fields ) ) {

			foreach ( $form->custom_fields as $k => $item ) {

				// @todo Use a registry.
				switch ( $item['type'] ) {

					case 'customer_name':
						$html .= Fields\Customer_Name::html( $item, 'customer-name', $form );
						break;

					case 'email':
						$html .= Fields\Email::html( $item, 'email', $form );
						break;

					case 'telephone':
						$html .= Fields\Telephone::html( $item, 'telephone', $form );
						break;

					case 'card':
						$html .= Fields\Card::html( $item );
						break;

					case 'address':
						$html .= Fields\Address::html( $item, 'address', $form );
						break;

					case 'checkbox':
						$html .= Fields\Checkbox::html( $item, 'checkbox', $form );
						break;

					case 'coupon':
						$html .= Fields\Coupon::html( $item, 'coupon', $form );
						break;

					case 'date':
						$html .= Fields\Date::html( $item, 'date', $form );
						break;

					case 'dropdown':
						$html .= Fields\Dropdown::html( $item, 'dropdown', $form );
						break;

					case 'number':
						$html .= Fields\Number::html( $item, 'number', $form );
						break;

					case 'radio':
						$html .= Fields\Radio::html( $item, 'radio', $form );
						break;

					case 'custom_amount':
						if ( $this->is_one_time_custom_amount ) {
							$html .= $this->print_custom_amount();
						}
						break;

					case 'plan_select':
						if ( $this->is_subscription() ) {
							$html .= $this->print_subscription_options( $this->has_subscription_custom_amount );
							Fields\Total_Amount::set_recurring_total( $this->recurring_total_amount );
						}
						break;

					case 'total_amount':
						Fields\Total_Amount::set_tax_amount( $this->tax_amount );

						// Set to subscription fee only if trial and not custom amount.
						if ( $this->is_trial && ! $this->is_one_time_custom_amount ) {
							Fields\Total_Amount::set_total( $this->subscription_setup_fee );
						} else {
							Fields\Total_Amount::set_total( $this->total_amount );
						}

						$html .= Fields\Total_Amount::html( $item );
						break;

					case 'text':
						$html .= Fields\Text::html( $item, 'text', $form );
						break;

					case 'hidden':
						$html .= Fields\Hidden::html( $item, 'hidden', $form );
						break;

					case 'recurring_amount_toggle':
						$html .= Fields\Recurring_Amount_Toggle::html( $item );
						break;

					case 'checkout_button':
						// TODO Need to use set_total like 'total_amount' case?
						$html .= Fields\Checkout_Button::html( $item );
						break;

					case 'payment_button':
						if ( 'overlay' !== $this->get_form_display_type() ) {
							$html .= \SimplePay\Core\Forms\Fields\Payment_Button::html( $item );
						}
						break;

					case 'payment_request_button':
						$html .= Fields\Payment_Request_Button::html( $item );
						break;

					default:
						$html .= apply_filters( 'simpay_custom_field_html_for_non_native_fields', '', $item, $form );
						break;
				}
			}
		}

		return $html;
	}

	/**
	 * Set the global settings options to the form attributes.
	 *
	 * @since unknown
	 */
	public function pro_set_global_settings() {
		// Tax percentage.
		$tax_percent       = floatval( simpay_get_global_setting( 'tax_percent' ) );
		$this->tax_percent = simpay_get_filtered( 'tax_percent', $tax_percent, $this->id );

		// Date format.
		$date_format       = simpay_get_date_format();
		$this->date_format = simpay_get_filtered( 'date_format', $date_format, $this->id );

		// Stripe Elements locale.
		$elements_locale       = simpay_get_filtered( 'elements_locale', simpay_get_global_setting( 'elements_locale' ), $this->id );
		$this->elements_locale = $elements_locale ? $elements_locale : 'auto';
	}

	/**
	 * Set the form settings options to the form attributes.
	 *
	 * @since unknown
	 */
	public function pro_set_post_meta_settings() {
		// Custom Payment Form fields.
		$custom_fields       = Custom_Fields::get_fields( $this->id );
		$this->custom_fields = Custom_Fields::sort( $custom_fields );

		// Subscription type.
		$subscription_type       = simpay_get_saved_meta( $this->id, '_subscription_type' );
		$this->subscription_type = simpay_get_filtered( 'subscription_type', $subscription_type, $this->id );

		// Shim a few properties that are referenced later without checking existence.
		// @todo Update implementation of these properties to check validitiy.
		$this->is_trial                       = false;
		$this->has_subscription_custom_amount = false;
		$this->tax_amount                     = 0;
		$this->subscription_setup_fee         = 0;
		$this->subscription_minimum_amount    = 0;
		$this->subscription_interval          = 0;
		$this->subscription_frequency         = 0;

		if ( $this->is_subscription() ) {

			//
			// Subscription-related properties.
			//

			// Reset base amount so it's not included in calculations.
			$this->amount = 0;

			// Multi-plan list and selected default.
			if ( 'user' === $this->subscription_type ) {
				$saved_plan_list = simpay_get_saved_meta( $this->id, '_multi_plan' );
				$this->plans     = simpay_get_filtered( 'plans', $saved_plan_list, $this->id );

				$default_plan       = simpay_get_saved_meta( $this->id, '_multi_plan_default_value' );
				$this->default_plan = simpay_get_filtered( 'default_plan', $default_plan, $this->id );

				$multi_plan_display_style        = simpay_get_saved_meta( $this->id, '_multi_plan_display' );
				$this->subscription_display_type = simpay_get_filtered( 'subscription_display_type', $multi_plan_display_style, $this->id );
			} else {
				// Single Plan ID.
				$single_plan_id    = simpay_get_saved_meta( $this->id, '_single_plan' );
				$this->single_plan = simpay_get_filtered( 'single_plan', $single_plan_id, $this->id );
			}

			// Subscription default amount.
			$subscription_default_amount       = simpay_get_saved_meta( $this->id, '_multi_plan_default_amount' );
			$this->subscription_default_amount = simpay_unformat_currency(
				simpay_get_filtered( 'subscription_default_amount', $subscription_default_amount, $this->id )
			);

			// Subscription minimum amount.
			$subscription_minimum_amount       = simpay_get_saved_meta( $this->id, '_multi_plan_minimum_amount' );
			$subscription_minimum_amount       = simpay_get_filtered( 'subscription_minimum_amount', $subscription_minimum_amount, $this->id );
			$this->subscription_minimum_amount = simpay_unformat_currency( $subscription_minimum_amount );
			// Added so the property is defined.
			$this->minimum_amount = simpay_unformat_currency( $subscription_minimum_amount );

			// Subscription interval count.
			$subscription_interval_count = intval( simpay_get_saved_meta( $this->id, '_plan_interval' ) );
			$this->subscription_interval = simpay_get_filtered( 'subscription_interval', $subscription_interval_count, $this->id );

			// Subscription interval frequency (day, month, year.
			$subscription_interval_frequency = simpay_get_saved_meta( $this->id, '_plan_frequency' );
			$this->subscription_frequency    = simpay_get_filtered( 'subscription_frequency', $subscription_interval_frequency, $this->id );

			// Subscription has "Custom Amount" enabled.
			$subscription_has_custom_amount       = simpay_get_saved_meta( $this->id, '_subscription_custom_amount' );
			$subscription_has_custom_amount       = simpay_get_filtered( 'subscription_custom_amount', $subscription_has_custom_amount, $this->id );
			$this->has_subscription_custom_amount = ( 'enabled' === $subscription_has_custom_amount || true === $subscription_has_custom_amount );

			// Subscription"Custom Amount" label.
			$subscription_custom_amount_label_default = esc_html__( 'Other amount', 'simple-pay' );
			$subscription_custom_amount_label         = simpay_get_saved_meta( $this->id, '_custom_plan_label', $subscription_custom_amount_label_default );
			$this->subscription_custom_amount_label   = simpay_get_filtered( 'subscription_custom_amount_label', $subscription_custom_amount_label, $this->id );

			// Subscription "Initial Setup Fee". (When "Custom Amount" is enabled.)
			$subscription_setup_fee       = simpay_get_saved_meta( $this->id, '_setup_fee' );
			$this->subscription_setup_fee = simpay_unformat_currency(
				simpay_get_filtered( 'subscription_setup_fee', $subscription_setup_fee, $this->id )
			);

			// Subscription "Max Charges" (When "Custom Amount" is enabled.)
			$subscription_max_charges       = simpay_get_saved_meta( $this->id, '_max_charges', 0 );
			$this->subscription_max_charges = simpay_get_filtered( 'subscription_max_charges', $subscription_max_charges, $this->id );

			if ( $this->subscription_max_charges > 0 ) {
				$this->has_max_charges = true;
			}

			// Subscription amount.
			$subscription_amount = 0;

			if ( 'single' === $this->subscription_type ) {
				// When a custom amount is the only choice for a single subscription,
				// try setting the base amount to the default amount, then minimum amount if none.
				if ( $this->has_subscription_custom_amount ) {
					if ( $this->subscription_default_amount > $this->subscription_minimum_amount ) {
						$this->subscription_amount = $this->subscription_default_amount;
					} else {
						$this->subscription_amount = $this->subscription_minimum_amount;
					}
				} else {
					if ( false !== $this->single_plan && 'empty' !== $this->single_plan ) {
						try {
							$plan = Stripe_API::request( 'Plan', 'retrieve', $this->single_plan );

							$this->subscription_amount    = simpay_convert_amount_to_dollars( $plan->amount );
							$this->amount                 = $this->subscription_amount;
							$this->is_trial               = $plan->trial_period_days > 0;
							$this->subscription_frequency = $plan->interval;
							$this->subscription_interval  = $plan->interval_count;
						} catch ( \Exception $e ) {
							$this->subscription_amount = 0;
						}
					}
				}
			} else {
				try {
					// If a non-custom subscription amount, retrieve the saved value from the selected plan.
					if ( false !== $this->default_plan && 'empty' !== $this->default_plan ) {
						$plan = Stripe_API::request( 'Plan', 'retrieve', $this->default_plan );
					} else {
						$plan = Stripe_API::request( 'Plan', 'retrieve', $this->plans[0]['plan_object'] );
					}

					$this->subscription_amount = simpay_convert_amount_to_dollars( $plan->amount );
				} catch ( \Exception $e ) {
					$this->subscription_amount = 0;
				}
			}

			// Subscription tax amount.
			$this->recurring_tax_amount = simpay_calculate_tax_amount( $this->subscription_amount );

			// Subscription total (amount + tax).
			$this->recurring_total_amount = $this->subscription_amount + $this->recurring_tax_amount;

		} else {

			//
			// Single payment-related properties.
			//

			// Amount type (One-time or One-time custom).
			$amount_type       = simpay_get_saved_meta( $this->id, '_amount_type' );
			$this->amount_type = simpay_get_filtered( 'amount_type', $amount_type, $this->id );

			$is_one_time_custom_amount       = 'one_time_custom' === $this->amount_type;
			$this->is_one_time_custom_amount = simpay_get_filtered( 'one_time_custom_amount', $is_one_time_custom_amount, $this->id );

			// Default amount.
			$default_amount       = simpay_get_saved_meta( $this->id, '_custom_amount_default' );
			$this->default_amount = simpay_unformat_currency( simpay_get_filtered( '_default_amount', $default_amount, $this->id ) );

			// Minimum amount.
			$minimum_amount       = simpay_get_saved_meta( $this->id, '_minimum_amount' );
			$this->minimum_amount = simpay_unformat_currency(
				simpay_get_filtered( 'minimum_amount', $minimum_amount, $this->id )
			);

			$custom_amount_label       = simpay_get_saved_meta( $this->id, '_custom_amount_label' );
			$this->custom_amount_label = simpay_get_filtered( 'custom_amount_label', $custom_amount_label, $this->id );

			if ( $this->is_one_time_custom_amount ) {
				// For custom amount, try setting the base amount to the default amount, then minimum amount if none.
				if ( $this->default_amount > $this->minimum_amount ) {
					$this->amount = $this->default_amount;
				} else {
					$this->amount = $this->minimum_amount;
				}
			} else {
				// If a non-custom one-time payment amount, retrieve the saved value.
				$amount       = simpay_get_saved_meta( $this->id, '_amount', simpay_global_minimum_amount() );
				$this->amount = simpay_unformat_currency( simpay_get_filtered( 'amount', $amount, $this->id ) );
			}
		}

		// Recurring Amount Toggle" "Frequency".
		$recurring_amount_toggle_frequency       = $this->extract_custom_field_setting( 'recurring_amount_toggle', 'plan_frequency', 'month' );
		$this->recurring_amount_toggle_frequency = $recurring_amount_toggle_frequency;

		// Recurring Amount Toggle" "Interval".
		$recurring_amount_toggle_interval       = $this->extract_custom_field_setting( 'recurring_amount_toggle', 'plan_interval', 1 );
		$this->recurring_amount_toggle_interval = absint( $recurring_amount_toggle_interval );

		// Recurring Amount Toggle" "Max Charges".
		$recurring_amount_toggle_max_charges       = $this->extract_custom_field_setting( 'recurring_amount_toggle', 'max_charges', 0 );
		$this->recurring_amount_toggle_max_charges = $recurring_amount_toggle_max_charges;

		// Optional fee.
		//
		// Not UI is provided for these, but they can be set via filters.
		$this->fee_percent = floatval( simpay_get_filtered( 'fee_percent', 0, $this->id ) );
		$this->fee_amount  = simpay_unformat_currency(
			simpay_get_filtered( 'fee_amount', 0, $this->id )
		);
	}

	/**
	 * Extract the value from a custom field setting if it exists
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param string $field_type Custom Field type.
	 * @param string $setting Custom field setting.
	 * @param string $default Default setting value.
	 * @return mixed
	 */
	public function extract_custom_field_setting( $field_type, $setting, $default = '' ) {
		$custom_fields = Custom_Fields::get_fields( $this->id );

		return Custom_Fields::extract_setting( $custom_fields, $field_type, $setting, $default );
	}

	/**
	 * Sort a list of custom fields.
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param array $custom_fields Custom fields to sort.
	 * @return array
	 */
	public function sort_fields( $custom_fields ) {
		return Custom_Fields::sort( $custom_fields );
	}

	/**
	 * Check if this form has subscriptions enabled or not.
	 *
	 * @since unknown
	 *
	 * @return bool
	 */
	public function is_subscription() {
		return ( 'disabled' !== $this->subscription_type && ! empty( $this->subscription_type ) ? true : false );
	}

	/**
	 * Place to set our script variables for this form.
	 *
	 * @return array
	 */
	public function pro_get_form_script_variables( $arr, $id ) {

		/**
		 * @todo Use `$this->extract_custom_field_setting`
		 *
		 * Not switching now, because I'm not confident $this->custom_fields is always correctly accessed.
		 *
		 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/860
		 */
		$custom_fields = simpay_get_saved_meta( $this->id, '_custom_fields' );

		$checkout_text         = __( 'Pay {{amount}}', 'simple-pay' );
		$checkout_loading_text = __( 'Please Wait...', 'simple-pay' );

		// Checkout Button (Embed + Overlay)
		if ( isset( $custom_fields['checkout_button'] ) && is_array( $custom_fields['checkout_button'] ) ) {
			// There can only be one Checkout Button, but it's saved in an array.
			$checkout_button = current( $custom_fields['checkout_button'] );

			// Base.
			if ( ! empty( $checkout_button['text'] ) ) {
				$checkout_text = $checkout_button['text'];
			}

			// Processing.
			if ( ! empty( $checkout_button['processing_text'] ) ) {
				$checkout_loading_text = $checkout_button['processing_text'];
			}
		}

		// Determine if Customer fields are being used.
		$has_customer_fields = (
			array_key_exists( 'customer_name', $custom_fields ) ||
			array_key_exists( 'email', $custom_fields ) ||
			array_key_exists( 'telephone', $custom_fields ) ||
			array_key_exists( 'address', $custom_fields ) ||
			array_key_exists( 'coupon', $custom_fields )
		);

		$form_arr = $arr[ $id ]['form'];

		$bools['bools'] = array_merge(
			isset( $form_arr['bools'] ) ? $form_arr['bools'] : array(),
			array(
				'isSubscription'          => $this->is_subscription(),
				'isTrial'                 => $this->is_trial,
				'hasCustomerFields'       => $has_customer_fields,
				'hasPaymentRequestButton' => isset( $custom_fields['payment_request_button'] ) ? array(
					'id'                => simpay_dashify( $custom_fields['payment_request_button'][0]['id'] ),
					'type'              => isset( $custom_fields['payment_request_button'][0]['type'] ) ? $custom_fields['payment_request_button'][0]['type'] : 'default',
					'requestPayerName'  => isset( $custom_fields['customer_name'] ) && isset( $custom_fields['customer_name'][0]['required'] ),
					'requestPayerEmail' => isset( $custom_fields['email'] ),
					// There can technically be two address fields.
					// @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/531
					// @todo Or switch to $this->enable_shipping_address when it returns the correct value (currently incorrect).
					'requestShipping'   => isset( $custom_fields['address'] ) && isset( $custom_fields['address'][0]['collect-shipping'] ),

					/**
					 * Filter shipping options presented in the Payment Request API.
					 *
					 * Note: The `amount` key is not used to calculate the payment total and these options
					 * are only present to satisfy the Stripe API when collecting a shipping address.
					 *
					 * @since 3.4.0
					 *
					 * @param array $shipping_options Shipping options.
					 */
					'shippingOptions'   => apply_filters(
						'simpay_payment_request_button_shipping_options',
						array(
							array(
								'id'     => '0',
								'label'  => _x( 'Default', 'payment request button shipping option label', 'simple-pay' ),
								'amount' => 0,
							),
						)
					),
					'i18n'              => array(
						'planLabel'     => _x( 'Subscription', 'payment request single subscription label', 'simple-pay' ),
						'totalLabel'    => _x( 'Total', 'payment request button total label', 'simple-pay' ),
						'taxLabel'      => _x( 'Tax: %s%', 'payment request button total label', 'simple-pay' ),
						'couponLabel'   => _x( 'Coupon: %s', 'payment request button total label', 'simple-pay' ),
						'setupFeeLabel' => _x( 'Setup Fee', 'payment request button total label', 'simple-pay' ),
					),
				) : false,
			)
		);

		$integers['integers'] = array_merge(
			isset( $form_arr['integers'] ) ? $form_arr['integers'] : array(),
			array(
				'setupFee'          => $this->subscription_setup_fee,
				'minAmount'         => $this->minimum_amount,
				'totalAmount'       => $this->total_amount,
				'subMinAmount'      => $this->subscription_minimum_amount,
				'planIntervalCount' => $this->subscription_interval,
				'taxPercent'        => $this->tax_percent,
				'feePercent'        => $this->fee_percent,
				'feeAmount'         => $this->fee_amount,
			)
		);

		$strings['strings'] = array_merge(
			isset( $form_arr['strings'] ) ? $form_arr['strings'] : array(),
			array(
				'subscriptionType'          => $this->subscription_type,
				'planInterval'              => $this->subscription_frequency,
				'checkoutButtonText'        => esc_html( $checkout_text ),
				'checkoutButtonLoadingText' => esc_html( $checkout_loading_text ),
				'dateFormat'                => $this->date_format,
				'formDisplayType'           => $this->get_form_display_type(),
			)
		);

		$i18n['i18n'] = array_merge(
			isset( $form_arr['i18n'] ) ? $form_arr['i18n'] : array(),
			array(
				/* translators: message displayed on front-end for amount below minimum amount for one-time payment custom amount field */
				'minCustomAmountError'    => sprintf( esc_html__( 'The minimum amount allowed is %s', 'simple-pay' ), simpay_format_currency( $this->minimum_amount ) ),
				/* translators: message displayed on front-end for amount below minimum amount for subscription custom amount field */
				'subMinCustomAmountError' => sprintf( esc_html__( 'The minimum amount allowed is %s', 'simple-pay' ), simpay_format_currency( $this->subscription_minimum_amount ) ),
			)
		);

		// Add Elements locale.
		if ( isset( $arr[ $id ]['stripe'] ) ) {
			$arr[ $id ]['stripe']['strings']['elementsLocale'] = $this->elements_locale;
		}

		$form_arr = array_merge( $form_arr, $integers, $strings, $bools, $i18n );

		$arr[ $id ]['form'] = $form_arr;

		return $arr;
	}

	/**
	 * Retrieve the form display type.
	 *
	 * @since unknown
	 *
	 * @return string
	 */
	public function get_form_display_type() {
		return simpay_get_saved_meta( $this->id, '_form_display_type', 'embedded' );
	}
}
