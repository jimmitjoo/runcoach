<?php
/**
 * Admin metaboxes: Subscription options
 *
 * @package SimplePay\Core\Admin\Metaboxes
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

use function SimplePay\Core\Admin\Notices\Promos\bfcm_is_promo_active;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the upgrade URL
$upgrade_url = simpay_pro_upgrade_url( 'under-box-promo' );
?>

<div class="simpay-promo-under-box">

	<h2><?php _e( 'Need your customers to sign up for recurring payments?', 'simple-pay' ); ?></h2>

	<p>
		<?php _e( 'By upgrading to a WP Simple Pay Pro Plus or higher license, you can connect Stripe subscriptions to your payment forms. You can also create installment plans, setup fees, and free trial periods.', 'simple-pay' ); ?>
	</p>

	<?php
	if ( true === bfcm_is_promo_active() ) {
		?>
		<p>
			<?php _e( 'Upgrade before <em>23:59 PM December 6th CST</em> and <strong>SAVE 25%</strong> during our Black Friday & Cyber Monday sale. Use code <code>BFCM2019</code> at checkout.', 'simple-pay' ); ?>
		</p>
		<?php

		// Adjust the upgrade URL if there's an active promotion
		$utm_args = array(
			'utm_source'   => 'form-settings',
			'utm_medium'   => 'wp-admin',
			'utm_campaign' => 'bfcm2019',
			'utm_content'  => 'upgrade-promo-subscription-options',
		);
		$upgrade_url = esc_url( add_query_arg( $utm_args, 'https://wpsimplepay.com/lite-vs-pro/' ) );
	}
	?>

	<p>
		<a href="<?php echo $upgrade_url; ?>" class="simpay-upgrade-btn simpay-upgrade-btn-large" target="_blank" rel="noopener noreferrer">
			<?php _e( 'Click here to Upgrade', 'simple-pay' ); ?>
		</a>
	</p>

</div>
