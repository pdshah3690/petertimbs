<?php

class WPURP_Timer extends WPURP_Premium_Addon {

    public function __construct( $name = 'timer' ) {
        parent::__construct( $name );

        // Actions
        add_action( 'init', array( $this, 'assets' ) );

        // Shortcode
        add_shortcode( 'recipe-timer', array( $this, 'timer_shortcode' ) );
    }

    public function assets() {
        // Get Icons.
		ob_start();
		include( $this->addonDir . '/icons/pause.svg' );
		$pause = ob_get_contents();
		ob_end_clean();

		ob_start();
		include( $this->addonDir . '/icons/play.svg' );
		$play = ob_get_contents();
		ob_end_clean();

		ob_start();
		include( $this->addonDir . '/icons/close.svg' );
		$close = ob_get_contents();
		ob_end_clean();

        WPUltimateRecipe::get()->helper( 'assets' )->add(
            array(
                'file' => $this->addonPath . '/css/timer.css',
                'premium' => true,
                'public' => true,
            ),
            array(
                'name' => 'wpurp-timer',
                'file' => $this->addonPath . '/js/timer.js',
                'premium' => true,
                'public' => true,
                'deps' => array(
                    'jquery',
                ),
                'data' => array(
                    'name' => 'wpurp_timer',
                    'icons' => array(
                        'pause' => $pause,
                        'play' => $play,
                        'close' => $close,
                    ),
                )
            )
        );
    }

    public function timer_shortcode( $atts, $content )
    {
        $atts = shortcode_atts( array(
			'seconds' => '0',
			'minutes' => '0',
			'hours' => '0',
		), $atts );

		$seconds = intval( $atts['seconds'] );
		$minutes = intval( $atts['minutes'] );
		$hours = intval( $atts['hours'] );

		$seconds = $seconds + (60 * $minutes) + (60 * 60 * $hours);

		if ( $seconds > 0 ) {
            $timer = '<a href="#" class="wpurp-timer-link" title="' . __( 'Click to Start Timer', 'wp-ultimate-recipe' ) . '">';
			$timer .= '<span class="wpurp-timer" data-seconds="' . esc_attr( $seconds ) . '">';
            $timer .= $content;
            $timer .= '</span></a>';

            return $timer;
		} else {
			return $content;
		}
    }
}

WPUltimateRecipe::loaded_addon( 'timer', new WPURP_Timer() );