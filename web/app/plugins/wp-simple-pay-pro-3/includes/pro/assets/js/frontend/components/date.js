/* global jQuery, datepicker */

/**
 * Initailize jQuery UI datepicker.
 *
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	if ( ! $.datepicker ) {
		return;
	}

	const dateInputEl = spFormElem.find( '.simpay-date-input' );

	dateInputEl.datepicker( {
		dateFormat: formData.dateFormat,
		beforeShow() {
			jQuery( '.ui-datepicker' ).css( 'font-size', 14 );
		},
	} );
}
