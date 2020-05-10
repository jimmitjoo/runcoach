<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-checkout-button-text-' . $counter; ?>"><?php esc_html_e( 'Checkout Button Text', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[checkout_button][' . $counter . '][text]',
				'id'          => 'simpay-checkout-button-text-' . $counter,
				'value'       => isset( $field['text'] ) ? $field['text'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Pay {{amount}}', 'simple-pay' ),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-processing-button-text-' . $counter; ?>"><?php esc_html_e( 'Checkout Button Processing Text', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[checkout_button][' . $counter . '][processing_text]',
				'id'          => 'simpay-processing-button-text-' . $counter,
				'value'       => isset( $field['processing_text'] ) ? $field['processing_text'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Please Wait...', 'simple-pay' ),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-checkout-button-style-' . $counter ); ?>"><?php esc_html_e( 'Payment Button Style', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[checkout_button][' . $counter . '][style]',
				'id'      => esc_attr( 'simpay-checkout-button-style-' . $counter ),
				'value'   => isset( $field['style'] ) ? $field['style'] : 'none',
				'class'   => array( 'simpay-multi-toggle' ),
				'options' => array(
					'stripe' => esc_html__( 'Stripe blue', 'simple-pay' ),
					'none'   => esc_html__( 'Default', 'simple-pay' ),
				),
				'inline'  => 'inline',
			)
		);
		?>
	</td>
</tr>

<!-- Hidden ID Field -->
<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-checkout-button-id-' . $counter ); ?>">
			<?php esc_html_e( 'Field ID', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		echo absint( $uid );

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'hidden',
				'name'       => '_simpay_custom_field[checkout_button][' . $counter . '][id]',
				'id'         => 'simpay-checkout-button-id-' . $counter,
				'value'      => isset( $field['id'] ) ? $field['id'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
	</td>
</tr>
