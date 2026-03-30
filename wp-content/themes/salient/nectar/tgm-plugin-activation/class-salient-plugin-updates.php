<?php
/**
 * Seamlessly expose bundled plugin updates to WP-CLI and auto-updaters.
 *
 * @package Salient
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Salient_Plugin_Updates' ) ) {

	class Salient_Plugin_Updates {

		/**
		 * @var Salient_Plugin_Updates|null
		 */
		protected static $instance = null;

		/**
		 * @var array
		 */
		protected $plugins = array();

		/**
		 * @var string
		 */
		protected $icon_url = '';

		/**
		 * Bootstrap the singleton.
		 *
		 * @return Salient_Plugin_Updates
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Salient_Plugin_Updates constructor.
		 */
		protected function __construct() {
			$this->plugins = $this->prepare_plugins();
			$this->icon_url = $this->determine_icon_url();

			if ( empty( $this->plugins ) ) {
				return;
			}

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'inject_updates' ) );
		}

		/**
		 * Transform the TGMPA list into an associative array keyed by plugin file.
		 *
		 * @return array
		 */
		protected function prepare_plugins() {
			$plugins = array();

			if ( function_exists( 'nectar_get_required_plugins_list' ) ) {
				$registered = nectar_get_required_plugins_list();

				foreach ( $registered as $plugin ) {
					if ( empty( $plugin['slug'] ) || empty( $plugin['version'] ) || empty( $plugin['source'] ) ) {
						continue;
					}

					$plugin_file = '';

					if ( ! empty( $plugin['plugin_file'] ) ) {
						$plugin_file = $plugin['plugin_file'];
					} else {
						$plugin_file = trailingslashit( $plugin['slug'] ) . $plugin['slug'] . '.php';
					}

					$plugins[ $plugin_file ] = array(
						'slug'      => $plugin['slug'],
						'name'      => isset( $plugin['name'] ) ? $plugin['name'] : $plugin['slug'],
						'version'   => $plugin['version'],
						'source'    => $plugin['source'],
						'changelog' => isset( $plugin['changelog'] ) ? $plugin['changelog'] : '',
					);
				}
			}

			return apply_filters( 'salient_plugin_updates_catalog', $plugins );
		}

		/**
		 * Add bundled plugins to the standard plugin update transient.
		 *
		 * @param stdClass $transient Update transient.
		 * @return stdClass
		 */
		public function inject_updates( $transient ) {
			if ( empty( $this->plugins ) ) {
				return $transient;
			}

			if ( ! is_object( $transient ) ) {
				$transient = new stdClass();
			}

			if ( empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
				return $transient;
			}

			foreach ( $this->plugins as $plugin_file => $data ) {
				if ( empty( $transient->checked[ $plugin_file ] ) ) {
					continue;
				}

				$installed_version = $transient->checked[ $plugin_file ];

				if ( version_compare( $installed_version, $data['version'], '>=' ) ) {
					continue;
				}

				// Only advertise an update if the bundled ZIP exists and is readable.
				if ( ! $this->is_package_available( $data['source'] ) ) {
					continue;
				}

				$response = array(
					'slug'        => $data['slug'],
					'plugin'      => $plugin_file,
					'new_version' => $data['version'],
					'package'     => $data['source'],
					'url'         => $data['changelog'],
				);

				$icons = $this->get_plugin_icons();

				if ( ! empty( $icons ) ) {
					$response['icons'] = $icons;
				}

				$transient->response[ $plugin_file ] = (object) $response;
			}

			return $transient;
		}

		/**
		 * Confirm the bundled package can be read from disk.
		 *
		 * @param string $path Local ZIP path.
		 * @return bool
		 */
		protected function is_package_available( $path ) {
			return is_string( $path ) && is_file( $path ) && is_readable( $path );
		}

		/**
		 * Determine the URL for the bundled plugin icon.
		 *
		 * @return string
		 */
		protected function determine_icon_url() {
			$icon_path = trailingslashit( get_template_directory() ) . 'img/salient-logo.jpg';

			if ( ! file_exists( $icon_path ) ) {
				return '';
			}

			return trailingslashit( get_template_directory_uri() ) . 'img/salient-logo.jpg';
		}

		/**
		 * Retrieve the array of icons to associate with plugin updates.
		 *
		 * @return array
		 */
		protected function get_plugin_icons() {
			if ( empty( $this->icon_url ) ) {
				return array();
			}

			return array(
				'default' => $this->icon_url,
				'1x'      => $this->icon_url,
				'2x'      => $this->icon_url,
			);
		}
	}

	Salient_Plugin_Updates::instance();
}
