<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table>
	<thead>
	<tr>
		<th colspan="2"><?php esc_html_e( 'Form Display Options', 'simple-pay' ); ?></th>
	</tr>
	</thead>
	<tbody class="simpay-panel-section">

		<?php
		/**
		 * Allow extra setting rows to be added at the bottom of the table.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_before_form_display_options_rows' );
		?>

	<tr class="simpay-panel-field">
		<th>
			<label for="_form_display_type"><?php esc_html_e( 'Form Display Type', 'simple-pay' ); ?></label>
		</th>
		<td style="padding-top: 0;">

			<?php

			// TODO Description for each form display option.

			$form_display_type = simpay_get_saved_meta( $post->ID, '_form_display_type', 'embedded' );

			simpay_print_field(
				array(
					'type'    => 'radio',
					'name'    => '_form_display_type',
					'id'      => '_form_display_type',
					'value'   => $form_display_type,
					'class'   => array(
						'simpay-field-text',
						'simpay-multi-toggle',
					),
					'options' => array(
						'embedded'        => esc_html__( 'Embedded', 'simple-pay' ),
						'overlay'         => esc_html__( 'Overlay', 'simple-pay' ),
						'stripe_checkout' => esc_html__( 'Stripe Checkout', 'simple-pay' ),
					),
					'inline'  => 'inline',
				 // Description for this field set below so we can use wp_kses() without clashing with the wp_kses() already being applied to simpay_print_field()
				)
			);
			?>

		</td>
	</tr>

		<?php
		/**
		 * Allow extra setting rows to be added at the bottom of the table.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_after_form_display_options_rows' );
		?>

	</tbody>
</table>

<?php
echo simpay_docs_link( __( 'Help docs for Form Display Options', 'simple-pay' ), 'form-display-options', 'form-settings' );
