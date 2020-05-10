/* globals _ */

/**
 * Internal dependencies.
 */
import { setup as setupDateField } from './components/date.js';
import { apply as applyCoupon, remove as removeCoupon } from './components/coupon.js';
import { update as updateAmountField } from './components/amount.js';
import { update as updateQuantiyField } from './components/quantity.js';
import { update as updateTotalAmountLabels } from './components/total-amount-labels.js';
import { update as updateMultiSubSelection } from './components/multi-sub.js';
import { toggleShippingAddressFields } from './components/address.js';
import { toggle as toggleOverlayForm } from './payment-forms/stripe-elements/overlays.js';
import {
	update as updateCustomAmount,
	enableCustomPlanAmount
} from './components/custom-amount.js';
import { 
	setup as enablePaymentRequestButton,
	update as updatePaymentRequestButton,
	getDisplayItems as getPaymentRequestDisplayItems,
	paymentRequestIsValid
} from './components/payment-request-button.js';

const doNothingFuncs = [
	'setupStripeElementsForm',
	'createCard',
	'getCardConfig',
	'handleCardFocus',
	'focusFirstField',
	'beforeSubmitPayment',
];

/**
 * Shim object properties for backwards compatibility.
 *
 * Some of these will do nothing, but it will help diagnose
 * issues for customers who have improperly referenced them in the past.
 */
export default _.extend(
	_.reduce( doNothingFuncs, ( memo, func ) => {
		memo[ func ] = ( func ) => {
			return window.spShared.debugLog( 'Deprecated:', `${ func } is no longer used.` );
		};

		return memo;
	}, {} ),
	{
		isCustomAmountFieldValid: ( spFormElem, formData ) => {
			return updateCustomAmount( null, spFormElem, formData );
		},

		initDateField: ( spFormElem ) => {
			return setupDateField( null, spFormElem, null );
		},

		handleCustomAmountFocusIn: ( spFormElem, formData ) => {
			return enableCustomPlanAmount( null, spFormElem, formData );
		},

		processCustomAmount: ( spFormElem, formData ) => {
			return updateCustomAmount( null, spFormElem, formData );
		},

		updateAmountSelect: ( spFormElem, formData ) => {
			return updateAmountField( null, spFormElem, formData );
		},

		updateQuantitySelect: ( spFormElem, formData ) => {
			return updateQuantityField( null, spFormElem, formData );
		},

		updateTotalAmountLabel: ( spFormElem, formData ) => {
			return updateTotalAmountLabels( null, spFormElem, formData );
		},

		updateRecurringAmountLabel: ( spFormElem, formData ) => {
			return updateTotalAmountLabels( null, spFormElem, formData );
		},

		updateTaxAmountLabel: ( spFormElem, formData ) => {
			return updateTotalAmountLabels( null, spFormElem, formData );
		},

		changeMultiSubAmount: ( spFormElem, formData ) => {
			return updateMultiSubSelection( null, spFormElem, formData );
		},

		enablePaymentRequestButton: ( spFormElem, formData ) => {
			return enablePaymentRequestButton( null, spFormElem, formData );
		},

		bindProFormEventsAndTriggers: ( e, spFormElem, formData ) => {
			return window.simpayAppPro.bindEvents( e, spFormElem, formData );
		},

		handleFieldFocus: ( spFormElem ) => {
			return window.simpayAppPro.setOnFieldFocus( spFormElem );
		},

		enableForm: ( spFormElem, formData ) => {
			return window.simpayApp.enableForm( spFormElem, formData );
		},

		disableForm: ( spFormElem, formData, setSubmitButtonAsLoading = false ) => {
			return window.simpayApp.disableForm( spFormElem, formData, setSubmitButtonAsLoading );
		},

		triggerBrowserValidation: ( spFormElem, formData ) => {
			return window.simpayApp.triggerBrowserValidation( spFormElem, formData );
		},

		setProFinalAmount: ( e, spFormElem, formData ) => {
			return window.simpayAppPro.updateAmounts( e, spFormElem, formData );
		},

		/**
		 * Calculate the amount of tax given an amount and percentage.
		 *
		 * @since 3.0
		 * @since 3.7.0 Deprecated.
		 *
		 * @param {number} amount Base amount.
		 * @param {number} percent Tax percent.
		 */
		calculateTaxAmount( amount, percent ) {
			const {
				debugLog,
			} = window.spShared;

			debugLog( 'calculateTaxAmount', 'Use spFormElem.cart to access cart tax calculations' );

			return Math.abs( accounting.toFixed( amount * ( percent / 100 ), window.spGeneral.integers.decimalPlaces ) );
		},

		updatePaymentRequestButton,
		paymentRequestIsValid,
		getPaymentRequestDisplayItems,
		toggleShippingAddressFields,
		applyCoupon,
		removeCoupon,
		toggleOverlayForm,
	}
);
