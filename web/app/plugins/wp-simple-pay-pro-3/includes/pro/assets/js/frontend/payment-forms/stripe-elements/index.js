/* global jQuery */

/**
 * Internal dependencies.
 */
import { convertFormDataToCartData } from '@wpsimplepay/cart';
import {
	Cart,

	handle as handleCardPaymentMethod,
	setup as setupCardPaymentMethod,
	create as createCardPaymentMethod,
} from '@wpsimplepay/pro/frontend/payment-methods/card';


const ELEMENTS_DEFAULT_STYLE = {
	base: {
		color: '#32325d',
		fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
		fontSize: '15px',
		fontSmoothing: 'antialiased',
		fontWeight: 500,

		'::placeholder': {
			color: '#aab7c4'
		}
	},
	invalid: {
		color: '#fa755a',
		iconColor: '#fa755a'
	}
};

/**
 * Gets Element styles based on an existing form input.
 *
 * Injects supplementary styles for the wrapper element.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param cardEl
 * @return {Object} Element style information.
 */
function getElementsStyle( spFormElem, cardEl ) {
	// Do nothing if an Element has already been styled;
	if ( document.getElementById( 'simpay-stripe-element-styles' ) ) {
		return ELEMENTS_DEFAULT_STYLE;
	}

	// Inject inline CSS instead of applying to the Element so it can be overwritten.
	const styleTag = document.createElement( 'style' );
	styleTag.id = 'simpay-stripe-element-styles';

	// Try to mimick existing input styles.
	let input;

	input = document.querySelector( 'input.simpay-email' );

	// Try one more input in the main page content.
	if ( ! input ) {
		input = document.querySelector( 'body [role="main"] input:not([type="hidden"])' );
	}

	// Use default styles if no other input exists.
	if ( ! input ) {
		styleTag.innerHTML = `.StripeElement.simpay-card-wrap {
			background: #fff;
			border: 1px solid #d1d1d1;
			border-radius: 4px;
			padding: 0.4375em;
			height: 36px;
			min-height: 36px;
		}`;

		document.body.appendChild( styleTag );

		return ELEMENTS_DEFAULT_STYLE;
	} else {
		const inputStyles = window.getComputedStyle( input );
		const placeholderStyles = window.getComputedStyle( input, '::placeholder' );

		const trbl = [ 'top', 'right', 'bottom', 'left' ].map( ( dir ) => (
			`border-${ dir }-color: ${ inputStyles.getPropertyValue( `border-${ dir }-color` ) };
			border-${ dir }-width: ${ inputStyles.getPropertyValue( `border-${ dir }-width` ) };
			border-${ dir }-style: ${ inputStyles.getPropertyValue( `border-${ dir }-style` ) };
			padding-${ dir }: ${ inputStyles.getPropertyValue( `padding-${ dir }` ) };`
		) );

		const corners = [ 'top-right', 'bottom-right', 'bottom-left', 'top-left' ].map( ( corner ) => (
			`border-${ corner }-radius: ${ inputStyles.getPropertyValue( `border-${ corner }-radius` ) };`
		) );

		// Generate longhand properties.
		styleTag.innerHTML = `.StripeElement.simpay-card-wrap {
			background-color: ${ inputStyles.getPropertyValue( 'background-color' ) };
			${ trbl.join( '' ) }
			${ corners.join( '' ) }
		}`;

		document.body.appendChild( styleTag );

		return {
			base: {
				color: inputStyles.getPropertyValue( 'color' ),
				fontFamily: inputStyles.getPropertyValue( 'font-family' ),
				fontSize: inputStyles.getPropertyValue( 'font-size' ),
				fontWeight: inputStyles.getPropertyValue( 'font-weight' ),
				fontSmoothing: inputStyles.getPropertyValue( '-webkit-font-smoothing' ),
				// This can't be fetched dynamically, unfortunately.
				'::placeholder': {
					color: '#c7c7c7',
				},
			},
		};
	}
}

/**
 * Get Card Element configuration.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param cardEl
 * @param {Object} formData Configured form data.
 * @return {Object}
 */
export function getElementsConfig( spFormElem, cardEl ) {
	// If a billing address field exists (overrides Card field setting).
	const hidePostalCode = (
		!! spFormElem[ 0 ].querySelector( '.simpay-address-zip' ) ||
		'no' === cardEl.dataset.showPostalCode
	);

	const style = getElementsStyle( spFormElem, cardEl );

	return {
		hidePostalCode,
		style,
	};
}

/**
 * Handles an error on Submission.
 *
 * @since 3.7.0
 *
 * @param {Object} error Error data.
 * @param {jquery} spformelem form element jquery object.
 * @param {object} formdata configured form data.
 */
function onError( error, spFormElem, formData ) {
	// @todo DRY with other form handling.
	if ( ! _.isObject( error ) ) {
		return;
	}

	const {
		responseJSON,
		responseText,
		message,
	} = error;

	const foundMessage = ( responseJSON && responseJSON.message ? responseJSON.message : responseText );
	const errorMessage = message ? message : foundMessage;

	const {
		showError,
		enableForm,
	} = window.simpayApp;

	const {
		debugLog,
	} = window.spShared;

	showError( spFormElem, formData, errorMessage );
	enableForm( spFormElem, formData );

	debugLog( 'Payment Form Error:', error );
}

/**
 * Submit payment form.
 *
 * @param {Event} e Form submit Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
function onSubmit( e, spFormElem, formData ) {
	e.preventDefault();

	// Remove existing errors.
	window.simpayApp.showError( spFormElem, formData, '' );

	// Disable form while processing.
	window.simpayApp.disableForm( spFormElem, formData, true );

	// HTML5 validation check.
	if ( ! spFormElem[ 0 ].checkValidity() ) {
		window.simpayApp.triggerBrowserValidation( spFormElem, formData );
		window.simpayApp.enableForm( spFormElem, formData );

		return;
	}

	// Allow further validation.
	//
	// jQuery( document.body ).on( 'simpayBeforeStripePayment', function( e, spFormElem, formData ) {
	//  formData.isValid = false;
	// } );
	spFormElem.trigger( 'simpayBeforeStripePayment', [ spFormElem, formData ] );

	if ( ! formData.isValid ) {
		window.simpayApp.enableForm( spFormElem, formData );

		return;
	}

	try {
		createCardPaymentMethod( spFormElem, formData )
			.then( ( result ) => handleCardPaymentMethod( result, spFormElem, formData ) )
			.catch( ( error ) => onError( error, spFormElem, formData ) );
	} catch ( error ) {
		onError( error, spFormElem, formData );
	}
}

/**
 * Bind events for Stripe Elements.
 *
 * @param {Event} e simpayBindCoreFormEventsAndTriggers Event.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	const {
		isStripeCheckoutForm,
		disableForm,
	} = window.simpayApp;

	const {
		debugLog,
	} = window.spShared;

	// Don't continue if this form is using Stripe Checkout.
	if ( isStripeCheckoutForm( formData ) ) {
		return;
	}

	disableForm( spFormElem, formData, true );

	// Cache form elements.
	const realFormElem = spFormElem[ 0 ];
	const submitBtn = realFormElem.querySelector( '.simpay-checkout-btn' );

	if ( ! submitBtn ) {
		return;
	}

	try {
		// Create a cart.

		// Convert legacay data in to something usable.
		const {
			items,
			currency,
			taxPercent,
			isNonDecimalCurrency,
		} = convertFormDataToCartData( formData );

		const cart = new Cart( {
			currency,
			taxPercent,
			isNonDecimalCurrency,
		} );

		if ( items.length > 0 ) {
			items.forEach( ( item ) => {
				cart.addLineItem( item );
			} );
		}

		// Attach cart.
		spFormElem.cart = cart;

		// Initilize "card" field.
		// Enables form.
		// @todo This should be called automatically when a "Card" field type is used.
		setupCardPaymentMethod( spFormElem, formData );

		// Handle form submission.
		realFormElem.addEventListener( 'submit', ( e ) => onSubmit( e, spFormElem, formData ) );
	} catch ( error ) {
		debugLog( error.id, error.message );
	}
}
