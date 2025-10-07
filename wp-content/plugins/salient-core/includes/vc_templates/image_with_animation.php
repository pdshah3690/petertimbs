<?php 

extract(shortcode_atts(array(
  "animation" => 'Fade In',
  "delay" => '0', 
  "image_url" => '', 
  'alt' => '', 
  'margin_top' => '', 
  'margin_right' => '', 
  'margin_bottom' => '', 
  'margin_left' => '', 
  'alignment' => 'left', 
  'border_radius' => '', 
  'img_link_target' => '_self', 
  'img_link' => '', 
  'img_link_large' => '', 
  'box_shadow' => 'none', 
  'box_shadow_direction' => 'middle', 
  'max_width' => '100%',
  'el_class' => ''), $atts));
    
  $parsed_animation = str_replace(" ","-",$animation);
  (!empty($alt)) ? $alt_tag = $alt : $alt_tag = null;
  
  $image_width  = '100';
  $image_height = '100';
  $image_srcset = null;
  
  if( preg_match('/^\d+$/',$image_url) ) {
    
    $image_src = wp_get_attachment_image_src($image_url, 'full');
  
    if (function_exists('wp_get_attachment_image_srcset')) {
  
      $image_srcset_values = wp_get_attachment_image_srcset($image_url, 'full');
      if($image_srcset_values) {
        $image_srcset = 'srcset="';
        $image_srcset .= $image_srcset_values;
        $image_srcset .= '" sizes="100vw"';
      }
    }
    
    $image_meta = wp_get_attachment_metadata($image_url);
    
    if(!empty($image_meta['width'])) {
      $image_width = $image_meta['width'];
    }
    if(!empty($image_meta['height'])) {
      $image_height = $image_meta['height'];}
  
    $wp_img_alt_tag = get_post_meta( $image_url, '_wp_attachment_image_alt', true );
    
    if(!empty($wp_img_alt_tag)) {
      $alt_tag = $wp_img_alt_tag;
    }
    
    $image_url = $image_src[0];
    
  }
  
  $margins = '';
  
  if( !empty($margin_top) ) {
    
    if( strpos($margin_top,'%') !== false ) {
      $margins .= 'margin-top: '.intval($margin_top).'%; ';
    } else {
      $margins .= 'margin-top: '.intval($margin_top).'px; ';
    }
  
  }
  if( !empty($margin_right) ) {
    
    if( strpos($margin_right,'%') !== false ) {
      $margins .= 'margin-right: '.intval($margin_right).'%; ';
    } else {
      $margins .= 'margin-right: '.intval($margin_right).'px; ';
    }
    
  }
  if( !empty($margin_bottom) ) {
    
    if( strpos($margin_bottom,'%') !== false ) {
      $margins .= 'margin-bottom: '.intval($margin_bottom).'%; ';
    } else {
      $margins .= 'margin-bottom: '.intval($margin_bottom).'px; ';
    }
    
  }
  if( !empty($margin_left) ) {
    
    if( strpos($margin_left,'%') !== false ) {
      $margins .= 'margin-left: '.intval($margin_left).'%;';
    } else {
      $margins .= 'margin-left: '.intval($margin_left).'px;';
    }
    
  }
  
  $margin_style_attr = '';
  
  if( !empty($margins) ) {
     $margin_style_attr = ' style="'.$margins.'"';
  }
  
  // Attributes applied to img-with-animation-wrap.
  $wrap_image_attrs_escaped  = 'data-max-width="'.esc_attr($max_width).'" ';
  $wrap_image_attrs_escaped .= 'data-border-radius="'.esc_attr($border_radius).'"';
  
  // Attributes applied to img.
  $image_attrs_escaped  = 'data-shadow="'.esc_attr($box_shadow).'" ';
  $image_attrs_escaped .= 'data-shadow-direction="'.esc_attr($box_shadow_direction).'" ';
  $image_attrs_escaped .= 'data-delay="'.esc_attr($delay).'" ';
  $image_attrs_escaped .= 'height="'.esc_attr($image_height).'" ';
  $image_attrs_escaped .= 'width="'.esc_attr($image_width).'" ';
  $image_attrs_escaped .= 'data-animation="'.esc_attr(strtolower($parsed_animation)).'" ';
  $image_attrs_escaped .= 'src="'.esc_url($image_url).'" ';
  $image_attrs_escaped .= 'alt="'.esc_attr($alt_tag).'" ';
  $image_attrs_escaped .= $image_srcset;
  $image_attrs_escaped .= $margin_style_attr;
  
  
  if( !empty($img_link) || !empty($img_link_large) ){
    
    if( !empty($img_link) && empty($img_link_large) ) {
      // Link image to larger version.
      echo '<div class="img-with-aniamtion-wrap '.esc_attr($alignment).'" '.$wrap_image_attrs_escaped.'>
      <div class="inner">
        <a href="'.esc_url($img_link).'" target="'.esc_attr($img_link_target).'" class="'.esc_attr($alignment).'">
          <img class="img-with-animation skip-lazy '.esc_attr($el_class).'" '.$image_attrs_escaped.' />
        </a>
      </div>
      </div>';
      
    } elseif(!empty($img_link_large)) {
      // Regular link image.
      echo '<div class="img-with-aniamtion-wrap '.esc_attr($alignment).'" '.$wrap_image_attrs_escaped.'>
      <div class="inner">
        <a href="'.esc_url($image_url).'" class="pp '.esc_attr($alignment).'">
          <img class="img-with-animation skip-lazy '.esc_attr($el_class).'" '.$image_attrs_escaped.' />
        </a>
      </div>
      </div>';
    }
    
  } else {
    // No link image.
    echo '<div class="img-with-aniamtion-wrap '.esc_attr($alignment).'" '.$wrap_image_attrs_escaped.'>
      <div class="inner">
        <img class="img-with-animation skip-lazy '.esc_attr($el_class).'" '.$image_attrs_escaped.' />
      </div>
    </div>';
  }

?>