<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Base class for Nectar Element Dynamic Styles.
 *
 * This class provides the foundation for all element-specific style generators.
 * Each element extends this class and implements the generate_styles() method.
 *
 * @since 11.1
 */
abstract class Nectar_Element_Base {

  /**
   * Element attributes array
   *
   * @var array
   */
  protected $atts = array();

  /**
   * Element shortcode content
   *
   * @var string
   */
  protected $shortcode = '';

  /**
   * Element shortcode inner content
   *
   * @var string
   */
  protected $shortcode_inner = '';

  /**
   * Generated CSS rules
   *
   * @var array
   */
  protected $css_rules = array();

  /**
   * Generated class names
   *
   * @var string
   */
  protected $class_names = '';

  /**
   * Constructor
   *
   * @param array  $atts           Element attributes
   * @param string $shortcode      Full shortcode string
   * @param string $shortcode_inner Inner shortcode content
   */
  public function __construct( $atts = array(), $shortcode = '', $shortcode_inner = '' ) {
    $this->atts = $atts;
    $this->shortcode = $shortcode;
    $this->shortcode_inner = $shortcode_inner;
    $this->css_rules = array();
    $this->class_names = '';
  }

  /**
   * Generate styles for the element
   *
   * This method must be implemented by each element class.
   * It should populate $this->css_rules and $this->class_names
   *
   * @return void
   */
  abstract public function generate_styles();

  /**
   * Get the generated CSS rules as a string
   *
   * @return string CSS rules
   */
  public function get_css() {
    return implode( "\n", $this->css_rules );
  }

  /**
   * Get the generated class names
   *
   * @return string Class names
   */
  public function get_class_names() {
    return trim( $this->class_names );
  }

  /**
   * Add a CSS rule to the collection
   *
   * @param string $css CSS rule
   * @return void
   */
  protected function add_css( $css ) {
    if ( ! empty( $css ) ) {
      $this->css_rules[] = $css;
    }
  }

  /**
   * Add a class name to the collection
   *
   * @param string $class_name Class name to add
   * @return void
   */
  protected function add_class( $class_name ) {
    if ( ! empty( $class_name ) ) {
      $this->class_names .= $class_name . ' ';
    }
  }

  /**
   * Check if an attribute exists and optionally compare its value
   *
   * @param string $key   Attribute key
   * @param mixed  $value Optional value to compare against
   * @return bool
   */
  protected function has_attr( $key, $value = null ) {
    if ( ! isset( $this->atts[ $key ] ) ) {
      return false;
    }

    if ( $value !== null ) {
      return $this->atts[ $key ] === $value;
    }

    return true;
  }

  /**
   * Get an attribute value with optional default
   *
   * @param string $key     Attribute key
   * @param mixed  $default Default value if attribute doesn't exist
   * @return mixed
   */
  protected function get_attr( $key, $default = '' ) {
    // Check if attribute exists and has a non-empty value
    if ( isset( $this->atts[ $key ] ) && ! empty( $this->atts[ $key ] ) ) {
      return $this->atts[ $key ];
    }
    return $default;
  }

  /**
   * Get an attribute value and escape it for CSS
   *
   * @param string $key     Attribute key
   * @param mixed  $default Default value if attribute doesn't exist
   * @return string
   */
  protected function get_attr_esc( $key, $default = '' ) {
    return esc_attr( $this->get_attr( $key, $default ) );
  }

  /**
   * Generate a CSS class based on attribute value
   *
   * @param string $attr_key    Attribute key
   * @param string $class_prefix CSS class prefix
   * @param mixed  $default     Default value if attribute doesn't exist
   * @return string Generated class name
   */
  protected function generate_attr_class( $attr_key, $class_prefix, $default = '' ) {
    $value = $this->get_attr( $attr_key, $default );
    if ( ! empty( $value ) && $value !== $default ) {
      return $class_prefix . esc_attr( $value );
    }
    return '';
  }

  /**
   * Generate a CSS class with percent/unit type handling
   *
   * @param string $attr_key    Attribute key
   * @param string $class_prefix CSS class prefix
   * @param mixed  $default     Default value if attribute doesn't exist
   * @return string Generated class name
   */
  protected function generate_unit_class( $attr_key, $class_prefix, $default = '' ) {
    $value = $this->get_attr( $attr_key, $default );
    if ( ! empty( $value ) && $value !== $default ) {
      // Use the existing nectar_el_percent_unit_type_class function if available
      if ( function_exists( 'nectar_el_percent_unit_type_class' ) ) {
        return $class_prefix . nectar_el_percent_unit_type_class( esc_attr( $value ) );
      }
      return $class_prefix . esc_attr( $value );
    }
    return '';
  }

  /**
   * Process the element and return both CSS and class names
   *
   * @return array Array with 'css' and 'class_names' keys
   */
  public function process() {
    $this->generate_styles();

    return array(
      'css' => $this->get_css(),
      'class_names' => $this->get_class_names()
    );
  }

  /**
   * Static method to create and process an element
   *
   * @param array  $atts           Element attributes
   * @param string $shortcode      Full shortcode string
   * @param string $shortcode_inner Inner shortcode content
   * @return array Array with 'css' and 'class_names' keys
   */
  public static function create_and_process( $atts = array(), $shortcode = '', $shortcode_inner = '' ) {
    $instance = new static( $atts, $shortcode, $shortcode_inner );
    return $instance->process();
  }





  /**
  * Prepares font sizing
  */
  public function font_sizing_format($str) {

    if( strpos($str,'vw') !== false ||
      strpos($str,'vh') !== false ||
      strpos($str,'em') !== false ||
      strpos($str,'rem') !== false ) {
      return $str;
    } else {
      return intval($str) . 'px';
    }

  }


  /**
  * Verifies custom coloring is in use.
  */
  public function custom_color_bool($param, $atts) {

    if(isset($atts[$param.'_type']) &&
			!empty($atts[$param.'_type']) &&
			'custom' === $atts[$param.'_type'] &&
			isset($atts[$param.'_custom']) &&
			!empty($atts[$param.'_custom']) ) {
			return true;
		}
		return false;

  }



  /**
  * Determines the unit type px or percent
  */
  public function percent_unit_type($str, $forced_unit = false) {

    if( false !== $forced_unit ) {
      return floatval($str) . $forced_unit;
    }

    if( false !== strpos($str,'%') ) {
      return intval($str) . '%';
    }
    else if( false !== strpos($str,'vh') ) {
      return intval($str) . 'vh';
    }
    else if( false !== strpos($str,'vw') ) {
      return intval($str) . 'vw';
    }
    else if( false !== strpos($str,'rem') ) {
      return intval($str) . 'rem';
    }
    else if( false !== strpos($str,'em') ) {
      return intval($str) . 'em';
    }
    else if( false !== strpos($str,'deg') ) {
      return intval($str) . 'deg';
    }
    else if( false !== strpos($str,'scale') ) {
      return intval($str)/100;
    }
    else if( 'auto' === $str ) {
			return 'auto';
		}
    return intval($str) . 'px';

  }

  /**
  * Determines the unit type classname
  */
  public function percent_unit_type_class($str, $forced_unit = false) {

    if( $forced_unit !== false ) {
      return str_replace('.','-',floatval($str)) . $forced_unit;
    }

    if( false !== strpos($str,'%') ) {
      return str_replace('%','pct', $str);
    } else if( false !== strpos($str,'vh') ) {
      return intval($str) . 'vh';
    } else if( false !== strpos($str,'vw') ) {
      return intval($str) . 'vw';
    } else if( false !== strpos($str,'rem') ) {
      return intval($str) . 'rem';
    } else if( false !== strpos($str,'em') ) {
      return intval($str) . 'em';
    } else if( 'auto' === $str ) {
			return 'auto';
		}

    return intval($str) . 'px';
  }

  public function font_size_output($str) {

    if( false !== strpos($str,'%') ) {
      return floatval($str) . '%';
    } else if( false !== strpos($str,'vh') ) {
      return floatval($str) . 'vh';
    } else if( false !== strpos($str,'vw') ) {
      return floatval($str) . 'vw';
    } else if( false !== strpos($str,'rem') ) {
      return floatval($str) . 'rem';
    } else if( false !== strpos($str,'em') ) {
      return floatval($str) . 'em';
    } else if( 'inherit' === $str ) {
			return 'inherit';
		}

    return floatval($str) . 'px';
  }


  public function line_height_output($str) {

    if( false !== strpos($str,'%') ) {
      return floatval($str) . '%';
    } else if( false !== strpos($str,'vh') ) {
      return floatval($str) . 'vh';
    } else if( false !== strpos($str,'vw') ) {
      return floatval($str) . 'vw';
    } else if( false !== strpos($str,'em') ) {
      return floatval($str) . 'em';
    } else if( false !== strpos($str,'px') ) {
      return floatval($str) . 'px';
    } else if( 'inherit' === $str ) {
			return 'inherit';
		}

    return floatval($str);
  }

  public function decimal_unit_type_class($str) {

    if( false !== strpos($str,'.') ) {
			return str_replace('.','-', $str);
		}
		else {
      return $this->percent_unit_type_class($str);
    }

	}

  /**
  * Generates the padding for a specific aspect ratio.
  */
  public function image_aspect_ratio($aspect) {

    if( '4-3' === $aspect )  {
      return 'calc((3 / 4) * 100%)';
    }
    else if( '3-2' === $aspect )  {
      return 'calc((2 / 3) * 100%)';
    }
    else if( '16-9' === $aspect )  {
      return 'calc((9 / 16) * 100%)';
    }
    else if( '2-1' === $aspect )  {
      return 'calc((1 / 2) * 100%)';
    }
    else if( '4-5' === $aspect )  {
      return 'calc((5 / 4) * 100%)';
    }
    else { //1-1 default
      return 'calc((1 / 1) * 100%)';
    }
  }

  public function image_aspect_ratio_direct($aspect) {

    if( '4-3' === $aspect )  {
      return '4 / 3';
    }
    else if( '3-2' === $aspect )  {
      return '3 / 2';
    }
    else if( '16-9' === $aspect )  {
      return '16 / 9';
    }
    else if( '2-1' === $aspect )  {
      return '2 / 1';
    }
    else if( '4-5' === $aspect )  {
      return '4 / 5';
    }
    else { //1-1 default
      return '1 / 1';
    }
  }


}
