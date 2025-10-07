<?php

class WPUPG_Assets {

    private $assets = array();

    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'public_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
    }

    public function public_assets()
    {
        $version = WPUltimatePostGrid::option( 'assets_use_cache', '1' ) == '1' ? WPUPG_VERSION : time();
        $base_layout = WPUltimatePostGrid::option( 'grid_template_force_style', '0' ) == '1' ? 'public-forced.css' : 'public.css';

        wp_enqueue_style( 'wpupg_public', WPUltimatePostGrid::get()->coreUrl . '/dist/' . $base_layout, array(), $version, 'all' );
        wp_register_script( 'wpupg_public', WPUltimatePostGrid::get()->coreUrl . '/dist/public.js', array( 'jquery' ), $version, true );

        wp_localize_script( 'wpupg_public', 'wpupg_public', array(
            'ajax_url' => WPUltimatePostGrid::get()->helper('ajax')->url(),
            'animationSpeed' => WPUltimatePostGrid::option( 'grid_animation_speed', '0.8' ) . 's',
            'animationShow' => $this->animation_string_to_array( WPUltimatePostGrid::option( 'grid_animation_show', 'opacity: 1' ) ),
            'animationHide' => $this->animation_string_to_array( WPUltimatePostGrid::option( 'grid_animation_hide', 'opacity: 0' ) ),
            'nonce' => wp_create_nonce( 'wpupg_grid' ),
            'rtl' => is_rtl(),
            'dropdown_hide_search' => WPUltimatePostGrid::option( 'filters_dropdown_hide_search', '0' ) == '1' ? true : false,
            'link_class' => WPUltimatePostGrid::option( 'grid_links_class', '' ),
        ));
    }

    public function admin_assets( $hook )
    {
        $version = WPUltimatePostGrid::option( 'assets_use_cache', '1' ) == '1' ? WPUPG_VERSION : time();

        $screen = get_current_screen();
        if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) && WPUPG_POST_TYPE === $screen->post_type ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'wpupg_admin_form', WPUltimatePostGrid::get()->coreUrl . '/dist/admin-form.css', array(), $version, 'all' );
            wp_enqueue_script( 'wpupg_admin_form', WPUltimatePostGrid::get()->coreUrl . '/dist/admin-form.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ), $version, true );

            wp_localize_script( 'wpupg_admin_form', 'wpupg_admin', array(
                'ajax_url' => WPUltimatePostGrid::get()->helper('ajax')->url(),
            ));
        }

        wp_enqueue_style( 'wpupg_admin_post', WPUltimatePostGrid::get()->coreUrl . '/dist/admin.css', array(), $version, 'all' );
        wp_enqueue_script( 'wpupg_admin_post', WPUltimatePostGrid::get()->coreUrl . '/dist/admin.js', array( 'jquery' ), $version, true );
        wp_localize_script( 'wpupg_admin_post', 'wpupg_admin_post', array(
            'text_add_grid_image' => __( 'Add Grid Image', 'wp-ultimate-post-grid' ),
            'text_remove_grid_image' => __( 'Remove Grid Image', 'wp-ultimate-post-grid' ),
        ));
    }

    private function animation_string_to_array( $string )
    {
        $array = array();

        $options = explode( ', ', $string );
        foreach( $options as $option ) {
            list( $k, $v ) = explode( ': ', $option );
            $array[$k] = $v;
        }

        return $array;
    }

    public function add()
    {
        $assets = func_get_args();

        foreach( $assets as $asset )
        {
            if( isset( $asset['file'] ) ) {

                if( !isset( $asset['type'] ) ) {
                    $asset['type'] = pathinfo( $asset['file'], PATHINFO_EXTENSION );
                }

                if( !isset( $asset['priority'] ) ) {
                    $asset['priority'] = 10;
                }

                // Set a URL and DIR variable
                if( isset( $asset['direct'] ) && $asset['direct'] ) {
                    $asset['url'] = $asset['file'];
                    $asset['dir'] = $asset['file'];
                } else {
                    $base_url = WPUltimatePostGrid::get()->coreUrl;
                    $base_dir = WPUltimatePostGrid::get()->coreDir;

                    if( isset( $asset['premium'] ) && $asset['premium'] ) {
                        $base_url = WPUltimatePostGridPremium::get()->premiumUrl;
                        $base_dir = WPUltimatePostGridPremium::get()->premiumDir;
                    }

                    $asset['url'] = $base_url . $asset['file'];
                    $asset['dir'] = $base_dir . $asset['file'];
                }

                $this->assets[] = $asset;
            }
        }
    }
}