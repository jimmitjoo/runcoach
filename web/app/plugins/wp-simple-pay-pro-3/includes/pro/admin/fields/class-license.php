<?php
/**
 * Setting field: License Management
 *
 * @package SimplePay\Pro\Admin\Fields
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License input field.
 *
 * @since 3.0.0
 */
class License extends Field {


	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field
	 */
	public function __construct( $field ) {

		$this->type_class = 'simpay-field-license';

		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		$license_data = get_option( 'simpay_license_data' );
		$status       = false !== $license_data ? $license_data->license : false;

		if ( $status !== 'valid' ) {
			$display_activate   = 'display: inline-block';
			$display_deactivate = 'display: none';
			$message_color      = '#f00';
		} else {
			$display_activate   = $active = 'display: none';
			$display_deactivate = 'display: inline-block';
			$message_color      = '#46b450';
		}

		?>

		<p>
			<?php esc_html_e( 'A valid license key is required for access to automatic updates and support.', 'simple-pay' ); ?>
		</p>

		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'Retrieve your license key from %1$syour WP Simple Pay account%2$s or purchase receipt email.', 'simple-pay' ),
					sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">', simpay_ga_url( simpay_get_url( 'my-account' ), 'settings-link' ) ),
					'</a>'
				)
			);
			?>
		</p>

		<div class="simpay-license-field-wrapper">

			<div class="simpay-license-field">

				<input
					type="text"
					name="<?php echo $this->name; ?>"
					id="<?php echo $this->id; ?>"
					value="<?php echo $this->value; ?>"
					class="<?php echo $this->class; ?>"
					disabled
				/>

				<button
					class="button-primary simpay-license-button simpay-license-button--activate"
					id="simpay-activate-license"
					data-busy-label="<?php esc_html_e( 'Verifying', 'simple-pay' ); ?>"
					data-activate-label="<?php esc_html_e( 'Activate', 'simple-pay' ); ?>"
					disabled
				>
					<?php esc_html_e( 'Verifying', 'simple-pay' ); ?>
				</button>

				<button class="button-secondary simpay-license-button simpay-license-button--deactivate" id="simpay-deactivate-license" disabled>
					<?php esc_html_e( 'Deactivate', 'simple-pay' ); ?>
				</button>

				<input type="hidden" id="simpay-license-nonce" value="<?php echo esc_attr( wp_create_nonce( 'simpay-manage-license' ) ); ?>" />
			</div>

			<div
				id="simpay-license-message"
				class="simpay-license-message"
				data-error-label="<?php esc_html_e( 'An unknown error has occured. Please try again.', 'simple-pay' ); ?>"
			>
			</div>

		</div>

		<?php if ( ! simpay_subscriptions_enabled() ) : ?>
		<div id="simpay-license-upgrade" class="simpay-license-upgrade-nag">
			<?php esc_html_e( 'Want to connect Stripe subscriptions to your payment forms?', 'simple-pay' ); ?>

			<p>
				<a
					class="simpay-upgrade-btn
					simpay-license-page-upgrade-btn"
					href="<?php echo simpay_my_account_url( 'general-settings' ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					>
						<?php esc_html_e( 'Upgrade Your License Now', 'simple-pay' ); ?>
				</a>
			</p>
		</div>
		<?php endif; ?>

		<?php
	}
}
