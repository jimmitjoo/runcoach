<?php

namespace SimplePay\Pro\Admin\Metaboxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that contains everything for the custom fields meta boxes UI
 */
class Custom_Fields {

	/**
	 * Custom_Fields constructor.
	 */
	public function __construct() {

		self::html();
	}

	/**
	 * Get the custom fields post meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_fields( $post_id ) {

		$fields = get_post_meta( $post_id, '_custom_fields', true );

		return $fields;
	}

	/**
	 * Get option group labels.
	 *
	 * @since 3.4.0
	 *
	 * @return array Group label names.
	 */
	public static function get_group_labels() {
		$groups = array(
			'payment'  => _x( 'Payment', 'custom field group', 'simple-pay' ),
			'customer' => _x( 'Customer', 'custom field group', 'simple-pay' ),
			'standard' => _x( 'Standard', 'custom field group', 'simple-pay' ),
			'custom'   => _x( 'Custom', 'custom field group', 'simple-pay' ),
		);

		/**
		 * Filter the labels associated with field groups.
		 *
		 * @since 3.4.0
		 *
		 * @param array $groups optgroup/category keys and associated labels.
		 */
		return apply_filters(
			'simpay_custom_field_group_labels',
			$groups
		);
	}

	/**
	 * Get a grouped list of options.
	 *
	 * @since 3.4.0
	 *
	 * @param array $options Flat list of options.
	 * @return array $options Grouped list of options.
	 */
	public static function get_grouped_options( $options ) {
		$result = array();
		$groups = self::get_group_labels();

		foreach ( $options as $key => $option ) {

			if ( isset( $option['category'] ) ) {
				$result[ $groups[ $option['category'] ] ][ $key ] = $option;
			} else {
				$result[ $groups['custom'] ][ $key ] = $option;
			}
		}

		return $result;
	}

	/**
	 * Get the available custom field options
	 *
	 * @return array
	 */
	public static function get_options() {
		$fields = array(
			'customer_name'           => array(
				'label'      => esc_html__( 'Name', 'simple-pay' ),
				'type'       => 'customer_name',
				'category'   => 'customer',
				'active'     => true,
				'repeatable' => false,
			),
			'email'                   => array(
				'label'      => esc_html__( 'Email', 'simple-pay' ),
				'type'       => 'email',
				'category'   => 'customer',
				'active'     => true,
				'repeatable' => false,
			),
			'telephone'               => array(
				'label'      => esc_html__( 'Phone', 'simple-pay' ),
				'type'       => 'telephone',
				'category'   => 'customer',
				'active'     => true,
				'repeatable' => false,
			),
			'address'                 => array(
				'label'      => esc_html__( 'Address', 'simple-pay' ),
				'type'       => 'address',
				'category'   => 'customer',
				'active'     => true,
				'repeatable' => false,
			),
			'card'                    => array(
				'label'      => esc_html__( 'Credit Card', 'simple-pay' ),
				'type'       => 'card',
				'category'   => 'payment',
				'active'     => true,
				'repeatable' => false,
			),
			'coupon'                  => array(
				'label'      => esc_html__( 'Coupon', 'simple-pay' ),
				'type'       => 'coupon',
				'category'   => 'payment',
				'active'     => true,
				'repeatable' => false,
			),
			'custom_amount'           => array(
				'label'      => esc_html__( 'Custom Amount', 'simple-pay' ),
				'type'       => 'custom_amount',
				'category'   => 'payment',
				'active'     => true,
				'repeatable' => false,
			),
			'plan_select'             => array(
				'label'      => esc_html__( 'Subscription Plan Selector', 'simple-pay' ),
				'type'       => 'plan_select',
				'category'   => 'payment',
				'active'     => simpay_subscriptions_enabled(),
				'repeatable' => false,
			),
			'recurring_amount_toggle' => array(
				'label'      => esc_html__( 'Recurring Amount Toggle', 'simple-pay' ),
				'type'       => 'recurring_amount_toggle',
				'category'   => 'payment',
				'active'     => simpay_subscriptions_enabled(),
				'repeatable' => false,
			),
			'total_amount'            => array(
				'label'      => esc_html__( 'Total Amount Label', 'simple-pay' ),
				'type'       => 'total_amount',
				'category'   => 'payment',
				'active'     => true,
				'repeatable' => true,
			),
			'payment_button'          => array(
				'label'      => esc_html__( 'Payment Button', 'simple-pay' ),
				'type'       => 'payment_button',
				'category'   => 'payment',
				'active'     => true,
				'repeatable' => false,
			),
			'payment_request_button'  => array(
				'label'      => esc_html__( 'Apple Pay/Google Pay Button', 'simple-pay' ),
				'type'       => 'payment_request_button',
				'category'   => 'payment',
				'active'     => simpay_can_use_payment_request_button(),
				'repeatable' => false,
			),
			'checkout_button'         => array(
				'label'      => esc_html__( 'Checkout Button', 'simple-pay' ),
				'type'       => 'checkout_button',
				'category'   => 'payment',
				'active'     => true,
				'repeatable' => false,
			),

			'text'                    => array(
				'label'      => esc_html__( 'Text', 'simple-pay' ),
				'type'       => 'text',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
			'dropdown'                => array(
				'label'      => esc_html__( 'Dropdown', 'simple-pay' ),
				'type'       => 'dropdown',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
			'radio'                   => array(
				'label'      => esc_html__( 'Radio Select', 'simple-pay' ),
				'type'       => 'radio',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
			'date'                    => array(
				'label'      => esc_html__( 'Date', 'simple-pay' ),
				'type'       => 'date',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
			'number'                  => array(
				'label'      => esc_html__( 'Number', 'simple-pay' ),
				'type'       => 'number',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
			'checkbox'                => array(
				'label'      => esc_html__( 'Checkbox', 'simple-pay' ),
				'type'       => 'checkbox',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
			'hidden'                  => array(
				'label'      => esc_html__( 'Hidden', 'simple-pay' ),
				'type'       => 'hidden',
				'category'   => 'standard',
				'active'     => true,
				'repeatable' => true,
			),
		);

		/**
		 * Filter custom fields available for forms.
		 *
		 * @since unknown
		 *
		 * @param array $fields Custom fields.
		 */
		return apply_filters(
			'simpay_custom_field_options',
			$fields
		);
	}

	/**
	 * Output the UI
	 */
	public static function html() {
		global $post;

		$groups = self::get_grouped_options( self::get_options() );
		$fields = self::get_fields( $post->ID );
		?>

		<div id="simpay-custom-fields-wrap" class="panel simpay-metaboxes-wrapper">
			<div class="toolbar toolbar-top">
				<label for="custom-field-select" class="screen-reader-text">
					<?php esc_html_e( 'Add a field, label or button', 'simple-pay' ); ?>:
				</label>

				<select name="simpay_field_select" id="custom-field-select" class="simpay-field-select">
					<option><?php esc_html_e( 'Choose a field...', 'simple-pay' ); ?></option>
						<?php
						if ( ! empty( $groups ) && is_array( $groups ) ) :
							foreach ( $groups as $group => $options ) :
								?>
							<optgroup label="<?php echo esc_attr( $group ); ?>">
								<?php
								foreach ( $options as $option ) :
									if ( ! isset( $option['active'] ) || ! $option['active'] ) :
										continue;
										endif;

									$disabled   = ! isset( $option['repeatable'] ) || ( isset( $fields[ $option['type'] ] ) && ! $option['repeatable'] );
									$repeatable = isset( $option['repeatable'] ) && true === $option['repeatable'];
									?>
										<option
											value="<?php echo esc_attr( $option['type'] ); ?>"
											data-counter="<?php echo esc_attr( self::get_counter() ); ?>"
											data-repeatable="<?php echo esc_attr( $repeatable ? 'true' : 'false' ); ?>"
										<?php disabled( true, $disabled ); ?>
										>
										<?php echo esc_html( $option['label'] ); ?>
										</option>
								<?php endforeach; ?>
							</optgroup>
								<?php
								endforeach;
							endif;
						?>
					</optgroup>
				</select>

				<button type="button" class="button add-field"><?php esc_html_e( 'Add', 'simple-pay' ); ?></button>
			</div>
			<div class="simpay-custom-fields simpay-metaboxes ui-sortable">
				<?php

				// Print the meta boxes according to saved order

				if ( ! empty( $fields ) && is_array( $fields ) ) {
					foreach ( $fields as $key => $v ) {
						foreach ( $v as $k2 => $field ) {

							$order   = isset( $field['order'] ) ? intval( $field['order'] ) : 1;
							$counter = intval( $k2 ) + 1;
							$uid     = isset( $field['uid'] ) ? intval( $field['uid'] ) : $counter;

							$key = sanitize_key( $key );

							// We use a different way of saving the custom amount label so we need to grab that post meta here so it will show the label in the custom field drag n drop header
							if ( 'custom_amount' === $key ) {

								$label = simpay_get_saved_meta( $post->ID, '_custom_amount_label' );

								$field['label'] = $label;
							} elseif ( 'plan_select' === $key ) {

								$label = simpay_get_saved_meta( $post->ID, '_plan_select_form_field_label' );

								$field['label'] = $label;
							}

							self::print_custom_field( $key, $order, $counter, $uid, $field );
						}
					}
				}

				?>
			</div>
		</div>

		<?php

		wp_nonce_field( 'simpay_custom_fields_nonce', 'simpay_custom_fields_nonce' );

		do_action( 'simpay_custom_field_panel' );
	}

	/**
	 * Function to get the current counter for a certain type of custom field.
	 */
	public static function get_counter() {

		global $post;

		$counter = 0;

		$fields = self::get_fields( $post->ID );

		if ( empty( $fields ) ) {
			return 0;
		}

		if ( is_array( $fields ) ) {
			foreach ( $fields as $k => $v ) {
				$counter = $counter + count( $v );
			}
		}

		return intval( $counter );
	}

	/**
	 * Print out a custom field inside the admin
	 *
	 * @param       $key
	 * @param       $order
	 * @param       $counter
	 * @param       $uid
	 * @param array   $field
	 */
	public static function print_custom_field( $key, $order, $counter, $uid, $field = array() ) {

		$options         = self::get_options();
		$accordion_label = '';

		// Don't render settings for custom field types that don't exist, possibly from an upgrade or downgrade.
		if ( ! isset( $options[ $key ] ) ) {
			return;
		}

		/**
		 * Set custom field accordion label to (in order of non-empty value found):
		 * Form field label value
		 * Placeholder value
		 * Field type name (option value)
		 */

		if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
			$accordion_label = $field['label'];
		} elseif ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
			$accordion_label = $field['placeholder'];
		} else {
			$accordion_label = $options[ $key ]['label'];
		}

		$accordion_label = esc_html( $accordion_label );

		?>

			<div class="simpay-field-metabox simpay-metabox closed simpay-custom-field-<?php echo simpay_dashify( $key ); ?>" rel="<?php echo $order; ?>" data-type="<?php echo esc_attr( $options[ $key ]['type'] ); ?>">
			<h3>
				<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'simple-pay' ); ?>"></div>
				<div class="simpay-field-type"><?php echo $options[ $key ]['label']; ?></div>
				<span class="custom-field-dashicon dashicons dashicons-menu"></span><strong><?php echo $accordion_label; ?></strong>
			</h3>
			<div class="simpay-field-data simpay-metabox-content">

				<!-- Hidden fields to keep track of the order and the unique ID -->
				<input type="hidden" name="<?php echo '_simpay_custom_field[' . $key . '][' . esc_attr( $counter ) . '][order]'; ?>" class="field-order"
					   value="<?php echo esc_attr( $order ); ?>" />
				<input type="hidden" name="<?php echo '_simpay_custom_field[' . $key . '][' . esc_attr( $counter ) . '][uid]'; ?>" class="field-uid"
					   value="<?php echo esc_attr( $uid ); ?>" />

				<table>

					<?php

					$admin_field_template = apply_filters( 'simpay_admin_' . esc_attr( $key ) . '_field_template', 'views/custom-fields/custom-fields-' . simpay_dashify( $key ) . '-html.php' );

					// Include template for each custom field rendered (can be more than once).
					include( $admin_field_template );

					do_action( 'simpay_after_' . $key . '_meta' );

					?>

					<tr>
						<td colspan="2">
							<a href="#" class="simpay-remove-field-link"><?php esc_html_e( 'Remove Field', 'simple-pay' ); ?></a>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<?php
	}
}
