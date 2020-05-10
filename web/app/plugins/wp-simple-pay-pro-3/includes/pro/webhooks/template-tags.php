<?php
/**
 * Webhooks: Template tags
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the description for webhooks
 *
 * @since unknown
 *
 * @return string
 */
function simpay_webhook_help_text() {
	$html  = '<p class="description">' . esc_html__( 'In order for Max Charges to function correctly, you must set up a Stripe webhook endpoint.', 'simple-pay' ) . '<br>';
	$html .= '<strong>' . sprintf( esc_html__( 'Your webhook URL: %s', 'simple-pay' ), simpay_get_webhook_url() ) . '</strong><br>';
	$html .= '<a href="' . simpay_docs_link( '', 'installment-plans', '', true ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'See our documentation for a step-by-step guide.', 'simple-pay' ) . '</a></p>';

	return $html;
}

/**
 * Return the webhook URL specific for this user's site
 *
 * @since unknown
 *
 * @return string
 */
function simpay_get_webhook_url() {
	return trailingslashit( rest_url( 'wpsp/v1/webhook-receiver' ) );
}
