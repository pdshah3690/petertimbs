<?php

class WPUPG_Template_Editor extends WPUPG_Premium_Addon {

    public function __construct( $name = 'template-editor' ) {
        parent::__construct( $name );

        // Actions
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
        add_action( 'admin_menu', array( $this, 'image_manager_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts_for_image_upload' ), -10 );

        // Filters
        add_filter( 'wpupg_output_grid_template', array( $this, 'template_editor_preview' ) );
    }

    public function assets() {
        if( isset( $_GET['wpupg_template_editor_preview'] ) ) {
            $version = WPUltimatePostGrid::option( 'assets_use_cache', '1' ) == '1' ? WPUPG_VERSION : time();

            wp_enqueue_style( 'wpupg_template_preview', $this->addonUrl . '/css/preview.css', array(), $version, 'all' );
            wp_enqueue_script( 'wpupg_template_preview', $this->addonUrl . '/js/preview.js', array( 'jquery' ), $version, true );

            wp_localize_script( 'wpupg_template_preview', 'wpupg_template_preview', array(
                'ajax_url' => WPUltimatePostGrid::get()->helper('ajax')->url(),
                'nonce' => wp_create_nonce( 'template_editor_preview' ),
                'recipe' => intval($_GET['wpupg_template_editor_preview']),
            ));

            $template = get_option( 'wpupg_custom_template_preview' );
            if( isset( $template->fonts ) && count( $template->fonts ) > 0 ) {
                wp_enqueue_style( 'wpupg_template_preview_fonts', 'http://fonts.googleapis.com/css?family=' . implode( '|', $template->fonts ), array(), $version, 'all' );
            }
        }
    }

    public function template_editor_preview( $template )
    {
        if( isset( $_GET['wpupg_template_editor_preview'] ) && WPUPG_POST_TYPE == get_post_type( $_GET['wpupg_template_editor_preview'] ) )
        {
            $template = get_option( 'wpupg_custom_template_preview' );
        }

        return $template;
    }

    public function editor_url()
    {
        return $this->addonUrl . '/templates/editor.php';
    }

    public function image_manager_menu()
    {
        add_submenu_page( null, 'WP Ultimate Post Grid ' . __( 'Image Manager', 'wp-ultimate-post-grid' ), __( 'Image Manager', 'wp-ultimate-post-grid' ), 'manage_options', 'wpupg_image_manager', array( $this, 'image_manager_menu_page' ) );
    }

    public function image_manager_menu_page()
    {
        include( $this->addonDir . '/templates/images.php' );
    }

    public function scripts_for_image_upload()
    {
        $screen = get_current_screen();
        if( $screen->id == 'wpupg_grid_page_wpupg_image_manager' && function_exists( 'wp_enqueue_media' ) ) {
            $version = WPUltimatePostGrid::option( 'assets_use_cache', '1' ) == '1' ? WPUPG_VERSION : time();

            wp_enqueue_media();
            wp_enqueue_script( 'wpupg_admin_template_editor', $this->addonUrl . '/js/images.js', array( 'jquery'), $version, true );
        }
    }
}

WPUltimatePostGrid::loaded_addon( 'template-editor', new WPUPG_Template_Editor() );