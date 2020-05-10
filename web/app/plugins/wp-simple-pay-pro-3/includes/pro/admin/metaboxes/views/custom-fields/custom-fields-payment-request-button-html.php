<?php
/**
 * Output Payment Request Button settings metabox in the admin.
 *
 * @link https://stripe.com/docs/stripe-js/elements/payment-request-button
 * @link https://www.w3.org/TR/payment-request/
 *
 * @since 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$counter = absint( $counter );

/**
 * Allow output of extra settings before the defaults.
 *
 * @since 3.4.0
 */
do_action( 'simpay_admin_before_custom_field_payment_request_button' );
?>

<tr class="simpay-panel-field">
	<td colspan="2">
		<?php esc_html_e( 'Using this field, site visitors are shown either an Apple Pay, Google Pay, or Microsoft Pay button if their browser and device combination supports it. If none are available, the button is not displayed.', 'simple-pay' ); ?>
		<br /><br />
		<strong><?php esc_html_e( 'To use Apple Pay, you must be connected to your Stripe account in Live mode or have your Live API keys enabled.', 'simple-pay' ); ?></strong>
		<br /><br />

		<a href="<?php echo simpay_docs_link( '', 'apple-pay-google-pay', 'payment-request-button-settings', true ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help docs for the Apple Pay/Google Pay Button', 'simple-pay' ); ?></a>
		<br /><br />
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<?php esc_html_e( 'Button Type', 'simple-pay' ); ?>
	</th>

	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[payment_request_button][' . $counter . '][type]',
				'id'      => '_payment_request_button_type',
				'class'   => array( 'simpay-multi-toggle' ),
				'options' => array(
					'default' => __( 'Pay', 'simple-pay' ),
					'donate'  => __( 'Donate', 'simple-pay' ),
					'buy'     => __( 'Buy', 'simple-pay' ),
				),
				'default' => 'default',
				'value'   => isset( $field['type'] ) ? $field['type'] : 'default',
				'inline'  => 'inline',
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<?php esc_html_e( 'Field ID', 'simple-pay' ); ?>
	</th>
	<td>
		<?php
		echo absint( $uid );

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'hidden',
				'name'       => '_simpay_custom_field[payment_request_button][' . $counter . '][id]',
				'id'         => 'simpay-payment-request-button-id-' . $counter,
				'value'      => isset( $field['id'] ) ? $field['id'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
	</td>
</tr>

<?php
/**
 * Allow output of extra settings after the defaults.
 *
 * @since 3.4.0
 */
do_action( 'simpay_admin_after_custom_field_payment_request_button' );
