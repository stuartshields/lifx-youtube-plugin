<?php

namespace Lifx_Youtube\Admin;

use const Lifx_Youtube\OPTION_KEY as OPTION_KEY;

const NONCE_NAME = 'lifx-youtube-settings-nonce';
const NONCE_ACTION = 'lifx-youtube-settings-save';

function bootstrap(): void {
	add_filter( 'admin_menu', __NAMESPACE__ . '\\admin_menu' );
}

/**
 * Add admin menu page.
 *
 * @return void
 */
function admin_menu() {
	add_management_page(
		esc_html__( 'Lifx YouTube Settings', 'lifx-youtube' ),
		esc_html__( 'Lifx YouTube Settings', 'lifx-youtube' ),
		'manage_options',
		'youtube-settings',
		__NAMESPACE__ . '\\register_options_page'
	);
}

/**
 * Register options page template.
 *
 * @return void
 */
function register_options_page() : void {
	if (
		isset( $_POST['updated'] )
		&& $_POST['updated'] === 'true'
	) {
		save_options();
	}

	$settings = get_youtube_option();
	?>
	<div class="wrap">

		<h2 ><?php esc_html_e( 'Lifx YouTube Settings', 'lifx-youtube' ); ?></h2>
		<form method="post">
			<input type="hidden" name="updated" value="true" />

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th>
							<label for="api_key">
								<?php esc_html_e( 'YouTube API Key:', 'lifx-youtube' ); ?>
							</label>
						</th>
						<td>
							<input
								id="api_key"
								type="password"
								name="api_key"
								class="regular-text"
								value="<?php echo esc_attr( $settings['api_key'] ); ?>"
							/><br />
							<p class="description">
								<?php
								printf(
									__( 'Get your <a href="%s" target="_blank">YouTube API Key</a>', 'lifx-youtube' ),
									esc_url( 'https://console.cloud.google.com/apis/credentials' )
								)
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label for="channel_id">
								<?php esc_html_e( 'Channel ID:', 'lifx-youtube' ); ?>
							</label>
						</th>
						<td>
							<input
								id="channel_id"
								type="text"
								name="channel_id"
								class="regular-text"
								value="<?php echo esc_attr( $settings['channel_id'] ); ?>"
							/><br />
							<p class="description">
								<?php echo __( 'Channel ID', 'lifx-youtube' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( NONCE_ACTION, NONCE_NAME ); ?>

			<p class="submit">
				<?php submit_button(); ?>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Save category homepage option settings.
 *
 * @return void
 */
function save_options() : void {
	if ( ! has_valid_nonce() ) {
		custom_display_message( 'error' );
		return;
	}

	$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
	$channel_id = sanitize_text_field( wp_unslash( $_POST['channel_id'] ) );

	$option_values = [
		'api_key' => $api_key,
		'channel_id' => $channel_id,
	];

	update_option( OPTION_KEY, $option_values );
}

/**
 * Has valid nonce.
 *
 * @return int|boolean
 */
function has_valid_nonce() {
	if ( empty( $_POST[ NONCE_NAME ] ) ) {
		return false;
	}

	$field  = wp_unslash( $_POST[ NONCE_NAME ] );
	$action = NONCE_ACTION;

	// Display success message.
	custom_display_message();

	return wp_verify_nonce( $field, $action );
}

/**
 * Custom display message based on type.
 *
 * @param string $type success|error string
 * @return void
 */
function custom_display_message( string $type = 'success' ) : void {
	$classes = sprintf( 'notice notice-%s is-dismissible', $type );
	$message = __( 'Lifx YouTube settings updated successfully.', 'lifx-youtube' );

	if ( $type === 'error' ) {
		$message = __( 'An error occurred, please try again.', 'lifx-youtube' );
	}
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<p><?php echo esc_html( $message ); ?></p>
	</div>
	<?php
}

function get_youtube_option() {
	return get_option(
		OPTION_KEY,
		[
			'api_key' => '',
			'channel_id' => '',
		]
	);
}
