<?php use function SimplePay\Core\Admin\Notices\Promos\bfcm_is_promo_active; ?>
<h2><?php _e( 'Want to customize your payment forms even more?', 'simple-pay' ); ?></h2>
<p>
	<?php _e( 'By upgrading to WP Simple Pay Pro, you get access to powerful features such as:', 'simple-pay' ); ?>
</p>

<!-- Repeat this bulleted list in sidebar.php & generic-tab-promo.php -->
<ul>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Unlimited custom fields to capture additional data', 'simple-pay' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Custom amounts - let customers enter an amount to pay', 'simple-pay' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Coupon code support', 'simple-pay' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'On-site checkout (no redirect) with custom forms', 'simple-pay' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Embedded & overlay form display options', 'simple-pay' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Apple Pay & Google Pay support with custom forms', 'simple-pay' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe Subscription support (Plus or higher license required)', 'simple-pay' ); ?></li>
</ul>

<?php if ( true === bfcm_is_promo_active() ) { ?>
	<h3>Black Friday & Cyber Monday sale!</h3>
	<p>
		<?php _e( '<strong>SAVE 25%</strong> on all WP Simple Pay Pro purchases this week, including renewals and upgrades! Sale ends <em>23:59 PM December 6th CST</em>. Use code <code>BFCM2019</code> at checkout.', 'simple-pay' ); ?>
	</p>
<?php } ?>
