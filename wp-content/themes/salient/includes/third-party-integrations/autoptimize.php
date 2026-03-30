<?php
/**
 * Autoptimize compatibility helpers.
 *
 * Clears the Autoptimize cache the first time a new Salient version loads.
 *
 * @package Salient WordPress Theme
 * @subpackage third-party-integrations
 * @since 18.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Salient_Autoptimize_Compatibility' ) ) {

	/**
	 * Autoptimize integration manager.
	 */
	class Salient_Autoptimize_Compatibility {

		const OPTION_KEY = 'salient_autoptimize_last_cleared_version';

		/**
		 * Boot integration.
		 *
		 * @return void
		 */
		public static function init() {
			if ( ! self::is_autoptimize_active() ) {
				return;
			}

			add_action( 'shutdown', array( __CLASS__, 'maybe_clear_cache' ) );
		}

		/**
		 * Determine if Autoptimize is active.
		 *
		 * @return bool
		 */
		private static function is_autoptimize_active() {
			return defined( 'AUTOPTIMIZE_PLUGIN_VERSION' )
				|| class_exists( 'autoptimizeCache' )
				|| function_exists( 'autoptimize_flush_cache' );
		}

		/**
		 * Clear cache when theme version changes.
		 *
		 * @return void
		 */
		public static function maybe_clear_cache() {

			if ( ! current_user_can( 'switch_themes' ) ) {
				return;
			}

			$current_version = nectar_get_theme_version();
			$cleared_version = get_option( self::OPTION_KEY, '' );

			if ( $current_version === $cleared_version ) {
				return;
			}

			if ( self::clear_cache() ) {
				update_option( self::OPTION_KEY, $current_version, false );
			}
		}

		/**
		 * Perform cache clearing.
		 *
		 * @return bool
		 */
		private static function clear_cache() {

			if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
				autoptimizeCache::clearall();
				return true;
			}

			if ( function_exists( 'autoptimize_flush_cache' ) ) {
				autoptimize_flush_cache();
				return true;
			}

			return false;
		}
	}

	add_action( 'after_setup_theme', array( 'Salient_Autoptimize_Compatibility', 'init' ) );
}
