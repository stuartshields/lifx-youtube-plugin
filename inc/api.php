<?php

namespace Lifx_Youtube\API;

use Lifx_Youtube\Admin;
use Lifx_Youtube\Effects;

const API_URL = 'https://www.googleapis.com/youtube/v3';

function bootstrap(): void {
	$settings = Admin\get_youtube_option();
	if (
		empty( $settings['api_key'] )
		&& empty( $settings['channel_id'] )
	) {
		return;
	}

	add_action( 'init', __NAMESPACE__ . '\\schedule_job' );
	add_action( 'lifx_youtube_check_for_subscribers', __NAMESPACE__ . '\\lifx_youtube_job' );
}

/**
 * Schedule Job
 *
 * @return void
 */
function schedule_job(): void {
	$settings = Admin\get_youtube_option();
	if ( false !== as_has_scheduled_action( 'lifx_youtube_check_for_subscribers' ) ) {
		return;
	}

	if ( ! $settings['allow_notifications'] ) {
		as_schedule_recurring_action(
			time(),
			$settings['scheduled_time'] * MINUTE_IN_SECONDS,
			'lifx_youtube_check_for_subscribers',
			[],
			'',
			true
		);

		return;
	}

	as_schedule_single_action(
		next_run_time( $settings ),
		'lifx_youtube_check_for_subscribers',
		[],
		'',
		true
	);
}

/**
 * A callback to run when the 'lifx_youtube_job' scheduled action is run.
 */
function lifx_youtube_job(): void {
	$settings = Admin\get_youtube_option();

	$url = add_query_arg(
		[
			'part' => 'statistics',
			'id' => $settings['channel_id'],
			'key' => $settings['api_key'],
		],
		API_URL . '/channels'
	);

	$response = wp_remote_get( $url );

	$http_response = wp_remote_retrieve_response_code( $response );
	if ( $http_response !== 200 ) {
		error_log(
			printf(
				__( 'Something went wrong - %s', 'lifx-youtube' ),
				$response
			),
		);
	}

	$resonse_body = wp_remote_retrieve_body( $response );

	$resonse_body_decoded = json_decode( $resonse_body );

	// Check hiddenSubscriberCount isn't set to true, as this won't work if it is.
	if ( $resonse_body_decoded->items[0]->statistics->hiddenSubscriberCount ) {
		error_log(
			printf(
				__( 'Something went wrong - %s', 'lifx-youtube' ),
				$response
			),
		);
	}


	$subscriber_count = $resonse_body_decoded->items[0]->statistics->subscriberCount;

	$old_subscriber_count = get_transient( 'youtube_subscriber_count' );
	if ( $old_subscriber_count ) {
		$colour = 'rebeccapurple';
		if ( $old_subscriber_count > $old_subscriber_count ) {
			$colour = 'cornflowerblue';
		} elseif ( $subscriber_count < $old_subscriber_count ) {
			$colour = 'firebrick';
		}

		Effects\show_effect( $settings['effect'], $colour );
	}

	set_transient(
		'youtube_subscriber_count',
		absint( $subscriber_count )
	);
}

/**
 * Next run time.
 *
 * @param array $settings Settings.
 *
 * @return string Next run time in seconds.
 */
function next_run_time( array $settings ): string {
	// Schedule job for today if time hasn't happened yet.
	if ( strtotime( 'today ' . $settings['allow_notification_from'] ) >= current_time( 'U' ) )
	{
		return time() + abs( strtotime( 'today ' . $settings['allow_notification_from'] ) - current_time( 'U' ) );
	} elseif (
		// Schedule the job to run tomorrow if outside our scheduled window.
		strtotime( 'today ' . $settings['allow_notification_from'] ) <= current_time( 'U' )
		&& current_time( 'U' ) >= strtotime( 'today ' . $settings['allow_notification_to'] )
	) {
		return time() + abs( strtotime( 'tomorrow ' . $settings['allow_notification_from'] ) - current_time( 'U' ) );
	} elseif (
		// Schedule last job before the scheduled window runs out.
		abs(
			strtotime( 'today ' . $settings['allow_notification_to'] ) - current_time( 'U' )
		) / 60 <= $settings['scheduled_time'] ) {
			return time() + abs( strtotime( 'today ' . $settings['allow_notification_to'] ) - current_time( 'U' ) );
	}

	return time() + $settings['scheduled_time'] * MINUTE_IN_SECONDS;
}
