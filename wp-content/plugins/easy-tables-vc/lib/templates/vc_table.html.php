<?php
/**
 * @package WPBakery
 * @var \WPBakeryShortCode_VC_Table $this
 */

$custom_css = '';
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
if ( empty( $content ) ) {
	return '<table><tr><td>Empty table</td></tr></table>';
}
if ( ! empty( $atts['vc_table_theme'] ) ) {
	$custom_css = ' class="vc-table-plugin-theme-' . esc_attr( $atts['vc_table_theme'] ) . '"';
}
if ( ! empty( $atts['el_class'] ) ) {
	$atts['el_class'] .= ' ' . $atts['el_class'];
}
$table_data = vc_table_parse_table_param( $content );
$atts['el_class'] = $this->getExtraClass( $atts['el_class'] );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_vc_table wpb_content_element' . $atts['el_class'], $this->settings['base'], $atts );

$output = '<div class="' . esc_attr( $css_class ) . '">';
$output .= '<table' . $custom_css . '>';
foreach ( $table_data as $index => $row ) {
	$output .= '<tr' . ( 0 === $index ? ' class="vc-th"' : '' ) . '>';
	foreach ( $row as $cell ) {
		$style = empty( $cell['css_style'] ) ? '' : ' style="' . esc_attr( implode( '', $cell['css_style'] ) ) . '"';
		$class = empty( $cell['css_class'] ) ? '' : ' class="' . esc_attr( implode( ' ', $cell['css_class'] ) ) . '"';
		$cell_content = $cell['content'];
		if ( empty( $atts['allow_html'] ) ) {
			$cell_content = $this->convertEncodeChars( htmlspecialchars( $cell_content ) );
		}
		$cell_content = apply_filters( 'wpb_vc_table_manager_table_content', $cell_content, $cell );
		$output .= '<td' . $style . $class . '><span class="vc_table_content">' . do_shortcode( $cell_content ) . '</span></td>';
	}
	$output .= '</tr>';
}
$output .= '</table>';
$output .= '</div>';

return $output;
