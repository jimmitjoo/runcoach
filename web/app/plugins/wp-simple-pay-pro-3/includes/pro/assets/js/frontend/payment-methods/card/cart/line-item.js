/**
 * Internal dependencies
 */
import { LineItem as BaseLineItem } from '@wpsimplepay/cart';

/**
 * LineItem
 */
class LineItem extends BaseLineItem {
	/**
	 * Retrieves a cart line item's subtotal.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item subtotal.
	 */
	getSubtotal() {
		return Math.round( this.getUnitPrice() * this.getQuantity() );
	}

	/**
	 * Retrieves a cart line item's total.
	 *
	 * @since 3.7.0
	 *
	 * @return {number} Cart line item total.
	 */
	getTotal() {
		return Math.round( this.getSubtotal() + this.getTax() );
	}
}

export default LineItem;
