/* global simplePayForms, spGeneral, jQuery, Stripe */

/**
 * Internal dependencies.
 */
import { default as simpayAppProCompat } from './compat.js';

import { setup as setupPaymentForm } from './payment-forms/stripe-elements';
import { setup as setupOverlayModals } from './payment-forms/stripe-elements/overlays.js';
import { setup as setupDateField } from './components/date.js';

import { update as updateTotalAmountLabels } from './components/total-amount-labels.js';
import { update as updateMultiSubSelection } from './components/multi-sub.js';
import { update as updateQuantityField } from './components/quantity.js';
import { update as updateAmountField } from './components/amount.js';

import { toggleShippingAddressFields } from './components/address.js';

import {
	setup as setupPaymentRequestButtons,
	update as updatePaymentRequestButtons,
} from './components/payment-request-button.js';

import {
	update as updateCustomAmount,
	enableCustomPlanAmount,
} from './components/custom-amount.js';

import {
	apply as applyCoupon,
	remove as removeCoupon,
} from './components/coupon.js';

let simpayAppPro = {};

( function( $ ) {
	'use strict';

	const body = $( document.body );

	/**
	 * Manage additional "Pro" functionality.
	 *
	 * This object mainly serves as a backwards compatibility shim.
	 */
	simpayAppPro = {
		// Manage multiple payment request buttons.
		paymentRequestButtons: {},

		/**
		 * Setup Payment Forms.
		 */
		init() {
			// Let `bindEvents` access other object property functions via `this`.
			this.bindEvents = this.bindEvents.bind( this );

			// Setup the payment form.
			body.on( 'simpayCoreFormVarsInitialized', setupPaymentForm );

			body.on( 'simpayBindCoreFormEventsAndTriggers', setupOverlayModals );
			body.on( 'simpayBindCoreFormEventsAndTriggers', setupDateField );

			// Bind interactions.
			body.on( 'simpayBindCoreFormEventsAndTriggers', this.bindEvents );

			body.on( 'simpayBindCoreFormEventsAndTriggers', updateCustomAmount );
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateQuantityField );
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateAmountField );
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateMultiSubSelection );

			//
			// This is a very important binding, as it eventually comes full circle calling
			// the `simpayFinalizeCoreAmount` trigger, which updates the final amount.
			//
			// This updateTotalAmountLabels also includes the submit button label.
			//
			// 1. updateTotalAmountLabels
			// 2. simpayApp.setCoreFinalAmount
			//      trigger:simpayFinalizeCoreAmount
			// 3. this.updateAmounts
			//
			// To alert of a form value change, `totalChanged` trigger should be fired.
			// This will call `updateTotalAmountLabels` and start the steps above again.
			//
			// The current circular logic remains for backwards compatibility.
			//
			body.on( 'simpayBindCoreFormEventsAndTriggers', updateTotalAmountLabels );
			body.on( 'simpayFinalizeCoreAmount', this.updateAmounts );
		},

		bindEvents( e, spFormElem, formData ) {
			// Toggle focus class for easier styling with CSS.
			this.setOnFieldFocus( spFormElem );

			// Update any components that need to use new total values after change.
			spFormElem.on(
				'totalChanged',
				/**
				 * Runs when the total amount has changed.
				 *
				 * @param {Event} e Event.
				 * @param {jQuery} spFormElem Form element jQuery object.
				 * @param {Object} formData Configured form data.
				 * @param {Bool} _removeCoupon Determines if the coupon should be removed or not. Defaults false.
				 */
				( e, spFormElem, formData, _removeCoupon = true ) => {
					if ( true === _removeCoupon ) {
						removeCoupon( spFormElem, formData );
					}

					updateTotalAmountLabels( e, spFormElem, formData );
					updatePaymentRequestButtons( spFormElem, formData );
				}
			);

			/**
			 * Validate custom field amount before a form is submitted.
			 *
			 * @param {Event} e Event.
			 * @param {jQuery} spFormElem Form element jQuery object.
			 * @param {Object} formData Configured form data.
			 */
			spFormElem.on( 'simpayBeforeStripePayment', ( e, spFormElem, formData ) => {
				// Backwards compatibility.
				// `simpayBeforeStripePayment` should be used directly.
				spFormElem.trigger( 'simpayFormValidationInitialized' );

				const isCustomAmountValid = updateCustomAmount( e, spFormElem, formData, false );

				formData.isValid = isCustomAmountValid;
			} );

			/**
			 * Validate and update amounts when the "Custom Amount" field loses focus.
			 *
			 * @param {Event} e Focusout event.
			 */
			spFormElem.find( '.simpay-custom-amount-input' ).on( 'focusout', ( e ) => updateCustomAmount( e, spFormElem, formData ) );

			/**
			 * Toggle the internal flags that a custom amount is being used for Subscriptions.
			 *
			 * @param {Event} e Focusin event.
			 */
			spFormElem.find( '.simpay-custom-amount-input' ).on( 'focusin', ( e ) => enableCustomPlanAmount( e, spFormElem, formData ) );

			/**
			 * Apply a coupon when the "Apply" button is clicked.
			 *
			 * @param {Event} e Click event.
			 */
			spFormElem.find( '.simpay-apply-coupon' ).on( 'click', ( e ) => {
				e.preventDefault();

				return applyCoupon( spFormElem, formData );
			} );

			/**
			 * Apply a coupon when the "Enter" key is pressed while focusing on the input field.
			 *
			 * @param {Event} e Click event.
			 */
			spFormElem.find( '.simpay-coupon-field' ).on( 'keypress', ( e ) => {
				if ( 13 !== e.which ) {
					return;
				}

				e.preventDefault();

				return applyCoupon( spFormElem, formData );
			} );

			/**
			 * Remove a coupon when the "Remove" button is clicked.
			 *
			 * @param {Event} e Click event.
			 */
			spFormElem.find( '.simpay-remove-coupon' ).on( 'click', ( e ) => {
				e.preventDefault();

				return removeCoupon( spFormElem, formData );
			} );

			/**
			 * Update amounts when a multi-plan subscription form updates.
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( '.simpay-multi-sub, .simpay-plan-wrapper select' ).on( 'change', ( e ) => updateMultiSubSelection( e, spFormElem, formData ) );

			/**
			 * Update amounts when a "Quantity" input changes.
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( '.simpay-quantity-input, .simpay-quantity-dropdown' ).on( 'change', ( e ) => updateQuantityField( e, spFormElem, formData ) );

			/**
			 * Update amounts when an "Amount" input changes.
			 *
			 * @param {Event} e Change
			 */
			spFormElem.find( '.simpay-amount-dropdown, .simpay-amount-radio' ).on( 'change', ( e ) => updateAmountField( e, spFormElem, formData ) );
			/**
			 * Toggle shipping fields when "Same billing & shipping info" is toggled.
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( '.simpay-same-address-toggle' ).on( 'change', ( e ) => toggleShippingAddressFields( spFormElem, formData ) );

			/**
			 * Toggle a recurring charge (generates a Subscription).
			 *
			 * @param {Event} e Change event.
			 */
			spFormElem.find( 'input[name="recurring_amount_toggle"]' ).on( 'change', ( e ) => {
				formData.isRecurring = e.target.checked;
			} );

			// Allow further processing.
			body.trigger( 'simpayBindProFormEventsAndTriggers', [ spFormElem, formData ] );
		},

		/**
		 * Toggle `is-focused` class on fields to allow for extra CSS styling.
		 *
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		setOnFieldFocus( spFormElem, formData ) {
			const fields = spFormElem.find( '.simpay-form-control' );

			fields.each( function( i, el ) {
				const field = $( el );

				field.on( 'focusin', setFocus );
				field.on( 'focusout', removeFocus );

				/**
				 * Add `is-focused` class.
				 *
				 * @param {Event} e Event focusin event.
				 */
				function setFocus( e ) {
					$( e.target ).addClass( 'is-focused' );
				}

				/**
				 * Remove `is-focused` class.
				 *
				 * @param {Event} e Event focusout event.
				 */
				function removeFocus( e ) {
					const $el = $( e.target );

					// Wait for DatePicker plugin
					setTimeout( function() {
						$el.removeClass( 'is-focused' );

						if ( field.val() ) {
							$el.addClass( 'is-filled' );
						} else {
							$el.removeClass( 'is-filled' );
						}
					}, 300 );
				}
			} );
		},

		/**
		 * Calculate payment amounts.
		 *
		 * @param {Event} e Mixed events. Not used.
		 * @param {jQuery} spFormElem Form element jQuery object.
		 * @param {Object} formData Configured form data.
		 */
		updateAmounts( e, spFormElem, formData ) {
			const {
				convertToDollars,
				debugLog,
			} = window.spShared;

			try {
				const {
					cart,
				} = spFormElem;

				const total = cart.getTotal();

				// Backwards compat.
				formData.finalAmount = convertToDollars( total );
				formData.stripeParams.amount = total;

				// Set the same cents value to hidden input for later form submission.
				spFormElem.find( '.simpay-amount' ).val( total );

				// Convert amount to dollars, as the server still expects this.
				spFormElem.find( '.simpay-tax-amount' ).val( convertToDollars( cart.getTax() ) );
			} catch ( error ) {
				debugLog( error );
			}
		},

		...simpayAppProCompat,
	};

	simpayAppPro.init();
}( jQuery ) );

window.simpayAppPro = simpayAppPro;

export default simpayAppPro;
