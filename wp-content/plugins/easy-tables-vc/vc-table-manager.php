<?php
/**
 * Plugin Name: Easy Tables
 * Plugin URI: http://vc.wpbakery.com/
 * Description: Table Manager for WPBakery Page Builder on Steroids
 * Version: 2.0.1
 * Author: WPBakery
 * Author URI: http://wpbakery.com
 * License: http://codecanyon.net/licenses
 *
 * @package WPBakery
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'WPB_VC_TABLE_MANAGER_VERSION', '2.0.1' );
define( 'WPB_VC_TABLE_REQUIRED_VERSION', '5.1' );

function vc_table_manager_notice() {
	echo sprintf( '<div class="updated"><p><strong>Easy Tables</strong> %s <strong><a href="http://bit.ly/vcomposer" target="_blank">WPBakery Page Builder</a></strong> %s</p></div>', esc_html__( 'requires', 'easy-tables-vc' ), esc_html__( 'plugin to be installed and activated on your site.', 'easy-tables-vc' ) );
}

function vc_table_manager_notice_version() {
	echo sprintf( '<div class="updated">
    <p><strong>Easy Tables</strong> %s <strong>%s</strong> %s <strong><a href="http://bit.ly/vcomposer" target="_blank">WPBakery Page Builder</a></strong> %s %s %s</p>
  </div>', esc_html__( 'requires', 'easy-tables-vc' ), esc_html( WPB_VC_TABLE_REQUIRED_VERSION ), esc_html__( 'version of', 'easy-tables-vc' ), esc_html__( 'plugin to be installed and activated on your site.', 'easy-tables-vc' ), esc_html__( 'Current version is', 'easy-tables-vc' ), esc_html( WPB_VC_VERSION ) );
}

// Get directory path of this plugin.
$dir = dirname( __FILE__ );

// Template manager main class is required.
require_once $dir . '/lib/vc_table_manager.php';

/**
 * Initialize Templatera with init action.
 */
function vc_table_manager_init() {
	global $vc_table_manager;
	$dir = dirname( __FILE__ );
	/**
	 * Display notice if WPBakery Page Builder is not installed or activated.
	 */
	if ( ! defined( 'WPB_VC_VERSION' ) ) {
		add_action( 'admin_notices', 'vc_table_manager_notice' );

		return;
	} elseif ( version_compare( WPB_VC_VERSION, WPB_VC_TABLE_REQUIRED_VERSION ) < 0 ) {
		add_action( 'admin_notices', 'vc_table_manager_notice_version' );

		return;
	}
	// Init or use instance of the manager.
	$vc_table_manager = new VcTableManager( $dir );
	$vc_table_manager->init();
}

/** @see vc_table_manager_init */
add_action( 'init', 'vc_table_manager_init' );
