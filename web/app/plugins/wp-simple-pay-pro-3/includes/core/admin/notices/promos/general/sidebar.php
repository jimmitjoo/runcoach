<div class="sidebar-container metabox-holder">
	<div class="postbox" style="margin-bottom: 0;">
		<h3 class="wp-ui-primary"><span><?php _e( 'Upgrade your Payment Forms', 'simple-pay' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="sidebar-heading centered">
					<?php _e( "Additional features included in<br />WP Simple Pay Pro", 'simple-pay' ); ?>
				</p>

				<!-- Repeat this bulleted list in sidebar.php & generic-tab-promo.php -->
				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Unlimited custom fields', 'simple-pay' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'User-entered amounts', 'simple-pay' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Coupon code support', 'simple-pay' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'On-site checkout (no redirect)', 'simple-pay' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Embedded & overlay forms', 'simple-pay' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Apple Pay & Google Pay', 'simple-pay' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe Subscription support', 'simple-pay' ); ?>*</li>
				</ul>

				<div class="centered">
					<a href="<?php echo simpay_pro_upgrade_url( 'sidebar-link' ); ?>"
					   class="simpay-upgrade-btn simpay-upgrade-btn-large" target="_blank">
						<?php _e( 'Click here to Upgrade', 'simple-pay' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox-nobg" style="padding-bottom: 20px;">
		<div class="inside">
			<p>
				*<?php _e( 'Plus or higher license required', 'simple-pay' ); ?>
			</p>
			<p>
				<a href="<?php echo simpay_ga_url( simpay_get_url( 'docs' ), 'sidebar-link' ); ?>"
				   target="_blank"><?php echo SIMPLE_PAY_PLUGIN_NAME; ?> <?php _e( 'Docs', 'simple-pay' ); ?></a>
				<br />
				<a href="https://dashboard.stripe.com/" target="_blank">
					<?php _e( 'Your Stripe Dashboard', 'simple-pay' ); ?></a>
			</p>
			<p>&nbsp;</p>
			<p class="centered">
				<a href="https://stripe.com/" target="_blank">
					<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/powered_by_stripe.png' ); ?>" />
				</a>
			</p>
		</div>
	</div>
</div>
