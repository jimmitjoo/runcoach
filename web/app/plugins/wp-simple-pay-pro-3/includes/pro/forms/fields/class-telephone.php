<?php
/**
 * Forms field: Telephone
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Telephone class.
 *
 * @since 3.5.0
 */
class Telephone extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id          = isset( $settings['id'] ) ? simpay_dashify( $settings['id'] ) : '';
		$label       = isset( $settings['label'] ) ? $settings['label'] : '';
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] ) ? 'required' : '';
		$default     = self::get_default_value();

		ob_start();
		?>

<div id="<?php echo esc_attr( $id ); ?>" class="simpay-form-control simpay-telephone-container">
	<div class="simpay-telephone-label simpay-label-wrap">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
	</div>

	<div class="simpay-telephone-field simpay-field-wrap">
		<input name="simpay_telephone" id="<?php echo esc_attr( $id ); ?>" type="tel" class="simpay-telephone" value="<?php echo esc_attr( $default ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo $required; ?> />
	</div>
</div>

		<?php
		return ob_get_clean();
	}

}
