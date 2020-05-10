<?php
/**
 * reCAPTCHA
 *
 * @package SimplePay\Pro\reCAPTCHA
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\reCAPTCHA;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine if keys are entered.
 *
 * @since 3.5.0
 *
 * @return bool
 */
function has_keys() {
	return get_key( 'site' ) && get_key( 'secret' );
}

/**
 * Retrieve a site key.
 *
 * @since 3.5.0
 *
 * @param string $key Type of key to retrieve. `site` or `secret`.
 * @return bool|string
 */
function get_key( $key ) {
	$settings = get_option( 'simpay_settings_general' );
	$key      = isset( $settings['recaptcha'][ $key ] ) ? $settings['recaptcha'][ $key ] : false;

	if ( ! $key || '' === $key ) {
		return false;
	}

	return $key;
}

/**
 * Generate custom HTML to output under reCAPTCHA title.
 *
 * @since 3.5.0
 */
function admin_setting_description() {
	ob_start();
	?>

	<p><?php esc_html_e( 'reCAPTCHA can help automatically protect your custom payment forms from spam and fraud.', 'simple-pay' ); ?></p>

	<br />

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'To enable reCAPTCHA %1$sregister your site with Google%2$s with reCAPTCHA v3 to retrieve the necessary credentials.', 'simple-pay' ),
			'<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer">',
			'</a>'
		)
	);
	?>
	</p>

	<br />

	<p>
	<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'For more information view our %1$shelp docs for reCAPTCHA%2$s.', 'simple-pay' ),
			'<a href="' . simpay_docs_link( '', 'recaptcha', 'global-settings', true ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		)
	);
	?>
	</p>

	<?php
	return ob_get_clean();
}

/**
 * Add "reCaptcha" section to General tab.
 *
 * @since 3.5.0
 *
 * @param array $sections Settings sections.
 * @return array
 */
function add_recaptcha_settings_section( $sections ) {
	$settings = array(
		'title' => __( 'reCAPTCHA', 'simple-pay' ),
	);

	return simpay_add_to_array_after( 'recaptcha', $settings, 'styles', $sections );
}
add_filter( 'simpay_add_settings_general_sections', __NAMESPACE__ . '\\add_recaptcha_settings_section' );

/**
 * Add "reCAPTCHA" fields to General tab.
 *
 * @since 3.5.0
 *
 * @param array $fields Settings fields.
 * @return array
 */
function add_recaptcha_settings_fields( $fields ) {
	$id      = 'settings';
	$group   = 'general';
	$section = 'recaptcha';

	$input_args = array(
		'type'    => 'standard',
		'subtype' => 'password',
		'default' => '',
		'class'   => array(
			'regular-text',
		),
	);

	$fields[ $section ]['setup'] = array(
		'title' => esc_html__( 'Setup', 'simple-pay' ),
		'type'  => 'custom-html',
		'html'  => admin_setting_description(),
		'name'  => 'simpay_' . $id . '_' . $group . '[' . $section . '][setup]',
		'id'    => 'simpay-' . $id . '-' . $group . '-' . $section . '-setup',
	);

	$fields[ $section ]['site'] = wp_parse_args(
		array(
			'subtype' => 'text',
			'title'   => esc_html__( 'Site Key', 'simple-pay' ),
			'name'    => 'simpay_' . $id . '_' . $group . '[recaptcha][site]',
			'id'      => 'simpay-' . $id . '-' . $group . '-recaptcha-site',
			'value'   => get_key( 'site' ),
		),
		$input_args
	);

	$fields[ $section ]['secret'] = wp_parse_args(
		array(
			'title' => esc_html__( 'Secret Key', 'simple-pay' ),
			'name'  => 'simpay_' . $id . '_' . $group . '[recaptcha][secret]',
			'id'    => 'simpay-' . $id . '-' . $group . '-recaptcha-secret',
			'value' => get_key( 'secret' ),
		),
		$input_args
	);

	return $fields;
}
add_filter( 'simpay_add_settings_general_fields', __NAMESPACE__ . '\\add_recaptcha_settings_fields' );

/**
 * Enqueue scripts necessary for generating a reCAPTCHA token.
 *
 * @since 3.5.0
 *
 * @param int    $form_id Current Form ID.
 * @param object $form Current form.
 */
function add_script( $form_id, $form ) {
	// No keys are entered.
	if ( ! has_keys() ) {
		return;
	}

	$url = add_query_arg(
		array(
			'render' => get_key( 'site' ),
		),
		'https://www.google.com/recaptcha/api.js?render=reCAPTCHA_site_key'
	);

	wp_enqueue_script( 'google-recaptcha', esc_url( $url ), array(), 'v3', true );

	wp_localize_script(
		'google-recaptcha',
		'simpayGoogleRecaptcha',
		array(
			'siteKey' => get_key( 'site' ),
			'i18n'    => array(
				'invalid' => esc_html__( 'Unable to verify Google reCAPTCHA response.', 'simple-pay' ),
			),
		)
	);

	wp_enqueue_script(
		'simpay-notices',
		SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-public-pro-recaptcha.min.js',
		array(
			'wp-util',
			'underscore',
			'google-recaptcha',
			'simpay-public-pro',
		),
		SIMPLE_PAY_VERSION,
		true
	);
}
add_action( 'simpay_form_before_form_bottom', __NAMESPACE__ . '\\add_script', 10, 2 );

/**
 * Validate a reCAPTCHA token on form submission.
 *
 * Only validates if a Customer record needs to be created for the Payment.
 * Otherwise the form is being sent to Stripe Checkout without interacting
 * further with the server.
 *
 * @since 3.5.0
 *
 * @param array                         $object_args Arguments used to create a Customer or Session.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                         $form_data Form data generated by the client.
 * @param array                         $form_values Values of named fields in the payment form.
 * @param int|string                    $customer_id Stripe Customer ID, or a blank string if none is needed.
 */
function validate_recaptcha() {
	// No keys are entered.
	if ( ! has_keys() ) {
		return;
	}

	$token   = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : false;
	$form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : false;

	// A token couldn't be generated, let it through.
	if ( false === $token || false === $form_id ) {
		return wp_send_json_success();
	}

	$secret = get_key( 'secret' );

	$request = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'body' => array(
				'secret'   => $secret,
				'response' => $token,
			),
		)
	);

	// Request fails.
	if ( is_wp_error( $request ) ) {
		return wp_send_json_error();
	}

	$response = json_decode( wp_remote_retrieve_body( $request ), true );

	// No score available.
	if ( ! isset( $response['score'] ) ) {
		return wp_send_json_error();
	}

	// Actions do not match.
	if ( isset( $response['action'] ) && 'simple_pay_form_' . $form_id !== $response['action'] ) {
		return wp_send_json_error();
	}

	/**
	 * Filter the minimum score allowed for a reCAPTCHA response to allow form submission.
	 *
	 * @since 3.5.0
	 *
	 * @param string $minimum_score Minumum score.
	 */
	$minimum_score = apply_filters( 'simpay_recpatcha_minimum_score', '0.5' );

	if ( floatval( $response['score'] ) < floatval( $minimum_score ) ) {
		return wp_send_json_error();
	}

	return wp_send_json_success();
}
add_action( 'wp_ajax_nopriv_simpay_validate_recaptcha', __NAMESPACE__ . '\\validate_recaptcha' );
add_action( 'wp_ajax_simpay_validate_recaptcha', __NAMESPACE__ . '\\validate_recaptcha' );
