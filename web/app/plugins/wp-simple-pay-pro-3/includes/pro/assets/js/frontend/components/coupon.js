/* global jQuery */

/**
 * Internal dependencies.
 */
import { update as updateTotalAmountLabels } from './total-amount-labels.js';

/**
 * Apply a coupon.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function apply( spFormElem, formData ) {
	const $ = jQuery;
	const couponField = spFormElem.find( '.simpay-coupon-field' );
	const responseContainer = spFormElem.find( '.simpay-coupon-message' );
	const loadingImage = spFormElem.find( '.simpay-coupon-loading' );
	const removeCoupon = spFormElem.find( '.simpay-remove-coupon' );
	const hiddenCouponElem = spFormElem.find( '.simpay-coupon' );

	const {
		convertToCents,
		debugLog,
	} = window.spShared;

	let coupon = '';
	let couponMessage = '';

	if ( ! couponField.val() && ! formData.couponCode ) {
		return;
	} else if ( formData.couponCode ) {
		coupon = formData.couponCode;
	} else {
		coupon = couponField.val();
	}

	// AJAX params
	const data = {
		coupon,
		amount: spFormElem.cart.getSubtotal(),
		action: 'simpay_get_coupon',
		form_id: formData.formId,
		couponNonce: spFormElem.find( '#simpay_coupon_nonce' ).val(),
	};

	// Clear the response container and hide the remove coupon link
	responseContainer.text( '' );
	removeCoupon.hide();

	// Clear textbox
	couponField.val( '' );

	// Show the loading image
	loadingImage.show();

	$.ajax( {
		url: window.spGeneral.strings.ajaxurl,
		method: 'POST',
		data,
		dataType: 'json',
		success( response ) {
			if ( response.success ) {
				// Backwards compatibility.
				formData.couponCode = coupon;

				// Backwards compatibility.
				formData.discount = response.discount;

				// Update the cart.
				try {
					spFormElem.cart.update( {
						coupon: response.stripeCoupon,
					} );
				} catch ( error ) {
					debugLog( error );
				}

				const {
					formatCurrency,
				} = window.spShared;

				// Coupon message for frontend
				couponMessage = response.coupon.code + ': ';

				// Output different text based on the type of coupon it is - amount off or a percentage
				if ( 'percent' === response.coupon.type ) {
					couponMessage += response.coupon.amountOff + spGeneral.i18n.couponPercentOffText;
				} else if ( 'amount' === response.coupon.type ) {
					couponMessage += formatCurrency( response.coupon.amountOff, true ) + ' ' + spGeneral.i18n.couponAmountOffText;
				}

				$( '.coupon-details' ).remove();

				// Update the coupon message text
				responseContainer
					.append( couponMessage )
					.show();

				// Create a hidden input to send our coupon details for Stripe metadata purposes
				$( '<input />', {
					name: 'simpay_coupon_details',
					type: 'hidden',
					value: couponMessage,
					class: 'simpay-coupon-details',
				} ).appendTo( responseContainer );

				// Show remove coupon link
				removeCoupon.show();

				// Add the coupon to our hidden element for processing
				hiddenCouponElem.val( coupon );

				// Hide the loading image
				loadingImage.hide();

				// Trigger custom event when coupon apply done.
				spFormElem.trigger( 'simpayCouponApplied' );
			} else {
				// Show invalid coupon message
				responseContainer
					.show()
					.append( $( '<p />' )
						.addClass( 'simpay-field-error' )
						.text( response.data.error ) );

				// Hide loading image
				loadingImage.hide();
			}
		},
		error( response ) {
			let errorMessage = '';

			const {
				debugLog,
			} = window.spShared;

			debugLog( 'Coupon error', response.responseText );

			if ( response.responseText ) {
				errorMessage = response.responseText;
			}

			// Show invalid coupon message
			responseContainer
				.show()
				.append( $( '<p />' )
					.addClass( 'simpay-field-error' )
					.text( errorMessage ) );

			// Hide loading image
			loadingImage.hide();
		},
		complete( response ) {
			// Alert the rest of the components they need to update.
			// Tell main totalChanged handler not to do anything with coupons.
			spFormElem.trigger( 'totalChanged', [ spFormElem, formData, false ] );
		},
	} );
}

/**
 * Remove a coupon.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function remove( spFormElem, formData ) {
	const {
		debugLog,
	} = window.spShared;

	try {
		spFormElem.cart.update( {
			coupon: false,
		} );

		// Trigger custom event when coupon apply done.
		spFormElem.trigger( 'simpayCouponRemoved' );

		// Alert the rest of the components they need to update.
		// Tell main totalChanged handler not to do anything with coupons.
		spFormElem.trigger( 'totalChanged', [ spFormElem, formData, false ] );
	} catch ( error ) {
		debugLog( error );
	}

	// Backwards compatibility.
	spFormElem.find( '.simpay-coupon-loading' ).hide();
	spFormElem.find( '.simpay-remove-coupon' ).hide();
	spFormElem.find( '.simpay-coupon-message' )
		.text( '' )
		.hide();
	spFormElem.find( '.simpay-coupon' ).val( '' );

	formData.couponCode = '';
	formData.discount = 0;
}
