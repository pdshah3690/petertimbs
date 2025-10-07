<?php 

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	"name" => esc_html__("Split Line Heading", "salient-core"),
	"base" => "split_line_heading",
	"icon" => "icon-wpb-split-line-heading",
	"allowed_container_element" => 'vc_row',
	"category" => esc_html__('Nectar Elements', 'salient-core'),
	"description" => esc_html__('Animated multi line heading', 'salient-core'),
	"params" => array(
		array(
			"type" => "textarea_html",
			"holder" => "div",
			"heading" => esc_html__("Text Content", "salient-core"),
			"param_name" => "content",
			"value" => '',
			"description" => esc_html__("Each Line of this editor will be animated separately. Separate text with the Enter or Return key on your Keyboard.", "salient-core"),
			"admin_label" => false
		),
		
	)
);

?>