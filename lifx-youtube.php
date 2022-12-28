<?php
/**
 * Plugin Name: Lifx + YouTube Plugin For WordPress
 * Plugin URI:  https://github.com/stuartshields/lifx-youtube-plugin
 * Description: Send YouTube Notifications to your Lifx Lights through WordPress.
 * Author:      Stuart Shields
 * Author URI:  https://stuartshields.com
 * Text Domain: lifx-youtube
 * Domain Path: /languages
 * Version:     0.1.0
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package     Lifx_Youtube
 */

namespace Lifx_Youtube;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const OPTION_KEY = 'lifx-youtube-settings';

require_once __DIR__ . '/inc/admin.php';
require_once __DIR__ . '/inc/api.php';
require_once __DIR__ . '/inc/effects.php';

Admin\bootstrap();
API\bootstrap();
Effects\bootstrap();
