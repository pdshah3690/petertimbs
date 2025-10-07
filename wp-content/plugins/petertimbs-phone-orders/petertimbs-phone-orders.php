<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://feathertechlabs.com
 * @since             27-08
 * @package           Petertimbs
 *
 * @wordpress-plugin
 * Plugin Name:       Petertimbs Phone Orders
 * Plugin URI:        https://feathertechlabs.com/wordpress/plugins/petertimbs
 * Description:       A Management Platform. Manage phone orders.
 * Version:           27-08
 * Author:            Feather Techlabs
 * Author URI:        https://feathertechlabs.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       petertimbs
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
define( 'PETERTIMBS_PHONE_ORDER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-petertimbs-phone-orders-activator.php
 */
function activate_petertimbs_phone_orders() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-petertimbs-phone-orders-activator.php';
	$activator = new Petertimbs_Phone_Orders_Activator;
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-petertimbs-phone-orders-deactivator.php
 */
function deactivate_petertimbs_phone_orders() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-petertimbs-phone-orders-deactivator.php';
	Petertimbs_Phone_Orders_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_petertimbs_phone_orders' );
register_deactivation_hook( __FILE__, 'deactivate_petertimbs_phone_orders' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-petertimbs-phone-orders.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pt_phone_orders() {
	$plugin = new Petertimbs_Phone_Orders();
	$plugin->run();
}

run_pt_phone_orders();
