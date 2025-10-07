<?php

class WPUPG_Content {

    public function __construct()
    {
        add_filter( 'the_content', array( $this, 'prefilter_grid' ), 1 );
        add_filter( 'the_content', array( $this, 'content_filter' ), 10 );
    }

    public function prefilter_grid( $content )
    {
		$pattern = get_shortcode_regex( array( 'wpupg-grid' ) );

		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
			foreach ( $matches[2] as $key => $value ) {
				if ( 'wpupg-grid' === $value ) {
                    $grid_shortcode = $matches[0][ $key ];
                    $grid_shortcode = str_ireplace( '[wpupg-grid ', '[wpupg-grid wpupg_no_output="1" ', $grid_shortcode );

                    // This forces a prefilter of the grid without actual output.
                    do_shortcode( $grid_shortcode );
				}
			}
        }
        
        return $content;
    }

    public function content_filter( $content )
    {
        $ignore_query = !is_main_query();
        if ( apply_filters( 'wpupg_content_loop_check', $ignore_query ) ) {
            return $content;
        }

        if ( get_post_type() == WPUPG_POST_TYPE ) {
            remove_filter( 'the_content', array( $this, 'content_filter' ), 10 );

            $grid = WPUPG_Grid_Manager::get( get_post() );

            $content .= '[wpupg-filter id="' . $grid->slug() . '"]';
            $content .= '[wpupg-grid id="' . $grid->slug() . '"]';

            add_filter( 'the_content', array( $this, 'content_filter' ), 10 );
        }

        return $content;
    }
}