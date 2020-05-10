/**
 * Internal dependencies
 */
import { Cart as BaseCart } from '@wpsimplepay/cart';
import LineItem from './line-item.js';

/**
 * Cart for Embedded/Overlay form types.
 *
 * @todo Move to /pro
 *
 * @since 3.7.0
 */
export const Cart = class Cart extends BaseCart {
	/**
	 * @since 3.7.0
	 *
	 * @param {Object} args Cart arguments.
	 */
	constructor( args ) {
		super( args );

		// Define the type of line item to use.
		this.LineItem = LineItem;
	}

	/**
	 * Retrieves subtotal.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart subtotal.
	 */
	getSubtotal() {
		let subtotal = 0;

		this.getLineItems().forEach( ( item ) => {
			subtotal += item.getSubtotal();
		} );

		return subtotal;
	}

	/**
	 * Retrieves the total discount amount.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart discount amount.
	 */
	getDiscount() {
		const coupon = this.getCoupon();
		let discount = 0;

		if ( false === coupon ) {
			return discount;
		}

		const {
			amount_off: amountOff,
			percent_off: percentOff,
		} = coupon;

		if ( percentOff ) {
			this.getLineItems().forEach( ( item ) => {
				discount += Math.round( item.getSubtotal() * ( percentOff / 100 ) );
			} );
		}

		if ( amountOff ) {
			discount += amountOff;
		}

		return discount;
	}

	/**
	 * Retrieves total tax amount.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart tax.
	 */
	getTax() {
		let tax = 0;

		const coupon = this.getCoupon();
		const taxDecimal = this.getTaxDecimal();
		const lineItems = this.getLineItems();

		// Calculate taxes with no coupon applied.
		if ( false === coupon ) {
			lineItems.forEach( ( item ) => {
				tax += Math.round( item.getSubtotal() * taxDecimal );
			} );

			return tax;
		}

		const {
			percent_off: percentOff,
			amount_off: amountOff,
		} = coupon;

		// Calculate taxes with a %-based coupon applied.
		//
		// Stripe applies the discount percent to each line item before
		// calculating the tax amount of each and summing.
		if ( percentOff ) {
			lineItems.forEach( ( item ) => {
				const lineItemDiscount = item.getSubtotal() * ( percentOff / 100 );
				const lineItemSubtotal = item.getSubtotal() - lineItemDiscount;

				tax += Math.round( lineItemSubtotal * taxDecimal );
			} );

			return tax;
		}

		const subtotal = this.getSubtotal();

		// Calculate taxes with a fixed-amount coupon applied.
		//
		// Stripe applies the discount amount to the cart subtotal before
		// calculating the tax amount for the whole cart.
		if ( amountOff ) {
			const cartSubtotal = subtotal - amountOff;

			tax += Math.round( cartSubtotal * taxDecimal );

			return tax;
		}
	}

	/**
	 * Retrieves the total.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart total.
	 */
	getTotal() {
		const subtotal = this.getSubtotal();
		const tax = this.getTax();
		const discount = this.getDiscount();

		return Math.round( ( subtotal - discount ) + tax );
	}
};
