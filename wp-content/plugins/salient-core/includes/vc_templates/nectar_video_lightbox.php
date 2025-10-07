<?php 

extract(shortcode_atts(array(
	"link_style" => "play_button", 
  'hover_effect' => 'default', 
	"font_style" => "p", 
	"video_url" => '#', 
	"link_text" => "", 
	"play_button_color" => "default", 
	"nectar_button_color" => "default", 
	'nectar_play_button_color' => 'Accent-Color', 
	'image_url' => '', 
	'border_radius' => 'none',
	'play_button_size' => 'default',
	'box_shadow' => ''), $atts));

$extra_attrs   = ($link_style === 'nectar-button') ? 'data-color-override="false"': null;

$the_link_text_escaped = ($link_style === 'nectar-button') ? wp_kses_post($link_text) : '<span class="play"><span class="inner-wrap"><svg version="1.1"
	 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="600px" height="800px" x="0px" y="0px" viewBox="0 0 600 800" enable-background="new 0 0 600 800" xml:space="preserve"><path fill="none" d="M0-1.79v800L600,395L0-1.79z"></path> </svg></span></span>';

$the_color     = ($link_style === 'nectar-button') ? $nectar_button_color : $play_button_color;

if( $link_style === 'play_button_with_text' ) {
	$the_color = $nectar_play_button_color;
}

if( $link_style == 'play_button_2' ) {

	  $image = null;

	  if( !empty($image_url) ) {
			
      	if( !preg_match('/^\d+$/',$image_url) ){
      		$image = '<img src="'.esc_url($image_url).'" alt="'. esc_html__('video preview', 'salient-core') .'" />';
      	} else {
      		$image = wp_get_attachment_image($image_url, 'full');
      	}  
		}

	echo '<div class="nectar-video-box" data-color="'.esc_attr(strtolower($nectar_play_button_color)).'" data-play-button-size="'.esc_attr($play_button_size).'" data-border-radius="'.esc_attr($border_radius).'" data-hover="'.esc_attr($hover_effect).'" data-shadow="'.esc_attr($box_shadow).'"><div class="inner-wrap"><a href="'.esc_url($video_url).'" class="full-link pp"></a>'. $image;
}

$pbwt_escaped = ($link_style === 'play_button_with_text') ? '<span class="link-text"><'.esc_html($font_style).'>'.wp_kses_post($link_text).'</'.esc_html($font_style).'></span>' : null;

echo '<a href="'.esc_url($video_url).'" '.$extra_attrs.' data-color="'.esc_attr(strtolower($the_color)).'" class="'.esc_attr($link_style).' large nectar_video_lightbox pp">'.$the_link_text_escaped .$pbwt_escaped.'</a>';

if( $link_style === 'play_button_2' ) {
	echo '</div></div>';
}

?>