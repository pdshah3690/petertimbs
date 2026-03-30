<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Loader for Nectar Element Dynamic Styles
 *
 * This file loads the base class and all element-specific style classes.
 * Include this file in your theme to make all element styles available.
 *
 * @since 11.1
 */

/**
 * Get the list of element class files to load
 * Add new element files here as you create them
 * Use the actual shortcode name as the filename
 *
 * This function provides a single source of truth for element files
 * and avoids global variable scope issues on different server configurations
 *
 * @return array Array of element filenames
 */
function nectar_get_dynamic_style_element_files() {
  return array(
    'nectar_sticky_media_sections.php',
    'nectar_chat_thread.php',
  );
}

/**
 * Load element dynamic styles classes
 *
 * @return bool True if classes loaded successfully, false otherwise
 */
function nectar_load_element_dynamic_styles() {

  // Prevent multiple loading
  static $loaded = false;
  if ( $loaded ) {
    return true;
  }

  $base_dir = __DIR__;
  $base_file = $base_dir . '/class-nectar-element-base.php';

  // Verify base class file exists and is readable
  if ( ! file_exists( $base_file ) || ! is_readable( $base_file ) ) {
    return false;
  }

  // Load the base class first
  try {
    require_once $base_file;

    // Verify the base class was loaded
    if ( ! class_exists( 'Nectar_Element_Base' ) ) {
      return false;
    }
  } catch ( Exception $e ) {
    return false;
  }

  // Load all element class files from the array
  $nectar_element_files = nectar_get_dynamic_style_element_files();
  $loaded_classes = array();
  $failed_files = array();

  foreach ( $nectar_element_files as $filename ) {
    $file_path = $base_dir . '/' . $filename;

    // Verify file exists and is readable
    if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
      $failed_files[] = $filename;
      continue;
    }

    // Load the element class file
    try {
      require_once $file_path;

      // Extract class name from filename and verify it was loaded
      $class_name = nectar_extract_class_name_from_filename( $filename );
      if ( $class_name && class_exists( $class_name ) ) {
        $loaded_classes[] = $class_name;
      } else {
        $failed_files[] = $filename;
      }
    } catch ( Exception $e ) {
      $failed_files[] = $filename;
    }
  }

  $loaded = true;
  return true;
}

/**
 * Convert kebab-case to PascalCase with underscores
 * PHP 8.0+ compatible version
 *
 * @param string $string The string to convert
 * @return string The converted string
 */
function nectar_convert_to_pascal_case( $string ) {
  // Split by hyphens and capitalize each part
  $parts = explode( '-', $string );
  $converted = '';

  foreach ( $parts as $part ) {
    $converted .= ucfirst( $part );
  }

  return $converted;
}

/**
 * Extract class name from filename
 * Now works with actual shortcode names like 'nectar_sticky_media_sections.php'
 *
 * @param string $filename The filename to extract class name from
 * @return string|false The class name or false if invalid
 */
function nectar_extract_class_name_from_filename( $filename ) {
  // Remove .php extension
  $class_name = str_replace( '.php', '', $filename );

  // Convert shortcode name to class name
  // 'nectar_sticky_media_sections' → 'Nectar_Sticky_Media_Sections'
  $class_name = str_replace( '_', ' ', $class_name );
  $class_name = ucwords( $class_name );
  $class_name = str_replace( ' ', '_', $class_name );

  // Basic validation - should start with Nectar_ and contain only letters, numbers, and underscores
  if ( ! preg_match( '/^Nectar_[A-Za-z0-9_]+$/', $class_name ) ) {
    return false;
  }

  return $class_name;
}

/**
 * Verify element class exists and is valid
 *
 * @param string $class_name The class name to verify
 * @return bool True if class is valid, false otherwise
 */
function nectar_verify_element_class( $class_name ) {
  if ( ! class_exists( $class_name ) ) {
    return false;
  }

  // Check if it extends the base class
  $reflection = new ReflectionClass( $class_name );
  if ( ! $reflection->isSubclassOf( 'Nectar_Element_Base' ) ) {
    return false;
  }

  // Check if it implements the required method
  if ( ! $reflection->hasMethod( 'generate_styles' ) ) {
    return false;
  }

  return true;
}

// Load the classes when this file is included
nectar_load_element_dynamic_styles();

/**
 * Helper function to get element styles
 *
 * @param string $element_name Element shortcode name (e.g., 'nectar_sticky_media_sections')
 * @param array  $atts        Element attributes
 * @param string $shortcode   Full shortcode string
 * @param string $shortcode_inner Inner shortcode content
 * @return array Array with 'css' and 'class_names' keys
 */
function nectar_get_element_styles( $element_name, $atts = array(), $shortcode = '', $shortcode_inner = '' ) {

  // Ensure classes are loaded
  nectar_load_element_dynamic_styles();

  // Validate inputs
  if ( ! is_string( $element_name ) || empty( $element_name ) ) {
    return array(
      'css' => '',
      'class_names' => ''
    );
  }

  if ( ! is_array( $atts ) ) {
    $atts = array();
  }

  if ( ! is_string( $shortcode ) ) {
    $shortcode = '';
  }

  if ( ! is_string( $shortcode_inner ) ) {
    $shortcode_inner = '';
  }

  // Convert shortcode name to class name
  $class_name = nectar_extract_class_name_from_filename( $element_name . '.php' );

  if ( ! $class_name || ! nectar_verify_element_class( $class_name ) ) {
    return array(
      'css' => '',
      'class_names' => ''
    );
  }

  try {
    return $class_name::create_and_process( $atts, $shortcode, $shortcode_inner );
  } catch ( Exception $e ) {
    return array(
      'css' => '',
      'class_names' => ''
    );
  }
}

/**
 * Helper function to get element class names only
 *
 * @param string $element_name Element shortcode name (e.g., 'nectar_sticky_media_sections')
 * @param array  $atts        Element attributes
 * @param string $shortcode   Full shortcode string
 * @param string $shortcode_inner Inner shortcode content
 * @return string Generated class names
 */
function nectar_get_element_classes( $element_name, $atts = array(), $shortcode = '', $shortcode_inner = '' ) {
  $styles = nectar_get_element_styles( $element_name, $atts, $shortcode, $shortcode_inner );
  return $styles['class_names'];
}

/**
 * Helper function to get element CSS only
 *
 * @param string $element_name Element shortcode name (e.g., 'nectar_sticky_media_sections')
 * @param array  $atts        Element attributes
 * @param string $shortcode   Full shortcode string
 * @param string $shortcode_inner Inner shortcode content
 * @return string Generated CSS
 */
function nectar_get_element_css( $element_name, $atts = array(), $shortcode = '', $shortcode_inner = '' ) {
  $styles = nectar_get_element_styles( $element_name, $atts, $shortcode, $shortcode_inner );
  return $styles['css'];
}

/**
 * Get list of available element classes
 *
 * @return array Array of available element shortcode names
 */
function nectar_get_available_elements() {
  nectar_load_element_dynamic_styles();

  $nectar_element_files = nectar_get_dynamic_style_element_files();
  $available_elements = array();

  foreach ( $nectar_element_files as $filename ) {
    $class_name = nectar_extract_class_name_from_filename( $filename );
    if ( $class_name && nectar_verify_element_class( $class_name ) ) {
      // Remove .php extension and return the shortcode name
      $element_name = str_replace( '.php', '', $filename );
      $available_elements[] = $element_name;
    }
  }

  return $available_elements;
}
