<?php

/**
* Demo Importer Initialize.
*
* @since 1.0
*/


// Only load when using Salient.
if( defined( 'NECTAR_THEME_NAME' ) ) {

  add_action("redux/extensions/salient_redux/before", 'redux_register_nectar_demo_importer_extension_loader', 0);
  add_filter( 'wbc_importer_description', 'nectar_wbc_importer_description_text', 10 );
  add_action( 'wbc_importer_after_content_import', 'nectar_after_ecommerce_demo_import', 10, 2 );
  add_action( 'wbc_importer_after_content_import', 'nectar_after_content_import', 10, 2 );
  add_action( 'wbc_importer_before_content_import', 'nectar_before_content_import', 10, 2 );

}




/**
* Loads demo importer extension.
*
* @since 1.0
*/
if( ! function_exists( 'redux_register_nectar_demo_importer_extension_loader' ) ) {

  function redux_register_nectar_demo_importer_extension_loader( $ReduxFramework ) {

    $extension_class = 'ReduxFramework_Extension_wbc_importer';

    if( ! class_exists( $extension_class ) ) {

      $path       = SALIENT_DEMO_IMPORTER_ROOT_DIR_PATH . 'includes/admin/redux-extensions/';
      $folder     = 'wbc_importer';
      $class_file = $path . $folder . '/extension_' . $folder . '.php';
      $class_file = apply_filters( 'redux/extension/salient_redux/'.$folder, $class_file );

      if( $class_file && file_exists($class_file) ) {
        require_once( $class_file );
        $extension = new $extension_class( $ReduxFramework );
      }

    }

  }

}


/**
* Alter demo importer top helper text.
*
* @since 1.0
*/
if ( ! function_exists( 'nectar_wbc_importer_description_text' ) ) {

  function nectar_wbc_importer_description_text( $description ) {
    $message = '';
    $value = ini_get('max_input_vars');
    $value = is_numeric($value) ? (int) $value : 0;
    if ( $value > 0 && $value < 2000 ) {
      $message .= '<p class="salient-redux-warning">' . esc_html__( 'Notice: Your server PHP max_input_vars is below the required minimum of 2000. This may prevent importing options from the demo reliably.', 'salient' ) . '</p>';
    }
    $message .= '<p>' . esc_html__( 'Note for users importing demos on an existing WordPress installation: When you select the option to import "Theme Option Settings", your current theme options will be overwritten.', 'salient-demo-importer' ) . '</p>';
    $message .= '<p>' . esc_html__( 'Ensure that all required plugins are installed and activated for the demo you want to import before confirming the import.', 'salient-demo-importer' ) . ' ' . esc_html__( 'For demos that require the WooCommerce plugin, do not forget to run the plugin setup wizard before using the demo importer if you have not previously used the plugin on your site.', 'salient-demo-importer' ) . '</p>';
    $message .= '<p>'.esc_html__('Demos that are marked as ','salient-demo-importer') . '<i>' . esc_html__('Legacy','salient-demo-importer') . '</i>' . esc_html__(' do not come with a set of dummy images and instead will only import placeholders.','salient-demo-importer').'</p>';
    $message .= '<p>' . esc_html__( 'See the', 'salient-demo-importer' ) . ' <a href="//themenectar.com/docs/salient/importing-demo-content/" target="_blank">' . esc_html__( 'documentation', 'salient-demo-importer' ) . '</a> ' . esc_html__( 'if you run into trouble importing a demo.', 'salient-demo-importer' ) . '</p>';
    return $message;
  }

}



/**
* Update WooCommerce category thumbnail.
*
* @since 1.0
*/
if ( ! function_exists( 'nectar_update_woo_cat_thumb' ) ) {

  function nectar_update_woo_cat_thumb( $cat_slug, $thumb_id ) {

    $n_woo_category    = get_term_by( 'slug', $cat_slug, 'product_cat' );
    $n_woo_category_id = ( $n_woo_category && isset( $n_woo_category->term_id ) ) ? $n_woo_category->term_id : false;
    if ( $n_woo_category_id ) {
      update_term_meta( $n_woo_category_id, 'thumbnail_id', $thumb_id );
    }

  }

}


/**
* Update Blog category colors.
*
* @since 1.0
*/
if ( ! function_exists( 'nectar_update_category_term_meta' ) ) {

  function nectar_update_category_term_meta( $data ) {

    // loop through blog categories and update the term meta from $data
    foreach( $data as $cat_slug => $term_meta ) {
      // get id of category from slug
      $cat_id = get_cat_ID( $cat_slug );

      // update term meta
      update_option('taxonomy_' . $cat_id, $term_meta);
    }



  }

}


/**
* Helper for assigning menu.
*
* @since 1.4
*/
function nectar_after_demo_import_assign_menu($slug, $location) {

  // Get Menu locations.
  $menu_locations = get_nav_menu_locations();

  // Get ID of menu by name.
  $nav_menu = get_term_by('slug', $slug, 'nav_menu');

  if( isset($nav_menu->term_id) ) {

     $nav_menu_id = $nav_menu->term_id;

     // Set menu.
     $menu_locations[$location] = $nav_menu_id;
     set_theme_mod( 'nav_menu_locations', $menu_locations );
  }

}


/**
* Helper for adding hash links.
*
* @since 1.4
* @param string $slug Menu slug
* @param array $hash_links_arr Array of hash links. Can be:
*   - Simple format: array('Name' => 'hash')
*   - Extended format: array('Name' => array('hash' => 'hash', 'meta' => array('key' => 'value')))
* @param string $position 'append' or 'prepend'
*/
function nectar_after_demo_import_add_hash_links($slug, $hash_links_arr, $position = 'append') {

  // Get menu id
  $nav_menu = get_term_by('slug', $slug, 'nav_menu');

  if( isset($nav_menu->term_id) ) {

    // Loop and add hash links
    $menu_position = 1;
    foreach($hash_links_arr as $hash_name => $hash_link_data) {

      // Support both simple string format and extended array format
      if( is_array($hash_link_data) ) {
        $hash_link = isset($hash_link_data['hash']) ? $hash_link_data['hash'] : '';
        $meta_data = isset($hash_link_data['meta']) ? $hash_link_data['meta'] : array();
      } else {
        // Simple string format for backward compatibility
        $hash_link = $hash_link_data;
        $meta_data = array();
      }

      // Generate menu URL - only add hash if hash_link is not empty
      if( !empty($hash_link) ) {
        $generated_menu_url = home_url( '/' ) . '#' . $hash_link;
      } else {
        $generated_menu_url = home_url( '/' );
      }

      $menu_item_args = array(
        'menu-item-title' => esc_html($hash_name),
        'menu-item-url' => esc_url($generated_menu_url),
        'menu-item-status' => 'publish',
        'menu-item-type' => 'custom',
      );

      // If prepending, set the position to insert at the beginning
      if( $position === 'prepend' ) {
        $menu_item_args['menu-item-position'] = $menu_position;
        $menu_position++;
      }

      $menu_item_id = wp_update_nav_menu_item($nav_menu->term_id, 0, $menu_item_args);

      // Add menu item meta data if provided
      if( $menu_item_id && !empty($meta_data) && is_array($meta_data) ) {
        foreach($meta_data as $meta_key => $meta_value) {
          update_post_meta( $menu_item_id, $meta_key, $meta_value );
        }
      }

    }

  }

}


/**
* Helper for getting post ID by title.
*
* @since 1.5
* @param string $title Post title to search for
* @param string $post_type Post type to search (default: 'any')
* @return int|null Post ID if found, null otherwise
*/
function nectar_after_demo_import_get_post_by_title($title, $post_type = 'any') {

  if( empty($title) ) {
    return null;
  }

  $args = array(
    'title' => $title,
    'post_type' => $post_type,
    'post_status' => 'any',
    'posts_per_page' => 1,
    'no_found_rows' => true,
    'fields' => 'ids'
  );

  $posts = get_posts($args);

  if( !empty($posts) && isset($posts[0]) ) {
    return $posts[0];
  }

  return null;

}


/**
* Helper for replacing {nectar_site_url} placeholder with actual site URL in post content.
*
* @since 1.5
* @param array $post_ids Array of post IDs to process
*/
function nectar_after_demo_import_replace_site_url($post_ids) {

  if( !is_array($post_ids) || empty($post_ids) ) {
    return;
  }

  $site_url = home_url();

  foreach($post_ids as $post_id) {

    $post = get_post($post_id);

    if( !$post ) {
      continue;
    }

    // Replace placeholder in post content
    $updated_content = str_replace('{nectar_site_url}', $site_url, $post->post_content);

    // Only update if content changed
    if( $updated_content !== $post->post_content ) {
      wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $updated_content
      ));
    }

  }

}


/**
* Helper for replacing {nectar_page_url title=X} placeholders with actual page URLs.
*
* @since 1.5
* @param array $post_ids Array of post IDs to process
* @return bool True if replacement was made, false otherwise
*/
function nectar_after_demo_import_replace_page_url($post_ids) {

  if( !is_array($post_ids) || empty($post_ids) ) {
    return false;
  }

  $replacement_made = false;

  // Loop through posts
  foreach($post_ids as $post_id) {

    $post = get_post($post_id);

    if( !$post ) {
      continue;
    }

    $original_content = $post->post_content;

    // Replace all {nectar_page_url title=X} patterns
    $updated_content = preg_replace_callback(
      '/\{nectar_page_url\s+title=([^}]+)\}/',
      function($matches) {
        $page_title = trim($matches[1]);

        if( empty($page_title) ) {
          return $matches[0]; // Return original if no title found
        }

        // Get page by title
        $page_id = nectar_after_demo_import_get_post_by_title($page_title, 'page');

        if( $page_id ) {
          // Return the page URL
          return get_permalink($page_id);
        }

        // Return original if page not found
        return $matches[0];
      },
      $original_content
    );

    // Only update if content changed
    if( $updated_content !== $original_content ) {
      wp_update_post(array(
        'ID' => $post_id,
        'post_content' => $updated_content
      ));
      $replacement_made = true;
    }

  }

  return $replacement_made;

}


/**
* Helper for importing Fluent Forms from JSON file.
*
* @since 1.5
* @param string $json_file_path Path to the JSON file containing form data
* @return array|bool Array of inserted form IDs on success, false on failure
*/
function nectar_after_demo_import_fluent_forms($json_file_path) {

  // Security: Verify user has permission to import (administrator only)
  if( !current_user_can('manage_options') ) {
    error_log('Fluent Forms: User lacks permission to import forms');
    return false;
  }

  $json_file_path = realpath($json_file_path);
  if( $json_file_path === false ) {
    error_log('Fluent Forms: Invalid file path provided');
    return false;
  }

  // Check if Fluent Forms is active and has required classes
  if( !function_exists('wpFluent') ) {
    error_log('Fluent Forms: wpFluent() function not found');
    return false;
  }

  // Check if required models exist
  if( !class_exists('FluentForm\App\Models\Form') || !class_exists('FluentForm\App\Models\FormMeta') ) {
    error_log('Fluent Forms: Required models not found');
    return false;
  }

  // Verify wpFluent() returns a valid query builder
  try {
    $query_builder = wpFluent();
    if( !is_object($query_builder) || !method_exists($query_builder, 'table') ) {
      error_log('Fluent Forms: wpFluent() does not return a valid query builder');
      return false;
    }
  } catch(Exception $e) {
    error_log('Fluent Forms: Error accessing wpFluent() - ' . $e->getMessage());
    return false;
  }

  // Check if file exists
  if( !file_exists($json_file_path) ) {
    error_log('Fluent Forms JSON file not found: ' . $json_file_path);
    return false;
  }

  // Read file using WordPress Filesystem API
  global $wp_filesystem;

  if( empty($wp_filesystem) ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
  }

  // Bail if WP_Filesystem is not available
  if( empty($wp_filesystem) ) {
    error_log('Fluent Forms: WP_Filesystem not available');
    return false;
  }

  // Read and decode JSON
  $json_content = $wp_filesystem->get_contents($json_file_path);

  if( $json_content === false ) {
    error_log('Fluent Forms: Failed to read JSON file');
    return false;
  }

  $forms = json_decode($json_content, true);

  if( !$forms || !is_array($forms) ) {
    error_log('Fluent Forms: Invalid JSON file');
    return false;
  }

  $inserted_form_ids = array();

  // Import each form
  foreach($forms as $formItem) {
    if( !isset($formItem['title']) ) {
      continue;
    }

    // Check if form with same title already exists
    $existing_form = wpFluent()
      ->table('fluentform_forms')
      ->where('title', $formItem['title'])
      ->first();

    if( $existing_form ) {
      error_log('Fluent Forms: Form "' . $formItem['title'] . '" already exists, skipping');
      continue;
    }

    // Extract form fields
    $formFields = json_encode([]);
    if( isset($formItem['form']) && !empty($formItem['form']) ) {
      $formFields = json_encode($formItem['form']);
    } elseif( isset($formItem['form_fields']) && !empty($formItem['form_fields']) ) {
      $formFields = json_encode($formItem['form_fields']);
    } else {
      error_log('Fluent Forms: Form "' . $formItem['title'] . '" missing form_fields');
      continue;
    }

    // Prepare form data
    $formTitle = sanitize_text_field($formItem['title']);
    $form = array(
      'title'       => $formTitle ?: 'Blank Form',
      'form_fields' => $formFields,
      'status'      => isset($formItem['status']) ? sanitize_text_field($formItem['status']) : 'published',
      'has_payment' => isset($formItem['has_payment']) ? sanitize_text_field($formItem['has_payment']) : 0,
      'type'        => isset($formItem['type']) ? sanitize_text_field($formItem['type']) : 'form',
      'created_by'  => get_current_user_id(),
    );

    if( isset($formItem['conditions']) ) {
      $form['conditions'] = $formItem['conditions'];
    }

    if( isset($formItem['appearance_settings']) ) {
      $form['appearance_settings'] = $formItem['appearance_settings'];
    }

    // Insert form
    try {
      $formId = \FluentForm\App\Models\Form::insertGetId($form);

      if( $formId ) {
        $inserted_form_ids[] = $formId;

        // Insert form metas
        if( isset($formItem['metas']) && is_array($formItem['metas']) ) {
          foreach($formItem['metas'] as $metaData) {
            if( !isset($metaData['meta_key']) ) {
              continue;
            }

            $metaKey = sanitize_text_field($metaData['meta_key']);
            $metaValue = isset($metaData['value']) ? $metaData['value'] : '';

            // Replace old form ID references with new form ID
            if( in_array($metaKey, array('ffc_form_settings_generated_css', 'ffc_form_settings_meta')) && isset($formItem['id']) ) {
              $metaValue = str_replace('ff_conv_app_' . $formItem['id'], 'ff_conv_app_' . $formId, $metaValue);
            }

            $settings = array(
              'form_id'  => $formId,
              'meta_key' => $metaKey,
              'value'    => $metaValue,
            );

            \FluentForm\App\Models\FormMeta::insert($settings);
          }
        }
      }
    } catch(Exception $e) {
      error_log('Fluent Forms import error for "' . $formTitle . '": ' . $e->getMessage());
      continue;
    }
  }

  return !empty($inserted_form_ids) ? $inserted_form_ids : false;

}


/**
* Helper for replacing external video URLs with local media library URLs in post content.
*
* @since 1.5
* @param array $post_ids Array of post IDs to process
* @param array $video_urls Array of external video URLs to search for and replace
* @return bool True if replacement was made, false otherwise
*/
function nectar_after_demo_import_replace_video_url($post_ids, $video_urls) {

  if( !is_array($post_ids) || empty($post_ids) || !is_array($video_urls) || empty($video_urls) ) {
    return false;
  }

  $replacement_made = false;

  // Loop through each video URL
  foreach($video_urls as $video_url) {

    if( empty($video_url) ) {
      continue;
    }

    // Extract filename from video URL
    $video_filename = basename(parse_url($video_url, PHP_URL_PATH));

    if( empty($video_filename) ) {
      continue;
    }

    // Search for video in media library by filename
    $args = array(
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'posts_per_page' => 1,
      'post_mime_type' => 'video',
      'meta_query' => array(
        array(
          'key' => '_wp_attached_file',
          'value' => $video_filename,
          'compare' => 'LIKE'
        )
      )
    );

    $attachments = get_posts($args);

    // If no attachment found, skip to next video
    if( empty($attachments) ) {
      continue;
    }

    // Get the local URL of the video
    $local_video_url = wp_get_attachment_url($attachments[0]->ID);

    if( !$local_video_url ) {
      continue;
    }

    // Loop through posts and replace the video URL
    foreach($post_ids as $post_id) {

      $post = get_post($post_id);

      if( !$post ) {
        continue;
      }

      // Replace external video URL with local URL
      $updated_content = str_replace($video_url, $local_video_url, $post->post_content);

      // Only update if content changed
      if( $updated_content !== $post->post_content ) {
        wp_update_post(array(
          'ID' => $post_id,
          'post_content' => $updated_content
        ));
        $replacement_made = true;
      }

    }

  }

  return $replacement_made;

}


function nectar_after_demo_import_alter_absolute_links($slug, $links_arr) {

  // Get menu id
  $nav_menu = get_term_by('slug', $slug, 'nav_menu');

  if( isset($nav_menu->term_id) ) {

    foreach($links_arr as $name => $link_data) {

      $generated_url = '';

      if ( isset($link_data['url']) ) {
        $generated_url = home_url( '/' ) . $link_data['url'];
      }

      if( isset($link_data['category_slug']) ) {
        $generated_url = get_category_link(  get_cat_ID( $link_data['category_slug'] ) );
      }

      $menu_item_id = wp_update_nav_menu_item($nav_menu->term_id, 0, array(
        'menu-item-title' => esc_html($name),
        'menu-item-url' => esc_url($generated_url),
        'menu-item-status' => 'publish',
        'menu-item-type' => 'custom'
      ));

      // Add menu options to menu item.
      if ( $menu_item_id && isset($link_data['nectar_menu_options']) ) {
        update_post_meta( $menu_item_id, 'nectar_menu_options', $link_data['nectar_menu_options'] );
      }



    }

  }

}


/**
* Helper for assigning front page.
*
* @since 1.4
*/
function nectar_after_demo_import_assign_front($page_name) {

  $page_query = new WP_Query(array(
    'post_type' => 'page',
    'title' => $page_name,
    'post_status' => 'any',
    'posts_per_page' => 1,
    'no_found_rows' => true
  ));

  $page = !empty($page_query->posts) ? $page_query->posts[0] : null;

  if ( $page && isset($page->ID) ) {
    update_option( 'page_on_front', $page->ID );
    update_option( 'show_on_front', 'page' );
  }

}


/**
* eCommerce Specific after a demo has imported.
*
* @since 1.0
*/
if ( ! function_exists( 'nectar_after_ecommerce_demo_import' ) ) {

  function nectar_after_ecommerce_demo_import( $demo_active_import, $demo_directory_path ) {

    global $woocommerce;

    // eCommerce Ultimate
    if ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ecommerce-Ultimate' ) && $woocommerce ) {

      // Update shop page page header.
      $shop_page_id = wc_get_page_id( 'shop' );
      if ( $shop_page_id ) {

        update_post_meta( $shop_page_id, '_nectar_header_bg_color', '#eaf0ff' );
        update_post_meta( $shop_page_id, '_nectar_header_title', 'All Products' );
        update_post_meta( $shop_page_id, '_nectar_header_font_color', '#000000' );
        update_post_meta( $shop_page_id, '_nectar_header_subtitle', 'Affordable designer clothing with unmatched quality' );
        update_post_meta( $shop_page_id, '_nectar_page_header_alignment', 'center' );
        update_post_meta( $shop_page_id, '_nectar_header_bg_height', '230' );
        update_post_meta( $shop_page_id, '_disable_transparent_header', 'on' );
      }

      // Update category thumbnails.
      nectar_update_woo_cat_thumb( 'accessories', 5688 );
      nectar_update_woo_cat_thumb( 'basic-t-shirts', 17 );
      nectar_update_woo_cat_thumb( 'casual-shirts', 29 );
      nectar_update_woo_cat_thumb( 'fresh-clothing', 18 );
      nectar_update_woo_cat_thumb( 'hipster-style', 41 );
      nectar_update_woo_cat_thumb( 'outerwear', 38 );
      nectar_update_woo_cat_thumb( 'sports-clothing', 5767 );

    } // end ecommerce ultimate

    // eCommerce Creative
    elseif ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ecommerce-Creative' ) && $woocommerce ) {

      // Update shop page page header.
      $shop_page_id = wc_get_page_id( 'shop' );
      if ( $shop_page_id ) {
        update_post_meta( $shop_page_id, '_nectar_header_title', 'The Shop' );
        update_post_meta( $shop_page_id, '_nectar_header_subtitle', 'Affordable designer clothing with unmatched quality' );
        update_post_meta( $shop_page_id, '_nectar_page_header_alignment', 'center' );
        update_post_meta( $shop_page_id, '_nectar_header_bg_height', '400' );
        update_post_meta( $shop_page_id, '_nectar_header_bg', 'http://themenectar.com/demo/salient-ecommerce-creative/wp-content/uploads/2018/08/adrian-sava-184378-unsplash.jpg' );
      }

      // Update category thumbnails.
      nectar_update_woo_cat_thumb( 'basic-t-shirts', 3002 );
      nectar_update_woo_cat_thumb( 'casual-shirts', 3004 );
      nectar_update_woo_cat_thumb( 'cool-clothing', 3003 );
      nectar_update_woo_cat_thumb( 'fresh-accessories', 3001 );
      nectar_update_woo_cat_thumb( 'hipster-style', 2960 );
      nectar_update_woo_cat_thumb( 'outerwear', 3060 );
      nectar_update_woo_cat_thumb( 'sport-clothing', 2970 );

    } // end ecommerce creative


  } // main function end

}



/**
* Disable additional image sizes for demos which do not need them.
*
* @since 1.2
*/
if( !function_exists('nectar_before_content_import') ) {
  function nectar_before_content_import( $demo_active_import, $demo_directory_path ) {
    if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ecommerce-Robust' ) ||
        isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Mag' ) ||
        isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Architect' ) ) {
      add_filter( 'intermediate_image_sizes_advanced', 'nectar_disable_additional_image_sizes', 10, 2 );
    }
  }
}

if( !function_exists('nectar_disable_additional_image_sizes') ) {
  function nectar_disable_additional_image_sizes( $sizes, $image_meta ) {

    $sizes_to_remove = array(
      'portfolio-thumb_large',
      'portfolio-thumb_small',
      'portfolio-thumb',
      'portfolio-widget',
      'nectar_small_square',
      'wide',
      'wide_small',
      'regular',
      'regular_small',
      'tall',
      'wide_tall',
      'wide_photography',
      'medium_featured',
      'large_featured',
      'medium',
    );

    foreach( $sizes_to_remove as $size ) {
      if( isset($sizes[$size]) ) {
        unset( $sizes[$size] );
      }
    }

    return $sizes;
  }
}


if( !function_exists('nectar_wpforms_import_form') ) {
  function nectar_wpforms_import_form($form, $pages_to_update = array()) {
    if ( class_exists('WPForms') &&
        function_exists('wpforms_current_user_can') &&
        function_exists('wpforms_encode') ) {

        if( wpforms_current_user_can( 'create_forms' ) ) {

          $title  = ! empty( $form['settings']['form_title'] ) ? esc_html($form['settings']['form_title']) : '';
			    $desc   = ! empty( $form['settings']['form_desc'] ) ? esc_html($form['settings']['form_desc']) : '';

          $imported_id = $form['id'];
          $new_id = wp_insert_post(
            [
              'post_title'   => $title,
              'post_status'  => 'publish',
              'post_type'    => 'wpforms',
              'post_excerpt' => $desc,
            ]
          );

          if ( $new_id ) {
            $form['id'] = $new_id;

            wp_update_post(
              [
                'ID'           => $new_id,
                'post_content' => wpforms_encode( $form ),
              ]
            );

            // Search and replace form ID in content.
            if ( ! empty( $pages_to_update ) ) {
              foreach ( $pages_to_update as $page_id ) {
                $page = get_post( $page_id );
                if ( $page ) {
                  $page->post_content = str_replace( '[wpforms id="' . $imported_id, '[wpforms id="' . $new_id, $page->post_content );
                  wp_update_post( $page );
                }
              }
            }
          } // end new id

        } // end user can create forms
    } // end class exists
  } // end nectar_wpforms_import_form
}
/**
* General after a demo has imported.
*
* Assigns menus and front.
*
* @since 1.4
*/
if( !function_exists('nectar_after_content_import') ) {

  function nectar_after_content_import( $demo_active_import, $demo_directory_path ) {



    // Harbor
    if ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Harbor' ) ) {
      nectar_after_demo_import_assign_menu('harbor-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Harbor Landing');

      // hash links
      $hash_links = array(
        'About' => 'about',
        'Services' => 'services',
        'Testimonials' => 'testimonials',
        'Pricing' => 'pricing',
      );
      nectar_after_demo_import_add_hash_links('harbor-navigation', $hash_links, 'prepend');

      // Replace {nectar_site_url} placeholder with actual site URL in post content.
      $global_sections_to_update = array();
      $footer_post = nectar_after_demo_import_get_post_by_title('Harbor Footer', 'salient_g_sections');
      if ( $footer_post ) {
        $global_sections_to_update[] = $footer_post;
      }

      if ( !empty( $global_sections_to_update ) ) {
        nectar_after_demo_import_replace_site_url($global_sections_to_update);
      }

      $pages_to_update = array();
      $landing_page_post = nectar_after_demo_import_get_post_by_title('Harbor Landing', 'page');
      $secondary_header_global_section = nectar_after_demo_import_get_post_by_title('Harbor Secondary Nav', 'salient_g_sections');
      if ( $landing_page_post ) {
        $pages_to_update[] = $landing_page_post;
      }
      if ( $secondary_header_global_section ) {
        $pages_to_update[] = $secondary_header_global_section;
      }

      if ( !empty( $pages_to_update ) ) {
        nectar_after_demo_import_replace_page_url($pages_to_update);
      }

      // Import Fluent Forms
      $fluentform_json_path = $demo_directory_path . '/fluentform.json';
      if( file_exists($fluentform_json_path) ) {
        nectar_after_demo_import_fluent_forms($fluentform_json_path);
      }

    }


    // Signal Landing
    if ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Signal' ) ) {
      nectar_after_demo_import_assign_menu('signal-cta', 'top_nav');
      nectar_after_demo_import_assign_menu('signal-navigation', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Signal Landing');

       // hash links
       $hash_links = array(
        'Index' => '',
        'Services' => 'services',
        'Testimonials' => 'testimonials'
      );
      nectar_after_demo_import_add_hash_links('signal-navigation', $hash_links, 'prepend');

      $pull_right_hash_links = array(
        'Get Started' => [
          'hash' => 'contact',
          'meta' => [
            'nectar_menu_options' => 'a:23:{s:15:"mega_menu_width";s:3:"100";s:19:"mega_menu_alignment";s:4:"left";s:24:"mega_menu_global_section";s:1:"-";s:31:"mega_menu_global_section_mobile";s:1:"-";s:16:"mega_menu_bg_img";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:26:"mega_menu_bg_img_alignment";s:6:"center";s:17:"mega_menu_padding";s:7:"default";s:19:"menu_item_icon_type";s:12:"font_awesome";s:21:"menu_item_icon_custom";a:2:{s:3:"url";s:84:"https://themenectar.com/salient/signal/wp-content/uploads/sites/45/2025/09/arrow.png";s:2:"id";s:3:"256";}s:30:"menu_item_link_text_color_type";s:7:"default";s:37:"menu_item_link_coloring_custom_text_p";s:7:"#000000";s:39:"menu_item_link_coloring_custom_text_h_p";s:7:"#777777";s:36:"menu_item_link_coloring_custom_label";s:7:"#999999";s:40:"menu_item_link_coloring_custom_button_bg";s:7:"#eeeeee";s:47:"menu_item_link_coloring_custom_button_bg_active";s:7:"#000000";s:49:"menu_item_link_coloring_custom_button_text_active";s:7:"#ffffff";s:23:"menu_item_icon_position";s:7:"default";s:24:"menu_item_icon_alignment";s:5:"right";s:19:"menu_item_icon_size";s:2:"16";s:31:"menu_item_persist_mobile_header";s:2:"on";s:34:"menu_item_hide_menu_title_modifier";s:3:"all";s:25:"menu_item_link_link_style";s:26:"button-border_accent-color";s:30:"menu_item_link_link_text_style";s:11:"text-reveal";}',
          ]
        ]
      );
      nectar_after_demo_import_add_hash_links('signal-cta', $pull_right_hash_links, 'prepend');

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

      // Replace video URLs
      $landing_page_post = nectar_after_demo_import_get_post_by_title('Signal Landing', 'page');
      if( $landing_page_post ) {
        $videos_replaced = nectar_after_demo_import_replace_video_url(
          array($landing_page_post),
          array(
            'https://themenectar.com/salient/signal/wp-content/uploads/sites/45/2025/11/agency.mp4'
          )
        );
      }

       // Import Fluent Forms
       $fluentform_json_path = $demo_directory_path . '/fluentform.json';
       if( file_exists($fluentform_json_path) ) {
         nectar_after_demo_import_fluent_forms($fluentform_json_path);
       }

    }


    // Tether
    if ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Tether' ) ) {
      nectar_after_demo_import_assign_menu('tether-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Tether Landing');

      // hash links
      $hash_links = array(
        'Overview' => 'overview',
        'Features' => 'features',
        'Results' => 'results',
      );
      nectar_after_demo_import_add_hash_links('tether-menu', $hash_links, 'prepend');


      // Replace video URLs
      $landing_page_post = nectar_after_demo_import_get_post_by_title('Tether Landing', 'page');
      if( $landing_page_post ) {
        $videos_replaced = nectar_after_demo_import_replace_video_url(
          array($landing_page_post),
          array(
            'https://themenectar.com/salient/tether/wp-content/uploads/sites/46/2025/09/testimonial-5.mp4',
            'https://themenectar.com/salient/tether/wp-content/uploads/sites/46/2025/09/testimonial-4.mp4',
            'https://themenectar.com/salient/tether/wp-content/uploads/sites/46/2025/08/testimonial.mp4',
            'https://themenectar.com/salient/tether/wp-content/uploads/sites/46/2025/09/testimonial-3.mp4'
          )
        );
      }

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

    }


    // Portfolio Layered
    if ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Portfolio-Layered' ) ) {

       // Assign primary menu/landing page.
       nectar_after_demo_import_assign_menu('portfolio-layered', 'top_nav');
       nectar_after_demo_import_assign_menu('portfolio-layered-cta', 'top_nav_pull_right');
       nectar_after_demo_import_assign_front('Portfolio Layered Landing');

      // hash links
       $hash_links = array(
        'Work' => 'work',
        'Awards' => 'awards'
      );
      nectar_after_demo_import_add_hash_links('portfolio-layered', $hash_links);

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

    }


    if ( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Portfolio-Quantum' ) ) {
       // Assign primary menu/landing page.
       nectar_after_demo_import_assign_menu('quantum-portfolio', 'top_nav');
       nectar_after_demo_import_assign_front('Portfolio Quantum Landing');

         // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }
    }

    // Mag
    if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Mag' )  ) {

      // Import Form.
      $wp_form = '{"fields":{"2":{"id":"2","type":"email","label":"","description":"","required":"1","size":"medium","placeholder":"Your Email","confirmation_placeholder":"","default_value":false,"filter_type":"","allowlist":"","denylist":"","css":""}},"id":145,"field_id":3,"settings":{"form_title":"Newsletter Signup Form 2","form_desc":"","submit_text":"Subscribe","submit_text_processing":"Sending...","form_class":"nectar-inline-subscribe-form ","submit_class":"","ajax_submit":"1","notification_enable":"1","notifications":{"1":{"email":"{admin_email}","subject":"New Entry: Newsletter Signup Form","sender_name":"Salient","sender_address":"{admin_email}","replyto":"{field_id=\"2\"}","message":"{all_fields}"}},"confirmations":{"1":{"type":"message","message":"<p>Thanks for signing up for the newsletter! We will be in touch soon.<\/p>","message_scroll":"1","page":"2","redirect":""}},"antispam":"1","form_tags":[]},"meta":{"template":"4bf2a29bffb74b5530d89949041b535d"}}';

      // Add landing page ID.
      $pages_to_update = array();
      $page_query_args  = array(
        'title'                  => 'Mag Landing',
        'post_type'              => 'page',
        'posts_per_page'         => 1,
      );
      $query = new WP_Query( $page_query_args );
      $pages = $query->posts;

      if ( !empty( $pages ) ) {
        $pages_to_update[] = $pages[0]->ID;
      }

      // Add global section ID.
      $footer_post = get_page_by_path( 'mag-footer', OBJECT, 'salient_g_sections' );
      if ( $footer_post ) {
        $pages_to_update[] = $footer_post->ID;
      }
      nectar_wpforms_import_form( json_decode($wp_form, true), $pages_to_update );


      // Category Colors.
      // term_id => array( 'category_color' => '#HEX', 'category_text_color' => '#HEX' )
      $colors = array(
        'career' => array(
          'category_color' => '#d4e8cc',
          'category_text_color' => '#000000'
        ),
        'design' => array(
          'category_color' => '#d5e6f6',
          'category_text_color' => '#000000'
        ),
        'tech' => array(
          'category_color' => '#f9e4b4',
          'category_text_color' => '#000000'
        )
      );
      nectar_update_category_term_meta( $colors );

      // Assign primary menu/landing page.
      nectar_after_demo_import_assign_menu('salient-mag', 'top_nav');
      nectar_after_demo_import_assign_menu('salient-mag-pull-right', 'top_nav_pull_right');
      nectar_after_demo_import_assign_front('Mag Landing');

      // Special Location global sections.
      $blog_archives_id = 0;
      $blog_archives_post = get_page_by_path( 'blog-archives', OBJECT, 'salient_g_sections' );
      if ( $blog_archives_post ) {
        $blog_archives_id = $blog_archives_post->ID;
      }

      $special_locations = array(
        'nectar_special_location__blog_loop' => $blog_archives_id
      );
      update_option( 'salient_global_section_special_locations', $special_locations );


      // absolute links
      $links = array(
        'Career' => array(
         'category_slug' => 'career',
         'nectar_menu_options' => 'a:18:{s:15:"mega_menu_width";s:3:"100";s:19:"mega_menu_alignment";s:4:"left";s:16:"mega_menu_bg_img";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:26:"mega_menu_bg_img_alignment";s:6:"center";s:17:"mega_menu_padding";s:7:"default";s:19:"menu_item_icon_type";s:12:"font_awesome";s:21:"menu_item_icon_custom";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:30:"menu_item_link_text_color_type";s:6:"custom";s:37:"menu_item_link_coloring_custom_text_p";s:7:"#000000";s:39:"menu_item_link_coloring_custom_text_h_p";s:7:"#000000";s:36:"menu_item_link_coloring_custom_label";s:7:"#000000";s:40:"menu_item_link_coloring_custom_button_bg";s:7:"#d4e8cc";s:47:"menu_item_link_coloring_custom_button_bg_active";s:7:"#d4e8cc";s:49:"menu_item_link_coloring_custom_button_text_active";s:7:"#000000";s:23:"menu_item_icon_position";s:7:"default";s:34:"menu_item_hide_menu_title_modifier";s:3:"all";s:25:"menu_item_link_link_style";s:7:"default";s:30:"menu_item_link_link_text_style";s:7:"default";}'
        ),
        'Design' => array(
          'category_slug' => 'design',
          'nectar_menu_options' => 'a:18:{s:15:"mega_menu_width";s:3:"100";s:19:"mega_menu_alignment";s:4:"left";s:16:"mega_menu_bg_img";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:26:"mega_menu_bg_img_alignment";s:6:"center";s:17:"mega_menu_padding";s:7:"default";s:19:"menu_item_icon_type";s:12:"font_awesome";s:21:"menu_item_icon_custom";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:30:"menu_item_link_text_color_type";s:6:"custom";s:37:"menu_item_link_coloring_custom_text_p";s:7:"#000000";s:39:"menu_item_link_coloring_custom_text_h_p";s:7:"#000000";s:36:"menu_item_link_coloring_custom_label";s:7:"#000000";s:40:"menu_item_link_coloring_custom_button_bg";s:7:"#d5e6f6";s:47:"menu_item_link_coloring_custom_button_bg_active";s:7:"#d5e6f6";s:49:"menu_item_link_coloring_custom_button_text_active";s:7:"#000000";s:23:"menu_item_icon_position";s:7:"default";s:34:"menu_item_hide_menu_title_modifier";s:3:"all";s:25:"menu_item_link_link_style";s:7:"default";s:30:"menu_item_link_link_text_style";s:7:"default";}'
         ),
        'Tech' => array(
          'category_slug' => 'tech',
          'nectar_menu_options' => 'a:18:{s:15:"mega_menu_width";s:3:"100";s:19:"mega_menu_alignment";s:4:"left";s:16:"mega_menu_bg_img";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:26:"mega_menu_bg_img_alignment";s:6:"center";s:17:"mega_menu_padding";s:7:"default";s:19:"menu_item_icon_type";s:12:"font_awesome";s:21:"menu_item_icon_custom";a:2:{s:3:"url";s:0:"";s:2:"id";s:0:"";}s:30:"menu_item_link_text_color_type";s:6:"custom";s:37:"menu_item_link_coloring_custom_text_p";s:7:"#000000";s:39:"menu_item_link_coloring_custom_text_h_p";s:7:"#000000";s:36:"menu_item_link_coloring_custom_label";s:7:"#000000";s:40:"menu_item_link_coloring_custom_button_bg";s:7:"#f9e4b4";s:47:"menu_item_link_coloring_custom_button_bg_active";s:7:"#f9e4b4";s:49:"menu_item_link_coloring_custom_button_text_active";s:7:"#000000";s:23:"menu_item_icon_position";s:7:"default";s:34:"menu_item_hide_menu_title_modifier";s:3:"all";s:25:"menu_item_link_link_style";s:7:"default";s:30:"menu_item_link_link_text_style";s:7:"default";}'
         )
      );
      nectar_after_demo_import_alter_absolute_links('salient-mag', $links);

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }


    }

    // SaaS.
    if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'SaaS' ) ) {
      nectar_after_demo_import_assign_menu('saas-main-navigation', 'top_nav');
      nectar_after_demo_import_assign_menu('saas-pull-right', 'top_nav_pull_right');
      nectar_after_demo_import_assign_front('SaaS Landing');

      $hash_links = array(
        'Our values' => 'intro',
        'Why Salient?' => 'testimonials',
        'How it works' => 'how-it-works'
      );
      nectar_after_demo_import_add_hash_links('saas-main-navigation', $hash_links);

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

       // Replace video URLs
       $landing_page_post = nectar_after_demo_import_get_post_by_title('SaaS Landing', 'page');
       if( $landing_page_post ) {
         $videos_replaced = nectar_after_demo_import_replace_video_url(
           array($landing_page_post),
           array(
             'https://themenectar.com/salient/saas/wp-content/uploads/sites/40/2025/08/pexels-tony-schnagl.mp4'
           )
         );
       }

    }

    // Architect.
    if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Architect' ) ) {
      nectar_after_demo_import_assign_menu('salient-architect-main-menu', 'off_canvas_nav');
      nectar_after_demo_import_assign_menu('salient-architect-top-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Salient Architect Landing');

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

    }

    // Resort.
    if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Resort' ) ) {
      nectar_after_demo_import_assign_menu('salient-resort-main-menu', 'off_canvas_nav');
      nectar_after_demo_import_assign_menu('salient-resort-side-buttons', 'top_nav_pull_right');
      nectar_after_demo_import_assign_front('Salient Resort Landing');

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

    }

    // eCommerce Robust
    if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ecommerce-Robust' ) ) {

      // Generate Menu CSS.
      if( class_exists('Nectar_WP_Menu_Style_Manager') ) {
        set_transient( 'salient_menu_css_regenerate', 1, DAY_IN_SECONDS );
      }

      // Assign menu/front.
      nectar_after_demo_import_assign_menu('ecommerce-robust', 'top_nav_pull_left');
      nectar_after_demo_import_assign_menu('ecommerce-robust-right-side', 'top_nav_pull_right');
      nectar_after_demo_import_assign_front('eCommerce Robust Landing');

      // Update category thumbnails.
      nectar_update_woo_cat_thumb( 'accessories', 1437 );
      nectar_update_woo_cat_thumb( 'cosmetics', 1442 );
      nectar_update_woo_cat_thumb( 'skincare', 1439 );
      nectar_update_woo_cat_thumb( 'supplements', 832 );
      nectar_update_woo_cat_thumb( 'anti-acne', 834 );
      nectar_update_woo_cat_thumb( 'cleanse', 839 );
      nectar_update_woo_cat_thumb( 'exfoliators', 835 );
      nectar_update_woo_cat_thumb( 'extracts', 1440 );
      nectar_update_woo_cat_thumb( 'moisturize', 836 );


    }

    // Wellness
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Wellness' ) ) {

      nectar_after_demo_import_assign_menu('wellness-off-canvas-menu', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Wellness - Home');

      $hash_links = array(
        'Home' => 'home',
        'Services' => 'services',
        'Our Story' => 'story',
        'Pricing' => 'pricing'
      );
      nectar_after_demo_import_add_hash_links('wellness-off-canvas-menu', $hash_links);
    }
    // Nonprofit
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Nonprofit' ) ) {

      nectar_after_demo_import_assign_menu('nonprofit-menu', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Nonprofit Landing');

      $hash_links = array(
        'Introduction' => 'home',
        'Philosophy' => 'philosophy',
        'Testimonials' => 'testimonials',
        'Areas of Impact' => 'impact'
      );
      nectar_after_demo_import_add_hash_links('nonprofit-menu', $hash_links);
    }
    // Business 3
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Business-3' ) ) {

      nectar_after_demo_import_assign_menu('business-3-main-menu', 'top_nav');
      nectar_after_demo_import_assign_menu('business-3-right-menu', 'top_nav_pull_right');
      nectar_after_demo_import_assign_front('Business Landing');
    }
    // Corporate 3
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Corporate-3' ) ) {

      nectar_after_demo_import_assign_menu('corporate-3', 'top_nav');
      nectar_after_demo_import_assign_front('Corporate 3 Landing');
    }
    // Freelance Portfolio
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Freelance-Portfolio' ) ) {

      nectar_after_demo_import_assign_menu('freelance-portfolio-menu', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Freelance Portfolio - Home');
    }
    // eCommerce Ultimate
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ecommerce-Ultimate' ) ) {

      nectar_after_demo_import_assign_menu('ecommerce-ultimate-main-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('eCommerce Ultimate Home Page');
    }

    // eCommerce Creative
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ecommerce-Creative' ) ) {

      nectar_after_demo_import_assign_menu('ecommerce-creative-main-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('eCommerce Creative Home Page');
    }

    // Blog Dark
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Dark-Blog' ) ) {
      nectar_after_demo_import_assign_front('Blog Dark Landing');
    }

    // Corporate 2
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Corporate-2' ) ) {

      nectar_after_demo_import_assign_menu('corporate-2-nav', 'top_nav');
      nectar_after_demo_import_assign_front('Corporate 2 Landing');
    }

    // Blog Ultimate
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Blog-Ultimate' ) ) {

      nectar_after_demo_import_assign_menu('blog-ultimate-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Ultimate Blog Landing');
    }

    // Corporate Creative
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Corporate-Creative' ) ) {

      nectar_after_demo_import_assign_menu('corporate-creative-nav', 'top_nav');
      nectar_after_demo_import_assign_front('Corporate Creative Landing');
    }

    // Blog Magazine
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Blog-Magazine' ) ) {

      nectar_after_demo_import_assign_menu('magazine-blog-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Magazine Blog Landing');
    }

    // Business 2
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Business-2' ) ) {

      nectar_after_demo_import_assign_menu('business-2-nav', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Business 2 Landing');
    }

    // Startup
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Company-Startup' ) ) {

      nectar_after_demo_import_assign_menu('startup-menu', 'top_nav');
      nectar_after_demo_import_assign_menu('startup-right-pull-menu', 'top_nav_pull_right');
      nectar_after_demo_import_assign_front('Startup Home');
    }

    // Fullscreen Portfolio
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Fullscreen Portfolio Slider' ) ) {

      nectar_after_demo_import_assign_menu('slider-portfolio-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Slider Portfolio');
    }

    // Band
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Band' ) ) {

      nectar_after_demo_import_assign_menu('band-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Band Home Page');
    }

    // Minimal Portfolio
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Minimal Portfolio' ) ) {

      nectar_after_demo_import_assign_menu('minimal-portfolio-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Minimal Portfolio');
    }

    // Corporate
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Corporate' ) ) {

      nectar_after_demo_import_assign_menu('corporate-main-nav', 'top_nav');
      nectar_after_demo_import_assign_front('Home');
    }

    // Agency
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Agency' ) ) {

      nectar_after_demo_import_assign_menu('main-nav', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Home - Default');
    }

    // Restaurant
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Restaurant' ) ) {

      nectar_after_demo_import_assign_menu('restaurant-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Restaurant - Home Page');
    }

    // Business
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Business' ) ) {

      nectar_after_demo_import_assign_menu('business-demo', 'top_nav');
      nectar_after_demo_import_assign_front('Home');
    }

    // Landing Service
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Landing Service' ) ) {

      nectar_after_demo_import_assign_menu('service-demo', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Service Demo');
    }

    // Photography
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Photography' ) ) {

      nectar_after_demo_import_assign_menu('photography-menu', 'top_nav');
      nectar_after_demo_import_assign_front('Featured');
    }

    // Landing Product
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Landing Product' ) ) {

      nectar_after_demo_import_assign_menu('product-landing-demo', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Home - Product Landing Demo');
    }

    // App
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'App' ) ) {

      nectar_after_demo_import_assign_menu('app-demo', 'off_canvas_nav');
      nectar_after_demo_import_assign_front('Home - App Demo');
    }

    // Simple Blog
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Simple Blog' ) ) {

      nectar_after_demo_import_assign_menu('simple-blog-main-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Landing Page');
    }

    // Old School Ecommerce
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Old-School-Ecommerce' ) ) {

      nectar_after_demo_import_assign_menu('top-nav', 'top_nav');
      nectar_after_demo_import_assign_front('Home');
    }

    // One Page
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'One-Page' ) ) {

      nectar_after_demo_import_assign_menu('header', 'top_nav');
      nectar_after_demo_import_assign_front('Home ');
    }

    // Ascend
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Ascend' ) ) {

      nectar_after_demo_import_assign_menu('ascend-main-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Landing Page');
    }

    // Frostwave
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Frostwave' ) ) {

      nectar_after_demo_import_assign_menu('frostwave-main-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Landing Page');
    }

    // Old School Classic
    else if( isset( $demo_directory_path ) && strpos( $demo_directory_path, 'Old-School-All-Purpose' ) ) {

      nectar_after_demo_import_assign_menu('main-navigation', 'top_nav');
      nectar_after_demo_import_assign_front('Home - Landing Page');
    }


  }

}