/**
 * Update the form amount based on "Amount" field selected value.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function update( e, spFormElem, formData ) {
	let amount;

	const {
		convertToCents,
	} = window.spShared;

	// Update the amount to the selected dropdown amount
	if ( 0 !== spFormElem.find( '.simpay-amount-dropdown' ).length ) {
		amount = spFormElem.find( '.simpay-amount-dropdown' ).find( 'option:selected' ).data( 'amount' );

		spFormElem.trigger( 'simpayDropdownAmountChange' );
	// Update the amount to the selected radio button
	} else if ( 0 !== spFormElem.find( '.simpay-amount-radio' ).length ) {
		amount = spFormElem.find( '.simpay-amount-radio' ).find( 'input[type="radio"]:checked' ).data( 'amount' );

		spFormElem.trigger( 'simpayRadioAmountChange' );
	}

	if ( amount > 0 ) {
		try {
			const item = spFormElem.cart.getLineItem( 'base' );

			item.update( {
				amount: convertToCents( amount ),
			} );

			// Backwards compatibility.
			formData.amount = amount;

			// Alert the rest of the components they need to update.
			spFormElem.trigger( 'totalChanged', [ spFormElem, formData ] );
		} catch ( error ) {
			// Error is logged, UI does not need updating.
		}
	}
}
