/**
 * Internal dependencies.
 */
import { apply as applyCoupon } from './coupon.js';
import { update as updateTotalAmountLabels } from './total-amount-labels.js';

export function update( e, spFormElem, formData ) {
	let quantity = 1;

	// Backwards compatibility.
	formData.quantity = quantity;

	const cart = spFormElem.cart;

	if ( spFormElem.find( '.simpay-quantity-dropdown' ).length ) {
		quantity = parseFloat( spFormElem.find( '.simpay-quantity-dropdown' ).find( 'option:selected' ).data( 'quantity' ) );

		spFormElem.trigger( 'simpayDropdownQuantityChange' );
	} else if ( spFormElem.find( '.simpay-quantity-radio' ).length ) {
		quantity = parseFloat( spFormElem.find( '.simpay-quantity-radio' ).find( 'input[type="radio"]:checked' ).data( 'quantity' ) );

		spFormElem.trigger( 'simpayRadioQuantityChange' );
	} else if ( spFormElem.find( '.simpay-quantity-input' ).length ) {
		quantity = parseFloat( spFormElem.find( '.simpay-quantity-input' ).val() );

		spFormElem.trigger( 'simpayNumberQuantityChange' );
	}

	if ( quantity < 1 ) {
		quantity = 1;
	}

	// Set cart base item quantity.
	try {
		const item = spFormElem.cart.getLineItem( 'base' );

		item.update( {
			quantity,
		} );

		// Backwards compatibility.
		formData.quantity = quantity;

		// Update hidden quantity field.
		spFormElem.find( '.simpay-quantity' ).val( quantity );

		// Alert the rest of the components they need to update.
		spFormElem.trigger( 'totalChanged', [ spFormElem, formData ] );
	} catch ( error ) {
		// Error is logged, UI does not need updating.
	}
}
