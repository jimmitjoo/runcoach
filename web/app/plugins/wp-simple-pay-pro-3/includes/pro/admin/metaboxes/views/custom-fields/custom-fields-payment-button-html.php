<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-payment-button-text-' . $counter; ?>"><?php esc_html_e( 'Payment Button Text', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[payment_button][' . $counter . '][text]',
				'id'          => 'simpay-payment-button-text-' . $counter,
				'value'       => isset( $field['text'] ) ? $field['text'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Pay with Card', 'simple-pay' ),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-processing-button-text-' . $counter; ?>"><?php esc_html_e( 'Payment Button Processing Text', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[payment_button][' . $counter . '][processing_text]',
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
		<label for="<?php echo esc_attr( 'simpay-payment-button-style-' . $counter ); ?>"><?php esc_html_e( 'Payment Button Style', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[payment_button][' . $counter . '][style]',
				'id'      => esc_attr( 'simpay-payment-button-style-' . $counter ),
				'value'   => isset( $field['style'] ) ? $field['style'] : ( simpay_get_global_setting( 'payment_button_style' ) ? simpay_get_global_setting( 'payment_button_style' ) : 'stripe' ),
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

<!-- Hidden ID Field -->
<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-payment-button-id-' . $counter ); ?>">
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
				'name'       => '_simpay_custom_field[payment_button][' . $counter . '][id]',
				'id'         => 'simpay-payment-button-id-' . $counter,
				'value'      => isset( $field['id'] ) ? $field['id'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
	</td>
</tr>
