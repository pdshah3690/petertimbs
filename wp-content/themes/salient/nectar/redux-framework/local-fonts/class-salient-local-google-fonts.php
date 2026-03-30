<?php
/**
 * Salient Local Google Fonts Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'Salient_Local_Google_Fonts' ) ) {

  class Salient_Local_Google_Fonts {

    private const MAX_FAMILIES = 30; // sanity cap similar to established plugins
    private const MAX_FONT_BYTES = 2097152; // 2MB safety cap per file

    /**
     * Clear all cached transients for local Google fonts.
     * Call this when font options are changed to force regeneration.
     */
    public static function clear_cache() {
      // Clear the stored CSS URL to force regeneration
      delete_option( 'salient_local_google_fonts_css' );

      // Clear known transients using WordPress native functions
      // This is safer than direct WPDB queries and works with object cache

      // Build list of possible transient keys based on current configuration
      if ( function_exists('get_nectar_theme_options') ) {
        $options = get_nectar_theme_options();

        // Safety check: ensure $options is an array to prevent errors
        if ( ! is_array( $options ) ) {
          $options = array();
        }

        $families = self::collect_selected_families( $options );

        if ( ! empty( $families ) && is_array( $families ) ) {
          $families = array_map( array( __CLASS__, 'sanitize_family_entry' ), $families );
          $families = array_filter( $families );

          if ( ! empty( $families ) && is_array( $families ) ) {
            sort( $families, SORT_STRING | SORT_FLAG_CASE );
            $display_swap = ( isset( $options['typography_font_swap'] ) && '1' === $options['typography_font_swap'] );
            $family_query = implode( '%7C', $families );
            $google_css_url = 'https://fonts.googleapis.com/css?family=' . $family_query . ( $display_swap ? '&display=swap' : '' );

            // Delete CSS response transient (with and without site suffix for multisite)
            $site_suffix = is_multisite() ? '_' . get_current_blog_id() : '';
            $css_response_key = 'salient_lgf_css_' . md5( $google_css_url );
            delete_transient( $css_response_key . $site_suffix );

            // Also try without suffix in case it was cached before the fix
            if ( is_multisite() ) {
              delete_transient( $css_response_key );
            }
          }
        }
      }

      // Clear any preload transients (these have dynamic keys, so we use a helper)
      // This uses WordPress's object cache invalidation rather than direct DB queries
      self::clear_preload_transients();

      // Delete all existing cache directories to force complete regeneration
      // This ensures changed fonts/weights trigger new downloads
      self::delete_all_font_caches();
    }

    /**
     * Delete all existing font cache directories.
     * Called when options change to force fresh font downloads.
     */
    private static function delete_all_font_caches() {
      $upload_info = wp_upload_dir();
      if ( ! empty( $upload_info['error'] ) ) {
        return;
      }

      $base_dir = $upload_info['basedir'];
      $parent_dir = trailingslashit( $base_dir ) . 'salient';

      if ( ! is_dir( $parent_dir ) ) {
        return;
      }

      if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
      }
      WP_Filesystem();
      global $wp_filesystem;

      if ( empty( $wp_filesystem ) ) {
        return;
      }

      $entries = @scandir( $parent_dir );
      $parent_real = @realpath( $parent_dir );

      if ( ! is_array( $entries ) || ! $parent_real ) {
        return;
      }

      foreach ( $entries as $entry ) {
        if ( $entry === '.' || $entry === '..' ) {
          continue;
        }

        // Skip dynamic CSS files (explicit safety check)
        if ( strpos( $entry, 'salient-dynamic-styles' ) !== false ) {
          continue;
        }

        // Skip menu dynamic CSS files
        if ( strpos( $entry, 'salient-wp-menu-dynamic' ) !== false ) {
          continue;
        }

        // Only delete directories that look like MD5 hashes (our cache dirs)
        if ( ! preg_match( '/^[a-f0-9]{32}$/i', $entry ) ) {
          continue;
        }

        $path = trailingslashit( $parent_dir ) . $entry;
        $path_real = @realpath( $path );

        if ( ! $path_real || strpos( $path_real, $parent_real ) !== 0 ) {
          continue;
        }

        if ( is_dir( $path_real ) ) {
          $wp_filesystem->delete( $path_real, true );
        }
      }
    }

    /**
     * Clear preload transients safely using WordPress APIs.
     * Uses transient expiration groups when available, otherwise sets short TTL.
     */
    private static function clear_preload_transients() {
      // In environments with object cache, we can't easily delete by pattern
      // Instead, we rely on the transient keys including configuration in their hash
      // When configuration changes, new keys are generated automatically
      // Old transients will expire naturally (12 hour TTL)

      // For sites without object cache, we can attempt pattern deletion safely
      // using WordPress's native delete_expired_transients() approach
      if ( ! wp_using_ext_object_cache() ) {
        // Use WordPress's built-in function to clean up old transients
        // This is safe and respects WordPress's transient architecture
        if ( function_exists( 'delete_expired_transients' ) ) {
          delete_expired_transients( true );
        }
      }

      // Note: In persistent object cache environments (Redis, Memcached),
      // transients are automatically scoped by site, and will naturally
      // expire after their 12-hour TTL. The configuration hash in the key
      // ensures new configurations get fresh transients.
    }

    public static function generate() {
      if ( ! function_exists('get_nectar_theme_options') ) {
        return;
      }

      $options = get_nectar_theme_options();
      if ( empty( $options ) ) {
        return;
      }

      // Respect toggle.
      if ( ! isset( $options['typography_google_fonts_local'] ) || '1' !== $options['typography_google_fonts_local'] ) {
        // Ensure no stale URL is left behind.
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      // Build family request string from Redux collected typography set during frontend render.
      $families = self::collect_selected_families( $options );
      if ( empty( $families ) ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      // Normalize and sanitize family entries to prevent malformed requests.
      $families = array_map( array( __CLASS__, 'sanitize_family_entry' ), $families );
      $families = array_filter( $families );
      if ( empty( $families ) ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      // Cap number of families.
      if ( count( $families ) > self::MAX_FAMILIES ) {
        $families = array_slice( $families, 0, self::MAX_FAMILIES );
      }

      // Normalize order to stabilize cache key.
      sort( $families, SORT_STRING | SORT_FLAG_CASE );

      $display_swap = ( isset( $options['typography_font_swap'] ) && '1' === $options['typography_font_swap'] );

      $family_query   = implode( '%7C', $families );
      $google_css_url = 'https://fonts.googleapis.com/css?family=' . $family_query . ( $display_swap ? '&display=swap' : '' );

      // Ensure we're talking to allowed host over https.
      $parsed_css_url = wp_parse_url( $google_css_url );
      if ( empty( $parsed_css_url['scheme'] ) || strtolower( $parsed_css_url['scheme'] ) !== 'https' || empty( $parsed_css_url['host'] ) || strtolower( $parsed_css_url['host'] ) !== 'fonts.googleapis.com' ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      // Cache key per site (multisite safe via uploads path) and selection.
      $cache_key = md5( $google_css_url );

      $upload_info = wp_upload_dir();
      if ( ! empty( $upload_info['error'] ) ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      $base_dir   = $upload_info['basedir'];
      $base_url   = $upload_info['baseurl'];
      $parent_dir = trailingslashit( $base_dir ) . 'salient';
      $target_dir = trailingslashit( $parent_dir ) . $cache_key;
      $target_url = trailingslashit( $base_url ) . 'salient/' . $cache_key;
      // Allow hosts to override storage dir/url if needed (edge cases/offload)
      $target_dir = apply_filters( 'salient_lgf_storage_dir', $target_dir, $cache_key, $options );
      $target_url = apply_filters( 'salient_lgf_storage_url', $target_url, $cache_key, $options );
      $css_path   = $target_dir . '/fonts.css';
      $css_url    = $target_url . '/fonts.css';
      if ( is_ssl() ) {
        $css_url = set_url_scheme( $css_url, 'https' );
      }

      if ( ! function_exists( 'wp_mkdir_p' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
      }
      if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
      }
      WP_Filesystem();
      global $wp_filesystem;

      // If filesystem API is not available, bail to external.
      if ( empty( $wp_filesystem ) ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      if ( ! ( method_exists( $wp_filesystem, 'exists' ) && $wp_filesystem->exists( $target_dir ) ) ) {
        if ( function_exists( 'wp_mkdir_p' ) ) { wp_mkdir_p( $target_dir ); }
      }
      // Bail if directory isn't writable (best-effort check across transports).
      $dir_writable = true;
      if ( method_exists( $wp_filesystem, 'is_writable' ) ) {
        $dir_writable = (bool) $wp_filesystem->is_writable( $target_dir );
      }
      if ( ! $dir_writable ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      // If this configuration is already cached locally, validate it has font files before reusing.
      if ( method_exists( $wp_filesystem, 'exists' ) && $wp_filesystem->exists( $css_path ) ) {
        // Validate that we have actual font files, not just an empty CSS file
        $has_fonts = self::validate_cached_fonts( $target_dir, $wp_filesystem );
        if ( $has_fonts ) {
          update_option( 'salient_local_google_fonts_css', esc_url_raw( $css_url ) );
          return;
        }
        // If validation failed, regenerate by continuing below
      }

      // Fetch CSS with transient cache to avoid repeated network calls.
      // In multisite, add site ID to transient key to avoid cross-site conflicts.
      $site_suffix = is_multisite() ? '_' . get_current_blog_id() : '';
      $css_response_key = 'salient_lgf_css_' . md5( $google_css_url ) . $site_suffix;

      // Allow forcing a fresh fetch (useful after options save in multisite).
      $force_refresh = apply_filters( 'salient_lgf_force_refresh', false );

      $response = $force_refresh ? false : get_transient( $css_response_key );
      if ( false === $response ) {
        $default_args = array( 'timeout' => (int) apply_filters( 'salient_lgf_http_timeout', 12, 'css' ), 'headers' => array( 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122 Safari/537.36' ) );
        $args = apply_filters( 'salient_lgf_http_args', $default_args, 'css', $google_css_url );
        $response = wp_remote_get( $google_css_url, $args );
        if ( ! is_wp_error( $response ) && (int) wp_remote_retrieve_response_code( $response ) === 200 ) {
          set_transient( $css_response_key, $response, DAY_IN_SECONDS );
        }
      }
      if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      $css = wp_remote_retrieve_body( $response );
      if ( ! is_string( $css ) || $css === '' ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      // Strip non-woff2 sources from src declarations to mirror plugin behavior.
      $css = preg_replace_callback( '/src\s*:\s*([^;]+);/i', function( $m ) {
        $sources = $m[1];
        preg_match_all( '/url\(([^)]+)\)[^,;]*/i', $sources, $matches );
        $kept = array();
        if ( ! empty( $matches[0] ) ) {
          foreach ( $matches[0] as $i => $full ) {
            $u = trim( $matches[1][ $i ] );
            $u = trim( $u, "\"'" );
            if ( preg_match( '/^https:\\/\\/fonts\\.gstatic\\.com\//i', $u ) && preg_match( '/\\.woff2(\\?|\\)|$)/i', $u ) ) {
              $kept[] = $full;
            }
          }
        }
        if ( empty( $kept ) ) {
          return $m[0];
        }
        return 'src: ' . implode( ', ', $kept ) . ';';
      }, $css );

      // Mirror font files locally and rewrite urls only if written successfully.
      $max_bytes = (int) apply_filters( 'salient_lgf_max_font_bytes', self::MAX_FONT_BYTES );
      $css = preg_replace_callback( '/url\(([^)]+)\)/i', function( $m ) use ( $target_dir, $target_url, $wp_filesystem, $max_bytes ) {
        $raw = trim( $m[1] );
        $raw = trim( $raw, "\"'" );

        // Coerce protocol-relative to https
        if ( strpos( $raw, '//' ) === 0 ) {
          $src = 'https:' . $raw;
        } else {
          $src = $raw;
        }

        // Only allow https and fonts.gstatic.com
        $p = wp_parse_url( $src );
        if ( empty( $p['scheme'] ) || strtolower( $p['scheme'] ) !== 'https' || empty( $p['host'] ) || strtolower( $p['host'] ) !== 'fonts.gstatic.com' ) {
          return $m[0];
        }

        if ( ! preg_match( '/\\.woff2(\\?|$)/i', $src ) ) {
          return $m[0];
        }

        if ( empty( $p['path'] ) ) {
          return $m[0];
        }
        $filename = basename( $p['path'] );
        // Strict filename validation
        if ( ! $filename || ! preg_match( '/^[A-Za-z0-9._-]+\\.woff2$/', $filename ) ) {
          return $m[0];
        }

        $dest_path = trailingslashit( $target_dir ) . $filename;
        $dest_url  = trailingslashit( $target_url ) . $filename;
        if ( is_ssl() ) {
          $dest_url = set_url_scheme( $dest_url, 'https' );
        }

        if ( ! ( method_exists( $wp_filesystem, 'exists' ) && $wp_filesystem->exists( $dest_path ) ) ) {
          $default_args = array( 'timeout' => (int) apply_filters( 'salient_lgf_http_timeout', 12, 'font' ), 'headers' => array( 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122 Safari/537.36' ) );
          $args = apply_filters( 'salient_lgf_http_args', $default_args, 'font', $src );
          $file_resp = wp_remote_get( $src, $args );
          if ( is_wp_error( $file_resp ) || (int) wp_remote_retrieve_response_code( $file_resp ) !== 200 ) {
            return $m[0];
          }
          // Enforce size cap via header and body length
          $len_hdr = wp_remote_retrieve_header( $file_resp, 'content-length' );
          if ( is_numeric( $len_hdr ) && (int) $len_hdr > $max_bytes ) {
            return $m[0];
          }
          $bytes = wp_remote_retrieve_body( $file_resp );
          if ( ! is_string( $bytes ) || $bytes === '' || strlen( $bytes ) > $max_bytes ) {
            return $m[0];
          }
          $written = (bool) $wp_filesystem->put_contents( $dest_path, $bytes, FS_CHMOD_FILE );
          if ( ! $written ) {
            return $m[0];
          }
        }

        // Cache-buster: prefer filemtime when available locally; otherwise skip versioning
        if ( ( method_exists( $wp_filesystem, 'exists' ) && $wp_filesystem->exists( $dest_path ) ) ) {
          $ver = '';
          if ( function_exists('filemtime') && @file_exists( $dest_path ) ) { $ver = @filemtime( $dest_path ); }
          if ( empty( $ver ) ) { $ver = null; }
          if ( $ver ) { $dest_url = add_query_arg( 'v', $ver, $dest_url ); }
        }
        return 'url(' . esc_url_raw( $dest_url ) . ')';
      }, $css );

      // Ensure font-display when requested.
      if ( $display_swap ) {
        $css = preg_replace_callback( '/@font-face\s*\{[^}]*\}/i', function( $m ) {
          $block = $m[0];
          if ( stripos( $block, 'font-display' ) === false ) {
            $block = rtrim( $block, '}' ) . 'font-display: swap;}';
          }
          return $block;
        }, $css );
      }

      // Save CSS file; bail to external if writing fails.
      $css_written = (bool) $wp_filesystem->put_contents( $css_path, $css, FS_CHMOD_FILE );
      if ( ! $css_written || ! file_exists( $css_path ) ) {
        delete_option( 'salient_local_google_fonts_css' );
        return;
      }

      update_option( 'salient_local_google_fonts_css', esc_url_raw( $css_url ) );

      // Best-effort cleanup (WP core FS only). If it fails, we silently keep old caches.
      self::cleanup_old_caches( $cache_key, $base_dir );
    }

    private static function collect_selected_families( $options ) {
      $ids = array(
        'logo_font_family',
        'navigation_font_family',
        'navigation_dropdown_font_family',
        'page_heading_font_family',
        'page_heading_subtitle_font_family',
        'off_canvas_nav_font_family',
        'off_canvas_nav_subtext_font_family',
        'body_font_family',
        'h1_font_family','h2_font_family','h3_font_family','h4_font_family','h5_font_family','h6_font_family',
        'i_font_family','bold_font_family','label_font_family',
        'sidebar_footer_h_font_family','nectar_sidebar_footer_headers_font_family',
        'nectar_slider_heading_font_family','home_slider_caption_font_family',
        'testimonial_font_family','blog_single_post_content_font_family',
        'portfolio_filters_font_family','portfolio_caption_font_family',
        'team_member_h_font_family',
        'nectar_woo_shop_product_title_font_family','nectar_woo_shop_product_secondary_font_family'
      );

      $families = array();
      foreach ( $ids as $id ) {
        if ( ! isset( $options[ $id ] ) || ! is_array( $options[ $id ] ) ) {
          continue;
        }
        $font = $options[ $id ];
        if ( empty( $font['font-family'] ) ) {
          continue;
        }
        if ( isset( $font['google'] ) && ! filter_var( $font['google'], FILTER_VALIDATE_BOOLEAN ) ) {
          continue;
        }

        $family = str_replace( ' ', '+', $font['font-family'] );

        $variants = array();
        $weight  = isset( $font['font-weight'] ) ? preg_replace( '/[^0-9]/', '', (string) $font['font-weight'] ) : '';
        $is_italic = ( isset( $font['font-style'] ) && strtolower( (string) $font['font-style'] ) === 'italic' );
        if ( $weight !== '' ) {
          $variant = $weight . ( $is_italic ? 'italic' : '' );
          $variants[] = $variant;
        } elseif ( $is_italic ) {
          // Allow italic alias when no explicit weight is provided (maps to 400italic)
          $variants[] = 'italic';
        }

        if ( ! empty( $variants ) ) {
          $families[] = $family . ':' . implode( ',', array_unique( $variants ) );
        } else {
          $families[] = $family;
        }
      }

      $families = array_values( array_unique( $families ) );
      return $families;
    }

    private static function sanitize_family_entry( $entry ) {
      // Allow letters, numbers, plus signs, dashes and underscores in family name; optional variants subset
      // Examples: Roboto, Open+Sans, Roboto:400,700, Roboto:400italic, Roboto:italic
      if ( ! is_string( $entry ) ) {
        return '';
      }
      $entry = trim( $entry );
      // Remove any characters outside of the safe set
      $entry = preg_replace( '/[^A-Za-z0-9+:_,-]/', '', $entry );
      // Normalize multiple commas
      $entry = preg_replace( '/,+/', ',', $entry );
      // Basic validation
      // Accept variants in the form: <weight>, <weight>italic, or italic (comma-separated)
      if ( ! preg_match( '/^[A-Za-z0-9+_-]+(?::(?:(?:[0-9]{2,4}(?:italic)?)|italic)(?:,(?:(?:[0-9]{2,4}(?:italic)?)|italic))*)?$/', $entry ) ) {
        // fallback to just the family name if possible
        $family_only = preg_replace( '/:.*/', '', $entry );
        if ( preg_match( '/^[A-Za-z0-9+_-]+$/', $family_only ) ) {
          return $family_only;
        }
        return '';
      }
      return $entry;
    }

    private static function cleanup_old_caches( $current_key, $base_dir ) {
      $parent = trailingslashit( $base_dir ) . 'salient';
      if ( ! is_dir( $parent ) ) {
        return;
      }
      if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
      }
      WP_Filesystem();
      global $wp_filesystem;
      if ( empty( $wp_filesystem ) ) {
        return;
      }
      $entries     = @scandir( $parent );
      $parent_real = @realpath( $parent );
      if ( ! is_array( $entries ) || ! $parent_real ) {
        return;
      }
      foreach ( $entries as $entry ) {
        if ( $entry === '.' || $entry === '..' ) {
          continue;
        }
        // Skip dynamic CSS files (explicit safety check)
        if ( strpos( $entry, 'salient-dynamic-styles' ) !== false ) {
          continue;
        }
        // Skip menu dynamic CSS files
        if ( strpos( $entry, 'salient-wp-menu-dynamic' ) !== false ) {
          continue;
        }
        if ( $entry === $current_key || ! preg_match( '/^[a-f0-9]{32}$/i', $entry ) ) {
          continue;
        }
        $path      = trailingslashit( $parent ) . $entry;
        $path_real = @realpath( $path );
        if ( ! $path_real || strpos( $path_real, $parent_real ) !== 0 ) {
          continue;
        }
        if ( is_dir( $path_real ) ) {
          $wp_filesystem->delete( $path_real, true );
        }
      }
    }

    /**
     * Validate that a cached font directory actually contains font files.
     * Returns true if at least one .woff2 file exists.
     */
    private static function validate_cached_fonts( $target_dir, $wp_filesystem ) {
      if ( ! is_object( $wp_filesystem ) ) {
        return false;
      }
      $entries = @scandir( $target_dir );
      if ( ! is_array( $entries ) ) {
        return false;
      }
      foreach ( $entries as $entry ) {
        if ( $entry === '.' || $entry === '..' || $entry === 'fonts.css' ) {
          continue;
        }
        if ( preg_match( '/\.woff2$/i', $entry ) ) {
          $file_path = trailingslashit( $target_dir ) . $entry;
          if ( method_exists( $wp_filesystem, 'exists' ) && $wp_filesystem->exists( $file_path ) ) {
            return true;
          }
        }
      }
      return false;
    }



    /**
     * Output preload links for local Google fonts (body and H1).
     * Delegates to the dedicated preload class.
     */
    public static function output_preload_links() {
      if ( class_exists( 'Salient_Local_Google_Fonts_Preload' ) ) {
        Salient_Local_Google_Fonts_Preload::output_preload_links();
      }
    }
  }
}

require_once __DIR__ . '/class-salient-local-google-fonts-preload.php';

