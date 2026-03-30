<?php
/**
 * Salient Local Google Fonts Preload helper.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'Salient_Local_Google_Fonts_Preload' ) ) {

  class Salient_Local_Google_Fonts_Preload {

    /**
     * Output preload links for local Google fonts (body and H1).
     * Safe to call from head; will echo at most two <link rel="preload"> tags.
     */
    public static function output_preload_links() {
      $nectar_options = function_exists( 'get_nectar_theme_options' ) ? get_nectar_theme_options() : array();
      if ( ! is_array( $nectar_options ) ) {
        $nectar_options = array();
      }

      if ( ! isset( $nectar_options['typography_google_fonts_local'] ) || '1' !== $nectar_options['typography_google_fonts_local'] ) {
        return;
      }

      $css_url = get_option( 'salient_local_google_fonts_css' );
      if ( empty( $css_url ) ) {
        return;
      }

      $u_parts = wp_parse_url( $css_url );
      $u_path  = isset( $u_parts['path'] ) ? $u_parts['path'] : '';
      if ( empty( $u_path ) || ! preg_match( '#/salient/[a-f0-9]{32}/fonts\.css$#i', $u_path ) ) {
        return;
      }

      $body_family = isset( $nectar_options['body_font_family']['font-family'] ) ? $nectar_options['body_font_family']['font-family'] : '';
      $h1_family   = isset( $nectar_options['h1_font_family']['font-family'] ) ? $nectar_options['h1_font_family']['font-family'] : '';
      $body_weight = isset( $nectar_options['body_font_family']['font-weight'] ) ? preg_replace('/[^0-9]/','',$nectar_options['body_font_family']['font-weight']) : '400';
      $h1_weight   = isset( $nectar_options['h1_font_family']['font-weight'] ) ? preg_replace('/[^0-9]/','',$nectar_options['h1_font_family']['font-weight']) : '700';
      $body_style  = ( isset( $nectar_options['body_font_family']['font-style'] ) && $nectar_options['body_font_family']['font-style'] === 'italic' ) ? 'italic' : 'normal';
      $h1_style    = ( isset( $nectar_options['h1_font_family']['font-style'] ) && $nectar_options['h1_font_family']['font-style'] === 'italic' ) ? 'italic' : 'normal';

      $row_tags = self::resolve_row_tags();
      $tag_sig = implode( ',', $row_tags );

      $site_suffix = is_multisite() ? '_' . get_current_blog_id() : '';
      $pre_key = 'salient_lgf_preload_' . md5( $css_url . '|' . $body_family . '|' . $h1_family . '|' . $body_weight . '|' . $h1_weight . '|' . $body_style . '|' . $h1_style . '|' . $tag_sig . '|v8' ) . $site_suffix;

      $urls = get_transient( $pre_key );
      if ( false === $urls ) {
        $css = self::get_local_css( $css_url );
        if ( ! is_string( $css ) || $css === '' ) {
          set_transient( $pre_key, array(), 12 * HOUR_IN_SECONDS );
          return;
        }
        $urls = self::build_preload_urls( $nectar_options, $css, $row_tags, $body_family, $body_weight, $body_style );
        set_transient( $pre_key, $urls, 12 * HOUR_IN_SECONDS );
      }

      if ( is_array( $urls ) ) {
        $printed = 0;
        $seen_urls = array();
        foreach ( $urls as $item ) {
          if ( $printed >= 3 ) {
            break;
          }
          $href  = '';
          $label = 'unknown';
          if ( is_array( $item ) ) {
            $href  = isset( $item['url'] ) ? $item['url'] : '';
            $label = isset( $item['label'] ) ? $item['label'] : 'unknown';
          } elseif ( is_string( $item ) ) {
            $href = $item;
          }
          if ( empty( $href ) || isset( $seen_urls[ $href ] ) ) {
            continue;
          }
          echo '<link rel="preload" as="font" type="font/woff2" crossorigin data-salient-font="' . esc_attr( $label ) . '" href="' . esc_url( $href ) . '">';
          $seen_urls[ $href ] = true;
          $printed++;
        }
      }
    }

    private static function get_first_vc_row_content() {
      global $post;
      if ( ! $post || ! isset( $post->post_content ) ) {
        return '';
      }
      $pattern = '\\[(\\[?)(vc_row)(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
      if ( preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches ) && array_key_exists( 0, $matches ) ) {
        if ( ! empty( $matches[0] ) ) {
          return $matches[0][0];
        }
      }
      return '';
    }

    private static function resolve_row_tags() {
      if ( function_exists('is_page') && ! is_page() ) {
        return array( 'h1' );
      }
      $first_row = self::get_first_vc_row_content();
      if ( ! is_string( $first_row ) || $first_row === '' ) {
        return array( 'h1' );
      }
      $inherited = array();
      if ( preg_match_all('/inherited_font_style="(h[1-6]|i|span)"/i', $first_row, $m_inh) ) {
        $inherited = $m_inh[1];
      }
      $pref = array();
      $plain = array();
      if ( preg_match_all('/heading_tag="(h[1-6]|i|span)"/i', $first_row, $m1) ) {
        $pref = array_merge( $pref, $m1[1] );
      }
      if ( preg_match_all('/font_style="(h[1-6]|i|span)"/i', $first_row, $m2) ) {
        $pref = array_merge( $pref, $m2[1] );
      }
      if ( preg_match_all('/\b(h[1-6])\b/i', $first_row, $m4) ) {
        $plain = array_merge( $plain, $m4[1] );
      }
      $row_tags = array_merge( $inherited, $pref, $plain );
      $row_tags = array_values( array_unique( array_map( 'strtolower', $row_tags ) ) );
      $row_tags = array_values( array_diff( $row_tags, array( 'span' ) ) );
      if ( empty( $row_tags ) ) {
        $row_tags = array( 'h1' );
      }
      return $row_tags;
    }

    private static function url_exists_locally( $url ) {
      if ( ! is_string( $url ) || $url === '' ) {
        return false;
      }
      $uploads = wp_upload_dir();
      if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
        return false;
      }
      $parts = wp_parse_url( $url );
      $path  = isset( $parts['path'] ) ? $parts['path'] : '';
      if ( empty( $path ) || ! preg_match( '#/salient/([a-f0-9]{32})/([A-Za-z0-9._-]+\.woff2)$#i', $path, $m ) ) {
        return false;
      }
      $rel = 'salient/' . $m[1] . '/' . $m[2];
      $abspath = trailingslashit( $uploads['basedir'] ) . $rel;

      $basedir_real = @realpath( $uploads['basedir'] );
      $file_real    = @realpath( $abspath );
      if ( $basedir_real && $file_real && strpos( $file_real, $basedir_real ) !== 0 ) {
        return false;
      }

      if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
      }
      WP_Filesystem();
      global $wp_filesystem;
      if ( ! is_object( $wp_filesystem ) ) {
        return false;
      }
      if ( method_exists( $wp_filesystem, 'exists' ) ) {
        return (bool) $wp_filesystem->exists( $abspath );
      }
      return false;
    }

    private static function get_local_css( $css_url ) {
      $css = '';
      $uploads = wp_upload_dir();
      if ( empty( $uploads['error'] ) ) {
        if ( empty( $uploads['basedir'] ) ) {
          return '';
        }
        $basedir = trailingslashit( $uploads['basedir'] );
        $parts   = wp_parse_url( $css_url );
        $path    = isset( $parts['path'] ) ? $parts['path'] : '';
        if ( empty( $path ) || ! preg_match( '#/salient/([a-f0-9]{32})/fonts\.css$#i', $path, $m ) ) {
          return '';
        }
        $relpath = 'salient/' . $m[1] . '/fonts.css';
        $abspath = $basedir . $relpath;

        $basedir_real = @realpath( $basedir );
        $abspath_real = @realpath( $abspath );
        if ( ! $basedir_real || ! $abspath_real || strpos( $abspath_real, $basedir_real ) !== 0 ) {
          return '';
        }

        if ( file_exists( $abspath_real ) ) {
          if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
          }
          WP_Filesystem();
          global $wp_filesystem;
          if ( is_object( $wp_filesystem ) && method_exists( $wp_filesystem, 'get_contents' ) ) {
            $css = $wp_filesystem->get_contents( $abspath_real );
          }
        }
      }
      if ( ! is_string( $css ) ) {
        $css = '';
      }
      if ( $css !== '' && strlen( $css ) > 500000 ) {
        $css = substr( $css, 0, 500000 );
      }
      return $css;
    }

    private static function extract_font_url( $css_blob, $family, $weight, $style ) {
      if ( empty( $family ) || ! is_string( $css_blob ) || $css_blob === '' ) {
        return '';
      }
      $pattern = '/@font-face\s*\{[^}]*font-family\s*:\s*["\']?'.preg_quote($family,'/').'["\']?;[^}]*\}/i';
      if ( ! preg_match_all( $pattern, $css_blob, $matches ) ) {
        return '';
      }
      $blocks = $matches[0];
      $target_w = (string)( $weight !== '' ? $weight : '400' );
      $target_i = (int) $target_w;
      $target_s = strtolower( $style ?: 'normal' );
      $parsed = array();
      foreach ( $blocks as $block ) {
        $bounds = self::parse_font_weight_bounds( $block );
        if ( ! $bounds ) {
          continue;
        }
        list( $min_w, $max_w ) = $bounds;
        $s = 'normal';
        if ( preg_match('/font-style\s*:\s*(\w+)/i', $block, $ms) ) {
          $s = strtolower($ms[1]);
        }
        if ( $s !== $target_s ) {
          continue;
        }
        $unicode = '';
        if ( preg_match('/unicode-range\s*:\s*([^;]+);/i', $block, $mr) ) {
          $unicode = $mr[1];
        }
        $subset_rank = self::rank_font_subset( $unicode );
        if ( $subset_rank <= 0 ) {
          continue;
        }
        $covers = ( $target_i >= $min_w && $target_i <= $max_w );
        if ( ! $covers ) {
          continue;
        }
        $url = '';
        if ( preg_match('/url\(([^)]+\.woff2[^)]*)\)/i', $block, $mu) ) {
          $url = trim($mu[1], "\"'");
        }
        if ( empty( $url ) ) {
          continue;
        }
        $parsed[] = array(
          'min_w'       => $min_w,
          'max_w'       => $max_w,
          'covers'      => $covers,
          'subset_rank' => $subset_rank,
          'url'         => $url
        );
      }
      if ( empty( $parsed ) ) {
        return '';
      }
      return self::pick_best_font_candidate( $parsed, $target_i );
    }

    private static function map_tag_to_spec( $tag, $nectar_options ) {
      $tag = strtolower( $tag );
      if ( preg_match('/^h([1-6])$/', $tag) ) {
        $key = $tag . '_font_family';
      } elseif ( $tag === 'i' ) {
        $key = 'i_font_family';
      } else {
        $key = 'body_font_family';
      }
      $opt = isset( $nectar_options[ $key ] ) ? $nectar_options[ $key ] : array();
      $fam = isset( $opt['font-family'] ) ? $opt['font-family'] : '';
      $w   = isset( $opt['font-weight'] ) ? preg_replace('/[^0-9]/','',$opt['font-weight']) : '';
      $s   = ( isset( $opt['font-style'] ) && $opt['font-style'] === 'italic' ) ? 'italic' : 'normal';
      if ( empty( $fam ) && preg_match('/^h([2-6])$/', $tag) ) {
        $opt_h1 = isset( $nectar_options['h1_font_family'] ) ? $nectar_options['h1_font_family'] : array();
        if ( is_array( $opt_h1 ) ) {
          $fam = isset( $opt_h1['font-family'] ) ? $opt_h1['font-family'] : $fam;
          if ( empty( $w ) && isset( $opt_h1['font-weight'] ) ) {
            $w = preg_replace('/[^0-9]/','',$opt_h1['font-weight']);
          }
          if ( $s === 'normal' && isset( $opt_h1['font-style'] ) && $opt_h1['font-style'] === 'italic' ) {
            $s = 'italic';
          }
        }
      }
      if ( empty( $fam ) && $tag === 'i' ) {
        $opt_b = isset( $nectar_options['body_font_family'] ) ? $nectar_options['body_font_family'] : array();
        if ( is_array( $opt_b ) ) {
          $fam = isset( $opt_b['font-family'] ) ? $opt_b['font-family'] : $fam;
          if ( empty( $w ) && isset( $opt_b['font-weight'] ) ) {
            $w = preg_replace('/[^0-9]/','',$opt_b['font-weight']);
          }
          if ( $s === 'normal' && isset( $opt_b['font-style'] ) && $opt_b['font-style'] === 'italic' ) {
            $s = 'italic';
          }
        }
      }
      if ( empty( $w ) ) {
        $w = '400';
      }
      return array( $fam, $w, $s );
    }

    private static function pick_best_font_candidate( $candidates, $target_weight ) {
      if ( empty( $candidates ) ) {
        return '';
      }
      usort( $candidates, function( $a, $b ) use ( $target_weight ) {
        $subset_a = isset( $a['subset_rank'] ) ? (int) $a['subset_rank'] : 0;
        $subset_b = isset( $b['subset_rank'] ) ? (int) $b['subset_rank'] : 0;
        if ( $subset_a !== $subset_b ) {
          return ( $subset_a > $subset_b ) ? -1 : 1;
        }
        $span_a = self::weight_range_span( isset( $a['min_w'] ) ? $a['min_w'] : 0, isset( $a['max_w'] ) ? $a['max_w'] : 0 );
        $span_b = self::weight_range_span( isset( $b['min_w'] ) ? $b['min_w'] : 0, isset( $b['max_w'] ) ? $b['max_w'] : 0 );
        if ( $span_a !== $span_b ) {
          return ( $span_a < $span_b ) ? -1 : 1;
        }
        $diff_a = abs( (int) ( isset( $a['min_w'] ) ? $a['min_w'] : 0 ) - $target_weight );
        $diff_b = abs( (int) ( isset( $b['min_w'] ) ? $b['min_w'] : 0 ) - $target_weight );
        if ( $diff_a === $diff_b ) {
          return 0;
        }
        return ( $diff_a < $diff_b ) ? -1 : 1;
      } );
      return isset( $candidates[0]['url'] ) ? $candidates[0]['url'] : '';
    }

    private static function parse_font_weight_bounds( $block ) {
      if ( ! is_string( $block ) || $block === '' ) {
        return null;
      }
      if ( ! preg_match( '/font-weight\s*:\s*([^;]+);/i', $block, $match ) ) {
        return null;
      }
      $raw = trim( $match[1] );
      if ( preg_match( '/^([0-9]{2,4})\s+([0-9]{2,4})$/', $raw, $range ) ) {
        $min = (int) min( $range[1], $range[2] );
        $max = (int) max( $range[1], $range[2] );
        return array( $min, $max );
      }
      if ( preg_match( '/^([0-9]{2,4})$/', $raw, $single ) ) {
        $val = (int) $single[1];
        return array( $val, $val );
      }
      return null;
    }

    private static function weight_range_span( $min, $max ) {
      $min = (int) $min;
      $max = (int) $max;
      if ( $max < $min ) {
        $tmp = $min;
        $min = $max;
        $max = $tmp;
      }
      return max( 0, $max - $min );
    }

    private static function rank_font_subset( $unicode_range ) {
      if ( ! is_string( $unicode_range ) || $unicode_range === '' ) {
        return 0;
      }
      $tokens = self::get_locale_unicode_tokens();
      if ( empty( $tokens ) ) {
        return 0;
      }
      $range = strtoupper( $unicode_range );
      $score = count( $tokens ) * 10;
      foreach ( $tokens as $index => $token ) {
        if ( strpos( $range, $token ) !== false ) {
          return (int) ( $score - $index );
        }
      }
      return 0;
    }

    private static function get_locale_unicode_tokens() {
      static $cache = null;
      if ( null !== $cache ) {
        return $cache;
      }

      $locale = '';
      if ( function_exists( 'determine_locale' ) ) {
        $locale = determine_locale();
      } elseif ( function_exists( 'get_locale' ) ) {
        $locale = get_locale();
      }
      $locale = strtolower( (string) $locale );

      $groups = array(
        'latin'        => array( 'U+0000-00FF', 'U+0020-00FF', 'U+0000-007F' ),
        'latin-ext'    => array( 'U+0100', 'U+1E00', 'U+2C60' ),
        'vietnamese'   => array( 'U+0102', 'U+1EA0' ),
        'greek'        => array( 'U+0370', 'U+0380', 'U+03A3' ),
        'cyrillic'     => array( 'U+0400', 'U+0460', 'U+0500' ),
        'cyrillic-ext' => array( 'U+0460-052F', 'U+1C80', 'U+2DE0' ),
        'hebrew'       => array( 'U+0590', 'U+05D0' ),
        'arabic'       => array( 'U+0600', 'U+0750', 'U+08A0' ),
        'devanagari'   => array( 'U+0900', 'U+097F' ),
        'thai'         => array( 'U+0E00', 'U+0E7F' ),
        'armenian'     => array( 'U+0530', 'U+058F' ),
        'georgian'     => array( 'U+10A0', 'U+10FF' ),
        'hangul'       => array( 'U+1100', 'U+11FF', 'U+3130', 'U+A960', 'U+AC00' ),
        'cjk'          => array( 'U+3000', 'U+3040', 'U+30A0', 'U+4E00' ),
      );

      $order = array( 'latin-ext', 'latin' );
      if ( preg_match( '/^(bg|ru|uk|sr|mk|kk|ky|be|ba|mn|tg)/', $locale ) ) {
        $order = array( 'cyrillic', 'cyrillic-ext', 'latin' );
      } elseif ( preg_match( '/^(el)/', $locale ) ) {
        $order = array( 'greek', 'latin' );
      } elseif ( preg_match( '/^(vi)/', $locale ) ) {
        $order = array( 'vietnamese', 'latin' );
      } elseif ( preg_match( '/^(he|iw)/', $locale ) ) {
        $order = array( 'hebrew', 'latin' );
      } elseif ( preg_match( '/^(ar|fa|ur)/', $locale ) ) {
        $order = array( 'arabic', 'latin' );
      } elseif ( preg_match( '/^(hi|bn|mr|ne|ta|te|kn|ml|pa|as)/', $locale ) ) {
        $order = array( 'devanagari', 'latin' );
      } elseif ( preg_match( '/^(th)/', $locale ) ) {
        $order = array( 'thai', 'latin' );
      } elseif ( preg_match( '/^(hy)/', $locale ) ) {
        $order = array( 'armenian', 'latin' );
      } elseif ( preg_match( '/^(ka)/', $locale ) ) {
        $order = array( 'georgian', 'latin' );
      } elseif ( preg_match( '/^(ko)/', $locale ) ) {
        $order = array( 'hangul', 'cjk', 'latin' );
      } elseif ( preg_match( '/^(ja|zh)/', $locale ) ) {
        $order = array( 'cjk', 'latin' );
      }

      $tokens = array();
      foreach ( $order as $group ) {
        if ( isset( $groups[ $group ] ) ) {
          $tokens = array_merge( $tokens, $groups[ $group ] );
        }
      }
      if ( empty( $tokens ) ) {
        if ( isset( $groups['latin-ext'] ) ) {
          $tokens = array_merge( $tokens, $groups['latin-ext'] );
        }
        if ( isset( $groups['latin'] ) ) {
          $tokens = array_merge( $tokens, $groups['latin'] );
        }
      }

      $cache = array_values( array_unique( array_map( 'strtoupper', $tokens ) ) );
      return apply_filters( 'salient_lgf_locale_unicode_tokens', $cache, $locale );
    }

    private static function build_preload_urls( $nectar_options, $css, $row_tags, $body_family, $body_weight, $body_style ) {
      $urls = array();
      $body_url = self::extract_font_url( $css, $body_family, $body_weight, $body_style );
      if ( $body_url && self::url_exists_locally( $body_url ) ) {
        $urls[] = array( 'url' => $body_url, 'label' => 'body', 'fam' => $body_family, 'w' => (string)$body_weight, 's' => $body_style );
      }
      $added = 0;
      foreach ( $row_tags as $t ) {
        if ( $added >= 2 ) {
          break;
        }
        list( $fam, $w, $s ) = self::map_tag_to_spec( $t, $nectar_options );
        if ( empty( $fam ) ) {
          continue;
        }
        $u = self::extract_font_url( $css, $fam, $w, $s );
        if ( ! $u || ! self::url_exists_locally( $u ) ) {
          continue;
        }
        $exists = false;
        foreach ( $urls as $it ) {
          if ( isset( $it['fam'], $it['w'], $it['s'] ) && $it['fam'] === $fam && (string) $it['w'] === (string) $w && $it['s'] === $s ) {
            $exists = true;
            break;
          }
        }
        if ( ! $exists ) {
          $urls[] = array( 'url' => $u, 'label' => strtolower( $t ), 'fam' => $fam, 'w' => (string) $w, 's' => $s );
          $added++;
        }
      }
      return $urls;
    }
  }
}

