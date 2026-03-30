<?php
/**
 * Central loader for third-party integrations.
 *
 * @package Salient WordPress Theme
 * @subpackage third-party-integrations
 * @since 18.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Salient_Third_Party_Manager' ) ) {

	/**
	 * Handles loading of third-party integration files.
	 */
	class Salient_Third_Party_Manager {

		/**
		 * Boot the integration loader.
		 *
		 * @return void
		 */
		public static function boot() {

			if ( ! defined( 'NECTAR_THEME_DIRECTORY' ) ) {
				return;
			}

			$integrations = apply_filters(
				'salient_third_party_integrations',
				array(
					'seo.php',
					'autoptimize.php',
					'siteground.php',
				)
			);

			if ( ! is_array( $integrations ) ) {
				return;
			}

			foreach ( $integrations as $integration ) {
				self::load_integration( $integration );
			}
		}

		/**
		 * Load an integration file when it exists.
		 *
		 * @param string $relative_path Relative file path within the integrations directory.
		 * @return void
		 */
		private static function load_integration( $relative_path ) {

			if ( ! is_string( $relative_path ) || empty( $relative_path ) ) {
				return;
			}

			$file_path = NECTAR_THEME_DIRECTORY . '/includes/third-party-integrations/' . wp_basename( $relative_path );

			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}
}

Salient_Third_Party_Manager::boot();
