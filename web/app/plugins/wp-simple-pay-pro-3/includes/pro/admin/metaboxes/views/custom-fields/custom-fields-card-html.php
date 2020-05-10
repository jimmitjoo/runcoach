<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-card-label-' . $counter; ?>"><?php esc_html_e( 'Form Field Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[card][' . $counter . '][label]',
				'id'          => 'simpay-card-label-' . $counter,
				'value'       => isset( $field['label'] ) ? $field['label'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_form_field_label_description(),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-card-postal_code-' . $counter; ?>"><?php esc_html_e( 'Hide Postal Code', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'checkbox',
				'name'       => '_simpay_custom_field[card][' . $counter . '][postal_code]',
				'id'         => 'simpay-card-postal_code-' . $counter,
				'value'      => isset( $field['postal_code'] ) ? $field['postal_code'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
	</td>
</tr>

<!-- Hidden ID Field -->
<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-card-id-' . $counter ); ?>">
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
				'name'       => '_simpay_custom_field[card][' . $counter . '][id]',
				'id'         => 'simpay-card-id-' . $counter,
				'value'      => isset( $field['id'] ) ? $field['id'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
	</td>
</tr>
