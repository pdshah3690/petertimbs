<?php
add_action( 'admin_footer', 'vc_table_param_form_field_templates' );

function vc_table_param_form_field( $settings, $value ) {
	return sprintf( '<div class="vc-table-param vc_table_plugin"><input name="%s" class="wpb_vc_param_value %s %s_field" type="hidden" value="%s"/><div class="vc-table"></div></div>', esc_attr( $settings['param_name'] ), esc_attr( $settings['param_name'] ), esc_attr( $settings['type'] ), $value );
}

function vc_table_param_form_field_templates() {
	require_once dirname( __FILE__ ) . '/templates/templates.html.php';
}

function vc_table_parse_table_param( $param_string ) {
	$border_styles = array(
		'border_left',
		'border_right',
		'border_top',
		'border_bottom',
		'borders_all',
	);
	$data = array();
	$rows = preg_split( '/\|/', $param_string );
	foreach ( $rows as $row_value ) {
		if ( ! empty( $row_value ) ) {
			$cells = array();
			$cells_split = preg_split( '/\,/', $row_value );
			foreach ( $cells_split as $key => $cell_value ) {
				$cells[ $key ]['content'] = rawurldecode( preg_replace( '/^\[[^\]]*\]/', '', $cell_value ) );
				$matched = preg_match( '/^\[([^\]]*)\]/', $cell_value, $matched_attr );
				$css_class = array( 'vc_table_cell' );
				$css_style = array();
				if ( $matched ) {
					$cells[ $key ]['attr'] = preg_split( '/\;/', $matched_attr[1] );
					foreach ( $cells[ $key ]['attr'] as $cell_value_inner ) {
						if ( 'b' === $cell_value_inner ) {
							$css_style[] = 'font-weight: bold;';
						} elseif ( 'u' === $cell_value_inner ) {
							$css_style[] = 'text-decoration: underline;';
						} elseif ( 'i' === $cell_value_inner ) {
							$css_style[] = 'font-style: italic;';
						} elseif ( 's' === $cell_value_inner ) {
							$css_class[] = 'vc_stroked';
						} elseif ( preg_match( '/px$/', $cell_value_inner ) ) {
							$css_style[] = 'font-size:' . $cell_value_inner . ';';
							$css_style[] = 'line-height:' . $cell_value_inner . ';';
						} elseif ( preg_match( '/^c/', $cell_value_inner ) ) {
							$css_style[] = 'color: ' . preg_replace( '/^c/', '', $cell_value_inner ) . ';';
						} elseif ( preg_match( '/^bg/', $cell_value_inner ) ) {
							$css_style[] = 'background-color:' . preg_replace( '/^bg/', '', $cell_value_inner ) . ';';
						} elseif ( in_array( $cell_value_inner, $border_styles, true ) ) {
							$css_class[] = 'vc_cell_' . $cell_value_inner;
						} elseif ( preg_match( '/^align\-/', $cell_value_inner ) ) {
							$css_style[] = 'text-align:' . preg_replace( '/^align\-/', '', $cell_value_inner ) . ';';
						} elseif ( preg_match( '/^valign\-/', $cell_value_inner ) ) {
							$css_style[] = 'vertical-align:' . preg_replace( '/^valign\-/', '', $cell_value_inner ) . ';';
						}
					}
				}
				$cells[ $key ]['css_class'] = $css_class;
				$cells[ $key ]['css_style'] = $css_style;
			}
			$data[] = $cells;

		}
	}

	return $data;
}

function vc_table_theme_form_field( $settings, $value ) {
	$css_class = 'vc-theme-icons';
	$param_line = '<input type="hidden" name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value" value="' . htmlspecialchars( $value ) . '">';
	$param_line .= sprintf( '<div class="vc_table_plugin"><div class="dropdown vc-theme-selector" data-name="%s" data-type="selector" data-icon="true">', esc_attr( $settings['param_name'] ) );
	$param_line .= '<a class="btn dropdown-toggle" data-toggle="dropdown" href="#" data-default="default" data-content-type="icons" data-icon-class="' . esc_attr( $css_class ) . '">';
	$param_line .= '<span class="' . esc_attr( $css_class ) . '-' . ( $value ? $value : 'default' ) . '"></span>';
	$param_line .= '<span class="caret"></span></a>';
	$param_line .= '<ul class="dropdown-menu">';
	foreach ( $settings['value'] as $text_val => $val ) {
		if ( ( is_numeric( $text_val ) && is_string( $val ) ) || ( is_numeric( $text_val ) && is_numeric( $val ) ) ) {
			$text_val = $val;
		}
		$param_line .= sprintf( '<li class="vc-%s"><a href="javascript:;" data-value="%s" data-selector-name="%s" class="%s" title="%s"></a></li>', esc_attr( $val ), $val, esc_attr( $settings['param_name'] ), esc_attr( $css_class . '-' . $val ), htmlspecialchars( $text_val ) );
	}
	$param_line .= '</ul></div></div>';

	return $param_line;
}
