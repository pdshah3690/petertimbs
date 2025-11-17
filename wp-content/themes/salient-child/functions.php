<?php
define('Delivery_Fee', 10);

$zone = get_option('timezone_string');
date_default_timezone_set($zone);
date_default_timezone_set('Pacific/Auckland');

require_once 'classes/class-butcher-box-admin.php';
require_once 'classes/class-butcher-box-public.php';
require_once 'classes/class-recipe-admin.php';
require_once 'classes/class-recipe-public.php';
require_once 'classes/class-petertimbs-settings-admin.php';
require_once 'classes/class-petertimbs-product-admin.php';
require_once 'classes/class-petertimbs-delivery-public.php';
require_once 'classes/class-petertimbs-product-filters.php';
require_once 'classes/class-petertimbs-meals-filter.php';
require_once 'classes/class-butcher-kitchen-public.php';
require_once 'classes/class-custom-widget.php';
require_once 'classes/class-pt-gss-public.php';
// require_once 'classes/class-atria-function.php';

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

add_action('init', 'set_default_time_zone');
function set_default_time_zone() {
    date_default_timezone_set('Pacific/Auckland');
}

function salient_child_enqueue_styles() {

    $nectar_theme_version = nectar_get_theme_version();

    wp_enqueue_style( 'datetime', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css', '', $nectar_theme_version );
    wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
    wp_enqueue_style( 'salient-child-custom', get_stylesheet_directory_uri() . '/css/custom.css', '', $nectar_theme_version );

    wp_deregister_script('nectar_woo_quick_view_js');
    wp_register_script('nectar_woo_quick_view_child_js', get_stylesheet_directory_uri() . '/nectar/woo/js/quick_view_actions.js', array('jquery'), '1.1', true);
    wp_enqueue_script('nectar_woo_quick_view_child_js');

    wp_enqueue_script('datetime', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js', array('jquery'), '1.1', true);
    wp_enqueue_script('googlemap', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAYyxEGB5J4cZGFf9TWu4N0gy0KQDWtFeI', array('jquery'), '1.1', true);

    $option = get_option("peter_timbs");
    $cut_off = empty($option['delivery_cut_off_time']) ? "12:00" : $option['delivery_cut_off_time'];
    $cut_off_day = empty($option['delivery_cut_off_day']) ? 0 : $option['delivery_cut_off_day'];
    // $cut_off_day = 4;
    $now = strtotime("now");
    $cut_off = strtotime($cut_off);
    $disabled_dates = get_deliery_cut_off_dates();
    $pickup_disabled_dates = [date("Y/m/d"), "2025/01/02", "2025/02/06", "2025/03/29", "2025/04/01", "2025/06/03", "2025/06/28", "2025/10/28", "2025/11/15", "2025/12/25", "2025/12/26"];

    // $is_disabled = 1;
    // if($cut_off_day == 0 && $cut_off < $now) {
    $disabled_dates[] = date("Y/m/d");
    $pickup_disabled_dates[] = date("Y/m/d");
    // }
    if($cut_off_day == 1) {
        $disabled_dates[] = date("Y/m/d", strtotime("+1 Days"));
    }
    if($cut_off_day == 2) {
        $disabled_dates[] = date("Y/m/d", strtotime("+1 Days"));
        $disabled_dates[] = date("Y/m/d", strtotime("+2 Days"));
        // $pickup_disabled_dates[] = date("Y/m/d", strtotime("+1 Days"));
    }
    if($cut_off_day == 4) {
        $disabled_dates[] = date("Y/m/d", strtotime("+1 Days"));
        $disabled_dates[] = date("Y/m/d", strtotime("+2 Days"));
        $disabled_dates[] = date("Y/m/d", strtotime("+3 Days"));
        $disabled_dates[] = date("Y/m/d", strtotime("+4 Days"));
        $pickup_disabled_dates[] = date("Y/m/d", strtotime("+1 Days"));
        $pickup_disabled_dates[] = date("Y/m/d", strtotime("+2 Days"));
        $pickup_disabled_dates[] = date("Y/m/d", strtotime("+3 Days"));
        $pickup_disabled_dates[] = date("Y/m/d", strtotime("+4 Days"));
    }
    // if($is_disabled) {
    //     $disabled_dates[] = date("Y/m/d");
    // }
    $pick_up_range = [];
    $from = $to = "";
    // if( !is_admin() && WC()->session->get('is_meal') ) {
    //     $meal_id = WC()->session->get('is_meal');
    //     $from = date("Y-m-d", strtotime(get_post_meta( $meal_id, 'date_product_from', true )));
    //     $to = date("Y-m-d", strtotime(get_post_meta( $meal_id, 'date_product_to', true )));
    // }
    wp_localize_script('jquery', 'pt_obj', [
        // 'is_disabled' => $is_disabled,
        'current_time' => date("H"),
        'current_day' =>  strtolower(date("l")),
        "next_day" => date("Y/m/d", strtotime("tomorrow")),
        "max_date" => date("Y/m/d", strtotime("+60 days")),
        "today" => date("Y/m/d", strtotime("now")),
        "delivery_distance" => $option['delivery_distance'],
        "disabled_dates" => $disabled_dates,
        "pickup_disabled_dates" => $pickup_disabled_dates,
        "pick_up_range_from"  => $from,
        "pick_up_range_to"  => $to
    ]);
    wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/js/custom1.js', array('jquery'), '2.5', true);
    if ( is_rtl() ) {
        wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
    }
}

add_action('admin_enqueue_scripts', 'salient_child_admin_enqueue_scripts');
function salient_child_admin_enqueue_scripts() {
    wp_enqueue_style( 'jquery_datatables_css', get_stylesheet_directory_uri() . '/assets/DataTables-1.10.18/css/jquery.dataTables.min.css');
    wp_enqueue_style( 'jquery_datatables_buttons_css', get_stylesheet_directory_uri() . '/assets/Buttons-1.5.6/css/buttons.dataTables.min.css');

    wp_enqueue_script( 'jquery_datatables_js', get_stylesheet_directory_uri().'/assets/DataTables-1.10.18/js/jquery.dataTables.min.js', array(), null, true );
    wp_enqueue_script( 'jquery_datatables_buttons_js', get_stylesheet_directory_uri().'/assets/Buttons-1.5.6/js/dataTables.buttons.min.js', array(), null, true );
    wp_enqueue_script( 'jquery_flash_datatables_js', get_stylesheet_directory_uri().'/assets/Buttons-1.5.6/js/buttons.flash.min.js', array(), null, true );
    wp_enqueue_script( 'jquery_datatables_jszip_js', get_stylesheet_directory_uri().'/assets/JSZip-2.5.0/jszip.min.js', array(), null, true );
    wp_enqueue_script( 'jquery_datatables_buttons_html5_js', get_stylesheet_directory_uri().'/assets/Buttons-1.5.6/js/buttons.html5.min.js', array(), null, true );
    wp_enqueue_script( 'jquery_datatables_buttons_print_js', get_stylesheet_directory_uri().'/assets/Buttons-1.5.6/js/buttons.print.min.js', array(), null, true );
    wp_enqueue_script( 'custom_admin', get_stylesheet_directory_uri().'/js/admin.js', array(), null, true );
}

function salient_redux_custom_fonts( $custom_fonts ) {
    return array(
        'Custom Fonts' => array(
             'veneerthree' => "veneerthree"
        )
    );
}
add_filter( "redux/salient_redux/field/typography/custom_fonts", "salient_redux_custom_fonts" );

// disable for posts
add_filter('use_block_editor_for_post', '__return_false', 10);

function _dd($array, $true = true) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";
    if($true) {
        exit;
    }
}

add_action('wp_ajax_add_quick_view_item_to_cart', 'add_quick_view_item_to_cart');
add_action('wp_ajax_nopriv_add_quick_view_item_to_cart', 'add_quick_view_item_to_cart');

function add_quick_view_item_to_cart() {
    if(wp_doing_ajax()) {
        $product_id = empty($_POST['variation_id']) ? $_POST['product_id'] : $_POST['variation_id'];
        $shipping_type = get_post_meta($product_id, "_shipping_type", 1);
        $shipping_type = empty($shipping_type) ? "both" : $shipping_type;
        $current_product_cat = has_term('meals', 'product_cat', $_POST['product_id']);

        if(!WC()->cart->is_empty()) {
            foreach(WC()->cart->get_cart() as $cart_items_key => $cart_items) {
                $item_id = $cart_items['product_id'];
                $item_cat = has_term('meals', 'product_cat', $item_id);
                if($item_cat == false && $current_product_cat == false) {
                    break;
                }
                if($current_product_cat == $item_cat) {
                    if ($item_id != $_POST['product_id']) {
                        wp_send_json([
                            "error" => true,
                            "message" => "Sorry, meals can only be purchased separately. If you want a different meal please order separately."
                        ]);
                        wp_die();
                    }
                }
                if($current_product_cat != $item_cat) {
                    if($current_product_cat == true) {
                        wp_send_json([
                            "error" => true,
                            // "message" => "Sorry, meals cannot be purchased with other items and need to be ordered separately."
                            "message" => "Please note, items purchased from Peter Timbs Edgeware cannot be purchased in the same order as The Butchers Kitchen"
                        ]);
                    } else {
                         wp_send_json([
                            "error" => true,
                            "message" => "You currently have a meal (or meals) in your cart, meals cannot be purchased with other items."
                        ]);
                    }
                    wp_die();
                }
                WC()->session->set('is_meal', $_POST['product_id']);
            }
        }

        if(!WC()->cart->is_empty() && $shipping_type != "both") {
            $cart_type = "both";
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $item_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
                $item_type = get_post_meta($item_id, "_shipping_type", 1);

                $item_type = empty($item_type) ? "both" : $item_type;
                if($item_type != "both") {
                    $cart_type = $item_type;
                    break;
                }
            }
            if($cart_type != "both") {
                if($cart_type != $shipping_type && $shipping_type == "delivery") {
                    wp_send_json([
                        "error" => true,
                        "message" => "Sorry, only specific items are available for pickup and only these items can be included in the same order."
                    ]);
                    wp_die();
                }
                if($cart_type != $shipping_type && $shipping_type == "pick_up") {
                    wp_send_json([
                        "error" => true,
                        "message" => "Sorry, only specific items are available for delivery and only these items can be included in the same order."
                    ]);
                    wp_die();
                }
            }
            WC()->session->set('cart_type', $shipping_type);
        }
        if(WC()->cart->is_empty()) {
            WC()->session->set('cart_type', $shipping_type);
        }

        $cart = WC()->cart->get_cart();
        if(!empty($cart)) {
            foreach ($cart as $item) {
                if($item['product_id'] == $_POST['product_id']) {
                    WC()->cart->remove_cart_item($item['key']);
                }
            }
        }
        WC()->cart->add_to_cart($_POST['product_id'], $_POST['qty'], $_POST['variation_id']);
        WC_AJAX::get_refreshed_fragments();
        wp_die();
    }
}

add_filter('woocommerce_checkout_fields', 'add_delivery_date_time_fields');

function add_delivery_date_time_fields($fields) {
    $user_id = get_current_user_id();
    WC()->session->set('delivery_type', '');

    $cart_total = WC()->cart->get_totals();
    $option = get_option("peter_timbs");
    // $amount = empty($option['minimum_delivery_amount']) ? 80 : $option['minimum_delivery_amount'];
    // $cart_type = WC()->session->get( 'cart_type' );
    $cart_type = empty($cart_type) ? "both" : $cart_type;

    foreach( WC()->cart->get_cart() as $cart_item ){
        $post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
        $product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);
        if(!empty($product_free_delivery)){
            $free_delivery[] = $product_free_delivery;
        }

    }
    if(!empty($free_delivery)){
        $amount = 0;
        $cart_type = "delivery";
    }else{
        $amount = empty($option['minimum_delivery_amount']) ? 80 : $option['minimum_delivery_amount'];
        $cart_type = WC()->session->get( 'cart_type' );
    }


    if(empty($user_id)) {
        $fields['billing']['loyalty_card_number'] = [
            "label" => "Loyalty Card Number",
            "required" => 0,
            "type" => "text",
            "class" => ["form-row-wide"],
            "priority" => "118",
            "default" => ""
        ];
    }
    if($cart_type == "pick_up" || $cart_type == "both") {
        WC()->session->set('delivery_type', 'pick_up');
        $date = get_earlier_pickup_time();
        $fields['billing']['pickup_date_time'] = [
            "label" => "Pick Up is from our Edgeware Store",
            "required" => 1,
            "type" => "text",
            "class" => ["form-row-wide"],
            "priority" => "120",
            "default" => ""
        ];
        $fields['billing']['delivery_type'] = [
            "label" => "Delivery Type",
            "required" => 1,
            "type" => "radio",
            "class" => ["form-row-wide", "hide"],
            "priority" => "119",
            "options" => [
                "pick_up"  => "Select pick up date",
                "delivery" => "Select delivery date",
            ],
            "default" => "pick_up"
        ];
    }

    if($cart_type == "delivery") {
        if($option['enable_delivery_on_site'] && $cart_total['subtotal'] >= $amount) {
            WC()->session->set('delivery_type', 'delivery');
            $is_under_range = get_user_meta($user_id, "is_under_range", 1);
            $fields['billing']['delivery_date'] = [
                "label" => "Delivery Date",
                "required" => 0,
                "type" => "text",
                "class" => ["form-row-first", "only_delivery_field", "hide"],
                "priority" => "121",
                "default" => ""
            ];
            $fields['billing']['delivery_type'] = [
                "label" => "Delivery Type",
                "required" => 1,
                "type" => "radio",
                "class" => ["form-row-wide","only_delivery_type_radio", "hide"],
                "priority" => "119",
                "options" => [
                    "pick_up"  => "Select pick up date",
                    "delivery" => "Select delivery date",
                ],
                "default" => "delivery"
            ];
            $fields['billing']['pickup_date_time'] = [
                "label" => "Pick Up is from our Edgeware Store",
                "required" => 0,
                "type" => "text",
                "class" => ["form-row-wide", "hide"],
                "priority" => "120",
                "default" => ""
            ];
        } else {
            $fields = ($cart_type == "both") ? $fields : [];
        }
    }
    if($cart_type == "both") {
        $fields['billing']['delivery_type'] = [
            "label" => "Delivery Type",
            "required" => 1,
            "type" => "radio",
            "class" => ["form-row-wide", "delivery_type_radio", "hide"],
            "priority" => "119",
            "options" => [
                "pick_up"  => "Select pick up date",
                "delivery" => "Select delivery date"
            ],
            "default" => ""
        ];

        if($option['enable_delivery_on_site'] && $cart_total['subtotal'] >= $amount) {
            $fields['billing']['delivery_date'] = [
                "label" => "Delivery Date",
                "required" => 0,
                "type" => "text",
                "class" => ["form-row-first", "hide"],
                "priority" => "121",
                "default" => ""
            ];
            $fields['billing']['pickup_date_time']['required'] = 0;
            $fields['billing']['pickup_date_time']['class'] = ["form-row-wide", "hide"];
        } else {
            $fields['billing']['delivery_type']['default'] = "pick_up";
            $fields['billing']['delivery_type']['class'] = ["form-row-wide", "hide"];
        }

    }
    WC()->cart->calculate_fees();
    WC()->cart->calculate_totals();

    // if($option['enable_delivery_on_site'] && $cart_total['subtotal'] >= $amount) {
    //     $user_id = get_current_user_id();
    //     $is_under_range = get_user_meta($user_id, "is_under_range", 1);
    //     WC()->cart->calculate_fees();
    //     WC()->cart->calculate_totals();

    //     $fields['billing']['delivery_date'] = [
    //         "label" => "Delivery Date",
    //         "required" => 1,
    //         "type" => "text",
    //         "class" => ["form-row-first", "hide"],
    //         "priority" => "121",
    //         "default" => ""
    //     ];
    //     // $fields['billing']['pickup_date_time']['required'] = 0;
    //     // $fields['billing']['pickup_date_time']['class'] = ["form-row-wide", "hide"];

    //     if(!$is_under_range) {
    //         // $fields['billing']['delivery_type']['class'] = ["form-row-wide", "delivery_type_radio", "hide"];
    //     }
    // } else {
    //     // $fields = [];
    //     // WC()->session->set( 'delivery_type', 'pick_up');
    //     // WC()->cart->calculate_fees();
    //     // WC()->cart->calculate_totals();
    // }
    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'save_pickup_date_time');
function save_pickup_date_time( $order_id ) {
    $pickup_date = $_POST['pickup_date'];
    if ( ! empty( $pickup_date ) ) {
        update_post_meta( $order_id, 'pickup_date', sanitize_text_field( $pickup_date ) );
    }
    update_post_meta( $order_id, '_is_order', "Web Order" );
}

add_action('woocommerce_after_checkout_billing_form', 'display_pickup_date_info');

function display_pickup_date_info() {
    $prep_time = get_earlier_pickup_time();
    $cart_total = WC()->cart->get_totals();
    // $display_date = date("jS M g:i a", strtotime($prep_time));
    $peter_timbs = get_option("peter_timbs");

    foreach( WC()->cart->get_cart() as $cart_item ){
        $post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
        $product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);
        if(!empty($product_free_delivery)){
            $free_delivery[] = $product_free_delivery;
        }
    }
    if(empty($free_delivery)){
        if(WC()->session->get('cart_type') === 'pick_up' || (WC()->session->get('cart_type') === 'both' && $cart_total['subtotal'] < $peter_timbs['minimum_delivery_amount'])) {
            if(!WC()->session->get('is_meal')) {
                echo "<p style='clear: both; color: #3452ff;'>For the delivery option, orders must be over $".$peter_timbs['minimum_delivery_amount']."</p>";
            }
        }
    }

    // if(WC()->session->get('cart_type') === 'pick_up' || (WC()->session->get('cart_type') === 'both' && $cart_total['subtotal'] < $peter_timbs['minimum_delivery_amount'])) {
    //     if(!WC()->session->get('is_meal')) {
    //         echo "<p style='clear: both; color: #3452ff;'>For the delivery option, orders must be over $".$peter_timbs['minimum_delivery_amount']."</p>";
    //     }
    // }

    echo "<input type='hidden' value='".$prep_time."' id='hidden_prep_time' />";

}
function get_earlier_pickup_time() {
    $cart = WC()->cart->get_cart();
    $total_prep_time = 1440;
    foreach ($cart as $item) {
        if(!empty($item['bb_items'])) {
            $bb_items = unserialize($item['bb_items']);
            foreach ($bb_items as $bbitem) {
                $post_id = empty($bbitem['variation_id']) ? $bbitem['product_id'] : $bbitem['variation_id'];
                $preparation_time = get_post_meta($post_id, "preparation_time", true);
                $total_prep_time = $total_prep_time + ($preparation_time * $bbitem['qty']);
            }
        } else {
            $post_id = empty($item['variation_id']) ? $item['product_id'] : $item['variation_id'];
            $preparation_time = get_post_meta($post_id, "preparation_time", true);
            $total_prep_time = $total_prep_time + ($preparation_time * $item['quantity']);
        }
        if(empty($preparation_time)) {
            continue;
        }
    }
    $prep_time = _calculate_pickup_datetime($total_prep_time);
    return $prep_time;
}

function _calculate_pickup_datetime($total_prep_time) {
    $peter_timbs = get_option("peter_timbs");
    $today = $day = strtolower(date('l'));

    for($i=0; $i<=6; $i++) {
        if($i == 0) {
            $day = strtolower(date('l'));
        } else {
            $day = strtolower(date('l', strtotime("+{$i} day")));
        }
        if(strtolower($peter_timbs[$day."_business_hours"]) == "closed") {
            continue;
        }
        $day_business_hours = explode("|", $peter_timbs[$day."_business_hours"]);
        $day_start = date("Y-m-d ".$day_business_hours[0], strtotime("+{$i} day"));
        $day_end = date("Y-m-d ".$day_business_hours[1], strtotime("+{$i} day"));
        if(strtotime($day_end) < strtotime("now")){
            $day = strtolower(date('l', strtotime("+{$i} day")));
        } else {
            break;
        }
    }

    if(date("H") < 13) {
        $total_prep_time = $total_prep_time + 720;
    }
    if($day == "saturday") {
        $total_prep_time = $total_prep_time + 2880;
    }
    if($day == "sunday") {
        $total_prep_time = $total_prep_time + 2880;
    }
    if($day == "tuesday" && $today != "tuesday") {
        $total_prep_time = $total_prep_time - 720;
    }

    if($today === $day) {
        $prep_time = date("Y-m-d H:i", strtotime("+{$total_prep_time} minutes", strtotime("now")));
    } else {
        $prep_time = date("Y-m-d H:i", strtotime("+{$total_prep_time} minutes", strtotime($day_start)));
    }

    $pick_up_day = strtolower(date("l", strtotime($prep_time)));
    $day_business_hours = explode("|", $peter_timbs[$pick_up_day."_business_hours"]);
    $day_start = date("Y-m-d ".$day_business_hours[0], strtotime($prep_time));
    $day_end = date("Y-m-d ".$day_business_hours[1], strtotime($prep_time));

    if(strtotime($prep_time) > strtotime($day_end)) {
        $next_day = strtolower(date("l", strtotime("+1 day", strtotime($day_start))));
        $day_business_hours = explode("|", $peter_timbs[$next_day."_business_hours"]);
        $day_start = date("Y-m-d ".$day_business_hours[0], strtotime("+1 day", strtotime($day_start)));
        $diff = strtotime($prep_time) - strtotime($day_end);

        $prep_time = date("Y-m-d H:i", $diff + strtotime($day_start));
    }
    $prep_time = date("Y-m-d", strtotime($prep_time));
    return $prep_time;
}

add_action('woocommerce_after_checkout_validation', 'validate_pickup_date', 100, 2);
function validate_pickup_date($fields, $errors) {

    $today = date("d-M-Y");
    $pickup_date_time = date('d-M-Y', strtotime($fields['pickup_date_time']));
    if(WC()->session->get('is_meal')) {
        $time = date('H', strtotime($fields['pickup_date_time']));
        if($today == $pickup_date_time && date("H") > 13) {
            $errors->add("validation", "Meals need to be ordered before 1pm for same day pickup.");
        }elseif($time < 16 || $time > 18 ){
            $errors->add("validation", "Meals are only available for pickup between 4pm - 6pm.");
        }
    }
    else{
        $prep_time = get_earlier_pickup_time();
        $display_date = date("jS M g:i a", strtotime($prep_time));
        // if($fields['delivery_type'] != 'delivery' && date("H") > 13 || $total_prep_time > 13 ){
        //     $errors->add("validation", "Meat need to be ordered before 1pm.");
        // }
        if($fields['delivery_type'] != 'delivery' && strtotime($fields['pickup_date_time']) < strtotime($prep_time)){
            $errors->add("validation", "Select pick up date time after ".$display_date);
        }elseif($fields['delivery_type'] == 'delivery' && empty($fields['delivery_date'])){
            $errors->add("validation", "Select delivery date.");
        }elseif($today == $pickup_date_time) {
            $errors->add("validation", "Select pick up date time after ".$display_date);
        }
    }

    // $prep_time = get_earlier_pickup_time();
    // $display_date = date("jS M g:i a", strtotime($prep_time));
    // if($fields['delivery_type'] != 'delivery' && strtotime($fields['pickup_date_time']) < strtotime($prep_time)){
    //     $errors->add("validation", "Select pick up date time after ".$display_date);
    // }
    // if($fields['delivery_type'] == 'delivery' && empty($fields['delivery_date'])){
    //     $errors->add("validation", "Select delivery date.");
    // }
}

add_action('woocommerce_checkout_create_order', 'before_checkout_create_order', 20, 2);
function before_checkout_create_order( $order, $data ) {
    WC()->session->set( 'delivery_type', '');
    if($data['delivery_type'] == "delivery") {
        $order->update_meta_data( '_order_delivery_date', date("Y-m-d", strtotime($data['delivery_date'])));
    } else {
        $order->update_meta_data( '_order_preparation_time', date("Y-m-d H:i:s", strtotime($data['pickup_date_time'])));
    }
    $order->update_meta_data( '_order_type', $data['delivery_type']);
    $order->update_meta_data('_store_collection','Edgeware');
}

add_filter('woocommerce_admin_reports', 'custom_customer_report');
function custom_customer_report($reports) {
    require_once 'classes/class-wc-report-customer-product-sales.php';
    $sales_report = new WC_Report_Customer_Product_Sales;

    $reports['orders']['reports']['acustomer_product_sales_report'] = [
        'title'       => __( 'Customer Product Sales Report', 'woocommerce' ),
        'description' => '',
        'hide_title'  => true,
        'callback'    => [$sales_report, 'output_report'],
    ];
    ksort($reports['orders']['reports']);
    return $reports;
}

function filter_recipes_from_news($query) {
    $query->set('post_type', 'post');
}


if( !function_exists('nectar_next_post_display') ) {
     function nectar_next_post_display() {
         global $post;
         global $nectar_options;
         $post_header_style            = ( ! empty( $nectar_options['blog_header_type'] ) ) ? $nectar_options['blog_header_type'] : 'default';
         $post_pagination_style        = ( ! empty( $nectar_options['blog_next_post_link_style'] ) ) ? $nectar_options['blog_next_post_link_style'] : 'fullwidth_next_only';
         $post_pagination_style_output = ( $post_pagination_style === 'contained_next_prev' ) ? 'fullwidth_next_prev' : $post_pagination_style;
         $full_width_content_class     = ( $post_pagination_style === 'contained_next_prev' ) ? '' : 'full-width-content';
         $blog_next_post_link_order    = ( ! empty( $nectar_options['blog_next_post_link_order'] ) ) ? $nectar_options['blog_next_post_link_order'] : 'default';
         $next_post = get_previous_post(false, $recipe_cat, true);

         if ( ! empty( $next_post ) && ! empty( $nectar_options['blog_next_post_link'] ) && $nectar_options['blog_next_post_link'] === '1' ||
         $post_pagination_style === 'contained_next_prev' && ! empty( $nectar_options['blog_next_post_link'] ) && $nectar_options['blog_next_post_link'] === '1' ||
         $post_pagination_style === 'fullwidth_next_prev' && ! empty( $nectar_options['blog_next_post_link'] ) && $nectar_options['blog_next_post_link'] === '1' ) { ?>

             <div data-post-header-style="<?php echo esc_attr( $post_header_style ); ?>" class="blog_next_prev_buttons wpb_row vc_row-fluid <?php echo esc_attr( $full_width_content_class ); ?> standard_section" data-style="<?php echo esc_attr( $post_pagination_style_output ); ?>" data-midnight="light">

                <?php
                $recipe_cat_data = get_terms("recipe_cat");
                $recipe_cat = [];
                foreach ($recipe_cat_data as $recipe_cat_data => $r) {
                    $recipe_cat[] = $r->term_id;
                }

                if ( ! empty( $next_post ) ) {
                    $bg       = get_post_meta( $next_post->ID, '_nectar_header_bg', true );
                    $bg_color = get_post_meta( $next_post->ID, '_nectar_header_bg_color', true );
                } else {
                    $bg       = '';
                    $bg_color = '';
                }

                if ( $post_pagination_style == 'fullwidth_next_prev' || $post_pagination_style == 'contained_next_prev' ) {
                    // next & prev
                    if( $blog_next_post_link_order === 'reverse' ) {
                        $previous_post = get_previous_post(false, $recipe_cat);
                        $next_post     = get_next_post(false, $recipe_cat);
                    } else {
                        $previous_post = get_next_post(false, $recipe_cat);
                        $next_post     = get_previous_post(false, $recipe_cat);
                    }
                    $hidden_class = ( empty( $previous_post ) ) ? 'hidden' : null;
                    $only_class   = ( empty( $next_post ) ) ? ' only' : null;
                    echo '<ul class="controls"><li class="previous-post ' . $hidden_class . $only_class . '">';
                    if ( ! empty( $previous_post ) ) {
                        $previous_post_id = $previous_post->ID;
                        $bg               = get_post_meta( $previous_post_id, '_nectar_header_bg', true );
                        if ( ! empty( $bg ) ) {
                            // page header
                            echo '<div class="post-bg-img" style="background-image: url(' . $bg . ');"></div>';
                        } elseif ( has_post_thumbnail( $previous_post_id ) ) {
                            // featured image
                            $post_thumbnail_id  = get_post_thumbnail_id( $previous_post_id );
                            $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
                            echo '<div class="post-bg-img" style="background-image: url(' . esc_url( $post_thumbnail_url ) . ');"></div>';
                        }
                        echo '<a href="' . esc_url( get_permalink( $previous_post_id ) ) . '"></a><h3><span>' . esc_html__( 'Previous Post', 'salient' ) . '</span><span class="text">' . wp_kses_post( $previous_post->post_title ) . '
                        <svg class="next-arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 39 12"><line class="top" x1="23" y1="-0.5" x2="29.5" y2="6.5" stroke="#ffffff;"></line><line class="bottom" x1="23" y1="12.5" x2="29.5" y2="5.5" stroke="#ffffff;"></line></svg><span class="line"></span></span></h3>';
                    }
                    echo '</li>';
                    $hidden_class = ( empty( $next_post ) ) ? 'hidden' : null;
                    $only_class   = ( empty( $previous_post ) ) ? ' only' : null;
                    echo '<li class="next-post ' . $hidden_class . $only_class . '">';

                    if ( ! empty( $next_post ) ) {
                        $next_post_id = $next_post->ID;
                        $bg           = get_post_meta( $next_post_id, '_nectar_header_bg', true );

                        if ( ! empty( $bg ) ) {
                            // page header
                            echo '<div class="post-bg-img" style="background-image: url(' . $bg . ');"></div>';
                        } elseif ( has_post_thumbnail( $next_post_id ) ) {
                            // featured image
                            $post_thumbnail_id  = get_post_thumbnail_id( $next_post_id );
                            $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
                            echo '<div class="post-bg-img" style="background-image: url(' . esc_url( $post_thumbnail_url ) . ');"></div>';
                        }
                        echo '<a href="' . esc_url( get_permalink( $next_post_id ) ) . '"></a><h3><span>' . esc_html__( 'Next Post', 'salient' ) . '</span><span class="text">' . wp_kses_post( $next_post->post_title ) . '

                        <svg class="next-arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 39 12"><line class="top" x1="23" y1="-0.5" x2="29.5" y2="6.5" stroke="#ffffff;"></line><line class="bottom" x1="23" y1="12.5" x2="29.5" y2="5.5" stroke="#ffffff;"></line></svg><span class="line"></span></span></h3>';
                    }
                    echo '</li></ul>';
                } else {
                    // next only
                    if ( ! empty( $bg ) ) {
                        // page header
                        echo '<div class="post-bg-img" style="background-image: url(' . esc_url( $bg ) . ');"></div>';
                    } elseif ( has_post_thumbnail( $next_post->ID ) ) {
                        // featured image
                        $post_thumbnail_id  = get_post_thumbnail_id( $next_post->ID );
                        $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
                        echo '<div class="post-bg-img" style="background-image: url(' . esc_url( $post_thumbnail_url ) . ');"></div>';
                    }
                    ?>
                    <div class="col span_12 dark left">
                        <div class="inner">
                            <?php
                            if( $blog_next_post_link_order === 'reverse' ) {
                                echo '<span><i>' . esc_html__( 'Previous Post', 'salient' ) . '</i></span>';
                            } else {
                                echo '<span><i>' . esc_html__( 'Next Post', 'salient' ) . '</i></span>';
                            }
                            previous_post_link( '%link', '<h3>%title</h3>' ); ?>
                         </div>
                     </div>
                     <span class="bg-overlay"></span>
                     <span class="full-link"><?php previous_post_link( '%link' ); ?></span>
                 <?php } ?>
             </div>
        <?php
        }
    }
}

add_action( 'post_updated', 'update_recipe_no_of_ingredients', 10, 3 );
function update_recipe_no_of_ingredients($post_id, $post_after, $post_before) {
    if($post_after->post_type === "recipe") {
        $no = count(get_post_meta($post_id, "recipe_ingredients", true));
        update_post_meta($post_id, "no_of_ingredients", $no);
    }
}

if ( ! function_exists( 'nectar_change_wp_search_size' ) ) {
    function nectar_change_wp_search_size( $query ) {
        if ( $query->is_search ) {
            $ids = get_hidden_product_ids();
            $post_type = get_query_var('post_type') === 'product' ? get_query_var('post_type') : ['post', 'page', 'recipe', 'product'];
            $query->query_vars['posts_per_page'] = 12;
            $query->query_vars['post_type'] = $post_type;
            $query->query_vars['post__not_in'] = $ids;
        }
        return $query;
    }
}

add_filter('woocommerce_get_children', 'peter_timbs_filters');
function peter_timbs_filters($args) {
    $variations = [];
    foreach ($args as $key => $variation_id) {
        $bool = get_post_meta($variation_id, "_hidden_on_site", true);
        if($bool == 0)
            $variations[] = $variation_id;
    }
    return $variations;
}

add_action('woocommerce_product_query', 'petertimbs_site_product_filter');
function petertimbs_site_product_filter($q) {
    if( is_shop() || is_product_category()) {
        $meta_query[] = [
            'key' => '_hidden_on_site',
            'value' => '1',
            'compare' => '!='
        ];
        $q->set( 'meta_query', $meta_query );
    }
    return $q;
}

add_action( 'woocommerce_email', 'unhook_those_pesky_emails' );

function unhook_those_pesky_emails( $email_class ) {
    if (empty( $_POST['wpo_wcpdf_send_emails'])) {
        remove_action('woocommerce_order_status_completed_notification', [$email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger'], 10, 2);
    }
}

function get_hidden_product_ids($hide_selected_product = true) {
    global $wpdb;
    $sql = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_hidden_on_site' AND meta_value = '1'";
    $data = $wpdb->get_results($sql, ARRAY_A);
    $ids = [];
    foreach ($data as $d) {
        $ids[] = $d['post_id'];
    }

    $today = strtotime('now');

    if($hide_selected_product) {
        $sql_date = "SELECT p.ID, pm1.meta_value as from_date, pm2.meta_value as to_date FROM {$wpdb->prefix}posts as p
                INNER JOIN {$wpdb->prefix}postmeta as pm1
                    ON p.ID = pm1.post_id and pm1.meta_key = 'date_product_from' and pm1.meta_key = 'date_product_from' != null
                INNER JOIN {$wpdb->prefix}postmeta as pm2
                    ON p.ID = pm2.post_id and pm2.meta_key = 'date_product_to' and pm2.meta_key = 'date_product_to' != null";

        $result = $wpdb->get_results($sql_date, ARRAY_A);

        foreach ($result as $r) {
            $start_date = strtotime(date("Y-m-d 00:00:00", strtotime($r['from_date'])));
            $end_date = strtotime(date("Y-m-d 23:59:59", strtotime($r['to_date'])));
            if ($today <= $end_date && $today >= $start_date) {
                continue;
            }
            else{
                $ids[] = $r['ID'];
            }
        }
    }
    return $ids;
}

add_action( 'woocommerce_email_order_meta', 'add_pick_up_date', 10, 3 );
function add_pick_up_date($order, $sent_to_admin, $plain_text) {
    $preparation_time = get_post_meta($order->get_id(), '_order_preparation_time', true);
    $date = date('d-M-Y', strtotime($preparation_time));
    $time = date('h:i a', strtotime($preparation_time));

    $delivery_date = get_post_meta($order->get_id(), '_order_delivery_date', true);
    $delivery_date = date('d-M-Y', strtotime($delivery_date));
    $type = get_post_meta($order->get_id(), '_order_type', true);
    if ( $plain_text === false ) {
        if($type == "delivery") {
            echo "<div>Delivery of your order will be on {$delivery_date}</div><br>";
        } else {
            echo '<div>Pick Up is on '.$date.' at '.$time.' from our Edgeware Store (70 Edgeware Road, St Albans, Christchurch 8014, New Zealand)</div><br>';
        }
        echo '<div>GST Number: 048866476</div><br>';
    }
}

add_filter('manage_edit-shop_order_columns', 'add_pick_up_date_column', 9999);
function add_pick_up_date_column($columns) {
    $columns = array(
        "cb" => '<input type="checkbox" />',
        "order_number" => 'Order',
        "order_date" => 'Order Create Date',
        "order_status" => 'Status',
        "billing_address" => 'Billing',
        "shipping_address" => 'Ship to',
        "_date" => 'Date',
        "order_total" => 'Total',
        "wc_actions" => 'Actions',
    );
    return $columns;
}

add_action('manage_shop_order_posts_custom_column', "edit_shop_order_columns_data", 10, 2);
function edit_shop_order_columns_data($column, $post_id) {
        $type = get_post_meta($post_id, "_order_type", true);
        $pickup_date = get_post_meta($post_id, "_order_preparation_time", true);
        $delivery_date = get_post_meta($post_id, "_order_delivery_date", true);

        switch ($column) {
            case '_date' :
                if($type == "delivery") {
                    echo "Delivery date: ". date('d-m-Y', strtotime($delivery_date));
                } else {
                    echo "Pick up date time: ". date('d-m-Y h:i a', strtotime($pickup_date));
                }
            break;
        }
}

add_filter( 'manage_edit-shop_order_sortable_columns', 'set_shop_order_sortable_columns' );
function set_shop_order_sortable_columns($columns) {
    $columns['preparation_time'] = 'preparation_time';
    return $columns;
}

function profile_updated_on_order( $the_query ) {
    if( ! is_admin() )
        return;

    $order_by = $the_query->get( 'orderby');

    if( 'preparation_time' == $order_by ) {
        $the_query->set('meta_key','_order_preparation_time');
        $the_query->set('meta_value_num',time());
        $the_query->set('orderby','_order_preparation_time');
    }
}
add_action( 'pre_get_posts', 'profile_updated_on_order' );

add_action( 'woocommerce_admin_order_data_after_order_details', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    $type = get_post_meta($order->get_id(), "_order_type", true);
    if($type == "delivery") {
        $date = get_post_meta($order->get_id(), "_order_delivery_date", true);
        $date = date('d-m-Y', strtotime($date));
        echo '<div style="margin-top: 20px; display: inline-block;"><strong>'.__('Delivery Date').':</strong> <br/>' .$date. '</div>';
    } else {
        $date = get_post_meta($order->get_id(), "_order_preparation_time", true);
        $date = date('d-m-Y h:i a', strtotime($date));
        echo '<div style="margin-top: 20px; display: inline-block;"><strong>'.__('Pick up Date/Time').':</strong> <br/>' .$date. '</div>';
    }
}

function unhide_app() {
    $args = [
        "post_type" => "product",
        "posts_per_page" => -1,
    ];
    $data = new WP_Query($args);
    foreach ($data->posts as $p) {
        update_post_meta($p->ID, "_hidden_on_app", 0);
    }
}
// unhide_app();

add_action('woocommerce_created_customer', 'check_customer_address', 10, 3);
function check_customer_address($customer_id, $new_customer_data, $password_generated) {
    if($_POST['woocommerce-register-nonce'] && $_POST['register']) {
        update_user_meta($customer_id, "billing_first_name", $_POST['first_name']);
        update_user_meta($customer_id, "billing_last_name", $_POST['last_name']);
        update_user_meta($customer_id, "billing_company", $_POST['billing_company']);
        update_user_meta($customer_id, "billing_phone", $_POST['billing_phone']);
        update_user_meta($customer_id, "billing_email", $_POST['email']);
        update_user_meta($customer_id, "first_name", $_POST['first_name']);
        update_user_meta($customer_id, "last_name", $_POST['last_name']);

        update_user_meta($customer_id, "billing_address_1", $_POST['billing_address_1']);
        update_user_meta($customer_id, "billing_address_2", $_POST['billing_address_2']);
        update_user_meta($customer_id, "billing_city", $_POST['billing_city']);
        update_user_meta($customer_id, "billing_state", $_POST['billing_state']);
        update_user_meta($customer_id, "billing_country", "NZ");
        update_user_meta($customer_id, "billing_postcode", $_POST['billing_postcode']);
        update_user_meta($customer_id, "is_under_range", $_POST['is_under_range']);
        update_user_meta($customer_id, "loyalty_card_number", $_POST['loyalty_card_number']);
    }
}
add_action( 'profile_update', 'update_user_custom_meta');
function update_user_custom_meta($user_id) {
    $loyalty_card_number = empty($_POST['loyalty_card_number']) ? $_POST['billing']['loyalty_card_number'] : $_POST['loyalty_card_number'];
    update_user_meta($user_id, "loyalty_card_number", $loyalty_card_number);
}
function measure_distance_from_store($dest) {
    $option = get_option("peter_timbs");
    $origin = "70+Edgeware+Rd+Edgeware+Christchurch+NZ";
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$dest}&mode=driving&key=AIzaSyAYyxEGB5J4cZGFf9TWu4N0gy0KQDWtFeI&unit=metric";
    $response = file_get_contents($url);
    $response = json_decode($response, true);
    $is_local = false;
    if($response['status'] == "OK") {
        $distance = $response['rows'][0]['elements'][0]['distance']['value'];
        $distance = $distance/1000;
        if($distance <= $option['delivery_distance']) {
            $is_local = true;
        }
    }
    return $is_local;
}

function set_user_range() {
    $users = get_users();
    foreach($users as $user) {
        $address_1 = get_user_meta($user->ID, "billing_address_1", 1);
        $address_2 = get_user_meta($user->ID, "billing_address_2", 1);
        $city = get_user_meta($user->ID, "billing_city", 1);
        $postcode = get_user_meta($user->ID, "billing_postcode", 1);
        if(empty($postcode) || empty($city)  || empty($address_1)) {
            update_user_meta($user->ID, 'is_under_range', 0);
        } else {
            $dest = $address_1;
            $dest .= empty($address_2) ? "+".$address_2 : "";
            $dest .= "+".$city."+".$postcode."+NZ";
            $dest = str_replace(", ", "+", $dest);
            $dest = str_replace(" ", "+", $dest);
            $dest = str_replace("++", "+", $dest);
            $is_local = measure_distance_from_store($dest);
        }
    }
}

add_action("wp_footer", "add_delivery_icon", 9999);
function add_delivery_icon() {
    global $wp;
    if(empty($wp->request)) {
        echo '<div id="slideout">
                <div id="slideout_inner">Apply for a delivery account</div>
                <a href="/my-account"><img src="'.site_url().'/wp-content/themes/salient-child/img/truck.png" alt="Feedback"></a>
            </div>';
    }
}

function get_deliery_cut_off_dates() {
    $option = get_option("peter_timbs");
    global $wpdb;
    $sql = "select COUNT(ID) as count, pm.meta_value as date from wp_posts as p
                INNER JOIN wp_postmeta as pm
                    ON p.ID = pm.post_id AND pm.meta_key = '_order_delivery_date'
                WHERE p.post_type = 'shop_order' AND pm.meta_value >= CURDATE()  GROUP BY pm.meta_value";
    $orders = $wpdb->get_results($sql, ARRAY_A);
    $dates = ["2025/01/02", "2025/02/06", "2025/03/29", "2025/04/01", "2025/06/03", "2025/06/28", "2025/10/28", "2025/11/15", "2025/12/25", "2025/12/26", "2025/12/23", "2025/12/24", "2025/12/27"];
    foreach ($orders as $o) {
        $day = strtolower(date("l", strtotime($o['date'])));
        $cut_off = $option[$day."_delivery_cut_off"];
        if($o['count'] >= $cut_off) {
            $dates[] = date("Y/m/d", strtotime($o['date']));
        }
    }
    return $dates;
}

add_action('woocommerce_add_to_cart_validation','check_meal', 10, 4);

function check_meal( $passed, $product_id, $quantity, $variation_id = '', $variations = '') {
    // $current_product_cat = has_term('meals', 'product_cat', $product_id);
    $current_product_cat = has_term(['meals','merchandise'], 'product_cat', $product_id);
    $check_is_meal = has_term('meals', 'product_cat', $product_id);
    $check_is_merchandise = has_term('merchandise', 'product_cat', $product_id);

    if(WC()->cart->is_empty()) {
        if($current_product_cat) {
            if($check_is_meal){
                WC()->session->set('is_meal', $product_id);
            }else{
                WC()->session->set('is_merchandise', $product_id);
            }
            // WC()->session->set('is_meal', $product_id);
        }
        return $passed;
    }

    if(!WC()->cart->is_empty()) {
        $counter = 0;
        $is_meal_in_cart = false;
        if($current_product_cat) {
            if($check_is_meal){
                WC()->session->set('is_meal', $product_id);
            }else{
                WC()->session->set('is_merchandise', $product_id);
            }
            // WC()->session->set('is_meal', $product_id);
        }
        foreach(WC()->cart->get_cart() as $cart_items_key => $cart_items) {
            $item_id = $cart_items['product_id'];
            $item_cat = has_term('meals', 'product_cat', $item_id);
            $item_cat_is_merchandise = has_term('merchandise', 'product_cat', $item_id);

            if($current_product_cat) {
                if($check_is_meal == $item_cat && $check_is_merchandise == false){
                    if ($check_is_meal == false && $item_cat == false && $item_cat_is_merchandise == false) {
                        return $passed;
                    }
                    else
                    {
                        if ($item_id == $product_id && $check_is_merchandise == false) {
                            WC()->session->set('is_meal', $product_id);
                            return $passed;
                        }
                        if($item_id == $product_id && $check_is_merchandise == true){
                            WC()->session->set('is_merchandise', $product_id);
                            return $passed;
                        }else{
                            $passed = false;
                            wc_add_notice(sprintf(__( 'Merchandise cannot be purchased with other items', 'error' )));
                            return $passed;
                        }

                    }
                }elseif($check_is_meal == true && $item_cat_is_merchandise == false)
                {
                    return $passed;
                }else{
                    if ($check_is_merchandise == true && $item_cat_is_merchandise == true){
                        WC()->session->set('is_merchandise', $product_id);
                        return $passed;
                    }else{
                        $passed = false;
                        wc_add_notice(sprintf(__( 'Merchandise cannot be purchased with other items', 'error' )));
                        return $passed;
                    }
                }

            } else {

                if ($check_is_merchandise == true && $item_cat_is_merchandise == true){
                    return $passed;
                }else{
                    if($item_cat_is_merchandise == true && $check_is_merchandise == false){
                        $passed = false;
                        wc_add_notice(sprintf(__( 'Merchandise cannot be purchased with other items', 'error' )));
                        return $passed;
                    }else{
                        return $passed;
                    }
                }
            }
        }
    }
}

add_action( 'woocommerce_cart_reset', 'cart_reset', 10, 2 );
function cart_reset( $that, $false ){
    $check_meal = false;
    foreach(WC()->cart->get_cart() as $cart_items_key => $cart_items) {
        $item_id = $cart_items['product_id'];
        if($check_meal == false){
            $check_meal = has_term('meals', 'product_cat', $item_id);
        }
        if($check_meal) {
            WC()->session->set('is_meal', $item_id);
            $shipping_type = "pick_up";
            WC()->session->set('cart_type', $shipping_type);
        }else{
            WC()->session->__unset('is_meal');
            $shipping_type = get_post_meta($item_id, "_shipping_type", 1);
            $shipping_type = empty($shipping_type) ? "both" : $shipping_type;
            WC()->session->set('cart_type', $shipping_type);
        }
    }

}

add_action( 'init', 'create_tag_taxonomies', 0 );
function create_tag_taxonomies()
{
    $labels = array(
        'name' => _x( 'Meal Tags', 'taxonomy general name' ),
        'singular_name' => _x( 'Meal Tags', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Meal Tags' ),
        'popular_items' => __( 'Popular Meal Tags' ),
        'all_items' => __( 'All Meal Tags' ),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __( 'Edit Meal Tags' ),
        'update_item' => __( 'Update Meal Tags' ),
        'add_new_item' => __( 'Add New Meal Tags' ),
        'new_item_name' => __( 'New Meal Tags Name' ),
        'separate_items_with_commas' => __( 'Separate meal tags with commas' ),
        'add_or_remove_items' => __( 'Add or remove meal tags' ),
        'choose_from_most_used' => __( 'Choose from the most used meal tags' ),
        'menu_name' => __( 'Meal Tags' ),
    );
    register_taxonomy('meals_tag','product',array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array( 'slug' => 'meals_tag' ),
    ));
}

function add_pet_info($pet_info) {
    global $post;
    $tags = get_the_terms($post->ID, 'meals_tag');
    if(!empty($tags)){
        echo '<span class="tagged_as">Tag:';
        foreach($tags as $key=>$tag){
            $list[] = '<a href="'.site_url().'/meals_tag/'.$tag->slug.'" rel="tag">'.$tag->name.'</a>';
        }
        echo implode(', ', $list);
        echo '</span>';
    }
}
add_action('woocommerce_product_meta_start','add_pet_info' );

function return_to_peter_timbs_meats_menu_show() {
    global $post;
$ignore = ["meals", "butchers-box", "deli-products", "grazing-boxes", "pies", "ready-to-eat-meals", "ready-to-heat-meals", "salads"];
$terms_slug = get_queried_object()->slug;
$taxonomy_slug = get_query_var( 'taxonomy' );
if ( is_shop())
    {
        ?>
        <style type="text/css">
            .material[data-button-style*="rounded"] .widget .tagcloud a, .material[data-button-style*="rounded"] #sidebar .widget .tagcloud a{
                width: 100%;
            }
        </style>
        <?php
    }
if ( is_page( 'butchers-kitchen' ) ) {
?>
  <style>
        nav ul {
            flex-flow: row-reverse;
        }
        nav ul .menu-item {
            display:none !important;
        }
        nav ul #menu-item-21119,
        nav ul #menu-item-5845{
            display:flex !important;
        }
        .menu{
            flex-direction: column-reverse;
            display: flex;
        }
        .menu .menu-item{
            display:none !important;
        }
        .menu .menu-item-21119,
        .menu .menu-item-5845 {
            display: flex !important;
        }
        #page-header-bg .span_6 h1{
            font-size: 70px;
        }


  </style>
 <?php
 }elseif( in_array( $terms_slug, $ignore ) || $taxonomy_slug == 'meals_tag' ){
    ?>
    <style>
        nav ul {
            flex-flow: row-reverse;
        }
        nav ul .menu-item {
            display:none !important;
        }
        nav ul #menu-item-21119,
        nav ul #menu-item-5845{
            display:flex !important;
        }
        .menu{
            flex-direction: column-reverse;
            display: flex;
        }
        .menu .menu-item{
            display:none !important;
        }
        .menu .menu-item-21119,
        .menu .menu-item-5845 {
            display: flex !important;
        }
        #page-header-bg .span_6 h1{
            font-size: 70px;
        }
        #sidebar .widget_product_categories,.widget_product_tag_cloud,.widget_product_search{
            display: none;
        }
        #sidebar .no-widget-title{
            display: block;
        }

    </style>
    <?php
 }else{
    ?>
        <style type="text/css">
            nav ul #menu-item-21120,
            nav ul #menu-item-21119,
            nav ul #menu-item-5845{
                display: none !important;
            }
            .menu .menu-item-21120,
            .menu .menu-item-21119,
            .menu .menu-item-5845 {
                display: none !important;
            }
        </style>
    <?php
 }
 if( is_product() ){
        $check_terms = get_the_terms ( $post->ID, 'product_cat' );
        $butchers_kitchen = false;
        foreach($check_terms as $term){
            if(in_array( $term->slug, $ignore )){
                $butchers_kitchen = true;
            }
        }
        if($butchers_kitchen == true){
        ?>
            <style>
        nav ul {
            flex-flow: row-reverse;
        }
        nav ul .menu-item {
            display:none !important;
        }
        nav ul #menu-item-21119,
        nav ul #menu-item-5845{
            display:flex !important;
        }
        .menu{
            flex-direction: column-reverse;
            display: flex;
        }
        .menu .menu-item{
            display:none !important;
        }
        .menu .menu-item-21119,
        .menu .menu-item-5845 {
            display: flex !important;
        }
        #page-header-bg .span_6 h1{
            font-size: 70px;
        }
        #sidebar .widget_product_categories,.widget_product_tag_cloud,.widget_product_search{
            display: none;
        }
        #sidebar .no-widget-title{
            display: block;
        }

    </style>
        <?php
        }

    }
}
add_action('wp_head', 'return_to_peter_timbs_meats_menu_show');

// afterpay js issue solve
add_filter( 'clean_url', 'script_attributes', 11, 1 );

if( !function_exists('script_attributes') )
{
    function script_attributes( $url )
    {
        if($url == "https://js.squarecdn.com/square-marketplace.js' charset='utf8")
        {
            $url = str_replace("' charset='utf8","","https://js.squarecdn.com/square-marketplace.js' charset='utf8");
            return $url;
        }
        return $url;
    }
}





//Test Product Code


// add_action( 'woocommerce_checkout_update_order_meta', 'bbloomer_save_new_checkout_field' );
// function bbloomer_save_new_checkout_field( $order_id ) {
//  $order = wc_get_order($order_id);
//  $total_points = 0;
//  foreach ($order->get_items() as $item_id => $items) {
//         $product_id = empty($items->get_variation_id()) ? $items->get_product_id() : $items->get_variation_id();
//      $point = get_post_meta($product_id, "_atria_points", 1);
//      $total_points = $total_points + ($point*$items->get_quantity());
//  }
//     if ( $_POST['final_delivery'] ) update_post_meta( $order_id, '_final_delivery', esc_attr( $_POST['final_delivery'] ) );
//    // _dd($total_points);
// }


//Edit User Profile Add Checkbox
