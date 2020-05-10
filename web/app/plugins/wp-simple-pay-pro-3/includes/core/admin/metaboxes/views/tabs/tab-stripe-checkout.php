<?php
/**
 * Admin metaboxes: Stripe Checkout
 *
 * @package SimplePay\Core\Admin\Metaboxes
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<table>
		<thead>
		<tr>
			<th colspan="2"><?php esc_html_e( 'Stripe Checkout Display', 'simple-pay' ); ?></th>
		</tr>
		</thead>

		<tbody class="simpay-panel-section">

		<?php
		/**
		 * Allow extra setting rows to be added at the top of the table.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_before_stripe_checkout_rows' );
		?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_image_url"><?php esc_html_e( 'Logo/Image URL', 'simple-pay' ); ?></label>
			</th>
			<td>
				<?php

				$image_url = simpay_get_saved_meta( $post->ID, '_image_url' );

				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'text',
						'name'    => '_image_url',
						'id'      => '_image_url',
						'value'   => $image_url,
						'class'   => array(
							'simpay-field-text',
						),
					 // Description set below so the add image button doesn't break to below the description
					)
				);
				?>
				<a class="simpay-media-uploader button"><?php esc_html_e( 'Add or Upload Image', 'simple-pay' ); ?></a>

				<p class="description">
					<?php esc_html_e( 'Upload or select a square image of your brand or product to show on on the Checkout page.', 'simple-pay' ); ?>
				</p>

				<!-- Image preview -->
				<div class="simpay-image-preview-wrap <?php echo( empty( $image_url ) ? 'simpay-panel-hidden' : '' ); ?>">
					<a href="#" class="simpay-remove-image-preview simpay-remove-icon" aria-label="<?php esc_attr_e( 'Remove image', 'simple-pay' ); ?>" title="<?php esc_attr_e( 'Remove image', 'simple-pay' ); ?>"></a>
					<img src="<?php echo esc_attr( $image_url ); ?>" class="simpay-image-preview" />
				</div>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<?php esc_html_e( 'Submit Button Color', 'simple-pay' ); ?>
			</th>
			<td>
				<p class="description">
					<?php
					echo wp_kses(
						sprintf(
							__( 'Adjust the Stripe Checkout submit button color in the Stripe %1$sBranding settings%2$s', 'simple-pay' ),
							'<a href="https://dashboard.stripe.com/account/branding" target="_blank" rel="noopener noreferrer">',
							'</a>'
						),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
								'rel'    => true,
							)
						)
					);
					?>
				</p>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_checkout_submit_type"><?php esc_html_e( 'Submit Button Type', 'simple-pay' ); ?></label>
			</th>
			<td>
				<?php
				$checkout_submit_type = simpay_get_saved_meta( $post->ID, '_checkout_submit_type', 'pay' );

				simpay_print_field(
					array(
						'type'        => 'select',
						'name'        => '_checkout_submit_type',
						'id'          => '_checkout_submit_type',
						'value'       => $checkout_submit_type,
						'options'     => array(
							'book'   => esc_html__( 'Booking', 'simple-pay' ),
							'donate' => esc_html__( 'Donate', 'simple-pay' ),
							'pay'    => esc_html__( 'Pay', 'simple-pay' ),
						),
						'description' => esc_html__( 'Describes the type of transaction being performed by Checkout in order to customize relevant text on the page, such as the submit button.', 'simple-pay' ),
					)
				);
				?>
			</td>
		</tr>

		<?php do_action( 'simpay_after_checkout_button_text' ); ?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_enable_billing_address"><?php esc_html_e( 'Require Billing Address', 'simple-pay' ); ?></label>
			</th>
			<td>
				<?php

				$enable_billing_address = simpay_get_saved_meta( $post->ID, '_enable_billing_address', 'no' );

				simpay_print_field( array(
					'type'        => 'checkbox',
					'name'        => '_enable_billing_address',
					'id'          => '_enable_billing_address',
					'value'       => $enable_billing_address,
					'class'       => array(
						'simpay-section-toggle',
					),
					'description' => esc_html__( 'If enabled, Checkout will always collect the customer’s billing address. If not, Checkout will only collect the billing address when necessary.', 'simple-pay' ),
				) );
				?>
			</td>
		</tr>

		<?php
		/**
		 * Allow extra setting rows to be added at the bottom of the table.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_after_stripe_checkout_rows' );
		?>

		</tbody>
	</table>

<?php
do_action( 'simpay_admin_after_stripe_checkout' );
