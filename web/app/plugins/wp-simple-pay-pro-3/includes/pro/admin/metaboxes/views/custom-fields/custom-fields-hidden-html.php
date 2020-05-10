<?php
/**
 * Form Builder: Hidden field
 *
 * @package SimplePay\Pro\Admin\Metaboxes\Views\Custom_Fields
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-hidden-default-' . $counter; ?>"><?php esc_html_e( 'Default Value', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => '_simpay_custom_field[hidden][' . $counter . '][default]',
				'id'         => 'simpay-hidden-default-' . $counter,
				'value'      => isset( $field['default'] ) ? $field['default'] : '',
				'class'      => array(
					'simpay-field-text',
					'simpay-label-input',
				),
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
		<label for="<?php echo 'simpay-hidden-metadata-' . $counter; ?>"><?php esc_html_e( 'Stripe Metadata Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[hidden][' . $counter . '][metadata]',
				'id'          => 'simpay-hidden-metadata-' . $counter,
				'value'       => isset( $field['metadata'] ) ? $field['metadata'] : '',
				'class'       => array(
					'simpay-field-text',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
					'maxlength'      => simpay_metadata_title_length(),
				),
				'description' => simpay_metadata_label_description(),
			)
		);

		?>
	</td>
</tr>

<!-- Hidden ID Field -->
<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-hidden-id-' . $counter ); ?>">
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
				'name'       => '_simpay_custom_field[hidden][' . $counter . '][id]',
				'id'         => 'simpay-hidden-id-' . $counter,
				'value'      => isset( $field['id'] ) ? $field['id'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);

		?>
	</td>
</tr>
