<?php
/*
Plugin Name: Automatic Alternative Text
Description: Automatically generate alt text for images with Microsoft's Cognitive Services Computer Vision API.
Version: 1.0.1
Author: Jacob Peattie
Author URI: https://profiles.wordpress.org/jakept
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: automatic-alternative-text
*/

/**
 * Add default settings on activation.
 *
 * @since 1.0
 */
function aat_activate_plugin() {
	add_option( 'aat_confidence', 15 );
}
register_activation_hook( __FILE__, 'aat_activate_plugin' );

/**
 * Add a setting slink to Plugins screen.
 *
 * @since 1.0
 *
 * @param array $links Array of plugin action links.
 * @return array Modified array of plugin action links.
 */
function aat_plugin_action_links( $links ) {
	array_unshift( $links, '<a href="'. admin_url( 'options-media.php' ) .'">' . __( 'Settings', 'automatic-alternative-text' ) . '</a>' );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'aat_plugin_action_links' );

/**
 * Notify user of API key requirement.
 *
 * @since 1.0
 */
function aat_admin_notices() {
	/**
	 * Don't display the notice if:
	 * 1. An API key is present.
	 * 2. The user cannot save settings.
	 * 3. The notice has been dismissed.
	 */
	if  ( get_option( 'aat_api_key' ) || ! current_user_can( 'manage_options' ) || get_option( 'aat_api_notice_dismissed' ) ) {
		return false;
	}
	?>

	<div class="eat-api-notice notice notice-warning is-dismissible">
		<p><?php printf( __( 'Thanks for installing Automatic Alternative Text! To start receiving alt text, enter your API key <a href="%s">here</a>. Don\'t have one yet? Sign up <a href="%s" target="_blank">here</a>.', 'automatic-alternative-text' ), admin_url( 'options-media.php' ), 'https://www.microsoft.com/cognitive-services/en-us/computer-vision-api' ); ?>

		<script type="text/javascript" >
			jQuery( '.eat-api-notice' ).on( 'click', '.notice-dismiss', function() {
				jQuery.post( ajaxurl, { "action": "aat_dismiss_api_notice" } );
			} );
		</script>
	</div>

	<?php
}
add_action( 'admin_notices', 'aat_admin_notices' );

/**
 * Remember API key notice dismissal.
 *
 * @since 1.0
 */
function aat_dismiss_api_notice() {
	update_option( 'aat_api_notice_dismissed', 1 );
	wp_die();
}
add_action( 'wp_ajax_aat_dismiss_api_notice', 'aat_dismiss_api_notice' );

/**
 * Register settings and fields.
 *
 * @since 1.0
 */
function aat_register_settings() {
	add_settings_section( 'eat-settings', __( 'Automatic Alternative Text', 'automatic-alternative-text' ), 'aat_settings_section', 'media' );
	register_setting( 'media', 'aat_api_key', 'sanitize_text_field' );
	add_settings_field( 'eat-api-key', __( 'API Key', 'automatic-alternative-text' ), 'aat_api_key_field', 'media', 'eat-settings', array( 'label_for' => 'aat_api_key' ) );
	register_setting( 'media', 'aat_confidence', 'aat_sanitize_confidence' );
	add_settings_field( 'eat-confidence', __( 'Confidence threshold', 'automatic-alternative-text' ), 'aat_confidence_field', 'media', 'eat-settings', array( 'label_for' => 'aat_confidence' ) );
}
add_action( 'admin_init', 'aat_register_settings' );

/**
 * Settings section.
 *
 * @since 1.0
 */
function aat_settings_section() {
	_e( 'Automatic Alternative Text is powered by <a href="https://www.microsoft.com/cognitive-services">Microsoft’s Cognitive Services</a>.', 'automatic-alternative-text' );
}

/**
 * Field for API key setting.
 *
 * @since 1.0
 */
function aat_api_key_field() {
	$option = get_option( 'aat_api_key' );
	?>

	<input id="aat_api_key" class="regular-text" name="aat_api_key" type="text" value="<?php echo esc_attr( $option ); ?>">
	<p class="description">
		<?php _e( 'Your Computer Vision API key. Sign up for a key <a href="https://www.microsoft.com/cognitive-services/en-us/computer-vision-api" target="_blank">here</a>. Once you\'re signed up you can find your key <a href="https://www.microsoft.com/cognitive-services/en-US/subscriptions" target="_blank">here</a>.', 'automatic-alternative-text' ); ?>
	</p>

	<?php
}

/**
 * Field for Confidence threshold setting.
 *
 * @since 1.0
 */
function aat_confidence_field() {
	$option = get_option( 'aat_confidence' );
	?>

	<input id="aat_confidence" class="small-text" name="aat_confidence" type="number" value="<?php echo esc_attr( $option ); ?>" min="0" max="100">%
	<p class="description">
		<?php _e( 'Only use captions when the API is at least this confident.', 'automatic-alternative-text' ); ?>
	</p>

	<?php
}

/**
 * Sanitize confidence threshold value.
 *
 * @since 1.0
 *
 * @param mixed $input Value to sanitize.
 * @return int An integer between 0 and 100.
 */
function aat_sanitize_confidence( $input ) {
	return absint( max( min(  $input, 100 ), 0 ) );
}

/**
 * Set the alt text to a caption from Microsoft's Cognitive Services Computer
 * Vision API.
 *
 * @since 1.0
 *
 * @param int $attachment_id ID of the attachment to get the caption for.
 * @return bool True on success and false on failure.
 */
function aat_add_alt_text( $attachment_id  ) {
	/* Only attempt to get a caption for supported image formats. */
	if ( ! in_array( get_post_mime_type( $attachment_id ), array( 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/x-windows-bmp' ) ) ) {
		return false;
	}

	/* Only attempt to get a caption for images under 4MB (Microsoft's limit) */
	if ( filesize( get_attached_file( $attachment_id ) ) > 4000000 ) {
		return false;
	}

	/* Request caption and update meta if one is received. */
	if ( $alt_text = aat_get_caption( $attachment_id ) ) {
		return update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
	} else {
		return false;
	}
}
add_action( 'add_attachment', 'aat_add_alt_text', 20, 2 );

/**
 * Get an image caption from Microsoft's Cognitive Services Computer Vision API
 * for a given attachment.
 *
 * @since 1.0
 *
 * @param int $attachment_id ID of the attachment to get the caption for.
 * @return string A caption for the attachment. False on failure.
 */
function aat_get_caption( $attachment_id ) {
	/* Bail if we don't have an API key. */
	if ( ! $api_key = get_option( 'aat_api_key' ) ) {
		return false;
	}

	/* Get the URL for the attachment image. */
	$url = wp_get_attachment_image_url( $attachment_id, 'large' );

	/* Bail if we don't have a URL. */
	if ( ! $url ) {
		return false;
	}

	/* Make API request. */
	$response = wp_remote_post( 'https://api.projectoxford.ai/vision/v1.0/describe', array(
		'body'    => '{"url" : "' . $url . '"}',
		'headers' => array(
			'Content-Type'              => 'application/json',
			'Ocp-Apim-Subscription-Key' => $api_key,
		),
	) );

	/* Bail on non-200 response. */
	if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	/* Get first caption from response. */
	$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
	$caption = $response_body['description']['captions'][0] ?: false;

	/* Get confidence threshold as a float. */
	$confidence = get_option( 'aat_confidence' ) / 100;

	/* Return the caption if valid and meets confidence threshold. */
	if ( isset( $caption['text'] ) && isset( $caption['confidence'] ) && $caption['confidence'] > $confidence ) {
		return sanitize_text_field( ucfirst( $caption['text'] ) );
	} else {
		return false;
	}
}