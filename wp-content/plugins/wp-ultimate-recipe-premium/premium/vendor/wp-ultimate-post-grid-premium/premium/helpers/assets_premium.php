<?php

class WPUPG_Assets_Premium {

    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'public_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
    }

    public function public_assets()
    {
        $version = WPUltimatePostGrid::option( 'assets_use_cache', '1' ) == '1' ? WPUPG_VERSION : time();

        wp_enqueue_style( 'wpupg_public_premium', WPUltimatePostGridPremium::get()->premiumUrl . '/dist/public.css', array(), $version, 'all' );
        wp_register_script( 'wpupg_public_premium', WPUltimatePostGridPremium::get()->premiumUrl . '/dist/public.js', array( 'jquery' ), $version, true );
    }

    public function admin_assets( $hook )
    {
        $version = WPUltimatePostGrid::option( 'assets_use_cache', '1' ) == '1' ? WPUPG_VERSION : time();

        wp_enqueue_script( 'wpupg_admin_premium', WPUltimatePostGridPremium::get()->premiumUrl . '/dist/admin.js', array( 'jquery' ), $version, true );
        wp_localize_script( 'wpupg_admin_premium', 'wpupg_cloner', array(
            'ajax_url' => WPUltimatePostGrid::get()->helper('ajax')->url(),
            'nonce' => wp_create_nonce( 'clone_grid' )
        ));
    }
}