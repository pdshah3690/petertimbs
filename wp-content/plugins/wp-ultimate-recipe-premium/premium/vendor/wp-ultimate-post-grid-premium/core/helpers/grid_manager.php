<?php

class WPUPG_Grid_Manager {

    private static $grids = array();

    public static function get( $post_or_post_id )
    {
        if( is_object( $post_or_post_id ) && $post_or_post_id instanceof WP_Post ) {
            $post_id = $post_or_post_id->ID;
        } else if( is_numeric( $post_or_post_id ) ) {
            $post_id = $post_or_post_id;
        }

        if ( ! array_key_exists( $post_id, self::$grids ) ) {
            self::$grids[ $post_id ] = new WPUPG_Grid( $post_or_post_id );
        }

        return self::$grids[ $post_id ];
    }
}