<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ReduxFramework_fluid_typography' ) ) {
    class ReduxFramework_fluid_typography {

        public $field = array();
        public $value = '';
        public $parent = null;

        /**
         * Field Constructor.
         * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
         *
         * @since ReduxFramework 1.0.0
         */
        function __construct( $field, $value, $parent ) {
            $this->parent = $parent;
            $this->field  = $field;
            $this->value  = $value;
        }

        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since ReduxFramework 1.0.0
         */
        function render() {

            // Set default values
            $defaults = array(
                'desktop' => array(
                    'min'    => '',
                    'max'    => '',
                    'custom' => ''
                ),
                'mobile' => array(
                    'min'    => '',
                    'max'    => '',
                    'custom' => ''
                )
            );

            $this->value = wp_parse_args( $this->value, $defaults );

            // Get field attributes
            $qtip_title = isset( $this->field['text_hint']['title'] ) ? 'qtip-title="' . wp_kses_post($this->field['text_hint']['title']) . '" ' : '';
            $qtip_text  = isset( $this->field['text_hint']['content'] ) ? 'qtip-content="' . wp_kses_post($this->field['text_hint']['content']) . '" ' : '';
            $readonly   = ( isset( $this->field['readonly'] ) && $this->field['readonly']) ? ' readonly="readonly"' : '';

            echo '<div class="redux-fluid-typography-container">';

            // Root label with device switcher
            echo '<div class="redux-fluid-typography-root">';
            echo '<div class="redux-fluid-typography-header">';
            echo '<h4 class="redux-fluid-typography-root-title">Root</h4>';
            echo '<div class="redux-fluid-typography-device-switcher">';
            echo '<button type="button" class="redux-fluid-typography-device-btn active" data-device="desktop" title="Desktop">';
            echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21 2H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h7l-2 3v1h8v-1l-2-3h7c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 12H3V4h18v10z"/></svg>';
            echo '</button>';
            echo '<button type="button" class="redux-fluid-typography-device-btn" data-device="mobile" title="Mobile">';
            echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14z"/></svg>';
            echo '</button>';
            echo '</div>';
            echo '</div>';

            // Desktop section
            echo '<div class="redux-fluid-typography-device-section" data-device="desktop">';
            echo '<div class="redux-fluid-typography-columns">';
            echo '<div class="redux-fluid-typography-column redux-fluid-typography-min">';
            echo '<label for="' . esc_attr($this->field['id']) . '-desktop-min">Min</label>';
            echo '<input ' . $qtip_title . $qtip_text . 'type="text" id="' . esc_attr($this->field['id']) . '-desktop-min" name="' . esc_attr($this->field['name']) . esc_attr($this->field['name_suffix']) . '[desktop][min]" value="' . esc_attr( $this->value['desktop']['min'] ) . '" class="redux-fluid-typography-input" placeholder="1rem"' . $readonly . ' />';
            echo '</div>';
            echo '<div class="redux-fluid-typography-column redux-fluid-typography-max">';
            echo '<label for="' . esc_attr($this->field['id']) . '-desktop-max">Max</label>';
            echo '<input ' . $qtip_title . $qtip_text . 'type="text" id="' . esc_attr($this->field['id']) . '-desktop-max" name="' . esc_attr($this->field['name']) . esc_attr($this->field['name_suffix']) . '[desktop][max]" value="' . esc_attr( $this->value['desktop']['max'] ) . '" class="redux-fluid-typography-input" placeholder="1.5rem"' . $readonly . ' />';
            echo '</div>';
            echo '</div>';
            echo '<div class="redux-fluid-typography-custom-section">';
            echo '<label for="' . esc_attr($this->field['id']) . '-desktop-custom">Responsive Formula</label>';
            echo '<input ' . $qtip_title . $qtip_text . 'type="text" id="' . esc_attr($this->field['id']) . '-desktop-custom" name="' . esc_attr($this->field['name']) . esc_attr($this->field['name_suffix']) . '[desktop][custom]" value="' . esc_attr( $this->value['desktop']['custom'] ) . '" class="redux-fluid-typography-input redux-fluid-typography-custom" placeholder="calc(1rem + 2vw)"' . $readonly . ' />';
            echo '</div>';
            echo '</div>';

            // Mobile section
            echo '<div class="redux-fluid-typography-device-section" data-device="mobile" style="display: none;">';
            echo '<div class="redux-fluid-typography-columns">';
            echo '<div class="redux-fluid-typography-column redux-fluid-typography-min">';
            echo '<label for="' . esc_attr($this->field['id']) . '-mobile-min">Min</label>';
            echo '<input ' . $qtip_title . $qtip_text . 'type="text" id="' . esc_attr($this->field['id']) . '-mobile-min" name="' . esc_attr($this->field['name']) . esc_attr($this->field['name_suffix']) . '[mobile][min]" value="' . esc_attr( $this->value['mobile']['min'] ) . '" class="redux-fluid-typography-input" placeholder="0.8rem"' . $readonly . ' />';
            echo '</div>';
            echo '<div class="redux-fluid-typography-column redux-fluid-typography-max">';
            echo '<label for="' . esc_attr($this->field['id']) . '-mobile-max">Max</label>';
            echo '<input ' . $qtip_title . $qtip_text . 'type="text" id="' . esc_attr($this->field['id']) . '-mobile-max" name="' . esc_attr($this->field['name']) . esc_attr($this->field['name_suffix']) . '[mobile][max]" value="' . esc_attr( $this->value['mobile']['max'] ) . '" class="redux-fluid-typography-input" placeholder="1.2rem"' . $readonly . ' />';
            echo '</div>';
            echo '</div>';
            echo '<div class="redux-fluid-typography-custom-section">';
            echo '<label for="' . esc_attr($this->field['id']) . '-mobile-custom">Responsive Formula</label>';
            echo '<input ' . $qtip_title . $qtip_text . 'type="text" id="' . esc_attr($this->field['id']) . '-mobile-custom" name="' . esc_attr($this->field['name']) . esc_attr($this->field['name_suffix']) . '[mobile][custom]" value="' . esc_attr( $this->value['mobile']['custom'] ) . '" class="redux-fluid-typography-input redux-fluid-typography-custom" placeholder="calc(0.8rem + 1.5vw)"' . $readonly . ' />';
            echo '</div>';
            echo '</div>';

            echo '</div>'; // End root
            echo '</div>'; // End container
        }

        /**
         * Enqueue Function.
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @since ReduxFramework 3.0.0
         */
        function enqueue() {

            wp_enqueue_style(
                'redux-field-fluid-typography-css',
                get_template_directory_uri() . '/nectar/redux-framework/extensions/fluid_typography/fluid_typography/field_fluid_typography.css',
                array(),
                time(),
                'all'
            );


            wp_enqueue_script(
                'redux-field-fluid-typography-js',
                get_template_directory_uri() . '/nectar/redux-framework/extensions/fluid_typography/fluid_typography/field_fluid_typography.js',
                array( 'jquery', 'redux-js' ),
                time(),
                true
            );
        }
    }
}
