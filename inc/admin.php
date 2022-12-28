<?php

namespace Lifx_Youtube\Admin;

use Lifx_Youtube\Effects;
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
		esc_html__( 'LIFX + YouTube', 'lifx-youtube' ),
		esc_html__( 'LIFX + YouTube', 'lifx-youtube' ),
		'manage_options',
		'lifx-youtube-settings',
		__NAMESPACE__ . '\\register_options_page',
	);
}

/**
 * Register options page template.
 *
 * @return void
 */
function register_options_page(): void {
	if (
		isset( $_POST['updated'] )
		&& $_POST['updated'] === 'true'
	) {
		save_options();
	}

	$settings = get_youtube_option();
	?>
	<div class="wrap">

		<h2 ><?php esc_html_e( 'LIFX + YouTube Settings', 'lifx-youtube' ); ?></h2>
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
								<?php printf(
									__( 'Get your <a href="%s" target="_blank">Channel ID</a>.', 'lifx-youtube' ),
									esc_url( 'https://studio.youtube.com/' )
								) ?>
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label for="scheduled_time">
								<?php esc_html_e( 'Run job every:', 'lifx-youtube' ); ?>
							</label>
						</th>
						<td>
							<input
								id="scheduled_time"
								type="number"
								min="5"
								name="scheduled_time"
								class="regular-text"
								value="<?php echo esc_attr( $settings['scheduled_time'] ); ?>"
							/><br />
							<p class="description">
								<?php esc_html_e( 'In Minutes.', 'lifx-youtube' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label for="allow_notifications">
								<?php esc_html_e( 'Allow Notifications:', 'lifx-youtube' ); ?>
							</label>
						</th>
						<td>
							<input
								name="allow_notifications"
								type="checkbox"
								value="1"
								<?php checked( 1, $settings['allow_notifications'] ); ?>
							><br /><br />
							<select name="allow_notification_from">
								<?php foreach ( time_increments() as $increment ) : ?>
									<option
										value="<?php echo esc_attr( $increment ); ?>"
										<?php
										selected(
											$increment,
											$settings['allow_notification_from']
										);
										?>
									>
										<?php echo esc_html( $increment ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<select name="allow_notification_to">
								<?php foreach ( time_increments() as $increment ) : ?>
									<option
										value="<?php echo esc_attr( $increment ); ?>"
										<?php
										selected(
											$increment,
											$settings['allow_notification_to']
										);
										?>
									>
										<?php echo esc_html( $increment ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<br />
							<p class="description">
								<?php
								esc_html_e(
									'Allow notifications to run only between selected times.', 'lifx-youtube'
								);
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label for="effect">
								<?php esc_html_e( 'Effect to run:', 'lifx-youtube' ); ?>
							</label>
						</th>
						<td>
							<select name="effect" id="effect">
								<?php foreach ( Effects\allowed_effects() as $effect ) : ?>
									<option
										value="<?php echo esc_attr( $effect ); ?>"
										<?php
										selected(
											$effect,
											$settings['effect']
										);
										?>
									>
										<?php echo esc_html( $effect ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<br />
							<p class="description">
								<?php
								esc_html_e(
									'Notification effect.', 'lifx-youtube'
								);
								?>
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
function save_options(): void {
	if ( ! has_valid_nonce() ) {
		custom_display_message( 'error' );
		return;
	}

	$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
	$channel_id = sanitize_text_field( wp_unslash( $_POST['channel_id'] ) );

	$scheduled_time = ! empty( $_POST['scheduled_time'] ) ? absint( $_POST['scheduled_time'] ) : 5;

	$allow_notifications = ! empty( $_POST['allow_notifications'] ) ? absint( $_POST['allow_notifications'] ) : 0;

	$effect = ! empty( $_POST['effect'] ) ? sanitize_text_field( wp_unslash( $_POST['effect'] ) ) : '';

	if ( $allow_notifications ) {
		$allow_notification_from = ! empty( $_POST['allow_notification_from'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_notification_from'] ) ) : '';
		$allow_notification_to = ! empty( $_POST['allow_notification_to'] ) ? sanitize_text_field( wp_unslash( $_POST['allow_notification_to'] ) ) : '';
	}

	// Unregister any scheduled actions.
	as_unschedule_action( 'lifx_youtube_check_for_subscribers' );

	$option_values = [
		'api_key' => $api_key,
		'channel_id' => $channel_id,
		'scheduled_time' => $scheduled_time,
		'effect' => $effect,
		'allow_notifications' => $allow_notifications,
		'allow_notification_from' => $allow_notification_from,
		'allow_notification_to' => $allow_notification_to,
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
function custom_display_message( string $type = 'success' ): void {
	$classes = sprintf( 'notice notice-%s is-dismissible', $type );
	$message = __( 'LIFX + YouTube settings updated successfully.', 'lifx-youtube' );

	if ( $type === 'error' ) {
		$message = __( 'An error occurred, please try again.', 'lifx-youtube' );
	}
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<p><?php echo esc_html( $message ); ?></p>
	</div>
	<?php
}

/**
 * Get YouTube Options
 *
 * @return void
 */
function get_youtube_option(): array {
	return get_option(
		OPTION_KEY,
		[
			'api_key' => '',
			'channel_id' => '',
			'scheduled_time' => 5,
			'effect' => 'breathe',
			'allow_notifications' => 0, // Default is false.
			'allow_notification_from' => '',
			'allow_notification_to' => '',
		]
	);
}

/**
 * Time increments
 *
 * @return array
 */
function time_increments(): array {
	return [
		'00:00',
		'00:30',
		'01:00',
		'01:30',
		'02:00',
		'02:30',
		'03:00',
		'03:30',
		'04:00',
		'04:30',
		'05:00',
		'05:30',
		'06:00',
		'06:30',
		'07:00',
		'07:30',
		'08:00',
		'08:30',
		'09:00',
		'09:30',
		'10:00',
		'10:30',
		'11:00',
		'11:30',
		'12:00',
		'12:30',
		'13:00',
		'13:30',
		'14:00',
		'14:30',
		'15:00',
		'15:30',
		'16:00',
		'16:30',
		'17:00',
		'17:30',
		'18:00',
		'18:30',
		'18:00',
		'18:30',
		'19:00',
		'19:30',
		'20:00',
		'20:30',
		'21:00',
		'21:30',
		'22:00',
		'22:30',
		'23:00',
		'23:30',
	];
}
