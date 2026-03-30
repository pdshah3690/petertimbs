# Nectar Element Dynamic Styles

## Structure

- `class-nectar-element-base.php` - Base abstract class that all element styles extend
- `class-nectar-{element-name}.php` - Individual element style classes
- `loader.php` - Loads all classes and provides helper functions


## Base Class Methods

### Protected Properties
- `$atts` - Element attributes array
- `$shortcode` - Full shortcode string
- `$shortcode_inner` - Inner shortcode content
- `$css_rules` - Array of CSS rules
- `$class_names` - String of class names

### Protected Methods
- `add_css( $css )` - Add a CSS rule
- `add_class( $class_name )` - Add a class name
- `has_attr( $key, $value = null )` - Check if attribute exists/equals value
- `get_attr( $key, $default = '' )` - Get attribute value
- `get_attr_esc( $key, $default = '' )` - Get escaped attribute value
- `generate_attr_class( $attr_key, $class_prefix, $default = '' )` - Generate class from attribute
- `generate_unit_class( $attr_key, $class_prefix, $default = '' )` - Generate class with unit handling

### Public Methods
- `generate_styles()` - Abstract method to implement
- `get_css()` - Get CSS as string
- `get_class_names()` - Get class names as string
- `process()` - Process element and return results
- `create_and_process( $atts, $shortcode, $shortcode_inner )` - Static method to create and process
