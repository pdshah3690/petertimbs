<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @subpackage  Field_social_repeater
 * @author      Custom Extension
 * @version     1.0.0
 */

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) ) {
    exit;
}

// Don't duplicate me!
if ( !class_exists ( 'ReduxFramework_social_repeater' ) ) {

    /**
     * Main ReduxFramework_social_repeater class
     *
     * @since       1.0.0
     */
    class ReduxFramework_social_repeater {

        public $field = array();
        public $value = '';
        public $parent = null;

        /**
         * Field Constructor.
         * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        function __construct ( $field = array(), $value = '', $parent = null ) {
            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;
        }

        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function render () {

            $defaults = array(
                'content_title' => __( 'Social Network', 'salient' ),
                'max_items' => 20
            );

            $this->field = wp_parse_args ( $this->field, $defaults );

            // Start the accordion container
            echo '<div class="redux-social-repeater-accordion" data-new-content-title="' . esc_attr ( sprintf ( __( 'New %s', 'salient' ), $this->field[ 'content_title' ] ) ) . '">';

            $x = 0;

            // Generate items for existing data
            if ( isset ( $this->value ) && is_array ( $this->value ) && !empty ( $this->value ) ) {

                $social_items = $this->value;

                foreach ( $social_items as $item ) {

                    if ( empty ( $item ) ) {
                        continue;
                    }

                    $defaults = array(
                        'name' => '',
                        'url' => '',
                        'icon' => '',
                        'attachment_id' => '',
                        'thumb' => '',
                        'width' => '',
                        'height' => '',
                    );
                    $item = wp_parse_args ( $item, $defaults );

                    if ( empty ( $item[ 'thumb' ] ) && !empty ( $item[ 'attachment_id' ] ) ) {
                        $img = wp_get_attachment_image_src ( $item[ 'attachment_id' ], 'full' );
                        $item[ 'icon' ] = $img[ 0 ];
                        $item[ 'width' ] = $img[ 1 ];
                        $item[ 'height' ] = $img[ 2 ];
                    }

                    // Generate accordion group inside the accordion container
                    echo '<div class="redux-social-repeater-accordion-group"><fieldset class="redux-field" data-id="' . esc_attr($this->field[ 'id' ]) . '"><h3><span class="redux-social-repeater-header">' . esc_attr($item[ 'name' ]) . '</span></h3><div>';

                    // Icon type selector - moved to top
                    $icon_type = isset($item['icon_type']) ? $item['icon_type'] : 'icon';
                    echo '<div class="redux-social-repeater-icon-type">';
                    echo '<label>' . __( 'Type', 'salient' ) . '</label>';
                    echo '<div class="button-set">';
                    echo '<input type="radio" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][icon_type]' . esc_attr($this->field['name_suffix']) . '" id="icon_type_icon_' . $x . '" value="icon" ' . checked($icon_type, 'icon', false) . ' />';
                    echo '<label for="icon_type_icon_' . $x . '" class="button-set-label">' . __( 'Icon Library', 'salient' ) . '</label>';
                    echo '<input type="radio" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][icon_type]' . esc_attr($this->field['name_suffix']) . '" id="icon_type_custom_' . $x . '" value="custom" ' . checked($icon_type, 'custom', false) . ' />';
                    echo '<label for="icon_type_custom_' . $x . '" class="button-set-label">' . __( 'Custom Icon', 'salient' ) . '</label>';
                    echo '</div>';
                    echo '</div>';

                    // Screenshot - only show for custom images
                    $hide = '';
                    if ( empty ( $item[ 'icon' ] ) || $icon_type === 'icon' ) {
                        $hide = ' hide';
                    }

                    echo '<div class="screenshot' . $hide . '">';
                    echo '<a class="of-uploaded-image" href="' . esc_attr($item[ 'icon' ]) . '">';
                    echo '<img class="redux-social-repeater-image" id="image_image_id_' . esc_attr($x) . '" src="' . esc_attr($item[ 'thumb' ]) . '" target="_blank" rel="external" />';
                    echo '</a>';
                    echo '</div>';

                    // Custom image upload section
                    $custom_upload_class = ($icon_type === 'icon') ? ' hide' : '';
                    echo '<div class="redux_social_repeater_add_remove' . $custom_upload_class . '">';

                    echo '<span class="button media_upload_button" id="add_' . esc_attr($x) . '">' . __( 'Upload Icon', 'salient' ) . '</span>';

                    $hide = '';
                    if ( empty ( $item[ 'icon' ] ) || $item[ 'icon' ] == '' ) {
                        $hide = ' hide';
                    }

                    echo '<span class="button remove-image' . $hide . '" id="reset_' . esc_attr($x) . '" rel="' . esc_attr($item[ 'attachment_id' ]) . '">' . __( 'Remove', 'salient' ) . '</span>';

                    echo '</div>' . "\n";

                    // Icon selector section
                    $icon_selector_class = ($icon_type === 'custom') ? ' hide' : '';
                    echo '<div class="redux-social-repeater-icon-selector' . $icon_selector_class . '">';
                    echo '<label>' . __( 'Select Icon', 'salient' ) . '</label>';
                    echo '<div class="icon-grid">';

                    // Get brand icons from theme function
                    $brand_icons = function_exists('nectar_brands_icon_list') ? nectar_brands_icon_list() : [];

                    foreach ($brand_icons as $icon_name => $icon_unicode) {
                        $display_name = str_replace(['nectar-brands-', '-'], ['', ' '], $icon_name);
                        $display_name = ucwords($display_name);
                        echo '<div class="icon-option" data-icon="' . esc_attr($icon_name) . '" data-icon-class="' . esc_attr($icon_name) . '">';
                        echo '<i class="' . esc_attr($icon_name) . '"></i>';
                        echo '<span>' . esc_html($display_name) . '</span>';
                        echo '</div>';
                    }

                    echo '</div>';
                    echo '<input type="hidden" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][selected_icon]' . esc_attr($this->field['name_suffix']) . '" id="selected_icon_' . $x . '" value="' . esc_attr(isset($item['selected_icon']) ? $item['selected_icon'] : '') . '" />';
                    echo '</div>';

                    echo '<ul id="' . esc_attr($this->field[ 'id' ]) . '-ul-' . esc_attr($x) . '" class="redux-social-repeater-list">';

                    $label = ( isset ( $this->field[ 'labels' ][ 'name' ] ) ) ? esc_attr ( $this->field[ 'labels' ][ 'name' ] ) : __( 'Network Name', 'salient' );
                    $placeholder = ( isset ( $this->field[ 'placeholder' ][ 'name' ] ) ) ? esc_attr ( $this->field[ 'placeholder' ][ 'name' ] ) : __( 'e.g., MySpace, TikTok', 'salient' );
                    echo '<li><label for="' . esc_attr($this->field[ 'id' ]) . '-name_' . esc_attr($x) . '">' . $label . '</label><input type="text" id="' . esc_attr($this->field[ 'id' ]) . '-name_' . esc_attr($x) . '" name="' . esc_attr($this->field[ 'name' ]) . '[' . esc_attr($x) . '][name]' . esc_attr($this->field['name_suffix']) . '" value="' . esc_attr ( $item[ 'name' ] ) . '" placeholder="' . $placeholder . '" class="full-text social-name" /></li>';

                    $label = ( isset ( $this->field[ 'labels' ][ 'url' ] ) ) ? esc_attr ( $this->field[ 'labels' ][ 'url' ] ) : __( 'Profile URL', 'salient' );
                    $placeholder = ( isset ( $this->field[ 'placeholder' ][ 'url' ] ) ) ? esc_attr ( $this->field[ 'placeholder' ][ 'url' ] ) : __( 'e.g., https://myspace.com/username', 'salient' );
                    echo '<li><label for="' . esc_attr($this->field[ 'id' ]) . '-url_' . $x . '">' . $label . '</label><input type="text" id="' . esc_attr($this->field[ 'id' ]) . '-url_' . $x . '" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][url]' . esc_attr($this->field['name_suffix']) .'" value="' . esc_attr ( $item[ 'url' ] ) . '" class="full-text" placeholder="' . $placeholder . '" /></li>';

                    echo '<li class="redux-social-repeater-hidden-fields"><input type="hidden" class="upload-id" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][attachment_id]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_id_' . $x . '" value="' . $item[ 'attachment_id' ] . '" />';
                    echo '<input type="hidden" class="upload-thumbnail" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][thumb]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-thumb_url_' . $x . '" value="' . $item[ 'thumb' ] . '" readonly="readonly" />';
                    echo '<input type="hidden" class="upload" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][icon]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_url_' . $x . '" value="' . $item[ 'icon' ] . '" readonly="readonly" />';
                    echo '<input type="hidden" class="upload-height" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][height]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_height_' . $x . '" value="' . $item[ 'height' ] . '" />';
                    echo '<input type="hidden" class="upload-width" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][width]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_width_' . $x . '" value="' . $item[ 'width' ] . '" /></li>';
                    echo '<li><a href="javascript:void(0);" class="button deletion redux-social-repeater-remove">' . __( 'Delete', 'salient' ) . '</a></li>';
                    echo '</ul></div></fieldset></div>';
                    $x ++;
                }
            }

            // Generate empty state if no data exists
            if ( $x == 0 ) {
                echo '<div class="redux-social-repeater-accordion-group"><fieldset class="redux-field" data-id="' . esc_attr($this->field[ 'id' ]) . '"><h3><span class="redux-social-repeater-header">' . esc_attr ( sprintf ( __( 'New %s', 'salient' ), $this->field[ 'content_title' ] ) ) . '</span></h3><div>';

                $hide = ' hide';

                echo '<div class="screenshot' . $hide . '">';
                echo '<a class="of-uploaded-image" href="">';
                echo '<img class="redux-social-repeater-image" id="image_image_id_' . esc_attr($x) . '" src="" target="_blank" rel="external" />';
                echo '</a>';
                echo '</div>';

                // Icon type selector
                echo '<div class="redux-social-repeater-icon-type">';
                echo '<label>' . __( 'Type', 'salient' ) . '</label>';
                echo '<div class="button-set">';
                echo '<input type="radio" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][icon_type]' . esc_attr($this->field['name_suffix']) . '" id="icon_type_icon_' . $x . '" value="icon" checked />';
                echo '<label for="icon_type_icon_' . $x . '" class="button-set-label">' . __( 'Icon Library', 'salient' ) . '</label>';
                echo '<input type="radio" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][icon_type]' . esc_attr($this->field['name_suffix']) . '" id="icon_type_custom_' . $x . '" value="custom" />';
                echo '<label for="icon_type_custom_' . $x . '" class="button-set-label">' . __( 'Custom Icon', 'salient' ) . '</label>';
                echo '</div>';
                echo '</div>';

                // Custom image upload section
                echo '<div class="redux_social_repeater_add_remove">';

                //If the user has WP3.5+ show upload/remove button
                echo '<span class="button media_upload_button" id="add_' . esc_attr($x) . '">' . __( 'Upload Icon', 'salient' ) . '</span>';

                echo '<span class="button remove-image' . $hide . '" id="reset_' . esc_attr($x) . '" rel="' . esc_attr($this->parent->args[ 'opt_name' ]) . '[' . esc_attr($this->field[ 'id' ]) . '][attachment_id]">' . __( 'Remove', 'salient' ) . '</span>';

                echo '</div>' . "\n";

                // Icon selector section
                echo '<div class="redux-social-repeater-icon-selector hide">';
                echo '<label>' . __( 'Select Icon', 'salient' ) . '</label>';
                echo '<div class="icon-grid">';

                // Get brand icons from theme function
                $brand_icons = function_exists('nectar_brands_icon_list') ? nectar_brands_icon_list() : [];

                foreach ($brand_icons as $icon_name => $icon_unicode) {
                    $display_name = str_replace(['nectar-brands-', '-'], ['', ' '], $icon_name);
                    $display_name = ucwords($display_name);
                    echo '<div class="icon-option" data-icon="' . esc_attr($icon_name) . '" data-icon-class="' . esc_attr($icon_name) . '">';
                    echo '<i class="' . esc_attr($icon_name) . '"></i>';
                    echo '<span>' . esc_html($display_name) . '</span>';
                    echo '</div>';
                }

                echo '</div>';
                echo '<input type="hidden" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][selected_icon]' . esc_attr($this->field['name_suffix']) . '" id="selected_icon_' . $x . '" value="" />';
                echo '</div>';

                echo '<ul id="' . esc_attr($this->field[ 'id' ]) . '-ul-' . esc_attr($x) . '" class="redux-social-repeater-list">';

                $label = ( isset ( $this->field[ 'labels' ][ 'name' ] ) ) ? esc_attr ( $this->field[ 'labels' ][ 'name' ] ) : __( 'Network Name', 'salient' );
                $placeholder = ( isset ( $this->field[ 'placeholder' ][ 'name' ] ) ) ? esc_attr ( $this->field[ 'placeholder' ][ 'name' ] ) : __( 'e.g., MySpace, TikTok', 'salient' );
                echo '<li><label for="' . esc_attr($this->field[ 'id' ]) . '-name_' . esc_attr($x) . '">' . $label . '</label><input type="text" id="' . esc_attr($this->field[ 'id' ]) . '-name_' . esc_attr($x) . '" name="' . esc_attr($this->field[ 'name' ]) . '[' . esc_attr($x) . '][name]' . esc_attr($this->field['name_suffix']) . '" value="" placeholder="' . $placeholder . '" class="full-text social-name" /></li>';

                $label = ( isset ( $this->field[ 'labels' ][ 'url' ] ) ) ? esc_attr ( $this->field[ 'labels' ][ 'url' ] ) : __( 'Profile URL', 'salient' );
                $placeholder = ( isset ( $this->field[ 'placeholder' ][ 'url' ] ) ) ? esc_attr ( $this->field[ 'placeholder' ][ 'url' ] ) : __( 'e.g., https://myspace.com/username', 'salient' );
                echo '<li><label for="' . esc_attr($this->field[ 'id' ]) . '-url_' . $x . '">' . $label . '</label><input type="text" id="' . esc_attr($this->field[ 'id' ]) . '-url_' . $x . '" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][url]' . esc_attr($this->field['name_suffix']) .'" value="" class="full-text" placeholder="' . $placeholder . '" /></li>';

                echo '<li><input type="hidden" class="upload-id" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][attachment_id]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_id_' . $x . '" value="" />';
                echo '<input type="hidden" class="upload-thumbnail" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][thumb]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-thumb_url_' . $x . '" value="" readonly="readonly" />';
                echo '<input type="hidden" class="upload" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][icon]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_url_' . $x . '" value="" readonly="readonly" />';
                echo '<input type="hidden" class="upload-height" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][height]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_height_' . $x . '" value="" />';
                echo '<input type="hidden" class="upload-width" name="' . esc_attr($this->field[ 'name' ]) . '[' . $x . '][width]' . esc_attr($this->field['name_suffix']) .'" id="' . esc_attr($this->field[ 'id' ]) . '-image_width_' . $x . '" value="" /></li>';
                echo '<li><a href="javascript:void(0);" class="button deletion redux-social-repeater-remove">' . __( 'Delete', 'salient' ) . '</a></li>';
                echo '</ul></div></fieldset></div>';
            }

            // Add button container
            echo '<div class="redux-social-repeater-add">';
            echo '<a href="javascript:void(0);" class="button redux-social-repeater-add button" rel-id="' . esc_attr($this->field[ 'id' ]) . '-ul" rel-name="' . esc_attr($this->field[ 'name' ]) . '[]">' . sprintf ( __( 'Add %s', 'salient' ), $this->field[ 'content_title' ] ) . '</a>';
            echo '</div>';

            // Close the accordion container
            echo '</div>';
        }

        /**
         * Enqueue Function.
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function enqueue () {
            // Enqueue jQuery UI Sortable
            wp_enqueue_script('jquery-ui-sortable');

            wp_enqueue_script(
                'redux-opts-field-social-repeater-js',
                get_template_directory_uri() . '/nectar/redux-framework/extensions/social_repeater/social_repeater/field_social_repeater.js',
                array('jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-ui-sortable'),
                time(),
                true
            );

            wp_enqueue_style(
                'redux-opts-field-social-repeater-css',
                get_template_directory_uri() . '/nectar/redux-framework/extensions/social_repeater/social_repeater/field_social_repeater.css',
                array(),
                time(),
                'all'
            );
        }
    }
}