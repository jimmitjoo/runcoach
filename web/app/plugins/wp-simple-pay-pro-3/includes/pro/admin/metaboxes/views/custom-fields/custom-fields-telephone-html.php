<?php
/**
 * Output telephone field settings metabox in the admin.
 *
 * @since 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$counter = absint( $counter );

/**
 * Allow output of extra settings before the defaults.
 *
 * @since 3.5.0
 */
do_action( 'simpay_admin_before_custom_field_telephone' );
?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-telephone-label-' . $counter; ?>"><?php esc_html_e( 'Form Field Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[telephone][' . $counter . '][label]',
				'id'          => 'simpay-telephone-label-' . $counter,
				'value'       => isset( $field['label'] ) ? $field['label'] : esc_html( 'Phone', 'simple-pay' ),
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
		<label for="<?php echo 'simpay-telephone-placeholder-' . $counter; ?>"><?php esc_html_e( 'Placeholder', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[telephone][' . $counter . '][placeholder]',
				'id'          => 'simpay-telephone-placeholder-' . $counter,
				'value'       => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
				'class'       => array(
					'simpay-field-text',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_placeholder_description(),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-telephone-required-' . $counter; ?>"><?php esc_html_e( 'Required Field', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'checkbox',
				'name'       => '_simpay_custom_field[telephone][' . $counter . '][required]',
				'id'         => 'simpay-telephone-required-' . $counter,
				'value'      => isset( $field['required'] ) ? $field['required'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-telephone-id-' . $counter ); ?>">
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
				'name'       => '_simpay_custom_field[telephone][' . $counter . '][id]',
				'id'         => 'simpay-telephone-id-' . $counter,
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
 * @since 3.5.0
 */
do_action( 'simpay_admin_after_custom_field_telephone' );
