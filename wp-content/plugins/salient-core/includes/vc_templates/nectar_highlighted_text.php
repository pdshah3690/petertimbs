<?php

$text = $color = '';

extract(shortcode_atts(array(
	'highlight_color' => '',
  'style' => 'full_text'
), $atts));

$using_custom_color = (!empty($highlight_color)) ? 'true' : 'false';

echo '<div class="nectar-highlighted-text" data-style="'.esc_attr($style).'" data-using-custom-color="'.esc_attr($using_custom_color).'" data-color="'.esc_attr($highlight_color).'" style="">'.$content.'</div>';