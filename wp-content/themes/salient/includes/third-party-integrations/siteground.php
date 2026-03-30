<?php
/**
 * SiteGround Optimizer compatibility helpers.
 *
 * Ensures Salient critical assets are excluded from SiteGround's
 * combine/minify routines to prevent layout/JS regressions.
 *
 * @package Salient WordPress Theme
 * @subpackage third-party-integrations
 * @since 18.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Salient_SiteGround_Compatibility' ) ) {

	/**
	 * Adds compatibility filters for SiteGround Optimizer.
	 */
	class Salient_SiteGround_Compatibility {

		/**
		 * Hook into SiteGround Optimizer filters.
		 *
		 * @return void
		 */
		public static function init() {
			if ( ! self::is_siteground_optimizer_active() ) {
				return;
			}

			add_filter( 'sgo_javascript_combine_exclude', array( __CLASS__, 'exclude_scripts' ) );
			add_filter( 'sgo_javascript_minify_exclude', array( __CLASS__, 'exclude_scripts' ) );
			add_filter( 'sgo_javascript_async_exclude', array( __CLASS__, 'exclude_scripts' ) );

			add_filter( 'sgo_css_combine_exclude', array( __CLASS__, 'exclude_styles' ) );
			add_filter( 'sgo_css_minify_exclude', array( __CLASS__, 'exclude_styles' ) );
		}

		/**
		 * Check for the SiteGround Optimizer plugin.
		 *
		 * @return bool
		 */
		private static function is_siteground_optimizer_active() {
			return defined( 'SG_CACHEPRESS_VERSION' )
				|| defined( 'SGO_VERSION' )
				|| class_exists( 'SG_CachePress' )
				|| defined( 'SiteGround_Optimizer\\VERSION' )
				|| class_exists( 'SiteGround_Optimizer\\Loader\\Loader' );
		}

		/**
		 * Normalize URL for comparison (handles http/https/protocol-relative).
		 *
		 * @param string $url URL to normalize.
		 * @return string
		 */
		private static function normalize_url( $url ) {
			return preg_replace( '#^(?:https?:)?//#i', '//', $url );
		}

		/**
		 * Cached script handles.
		 *
		 * @var array|null
		 */
		private static $script_handles_cache = null;

		/**
		 * Cached style handles.
		 *
		 * @var array|null
		 */
		private static $style_handles_cache = null;

		/**
		 * Get Salient JS handles (cached).
		 *
		 * @return array
		 */
		private static function get_script_handles() {
			if ( null === self::$script_handles_cache ) {
				self::$script_handles_cache = self::collect_script_handles();
			}
			return apply_filters( 'salient_siteground_excluded_scripts', self::$script_handles_cache );
		}

		/**
		 * Collect Salient JS handles from the script queue.
		 *
		 * @return array
		 */
		private static function collect_script_handles() {
			$wp_scripts = wp_scripts();
			$base_url   = self::normalize_url( trailingslashit( get_template_directory_uri() ) . 'js/' );
			$handles    = array();

			foreach ( $wp_scripts->queue as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$src = self::normalize_url( $wp_scripts->registered[ $handle ]->src );
					if ( 0 === strpos( $src, $base_url ) ) {
						$handles[] = $handle;
					}
				}
			}

			return $handles;
		}

		/**
		 * Get Salient CSS handles (cached).
		 *
		 * @return array
		 */
		private static function get_style_handles() {
			if ( null === self::$style_handles_cache ) {
				self::$style_handles_cache = self::collect_style_handles();
			}
			return apply_filters( 'salient_siteground_excluded_styles', self::$style_handles_cache );
		}

		/**
		 * Collect Salient CSS handles from the style queue.
		 *
		 * @return array
		 */
		private static function collect_style_handles() {
			$wp_styles    = wp_styles();
			$theme_base   = self::normalize_url( trailingslashit( get_template_directory_uri() ) . 'css/' );
			$uploads_dir  = wp_upload_dir();
			$uploads_base = self::normalize_url( trailingslashit( $uploads_dir['baseurl'] ) . 'salient/' );
			$handles      = array();

			foreach ( $wp_styles->queue as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					$src = self::normalize_url( $wp_styles->registered[ $handle ]->src );
					if ( 0 === strpos( $src, $theme_base ) || 0 === strpos( $src, $uploads_base ) ) {
						$handles[] = $handle;
					}
				}
			}

			return $handles;
		}

		/**
		 * Add Salient scripts to SiteGround exclusion list.
		 *
		 * @param array $excluded Existing exclusions.
		 * @return array
		 */
		public static function exclude_scripts( $excluded ) {
			$excluded = is_array( $excluded ) ? $excluded : array();
			return array_merge( $excluded, self::get_script_handles() );
		}

		/**
		 * Add Salient styles to SiteGround exclusion list.
		 *
		 * @param array $excluded Existing exclusions.
		 * @return array
		 */
		public static function exclude_styles( $excluded ) {
			$excluded = is_array( $excluded ) ? $excluded : array();
			return array_merge( $excluded, self::get_style_handles() );
		}
	}

	add_action( 'after_setup_theme', array( 'Salient_SiteGround_Compatibility', 'init' ) );
}
