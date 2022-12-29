<?php
/**
 * Lifx Effects
 */
namespace Lifx_Youtube\Effects;

use function Lifx\Effects\breathe as Lifx_breath;
use function Lifx\Effects\pulse as Lifx_pulse;

function bootstrap(): void {

}

/**
 * Effects.
 *
 * @return array
 */
function allowed_effects(): array {
	return [
		'breathe' => 'Breathe',
		'pulse' => 'Pulse',
	];
}

function show_effect( string $effect_name ): void {
	switch( strtolower( $effect_name ) ) {
		case 'breathe':
			breathe();
			break;
		case 'pulse':
			pulse();
			break;
	}

}

/**
 * Breath Effect
 *
 * @param string $colour
 *
 * @return void
 */
function breathe( $colour = 'rebeccapurple' ): void {
	$colour = strtolower( $colour );
	$selector = 'all';
	$from_colour = null;
	$period = 1;
	$cycles = 1;
	$persist = false;
	$power_on = true;
	$peak = 0.5;

	/**
	 * @param string  $colour      The colour to set the light to. This takes a few formats. i.e. rebeccapurple, random, '#336699', 'hue:120 saturation:1.0 brightness:0.5'
	 * @param string  $from_colour (Optional) The colour to start the effect from. This takes a few formats. i.e. rebeccapurple, random, '#336699', 'hue:120 saturation:1.0 brightness:0.5'
	 * @param string  $selector    (Optional) Selector used to filter lights. Defaults to `all`.
	 * @param int     $period      (Optional) The time in seconds for one cycle of the effect.
	 * @param int     $cycles      (Optional) The number of times to repeat the effect.
	 * @param boolean $persist     (Optional) If false set the light back to its previous value of 'from_color' when effect ends, if true leave the last effect color.
	 * @param boolean $power_on    (Optional) If true, turn the bulb on if it is not already on.
	 * @param float   $peak        (Optional) Defines where in a period the target color is at its maximum. Minimum 0.0, maximum 1.0.
	 *
	 */
	$response = Lifx_breath( $colour, $from_colour, $selector, $period, $cycles, $persist, $power_on, $peak );

	// The response should be a 207 Multi-Status.
	if ( 207 !== wp_remote_retrieve_response_code( $response ) ) {
		error_log(
			print_r( $response->get_error_message(), true )
		);

		return;
	}

	// The response will be a 207.
	if ( 207 === wp_remote_retrieve_response_code( $response ) ) {
		$payload = json_decode( wp_remote_retrieve_body( $response ), true );
		foreach ( $payload['results'] as $light ) {
			error_log(
				"{$light['label']} is completing the breathe effect."
			);
		}
	}
}

/**
 * Pulse effect
 *
 * @param string $colour
 *
 * @return void
 */
function pulse( $colour = 'rebeccapurple' ): void {
	$colour = strtolower( $colour );
	$selector = 'all';
	$from_colour = null;
	$period = 1;
	$cycles = 1;
	$persist = false;
	$power_on = true;

	/**
	 * @param string  $colour      The colour to set the light to. This takes a few formats. i.e. rebeccapurple, random, '#336699', 'hue:120 saturation:1.0 brightness:0.5'
	 * @param string  $from_colour (Optional) The colour to start the effect from. This takes a few formats. i.e. rebeccapurple, random, '#336699', 'hue:120 saturation:1.0 brightness:0.5'
	 * @param string  $selector    (Optional) Selector used to filter lights. Defaults to `all`.
	 * @param int     $period      (Optional) The time in seconds for one cycle of the effect.
	 * @param int     $cycles      (Optional) The number of times to repeat the effect.
	 * @param boolean $persist     (Optional) If false set the light back to its previous value of 'from_color' when effect ends, if true leave the last effect color.
	 * @param boolean $power_on    (Optional) If true, turn the bulb on if it is not already on.
	 *
	 */
	$response = Lifx_pulse( $colour, $from_colour, $selector, $period, $cycles, $persist, $power_on );

	// The response should be a 207 Multi-Status.
	if ( 207 !== wp_remote_retrieve_response_code( $response ) ) {
		error_log(
			print_r( $response->get_error_message(), true )
		);

		return;
	}

	// The response will be a 207.
	if ( 207 === wp_remote_retrieve_response_code( $response ) ) {
		$payload = json_decode( wp_remote_retrieve_body( $response ), true );
		foreach ( $payload['results'] as $light ) {
			error_log(
				"{$light['label']} is completing the pulse effect."
			);
		}
	}
}
