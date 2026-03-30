<?php
/**
 * Redux theme options Salient helpers
 *
 * @package Salient WordPress Theme
 * @subpackage helpers
 * @version 18.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Load redux and options.
$using_nectar_redux_framework = false;

if ( ! class_exists( 'ReduxFramework' ) && file_exists( NECTAR_THEME_DIRECTORY . '/nectar/redux-framework/ReduxCore/framework.php' ) ) {
	require_once NECTAR_THEME_DIRECTORY . '/nectar/redux-framework/ReduxCore/framework.php';
	$using_nectar_redux_framework = true;
}

add_action('after_setup_theme', 'salient_redux_options_initialize');

function salient_redux_options_initialize() {
	if ( ! isset( $redux_demo ) && file_exists( NECTAR_THEME_DIRECTORY . '/nectar/redux-framework/options-config.php' ) ) {
		require_once NECTAR_THEME_DIRECTORY . '/nectar/redux-framework/options-config.php';
	}
}


/**
 * Add nectar redux styling/custom deps.
 *
 * @since 5.0
 */
function nectar_redux_deps( $hook_suffix ) {

	global $using_nectar_redux_framework;

	// Detect the Redux options page by dynamic slug instead of theme name in hook suffix.
	$is_salient_redux_page = false;
	$theme_obj = wp_get_theme();
	$theme_slug = sanitize_html_class( $theme_obj->get( 'Name' ) );

	// Primary: check current screen id (e.g. 'toplevel_page_{slug}').
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( isset( $screen->id ) && $screen->id === 'toplevel_page_' . $theme_slug ) {
			$is_salient_redux_page = true;
		}
	}

	// Fallback: check the page query arg.
	if ( false === $is_salient_redux_page && isset( $_GET['page'] ) && $_GET['page'] === $theme_slug ) {
		$is_salient_redux_page = true;
	}

	if ( $is_salient_redux_page || strstr( $hook_suffix, 'Salient' ) || strstr( $hook_suffix, 'salient' ) ) {

		$nectar_theme_version = nectar_get_theme_version();

		// Ensure Salient's Redux admin base CSS always loads on our settings page.
		$salient_redux_admin_css = get_template_directory_uri() . '/nectar/redux-framework/ReduxCore/assets/css/redux-admin.css';
		wp_dequeue_style( 'redux-admin-css' );
		wp_deregister_style( 'redux-admin-css' );
		wp_enqueue_style( 'redux-admin-css', $salient_redux_admin_css, array(), $nectar_theme_version, 'all' );

		wp_enqueue_style( 'nectar_redux_admin_style', get_template_directory_uri() . '/nectar/redux-framework/salient-redux-styling.css', array(), $nectar_theme_version, 'all' );

		if ( $using_nectar_redux_framework === false ) {
			wp_enqueue_style( 'nectar_redux_select_2', get_template_directory_uri() . '/nectar/redux-framework/extensions/vendor_support/vendor/select2/select2.css', array(), time(), 'all' );
			wp_enqueue_script( 'nectar_redux_ace', get_template_directory_uri() . '/nectar/redux-framework/extensions/vendor_support/vendor/ace_editor/ace.js', array(), time(), 'all' );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'nectar_redux_deps' );



/**
 * Removes the redux demo.
 *
 * @since 5.0
 */
function nectar_removeDemoModeLink() {
	if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
		remove_action( 'admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );
	}
}



if ( is_admin() ) {

	add_action( 'init', 'nectar_removeDemoModeLink' );
	add_action( 'admin_menu', 'nectar_remove_redux_menu', 12 );

	function nectar_remove_redux_menu() {
		remove_submenu_page( 'tools.php', 'redux-about' );
	}

	/**
	 * Adds lovelo font to admin for live typography preview.
	 *
	 * @since 3.0
	 */
	if ( ! function_exists( 'nectar_admin_lovelo_font' ) ) {
		function nectar_admin_lovelo_font() {

			if( isset($_GET['page']) && $_GET['page'] === 'Salient' || isset($_GET['page']) && $_GET['page'] === 'salient' ) {
				echo "
				<!-- A font fabric font - http://fontfabric.com/lovelo-font/ -->
				<style> @font-face { font-family: 'Lovelo'; src: url('" . get_template_directory_uri() . "/css/fonts/Lovelo_Black.eot'); src: url('" . get_template_directory_uri() . "/css/fonts/Lovelo_Black.eot?#iefix') format('embedded-opentype'), url('" . get_template_directory_uri() . "/css/fonts/Lovelo_Black.woff') format('woff'),  url('" . get_template_directory_uri() . "/css/fonts/Lovelo_Black.ttf') format('truetype'), url('" . get_template_directory_uri() . "/css/fonts/Lovelo_Black.svg#loveloblack') format('svg'); font-weight: normal; font-style: normal; } </style>";
			}

		}
	}
	add_action( 'admin_head', 'nectar_admin_lovelo_font' );

}


// ------- Local Google Fonts integration -------

// Mark pending on save/import.
if ( ! function_exists('salient_local_fonts_mark_pending') ) {
  function salient_local_fonts_mark_pending( $arg1 = null, $arg2 = null ) {
    // Load the class file if not already loaded
    if ( ! class_exists( 'Salient_Local_Google_Fonts' ) ) {
      $class_file = get_template_directory() . '/nectar/redux-framework/local-fonts/class-salient-local-google-fonts.php';
      if ( file_exists( $class_file ) ) {
        require_once $class_file;
      }
    }

    // Clear any cached transients to force fresh generation (important for multisite)
    // Wrapped in try-catch to ensure save process never breaks
    if ( class_exists( 'Salient_Local_Google_Fonts' ) ) {
      try {
        Salient_Local_Google_Fonts::clear_cache();
      } catch ( Exception $e ) {
        // Silently fail - the pending flag will still trigger regeneration
        // Log error if WP_DEBUG is enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
          error_log( 'Salient Local Fonts: clear_cache error - ' . $e->getMessage() );
        }
      }
    }

    update_option( 'salient_local_google_fonts_pending', 1 );
    // Force clear object cache to ensure the value is fresh (important for Redis/Memcached in multisite)
    wp_cache_delete( 'salient_local_google_fonts_pending', 'options' );
  }
}
add_action('redux/options/salient_redux/saved', 'salient_local_fonts_mark_pending', 10, 2);
add_action('wbc_importer_after_theme_options_import', 'salient_local_fonts_mark_pending', 10, 2);

// Maybe generate on first subsequent request (front and admin), skip AJAX and non-GET.
if ( ! function_exists('salient_local_fonts_maybe_generate') ) {
  function salient_local_fonts_maybe_generate() {
    if ( isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET' ) {
      return;
    }
    if ( defined('DOING_AJAX') && DOING_AJAX ) {
      return;
    }
    $pending = (int) get_option( 'salient_local_google_fonts_pending', 0 );
    if ( 1 !== $pending ) {
      return;
    }
    if ( get_transient( 'salient_local_google_fonts_generating' ) ) {
      return;
    }
    set_transient( 'salient_local_google_fonts_generating', 1, 5 * MINUTE_IN_SECONDS );

    if ( ! class_exists( 'Salient_Local_Google_Fonts' ) ) {
      $class_file = get_template_directory() . '/nectar/redux-framework/local-fonts/class-salient-local-google-fonts.php';
      if ( file_exists( $class_file ) ) {
        require_once $class_file;
      }
    }
    if ( class_exists( 'Salient_Local_Google_Fonts' ) ) {
      Salient_Local_Google_Fonts::generate();
    }
    delete_option( 'salient_local_google_fonts_pending' );
    delete_transient( 'salient_local_google_fonts_generating' );
  }
}
add_action('template_redirect', 'salient_local_fonts_maybe_generate');
add_action('admin_init', 'salient_local_fonts_maybe_generate');
