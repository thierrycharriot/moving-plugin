<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://oclock.io/
 * @since             1.0.0
 * @package           Moving_Forward
 *
 * @wordpress-plugin
 * Plugin Name:       Moving Forward
 * Plugin URI:        https://oclock.io/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            O'Clock
 * Author URI:        https://oclock.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       moving-forward
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MOVING_FORWARD_VERSION', '1.0.0' );
# Add plugin url
define( 'MOVING_FORWARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
# Add plugin path
define( 'MOVING_FORWARD_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-moving-forward-activator.php
 */
function activate_moving_forward() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moving-forward-activator.php';
	Moving_Forward_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-moving-forward-deactivator.php
 */
function deactivate_moving_forward() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-moving-forward-deactivator.php';
	Moving_Forward_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_moving_forward' );
register_deactivation_hook( __FILE__, 'deactivate_moving_forward' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-moving-forward.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_moving_forward() {

	$plugin = new Moving_Forward();
	$plugin->run();

}
run_moving_forward();
