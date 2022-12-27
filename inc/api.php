<?php

namespace Lifx_Youtube\API;

use Lifx_Youtube\Admin;

use function Lifx\Effects\breathe;

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
	add_action( 'check_for_subscribers', __NAMESPACE__ . '\\lifx_youtube_job' );
}

/**
 * Schedule Job
 *
 * @return void
 */
function schedule_job(): void {
	if ( false === as_has_scheduled_action( 'lifx_youtube_job' ) ) {
		$settings = Admin\get_youtube_option();
		as_schedule_recurring_action(
			time(),
			$settings['scheduled_time'] * MINUTE_IN_SECONDS,
			'lifx_youtube_check_for_subscribers',
			[],
			'',
			true
		);
	}
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
		$from_colour = null;
		$selector = 'all';
		$period = 1;
		$cycles = 1;
		$persist = false;
		$power_on = true;
		$peak = 0.5;

		if ( $old_subscriber_count > $old_subscriber_count ) {
			$colour = 'cornflowerblue';

		} elseif ( $subscriber_count < $old_subscriber_count ) {
			$colour = 'firebrick';
		}

		$lifx_response = breathe(
			$colour,
			$from_colour,
			$selector,
			$period,
			$cycles,
			$persist,
			$power_on,
			$peak
		);

		// The response should be a 207 Multi-Status.
		if ( 207 !== wp_remote_retrieve_response_code( $lifx_response ) ) {
			error_log( $lifx_response->get_error_message() );

			return;
		}

		// The response will be a 207.
		if ( 207 === wp_remote_retrieve_response_code( $lifx_response ) ) {
			$payload = json_decode( wp_remote_retrieve_body( $lifx_response), true );
			foreach ( $payload['results'] as $light ) {
				error_log( "{$light['label']} has completed the breathe effect." );
			}
		}
	}

	set_transient(
		'youtube_subscriber_count',
		absint( $subscriber_count )
	);
}
