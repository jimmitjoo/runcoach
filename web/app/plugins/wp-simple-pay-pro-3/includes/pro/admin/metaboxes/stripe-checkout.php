<?php
/**
 * Modify the "Stripe Checkout" tab functionality.
 *
 * @since 3.5.0
 */

namespace SimplePay\Pro\Admin\Metaboxes\Stripe_Checkout;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow custom form styles to be used on the frontend.
 *
 * @since 3.5.0
 */
function enable_styles_setting() {
	global $post;
	?>

<tr class="simpay-panel-field">
	<th>
		<label for="_enable_stripe_checkout_form_styles"><?php esc_html_e( 'Enable Form Styles', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => '_enable_stripe_checkout_form_styles',
				'id'          => '_enable_stripe_checkout_form_styles',
				'value'       => simpay_get_saved_meta( $post->ID, '_enable_stripe_checkout_form_styles', 'no' ),
				'description' => esc_html__( 'Apply plugin styling to form fields that appear on-page. Otherwise the styles will inherit from the current theme.', 'simple-pay' ),
			)
		);
		?>
	</td>
</tr>

	<?php
}
add_action( 'simpay_after_checkout_button_text', __NAMESPACE__ . '\\enable_styles_setting' );

/**
 * Save the value for using custom form styles.
 *
 * @since 3.5.0
 *
 * @param int $post_id Form ID.
 */
function save_styles_setting( $post_id ) {
	$enable_form_styles = isset( $_POST['_enable_stripe_checkout_form_styles'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_enable_stripe_checkout_form_styles', $enable_form_styles );
}
add_action( 'simpay_save_form_settings', __NAMESPACE__ . '\\save_styles_setting' );
