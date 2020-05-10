/**
 * Update all labels.
 *
 * @param {Event} e Change event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function update( e, spFormElem, formData ) {
	totalAmount( spFormElem, formData );
	recurringAmount( spFormElem, formData );
	taxAmount( spFormElem, formData );
}

/**
 * Update "Total Amount" label, and Submit Button label.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function totalAmount( spFormElem, formData ) {
	const {
		convertToDollars,
		formatCurrency,
	} = window.spShared;

	const {
		cart,
	} = spFormElem;

	const total = convertToDollars( cart.getTotal() );
	const formatted = formatCurrency( total, true );

	// @todo Remove and run elsewhere.
	window.simpayApp.setCoreFinalAmount( spFormElem, formData );

	spFormElem.find( '.simpay-total-amount-value' ).text( formatted );
}

/**
 * Update "Recurring Amount" label.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function recurringAmount( spFormElem, formData ) {
	const {
		convertToDollars,
		formatCurrency,
	} = window.spShared;

	const {
		cart,
	} = spFormElem;

	try {
		const item = cart.getLineItem( 'base' );

		const {
			subscription: {
				interval,
				intervalCount,
			},
		} = item;

		const recurringAmountFinal = convertToDollars( item.getTotal() );

		let recurringAmountFormatted = formatCurrency( recurringAmountFinal, true );

		if ( intervalCount > 1 ) {
			recurringAmountFormatted += ' every ' + intervalCount + ' ' + interval + 's';
		} else {
			recurringAmountFormatted += '/' + interval;
		}

		spFormElem.find( '.simpay-total-amount-recurring-value' ).text( recurringAmountFormatted );
	} catch ( error ) {
		// Fallback to cart total.
		const cartTotal = formatCurrency( convertToDollars( cart.getTotal() ), true );

		return spFormElem.find( '.simpay-total-amount-recurring-value' ).text( cartTotal );
	}
}

/**
 * Update "Tax Amount" label.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function taxAmount( spFormElem, formData ) {
	const {
		convertToDollars,
		formatCurrency,
	} = window.spShared;

	const {
		cart,
	} = spFormElem;

	const taxAmount = convertToDollars( cart.getTax() );
	const formatted = formatCurrency( taxAmount, true );

	spFormElem.find( '.simpay-tax-amount-value' ).text( formatted );
}
