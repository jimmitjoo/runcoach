/**
 * Internal dependencies.
 */
import { update as updateTotalAmountLabels } from './total-amount-labels.js';

/**
 * @param {Event} e Focusout event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @param {Bool} triggerTotalsChanged Determines if totalsChanged event should fire.
 */
export function update( e, spFormElem, formData, triggerTotalsChanged = true ) {
	const customAmountInput = spFormElem.find( '.simpay-custom-amount-input' );
	const errorEl = spFormElem.find( '.simpay-errors' );

	// Exit if no custom amount field.
	if ( 0 === customAmountInput.length ) {
		return true;
	}

	const {
		convertToCents,
		formatCurrency,
		unformatCurrency,
	} = window.spShared;

	// Update internal traacking.
	const unformattedAmount = customAmountInput.val();

	if ( formData.isSubscription ) {
		formData.customAmount = unformatCurrency( '' !== unformattedAmount ? unformattedAmount : formData.subMinAmount );
		formData.planAmount = formData.customAmount;
		formData.customPlanAmount = formData.planAmount;
	} else {
		formData.customAmount = unformatCurrency( '' !== unformattedAmount ? unformattedAmount : formData.minAmount );
	}

	let minAmount;
	let isValid;

	// Compare amount in cents.
	const customAmountVal = unformatCurrency( customAmountInput.val() );

	// Subscriptions minimum amount requirement is separate from one-time minimum amount requirement.
	if ( formData.isSubscription ) {
		minAmount = unformatCurrency( formData.subMinAmount );
	} else {
		minAmount = unformatCurrency( formData.minAmount );
	}

	// Make sure custom amount meets minimum value.
	// Give does: ( ( -1 < amount ) && ( amount >= min_amount ) )
	isValid = ( ( -1 < customAmountVal ) && ( customAmountVal >= minAmount ) );

	if ( isValid ) {
		errorEl.empty();
		customAmountInput.removeClass( 'simpay-input-error' );
	} else {
		// Set error message.
		if ( formData.isSubscription ) {
			errorEl.html( formData.subMinCustomAmountError );
		} else {
			errorEl.html( formData.minCustomAmountError );
		}

		// Change amount input border color w/ CSS class.
		customAmountInput.addClass( 'simpay-input-error' );
	}

	try {
		const item = spFormElem.cart.getLineItem( 'base' );

		item.update( {
			amount: convertToCents( customAmountVal ),
		} );

		// Backwards compatibility.
		customAmountInput.val( formatCurrency( customAmountVal, false ) );

		// Call this directly in case the trigger is not run.
		updateTotalAmountLabels( e, spFormElem, formData );

		if ( true === triggerTotalsChanged ) {
			// Alert the rest of the components they need to update.
			spFormElem.trigger( 'totalChanged', [ spFormElem, formData ] );
		}

		return isValid;
	} catch {
		// Error is logged, UI does not need updating.
		return false;
	}
}

/**
 * Mark the form as using a custom amount.
 *
 * @param {Event} e Focus event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function enableCustomPlanAmount( e, spFormElem, formData ) {
	const customOption = spFormElem.find( '.simpay-custom-plan-option[value="simpay_custom_plan"]' );

	const {
		convertToCents,
	} = window.spShared;

	customOption
		.prop( 'checked', true )
		.trigger( 'change' );

	formData.useCustomPlan = true;
	formData.customPlanAmount = convertToCents( customOption.val() );

	spFormElem.find( '.simpay-has-custom-plan' ).val( 'true' );
}
