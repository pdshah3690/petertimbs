<?php
/**
 * Main object for controls
 *
 * @package vc_table_manager
 */

if ( ! class_exists( 'VcTableManager' ) ) {
	/**
	 * Class VcTableManager
	 */
	class VcTableManager {
		/**
		 * @var string
		 */
		protected $dir;
		/**
		 * @var string
		 */
		protected $plugin_dir;
		/**
		 * @var bool
		 */
		protected $init = false;
		/**
		 * @var string
		 */
		protected static $param_name = 'table_param';

		/**
		 * VcTableManager constructor.
		 * @param $dir
		 */
		public function __construct( $dir ) {
			$this->dir = empty( $dir ) ? dirname( dirname( __FILE__ ) ) : $dir; // Set dir or find by current file path.
			$this->plugin_dir = basename( $this->dir ); // Plugin directory name required to append all required js/css files.
		}

		/**
		 * Initialize plugin data
		 */
		public function init() {
			if ( $this->init ) {
				return;
			}
			$this->init = true;
			/** @see \VcTableManager::initPlugin */
			add_action( 'admin_print_scripts-post.php', array(
				$this,
				'initPlugin',
			), 5 );
			add_action( 'admin_print_scripts-post-new.php', array(
				$this,
				'initPlugin',
			), 5 );
			add_action( 'vc_admin_inline_editor', array(
				$this,
				'initFePlugin',
			), 5 );
			add_action( 'vc_frontend_editor_enqueue_js_css', array(
				$this,
				'assetsAdmin',
			) );
			add_action( 'wp_loaded', array(
				$this,
				'createShortcode',
			) );
			add_action( 'wp_enqueue_scripts', array(
				$this,
				'assets',
			) );
		}

		/**
		 *
		 */
		public function initPlugin() {
			load_plugin_textdomain( 'easy-tables-vc', false, basename( $this->dir ) . '/locale' );
			add_action( 'vc_backend_editor_enqueue_js_css', array(
				$this,
				'assetsAdmin',
			) );
		}

		/**
		 *
		 */
		public function initFePlugin() {
			load_plugin_textdomain( 'easy-tables-vc', false, basename( $this->dir ) . '/locale' );
		}

		/**
		 * Maps vc_table shortcode
		 */
		public function createShortcode() {
			$param_name = $this->getParamName();
			require_once $this->dir . '/lib/vc_table_param.php';
			$script_url = $this->assetUrl( 'js/table_param.min.js' );
			vc_add_shortcode_param( $param_name, 'vc_' . $param_name . '_form_field', $script_url );
			vc_add_shortcode_param( 'table_theme', 'vc_table_theme_form_field' );
			require_once $this->dir . '/lib/vc_table_shortcode.php';
			vc_map( array(
				'name' => esc_html__( 'Table', 'easy-tables-vc' ),
				'base' => 'vc_table',
				'icon' => $this->assetUrl( 'img/vc_tables.png' ),
				'category' => esc_html__( 'Content', 'easy-tables-vc' ),
				'description' => __( 'Simple table for your data', 'easy-tables-vc' ),
				'params' => array(
					array(
						'type' => 'table_theme',
						'heading' => esc_html__( 'Theme', 'easy-tables-vc' ),
						'param_name' => 'vc_table_theme',
						'value' => array(
							esc_html__( 'Default', 'easy-tables-vc' ) => 'default',

							esc_html__( 'Classic', 'easy-tables-vc' ) => 'classic',
							esc_html__( 'Classic Orange', 'easy-tables-vc' ) => 'classic_orange',
							esc_html__( 'Classic Pink', 'easy-tables-vc' ) => 'classic_pink',
							esc_html__( 'Classic Purple', 'easy-tables-vc' ) => 'classic_purple',
							esc_html__( 'Classic Blue', 'easy-tables-vc' ) => 'classic_blue',
							esc_html__( 'Classic Green', 'easy-tables-vc' ) => 'classic_green',

							esc_html__( 'Simple', 'easy-tables-vc' ) => 'simple',
							esc_html__( 'Simple Orange', 'easy-tables-vc' ) => 'simple_orange',
							esc_html__( 'Simple Pink', 'easy-tables-vc' ) => 'simple_pink',
							esc_html__( 'Simple Purple', 'easy-tables-vc' ) => 'simple_purple',
							esc_html__( 'Simple Blue', 'easy-tables-vc' ) => 'simple_blue',
							esc_html__( 'Simple Green', 'easy-tables-vc' ) => 'simple_green',
						),
					),
					array(
						'type' => $param_name,
						'holder' => 'div',
						'heading' => esc_html__( 'Table', 'easy-tables-vc' ),
						'param_name' => 'content',
						'value' => '',
						'description' => esc_html__( 'Use right click to manage table.', 'easy-tables-vc' ),
					),
					array(
						'type' => 'checkbox',
						'heading' => esc_html__( 'Allow HTML?', 'easy-tables-vc' ),
						'value' => array( esc_html__( 'Yes', 'easy-tables-vc' ) => true ),
						'param_name' => 'allow_html',
						'description' => esc_html__( 'Check if you wish to use html in the table.', 'easy-tables-vc' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'easy-tables-vc' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'easy-tables-vc' ),
					),
				),
			) );
		}

		/**
		 * Url to js/css or image assets of plugin
		 * @param $file
		 * @return string
		 */
		public function assetUrl( $file ) {
			return plugins_url( $this->plugin_dir . '/assets/' . $file );
		}

		/**
		 * Absolute path to assets files
		 * @param $file
		 * @return string
		 */
		public function assetPath( $file ) {
			return $this->dir . '/assets/' . $file;
		}

		/**
		 * Load admin required js and css files
		 */
		public function assetsAdmin() {
			wp_register_script( 'vc_jquery_handsontable', $this->assetUrl( 'lib/jquery-handsontable/dist/jquery.handsontable.full.js' ), array( 'jquery' ), WPB_VC_TABLE_MANAGER_VERSION, true );
			wp_register_script( 'vc_bootstrap_dropdown', $this->assetUrl( 'lib/bootstrap-dropdown/bootstrap.min.js' ), array(
				'jquery',
				'underscore',
				'vc_jquery_handsontable',
			), WPB_VC_TABLE_MANAGER_VERSION, true );
			wp_register_script( 'vc_plugin_table', $this->assetUrl( 'js/table.min.js' ), array( 'vc_bootstrap_dropdown' ), WPB_VC_TABLE_MANAGER_VERSION, true );
			wp_enqueue_script( 'vc_plugin_table' );
			wp_localize_script( 'vc_plugin_table', 'i18nVcTable', array(
				'enter_rows_count' => esc_html__( 'Enter rows count to add', 'easy-tables-vc' ),
				'enter_cols_count' => esc_html__( 'Enter columns count to add', 'easy-tables-vc' ),
				'max_rows_count' => esc_html__( 'Max allowed rows count to add is 10', 'easy-tables-vc' ),
				'max_cols_count' => esc_html__( 'Max allowed columns count to add is 10', 'easy-tables-vc' ),
			) );
			wp_register_style( 'vc_jquery_handsontable_css', $this->assetUrl( 'lib/jquery-handsontable/dist/jquery.handsontable.full.css' ), array(), WPB_VC_TABLE_MANAGER_VERSION );
			wp_register_style( 'vc_plugin_table_admin_css', $this->assetUrl( 'css/admin.min.css' ), array( 'vc_jquery_handsontable_css' ), WPB_VC_TABLE_MANAGER_VERSION );
			wp_enqueue_style( 'vc_plugin_table_admin_css' );
			wp_register_style( 'vc_plugin_themes_css', $this->assetUrl( 'css/themes.min.css' ), array(), WPB_VC_TABLE_MANAGER_VERSION );
			wp_enqueue_style( 'vc_plugin_themes_css' );
			wp_register_style( 'vc_plugin_table_style_css', $this->assetUrl( 'css/style.min.css' ), array(), '1.0.0' );
		}

		/**
		 * @return string
		 */
		public static function getParamName() {
			return self::$param_name;
		}

		/**
		 *
		 */
		public function assets() {
			wp_register_style( 'vc_plugin_table_style_css', $this->assetUrl( 'css/style.min.css' ), array(), WPB_VC_TABLE_MANAGER_VERSION );
			wp_register_style( 'vc_plugin_themes_css', $this->assetUrl( 'css/themes.min.css' ), array(), WPB_VC_TABLE_MANAGER_VERSION );

			wp_enqueue_style( 'vc_plugin_table_style_css' );
			wp_enqueue_style( 'vc_plugin_themes_css' );
		}
	}
}
