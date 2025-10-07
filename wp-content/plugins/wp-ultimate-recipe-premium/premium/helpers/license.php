<?php

class WPURP_License {

    public function __construct()
    {
        add_action( 'admin_init', array( $this, 'edd_fs_license_key_migration' ) );
    }

    public function edd_fs_license_key_migration() {
        if ( ! wpurp_fs()->has_api_connectivity() || wpurp_fs()->is_registered() ) {
            // No connectivity OR the user already opted-in to Freemius.
            return;
        }
        
        if ( 'pending' != get_option( 'wpurp_fs_migrated2fs', 'pending' ) ) {
            return;
        }
        // Get the license key from the previous eCommerce platform's storage.
        $license_key = $this->get_edd_license();
        if ( empty( $license_key ) ) {
            // No key to migrate.
            return;
        }
        // Get the first 32 characters.
        $license_key = substr( $license_key, 0, 32 );
        try {
            $next_page = wpurp_fs()->activate_migrated_license( $license_key );
        } catch (Exception $e) {
            update_option( 'wpurp_fs_migrated2fs', 'unexpected_error' );
            return;
        }
        if ( wpurp_fs()->can_use_premium_code() ) {
            update_option( 'wpurp_fs_migrated2fs', 'done' );
            if ( is_string( $next_page ) ) {
                fs_redirect( $next_page );
            }
        } else {
            update_option( 'wpurp_fs_migrated2fs', 'failed' );
        }
    }

    public function get_edd_license() {
        return trim( get_option( 'edd_wpurp_license_key' ) );
    }
}