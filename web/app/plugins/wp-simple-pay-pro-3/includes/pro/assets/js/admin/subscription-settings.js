/* global jQuery */

let spSubAdmin = {};

( function( $ ) {
	'use strict';

	let body,
		spSubSettings;

	spSubAdmin = {

		init() {
			// We need to initialize these here because that's when the document is finally ready
			body = $( document.body );
			spSubSettings = body.find( '#subscription-options-settings-panel' );

			this.loadMultiPlanSubscriptions();

			// Initialize sortable fields for multi-plans
			this.initSortablePlans( spSubSettings.find( '.simpay-multi-subscriptions tbody' ) );

			// Add plan button
			spSubSettings.find( '.simpay-add-plan' ).on( 'click.simpayAddPlan', function( e ) {
				e.preventDefault();

				spSubAdmin.addPlan( e );
			} );

			// Remove Plan action
			spSubSettings.find( '.simpay-panel-field' ).on( 'click.simpayRemovePlan', '.simpay-remove-plan', function( e ) {
				spSubAdmin.removePlan( $( this ), e );
			} );

			// Search for and set the default plan on load.
			spSubAdmin.setDefaultPlan();

			// Update default subscription
			spSubSettings.find( '.simpay-multi-subscriptions' ).on( 'click.simpayUpdateDefaultPlan', '.simpay-multi-plan-default input[type="radio"]', function( e ) {
				spSubAdmin.updateDefaultPlan( $( this ) );
			} );

			// Trigger update of plan ID on change of select
			spSubSettings.find( '.simpay-multi-subscriptions' ).on( 'change.simpayUpdatePlanSelect', '.simpay-multi-plan-select', function( e ) {
				spSubAdmin.updatePlanSelect( $( this ) );
			} );

			// Enable/Disable single subscription plan dropdown
			spSubSettings.find( '#_subscription_custom_amount' ).find( 'input[type="radio"]' ).on( 'change.simpayToggleSubscription', function( e ) {
				spSubAdmin.togglePlans( $( this ) );
			} );

			// Trigger for default plan value if none are selected
			if ( '' === spSubSettings.find( '#simpay-multi-plan-default-value' ).val() ) {
				spSubSettings.find( '.simpay-multi-plan-default input[type="radio"]:first' ).trigger( 'click.simpayUpdateDefaultPlan' );
			}
		},

		initSortablePlans( el ) {
			el.sortable( {
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				handle: 'td.sort-handle',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				opacity: 0.65,
				stop( e, ui ) {
					spSubAdmin.orderPlans();
				},

				// @link https://core.trac.wordpress.org/changeset/35809
				helper( event, element ) {
					/* `helper: 'clone'` is equivalent to `return element.clone();`
					 * Cloning a checked radio and then inserting that clone next to the original
					 * radio unchecks the original radio (since only one of the two can be checked).
					 * We get around this by renaming the helper's inputs' name attributes so that,
					 * when the helper is inserted into the DOM for the sortable, no radios are
					 * duplicated, and no original radio gets unchecked.
					 */
					return element.clone()
						.find( ':input' )
						.attr( 'name', function( i, currentName ) {
							return 'sort_' + parseInt( Math.random() * 100000, 10 ).toString() + '_' + currentName;
						} )
						.end();
				},
			} );
		},

		loadMultiPlanSubscriptions() {
			const simpayPlans = spSubSettings.find( '.simpay-multi-sub' ).get();

			simpayPlans.sort( function( a, b ) {
				const compA = parseInt( $( a ).attr( 'rel' ), 10 );
				const compB = parseInt( $( b ).attr( 'rel' ), 10 );
				return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
			} );

			spSubSettings.find( simpayPlans ).each( function( idx, itm ) {
				spSubSettings.find( '.simpay-multi-subscriptions tbody' ).append( itm );
			} );
		},

		togglePlans( el ) {
			// TODO DRY

			if ( 'enabled' === el.val() && el.is( ':checked' ) ) {
				body.find( '#_single_plan' ).prop( 'disabled', true ).trigger( 'chosen:updated' );
			} else {
				body.find( '#_single_plan' ).prop( 'disabled', false ).trigger( 'chosen:updated' );
			}
		},

		updatePlanSelect( el ) {
			const fieldKey = el.parent().data( 'field-key' );

			if ( spSubSettings.find( '#simpay-subscription-multi-plan-default-' + fieldKey + '-yes' ).is( ':checked' ) ) {
				spSubSettings.find( '#simpay-multi-plan-default-value' ).val( el.find( 'option:selected' ).val() );
			}
		},

		/**
		 * Validate (and potentially fix) the default subscription plan.
		 */
		setDefaultPlan() {
			const savedDefaultField = document.getElementById( 'simpay-multi-plan-default-value' );
			const selectedDefaultField = document.querySelector( '.simpay-multi-plan-default input:checked' );

			// Selected radio matches saved meta, do nothing.
			if ( selectedDefaultField && ( selectedDefaultField.dataset.planId === savedDefaultField.value ) ) {
				return;
			}

			// There is a selected default but it doesn't match, so update the hidden field.
			if ( selectedDefaultField && ( selectedDefaultField.dataset.planId !== savedDefaultField.value ) ) {
				savedDefaultField.value = selectedDefaultField.dataset.planId;
			}

			// There is no selected default, updated the saved meta with the first option.
			if ( ! selectedDefaultField ) {
				const firstRadio = document.querySelector( '.simpay-multi-plan-default input' );

				if ( ! firstRadio ) {
					return;
				}

				// Check...
				firstRadio.checked = true;

				// Set hidden value.
				savedDefaultField.value = firstRadio.dataset.planId;
			}
		},

		updateDefaultPlan( el ) {
			const plan = el.closest( '.simpay-multi-plan-default' ).parent().find( '.simpay-multi-plan-select' ).find( 'option:selected' ).val();

			spSubSettings.find( '#simpay-multi-plan-default-value' ).val( plan );
		},

		orderPlans() {
			spSubSettings.find( '.simpay-multi-sub' ).each( function( index, el ) {
				const planIndex = parseInt( $( el ).index( '.simpay-multi-sub' ) );

				spSubSettings.find( '.plan-order', el ).val( planIndex );
			} );
		},

		addPlan( e ) {
			const wrapper = spSubSettings.find( '.simpay-multi-subscriptions tbody' ); // Main table
			const currentKey = wrapper.find( 'tr' ).length;

			const data = {
				action: 'simpay_add_plan',
				counter: parseInt( currentKey ),
				addPlanNonce: body.find( '#simpay_add_plan_nonce' ).val(),
			};

			e.preventDefault();

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data,
				success( response ) {
					wrapper.append( response );
					wrapper.find( 'select' ).chosen();
				},
				error( response ) {
					spShared.debugLog( response );
				},
			} );
		},

		removePlan( el, e ) {
			e.preventDefault();

			el.closest( '.simpay-multi-sub' ).remove();
			spSubAdmin.setDefaultPlan();
		},
	};

	$( document ).ready( function( $ ) {
		spSubAdmin.init();
	} );
}( jQuery ) );
