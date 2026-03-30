<?php
/**
 * Auth Class.
 *
 * @link       https://FeatherTechlabs.com
 * @since      1.0.0
 *
 * @package    Petertimbs
 * @subpackage Petertimbs/public
 * @author     FeatherTechlabs <dev@FeatherTechlabs.com>
 */
use \Firebase\JWT\JWT;

class Petertimbs_General {

    /* Constructor */
    private $user;

    public function __construct() {
        if( !defined("SHORTINIT")) {
            define( 'SHORTINIT', true );
        }
    }

    public function home($request) {
        global $wpdb;

        $this->user = pt_validate_request($request);
        $page = empty($request->get_param('page')) ? 1 : $request->get_param('page');
        $limit = empty($request->get_param('limit')) ? 10 : $request->get_param('limit');
        $type = $request->get_param('type');
        $user_id = $this->user->user_id;

        $unseen = $wpdb->get_var("SELECT count(id) FROM `{$wpdb->prefix}notification` WHERE is_seen = 0 AND is_sent = 1 AND user_id = $user_id ");

        $response['success'] = true;
        $response['notification_count'] = $unseen;
        if($type == 'product' || empty($type)) {
            $sql = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_hidden_on_app' AND meta_value = '1'";
            $hidden_products_ids = $wpdb->get_results($sql, ARRAY_A);
            $hidden_products_ids = empty($hidden_products_ids) ? [] : array_column($hidden_products_ids, 'post_id');

            $args = [
                "status"  => "publish",
                "limit"	  => $limit,
                // "orderby" => "ID",
                // "order"	  => "DESC",
                "order" => "ASC",
                "orderby"   => "menu_order",
                "page"    => $page,
                "paginate" => true,
                "exclude" => $hidden_products_ids,
            ];
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'meals',
                'operator' => 'NOT IN',
            ];

            $data = wc_get_products( $args );
            $response['products'] = [];
            $response['total_products'] = $data->total;
            $response['product_pages'] = $data->max_num_pages;
            foreach($data->products as $d) {
                $price = html_entity_decode(strip_tags($d->get_price_html()));
                // $price = empty($d->get_regular_price()) ? html_entity_decode(get_woocommerce_currency_symbol().$d->get_variation_regular_price()) : html_entity_decode(get_woocommerce_currency_symbol().$d->get_regular_price());
                $sale_price = empty($d->get_sale_price()) ? "" : html_entity_decode(get_woocommerce_currency_symbol().$d->get_sale_price());
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $d->get_id() ), 'woocommerce_single' );
                $image = empty($image) ? "" : $image[0];
                $response['products'][] = [
                    "ID"   => $d->get_ID(),
                    "name" => $d->get_title(),
                    "price" => $price,
                    "sale_price" => $sale_price,
                    "image" => $image,
                    "stock_status" => $d->get_stock_status(),
                ];
            }
        }

        if($type == 'recipe' || empty($type)) {
            $args = [
                "post_type" => "recipe",
                "post_status" => "publish",
                "posts_per_page" => $limit,
                "order"     => "DESC",
                "orderby"   => "ID",
                "paging"    => true,
            ];
            $recipes = new WP_Query($args);
            $response['recipes'] = [];
            $response['total_recipes'] = $recipes->found_posts;
            $response['recipe_pages'] = $recipes->max_num_pages;
            foreach ($recipes->posts as $r) {
                $image = wp_get_attachment_url(get_post_thumbnail_id($r->ID));
                $difficulty = get_the_terms($r->ID, "difficulty");
                $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;
                $response['recipes'][] = [
                    "ID" => $r->ID,
                    "image" => $image,
                    "name" => $r->post_title,
                    "ratings" => get_post_meta($r->ID, "recipe_user_ratings_rating", true),
                    "serving_size" => get_post_meta($r->ID, "recipe_servings", true),
                    "cook_time" => get_post_meta($r->ID, "recipe_cook_time", true),
                    "difficulty" => $difficulty
                ];
            }
        }

        if($type == 'meals' || empty($type)) {
            $args = [
                "status"  => "publish",
                "limit"	  => $limit,
                // "orderby" => "ID",
                // "order"	  => "DESC",
                "order" => "ASC",
                "orderby"   => "menu_order",
                "page"    => $page,
                "paginate" => true
            ];
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'meals',
                'operator' => 'IN',
            ];

            $args['meta_query']['relation'] = 'AND';
            add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [$this, 'handle_custom_query_var'], 10, 2 );

            $data = wc_get_products( $args );
            $response['meals'] = [];
            $response['total_meals'] = $data->total;
            $response['meal_pages'] = $data->max_num_pages;
            foreach($data->products as $d) {
                $price = html_entity_decode(strip_tags($d->get_price_html()));
                // $price = empty($d->get_regular_price()) ? html_entity_decode(get_woocommerce_currency_symbol().$d->get_variation_regular_price()) : html_entity_decode(get_woocommerce_currency_symbol().$d->get_regular_price());
                $sale_price = empty($d->get_sale_price()) ? "" : html_entity_decode(get_woocommerce_currency_symbol().$d->get_sale_price());
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $d->get_id() ), 'woocommerce_single' );
                $image = empty($image) ? "" : $image[0];
                $response['meals'][] = [
                    "ID"   => $d->get_ID(),
                    "name" => $d->get_title(),
                    "price" => $price,
                    "sale_price" => $sale_price,
                    "image" => $image,
                    "stock_status" => $d->get_stock_status(),
                ];
            }
        }
        wp_send_json($response, 200);
        wp_die();
    }

    public function view_cart($request) {

        $this->user = pt_validate_request($request);

        $options = get_option("peter_timbs");

        $cart = $request->get_param('cart');

        $full_address = $request->get_param('full_address');
        if(!empty($full_address)) {
            update_user_meta($this->user->user_id, "full_address", $full_address);
        }

        $postcode = _get_address_postcode($full_address);

        // echo $postcode;
        // die;
        // 17-05-22 zone postcode fee add start
        // $postcode = get_user_meta( $this->user->user_id, "billing_postcode", true );
        // 17-05-22 zone postcode fee add end

        $response['success'] = true;
        $response['cart'] = [];

        $response['delivery_total'] = $response['pickup_total'] = 0;


        $total_prep_time = 0;
        $total = 0;
        $cart_shipping_type = "both";
        $is_meal = false;
        $is_merchandise = false;
        $meal_id = 0;
        $merchandise_id = 0;

        $current_product_cat;
        foreach ($cart as $item) {
            $is_meal = has_term( 'meals', 'product_cat', $item['product_id']);
            $is_merchandise = has_term( 'merchandise', 'product_cat', $item['product_id']);
            if(empty($current_product_cat)){
                if($is_meal) {
                    $current_product_cat = "true";
                }elseif($is_merchandise){
                    $current_product_cat = "true";
                }else{
                    $current_product_cat = "false";
                }
            }
            
            if($is_meal) {
                $meal_id = $item['product_id'];
                break;
            }
            if($is_merchandise) {
                $merchandise_id = $item['product_id'];
                break;
            }
            $product_id = empty($item['variation_id']) ? $item['product_id'] : $item['variation_id'];
            $shipping_type = get_post_meta($product_id, "_shipping_type", 1);
            $shipping_type = empty($shipping_type) ? "both" : $shipping_type;
            if($shipping_type != 'both') {
                $cart_shipping_type = $shipping_type;
                break;
            }

            $product_free_delivery = get_post_meta($product_id, "product_free_delivery", true);
            if(!empty($product_free_delivery)){
                $product_free_delivery_check = true;
            }

        }
        $response['product_free_delivery'] = $product_free_delivery_check != '' ? true : false;

        if($product_free_delivery_check){
            $cart_shipping_type = 'delivery';
        }else{
            $cart_shipping_type = $cart_shipping_type;
        }


        $response['shipping_type'] = $cart_shipping_type;
        $response['max_meal_pickup_time'] = "16:00|18:00";
        foreach ($cart as $item) {
            $item_cat = has_term('meals', 'product_cat', $item['product_id']);
            $item_cat = $item_cat == 1 ? 'true' : 'false' ; 
            $item_type = get_post_meta($item['product_id'], "_shipping_type", 1);
            $item_type = empty($item_type) ? "both" : $item_type;
            if($cart_shipping_type != "both") {
                if($cart_shipping_type != $item_type && $item_type == "delivery") {
                    $status = 200;
                    $response = [
                        "success" => false,
                        "message" => "Sorry, only specific items are available for pickup and only these items can be included in the same order."
                    ];
                    wp_send_json($response, $status);
                    wp_die();
                }
                if($cart_shipping_type != $item_type && $item_type == "pick_up") {
                    $status = 200;
                    $response = [
                        "success" => false,
                        "message" => "Sorry, only specific items are available for delivery and only these items can be included in the same order."
                    ];
                    wp_send_json($response, $status);
                    wp_die();
                }   
            }
            // if($current_product_cat != $item_cat) {
            //     if($current_product_cat == 'true') {
            //         $status = 200;
            //         $response = [
            //             "success" => false,
            //             "message" => "You currently have a meal (or meals) in your cart, meals cannot be purchased with other items."
            //         ];
            //     } else {
            //         $status = 200;
            //         $response = [
            //             "success" => false,
            //             "message" => "Sorry, meals cannot be purchased with other items and need to be ordered separately."
            //         ];
            //     }
            //     wp_send_json($response, $status);
            //     wp_die();
            // }

            $post_id = empty($item['variation_id']) ? $item['product_id'] : $item['variation_id'];
            $preparation_time = get_post_meta($post_id, "preparation_time", true);
            if(!empty($preparation_time)) {
                $total_prep_time = $total_prep_time + ($preparation_time * $item['qty']);
            }
            $product = wc_get_product(empty($item['variation_id']) ? $item['product_id'] : $item['variation_id']);
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $item['product_id'] ), 'woocommerce_single' );
            $image = empty($image) ? "" : $image[0];

            $total = $total + ($product->get_price()*$item['qty']);

            $attr = "";
            if($product->get_type() == "variation") {
                $attr = array_values($product->get_attributes());
                $attr = implode(", ", $attr);
            }

            $response['cart'][] = [
                "ID"   => $item['product_id'],
                "name" => $product->get_title(),
                "type" => $product->get_type(),
                "price" => "$".$product->get_price(),
                "image" => $image,
                "description" => $attr,
                "total_price"  => "$".sprintf('%0.2f', $product->get_price()*$item['qty']),
                "quantity" => $item['qty']

            ];
        }
        $response['minimum_delivery_amount'] = $options['minimum_delivery_amount'];
        $response['available_delivery'] = $deliery_cut_off_dates = $postcode_days = [];

        if($product_free_delivery_check){
            $response['available_delivery'] = ['delivery'];
            $deliery_cut_off_dates = get_deliery_cut_off_dates();
            $postcode_days = _check_specific_delivery_postcodes($postcode);
        }else{
            if($total < $options['minimum_delivery_amount'] || $is_meal) {
                $response['available_delivery'] = ['pick_up'];
                $response['shipping_type'] = "pick_up";
            } else {
                if($cart_shipping_type == 'both') {
                    $response['available_delivery'] = ['delivery', 'pick_up'];
                } else {
                    $response['available_delivery'] = [$shipping_type];
                }
                $deliery_cut_off_dates = get_deliery_cut_off_dates();
                $postcode_days = _check_specific_delivery_postcodes($postcode);
            }
        }

        $option = get_option("peter_timbs");
        $north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);
        $south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);
        
        if(empty($postcode_days) && in_array($postcode,$north_island_postcodes) && $is_merchandise == true ){
            $postcode_days[] = $postcode;
        }
        if (empty($postcode_days) && in_array($postcode,$south_island_postcodes) && $is_merchandise == true ) {
            $postcode_days[] = $postcode;
        }
        
        if(empty($postcode_days) || $postcode_days == '' ) {
            $response['available_delivery'] = ['pick_up'];
            $response['shipping_type'] = "pick_up";

            // 17-05-22 zone postcode fee add start
            $option = get_option("peter_timbs");
            $zone['zone_one'] = empty($option['zone_one']) ? [] : $option['zone_one'];
            $zone['zone_two'] = empty($option['zone_two']) ? [] : $option['zone_two'];
            $zone['zone_three'] = empty($option['zone_three']) ? [] : $option['zone_three'];
            $zone['zone_four'] = empty($option['zone_four']) ? [] : $option['zone_four'];
            $zone['north_island_postcodes'] = empty($option['north_island_postcodes']) ? [] : $option['north_island_postcodes'];
            $zone['south_island_postcodes'] = empty($option['south_island_postcodes']) ? [] : $option['south_island_postcodes'];

            foreach($zone as $key => $val){
                $data = explode(",", $val['post_code']);
                $result[$key] = $val;
                unset($result[$key]['post_code']);
                array_push($result[$key],$data);
                $result[$key]['post_code'] = $result[$key]['0'];
                unset($result[$key]['0']);
            }
            $response['shipping_zone'] = $result;
            // 17-05-22 zone postcode fee add end
        } else {
            $disabled_dates = [date("Y-m-d")];
            // $disable_days = ["saturday"];
            // $day = strtolower(date("l"));
            // if(in_array($day, $disable_days)) {
            //     $disabled_dates = [];
            // }
            _get_postcode_disabled_dates($postcode_days, $disabled_dates);
            foreach ($deliery_cut_off_dates as $d) {
                $disabled_dates[] = date("Y-m-d", strtotime($d));
            }
            $response['delivery_min_date'] = date("Y/m/d");
            $response['delivery_max_date'] = date("Y/m/d", strtotime("+60 days"));
            $option = get_option("peter_timbs");
            // $cut_off = empty($option['delivery_cut_off_time']) ? "12:00" : $option['delivery_cut_off_time'];
            $cut_off_day = empty($option['delivery_cut_off_day']) ? "today" : $option['delivery_cut_off_day'];
            $now = strtotime("now");
            // $cut_off = strtotime($cut_off);
            // if($cut_off_day == 0 && $cut_off < $now) {
            //     $disabled_dates[] = date("Y-m-d");
            // }
            if($cut_off_day == 1) {
                $disabled_dates[] = date("Y-m-d", strtotime("+1 Days"));
            }
            if($cut_off_day == 2) {
                $disabled_dates[] = date("Y-m-d", strtotime("+1 Days"));
                $disabled_dates[] = date("Y-m-d", strtotime("+2 Days"));
            }
            $response['delivery_disabled_dates'] = $disabled_dates;
            $response['disable_date_message'] = "Delivery for this day is currently not available";

            // 17-05-22 zone postcode fee add start
            $zone['zone_one'] = empty($option['zone_one']) ? [] : $option['zone_one'];
            $zone['zone_two'] = empty($option['zone_two']) ? [] : $option['zone_two'];
            $zone['zone_three'] = empty($option['zone_three']) ? [] : $option['zone_three'];
            $zone['zone_four'] = empty($option['zone_four']) ? [] : $option['zone_four'];
            $zone['north_island_postcodes'] = empty($option['north_island_postcodes']) ? [] : $option['north_island_postcodes'];
            $zone['south_island_postcodes'] = empty($option['south_island_postcodes']) ? [] : $option['south_island_postcodes'];
            foreach($zone as $key => $val){
                $data = explode(",", $val['post_code']);
                $result[$key] = $val;
                unset($result[$key]['post_code']);
                array_push($result[$key],$data);
                $result[$key]['post_code'] = $result[$key]['0'];
                unset($result[$key]['0']);
            }
            $response['shipping_zone'] = $result;
            if(($cart_shipping_type == 'both') || ($cart_shipping_type == 'delivery')){
                $option = get_option("peter_timbs");

                $zone_price = 0;
                $zone_one = empty($option['zone_one']) ? [] : explode(",", $option['zone_one']['post_code']);
                $zone_two = empty($option['zone_two']) ? [] : explode(",", $option['zone_two']['post_code']);
                $zone_three = empty($option['zone_three']) ? [] : explode(",", $option['zone_three']['post_code']);
                $zone_four = empty($option['zone_four']) ? [] : explode(",", $option['zone_four']['post_code']);
                $north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);
                $south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);

                if(in_array($postcode,$zone_one)) {
                    $zone_price = $option['zone_one']['min_cart_amount'];
                    $discount = $option['zone_one']['discount'];
                    $discount_type = $option['zone_one']['discount_type'];
                    $delivery_fee = $option['zone_one']['delivery_fee'];
                }
                elseif(in_array($postcode,$zone_two)) {
                    $zone_price = $option['zone_two']['min_cart_amount'];
                    $discount = $option['zone_two']['discount'];
                    $discount_type = $option['zone_two']['discount_type'];
                    $delivery_fee = $option['zone_two']['delivery_fee'];
                }
                elseif(in_array($postcode,$zone_three)) {
                    $zone_price = $option['zone_three']['min_cart_amount'];
                    $discount = $option['zone_three']['discount'];
                    $discount_type = $option['zone_three']['discount_type'];
                    $delivery_fee = $option['zone_three']['delivery_fee'];
                }
                elseif(in_array($postcode,$zone_four)) {
                    $zone_price = $option['zone_four']['min_cart_amount'];
                    $discount = $option['zone_four']['discount'];
                    $discount_type = $option['zone_four']['discount_type'];
                    $delivery_fee = $option['zone_four']['delivery_fee'];
                }
                elseif(in_array($postcode,$north_island_postcodes) && $is_merchandise == true ) {
                    $zone_price = $option['north_island_postcodes']['min_cart_amount'];
                    $discount = $option['north_island_postcodes']['discount'];
                    $discount_type = $option['north_island_postcodes']['discount_type'];
                    $delivery_fee = $option['north_island_postcodes']['delivery_fee'];
                }
                elseif(in_array($postcode,$south_island_postcodes) && $is_merchandise == true ) {
                    $zone_price = $option['south_island_postcodes']['min_cart_amount'];
                    $discount = $option['south_island_postcodes']['discount'];
                    $discount_type = $option['south_island_postcodes']['discount_type'];
                    $delivery_fee = $option['south_island_postcodes']['delivery_fee'];
                }else{
                    $delivery_fee = Delivery_Fee;
                    // $delivery_fee = 10;
                }
                if($product_free_delivery_check){
                    $response['min_cart_amount'] = 0;
                }else{
                    $response['min_cart_amount'] = $zone_price;
                }

                foreach ($cart as $item) {
                    $product = wc_get_product(empty($item['variation_id']) ? $item['product_id'] : $item['variation_id']);

                    $product_free_delivery = get_post_meta($product->id, "product_free_delivery", true);
                    // $response['product_free_delivery'] = $product_free_delivery != '' ? true : false;
                    if(!empty($product_free_delivery)){
                        $free_delivery[] = $product_free_delivery;
                    }
                }
                // echo $discount;
                // exit;
                if(($discount >= 0 ) && empty($free_delivery)){
                    // $amount = empty($option['minimum_delivery_amount']) ? 100 : $option['minimum_delivery_amount'];
                    // $amount = Delivery_Fee;

                    $cart_total = $total;
                    // echo $discount;
                    // die;

                    if($zone_price > $cart_total ){
                        $dis_cal = $delivery_fee;
                        $delivery_actual_fee = $delivery_fee;
                        $cal = 0;
                    }else{
                        if($discount_type == 'percentage'){
                            $cal = $delivery_fee * $discount / 100;
                            $dis_cal = $delivery_fee - $cal;
                            $delivery_actual_fee = $delivery_fee;
                        }else{
                            $dis_cal = $delivery_fee - $discount;
                            $cal = $discount;
                            $delivery_actual_fee = $delivery_fee;
                        }
                    }
                }elseif(!empty($free_delivery)){
                    $dis_cal = 0;
                    $cal = 0;
                    $delivery_actual_fee = 0;
                }else{
                    $dis_cal = Delivery_Fee;
                    // $dis_cal = 10;
                    $cal = 0;
                    $delivery_actual_fee = Delivery_Fee;
                    // $delivery_actual_fee = 10;
                }
            }
            else{
                $dis_cal = 0;
                $cal = 0;
                $delivery_actual_fee = 0;
            }


            // 17-05-22 zone postcode fee add end
        }

        $prep_time = _calculate_pickup_datetime($total_prep_time);

        $response['store_time'] = [
            "sunday_business_hours"     => strtolower($options['sunday_business_hours']),
            "monday_business_hours"     => strtolower($options['monday_business_hours']),
            "tuesday_business_hours"    => strtolower($options['tuesday_business_hours']),
            "wednesday_business_hours"  => strtolower($options['wednesday_business_hours']),
            "thursday_business_hours"   => strtolower($options['thursday_business_hours']),
            "friday_business_hours"     => strtolower($options['friday_business_hours']),
            "saturday_business_hours"   => strtolower($options['saturday_business_hours']),
        ];

        $gst = round((($total*15) / 115), 2);
        $subtotal = $total - $gst;

        // if($cart_shipping_type)
        $response['pickup_total'] = ($cart_shipping_type != "delivery") ? sprintf('%0.2f',$total) : 0;
        if($total < $options['minimum_delivery_amount'] || $is_meal) {
            $response['pickup_total'] = sprintf('%0.2f',$total);
        }
        $response['delivery_total'] = ($cart_shipping_type != "pickup") ? sprintf('%0.2f',$total+$dis_cal): 0;
        $response['meal_pickup_time_limit'] = "01:00pm";
        // 17-05-22 zone postcode fee add start
        if(($dis_cal >= 0) && ($cart_shipping_type != 'pick_up') ){
            $response['delivery_actual_fee'] = sprintf('%0.2f',$delivery_actual_fee);
            $response['delivery_discount'] = $cal;
            $response['delivery_charge'] = sprintf('%0.2f',$dis_cal);
        }
        if(($cart_shipping_type == 'delivery') && ($dis_cal >= 0)){
            $total = $total + $dis_cal;
            $response['total'] = sprintf('%0.2f', $total).' include delivery charge';

        }else{
            $response['total'] = sprintf('%0.2f', $total);
        }
        // 17-05-22 zone postcode fee add end

        $response['subtotal'] = "$".sprintf('%0.2f', $subtotal);
        $response['gst'] = "$".sprintf('%0.2f', $gst);
        $response['available_method'] = ["stripe"];
        $response['pickup_datetime'] = $prep_time;
        $response['cards'] = _get_user_cards($this->user->user_id);
        // 17-05-22 zone postcode fee add start
        // $response['new_delivery_fee'] = $dis_cal == '' ? 0 : $dis_cal ;
        // 17-05-22 zone postcode fee add end

        if($is_meal) {
            // _dd("here");
            $from = date("Y-m-d", strtotime(get_post_meta( $meal_id, 'date_product_from', true )));
            $to = date("Y-m-d", strtotime(get_post_meta( $meal_id, 'date_product_to', true )));
            $from   = empty(get_post_meta( $meal_id, 'date_product_from', true )) ? date("Y-m-d") : $from;
            $to     = empty(get_post_meta( $meal_id, 'date_product_from', true )) ? date("Y-m-d", strtotime("+60 days")) : $to;
            $response['pickup_min_date'] = $from;
            $response['pickup_max_date'] = $to;
            $response['pickup_disabled_dates'] = _get_pickup_disabled_dates($is_meal);
        } else {
            $response['pickup_min_date'] = date("Y/m/d");
            $response['pickup_max_date'] = date("Y/m/d", strtotime("+60 days"));
            $response['pickup_disabled_dates'] = _get_pickup_disabled_dates($is_meal);
        }
        $response["is_meal"] = $is_meal;
        $response["is_merchandise"] = $is_merchandise;
        $response['full_address'] = get_user_meta($this->user->user_id, "full_address", true);
        $response['pickup_disable_date_message'] = "Pickup for this day is currently not available";

        $response["store_location"] = [
            [
                "label" => "Edgeware Store",
                "address" => "70 Edgeware Rd, Edgeware, Christchurch, NZ"
            ], [
                "label" => "Bishopdale Store",
                "address" => ""
            ],
        ];
        wp_send_json($response, 200);
        wp_die();
    }

    public function search($request) {
        $this->user = pt_validate_request($request);
        $post = $request->get_json_params();
        $page = empty($request->get_param('page')) ? 1 : $request->get_param('page');
        $limit = empty($request->get_param('limit')) ? 10 : $request->get_param('limit');
        $s = $request->get_param('s');
        $type = empty($request->get_param('type')) ? "product" : $request->get_param('type');
        remove_filter( 'pre_get_posts', 'nectar_change_wp_search_size' );

        if($type === "product") {
            $args = [
                's' => $s,
                "post_type" => "product",
                "post_status" => "publish",
                "posts_per_page" => $limit,
                "paged" => $page,
                "order" => "DESC",
                "orderby"   => "ID",
                "fields" => "ids",
                'meta_key' => '_hidden_on_app',
                'meta_value' => '1',
                'meta_compare' => '!=',
                'paging' => true
            ];
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'meals',
                'operator' => 'NOT IN',
            ];
            $data = new WP_Query($args);
            $products = [];
            foreach ($data->posts as $d) {
                $product = wc_get_product($d);
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_ID() ), 'woocommerce_single' );
                $image = empty($image) ? "" : $image[0];

                $price = html_entity_decode(strip_tags($product->get_price_html()));

                $products[] = [
                    "ID"   => $product->get_ID(),
                    "name" => $product->get_title(),
                    "price" => $price,
                    "image" => $image,
                ];
            }
            $response['success'] = true;
            $response['total_products'] = $data->found_posts;
            $response['product_pages'] = $data->max_num_pages;
            $response['products'] = $products;
        }
        if($type === "recipe") {
            $args = [
                's' => $s,
                "post_type" => "recipe",
                "post_status" => "publish",
                "posts_per_page" => $limit,
                "paged" => $page,
                "order" => "DESC",
                "orderby"   => "ID",
                "paging"    => true
            ];
            $recipes = new WP_Query($args);
            $response['success'] = true;
            foreach ($recipes->posts as $r) {
                $image = wp_get_attachment_url(get_post_thumbnail_id($r->ID));
                $difficulty = get_the_terms($r->ID, "difficulty");
                $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;
                $response['recipes'][] = [
                    "ID" => $r->ID,
                    "image" => $image,
                    "name" => $r->post_title,
                    "ratings" => get_post_meta($r->ID, "recipe_user_ratings_rating", true),
                    "serving_size" => get_post_meta($r->ID, "recipe_servings", true),
                    "cook_time" => get_post_meta($r->ID, "recipe_cook_time", true),
                    "difficulty" => $difficulty
                ];
            }
            $response['total_recipes'] = $recipes->found_posts;
            $response['recipe_pages'] = $recipes->max_num_pages;
        }

        if($type === "meal") {
            $args = [
                's' => $s,
                "post_type" => "product",
                "post_status" => "publish",
                "posts_per_page" => $limit,
                "paged" => $page,
                "order" => "DESC",
                "orderby"   => "ID",
                "fields" => "ids",
                'meta_key' => '_hidden_on_app',
                'meta_value' => '1',
                'meta_compare' => '!=',
                'paging' => true
            ];
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'meals',
                'operator' => 'IN',
            ];
            $data = new WP_Query($args);
            $products = [];
            foreach ($data->posts as $d) {
                $product = wc_get_product($d);
                $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_ID() ), 'woocommerce_single' );
                $image = empty($image) ? "" : $image[0];

                $price = html_entity_decode(strip_tags($product->get_price_html()));

                $products[] = [
                    "ID"   => $product->get_ID(),
                    "name" => $product->get_title(),
                    "price" => $price,
                    "image" => $image,
                ];
            }
            $response['success'] = true;
            $response['total_meals'] = $data->found_posts;
            $response['meal_pages'] = $data->max_num_pages;
            $response['meals'] = $products;
        }
        wp_send_json($response, 200);
        wp_die();
    }

    // private function _get_user_cards() {
    //     global $wpdb;
    //     $sql = "SELECT pt.token_id, pt.token, pt.type, ptm.meta_key, ptm.meta_value FROM {$wpdb->prefix}woocommerce_payment_tokens as pt
    //     INNER JOIN {$wpdb->prefix}woocommerce_payment_tokenmeta as ptm
    //     ON pt.token_id = ptm.payment_token_id
    //     WHERE pt.user_id = ".$this->user->user_id;
    //     $data = $wpdb->get_results($sql, ARRAY_A);
    //     $cardsData = [];
    //     foreach($data as $d) {
    //         $cardsData[$d['token_id']]["token"] = $d['token'];
    //         $cardsData[$d['token_id']]["token_id"] = $d['token_id'];
    //         $cardsData[$d['token_id']][$d['meta_key']] = $d['meta_value'];
    //     }
    //     $cards = [];
    //     foreach ($cardsData as $c) {
    //         $cards[] = $c;
    //     }
    //     return $cards;
    // }

    // private function _check_specific_delivery_postcodes($postcode) {
    //     $option = get_option("peter_timbs");
    //     $specific_postcodes = [];
    //     $sunday = empty($option['sunday_delivery_postcodes']) ? [] : explode(",", $option['sunday_delivery_postcodes']);
    //     $monday = empty($option['monday_delivery_postcodes']) ? [] : explode(",", $option['monday_delivery_postcodes']);
    //     $tuesday = empty($option['tuesday_delivery_postcodes']) ? [] : explode(",", $option['tuesday_delivery_postcodes']);
    //     $wednesday = empty($option['wednesday_delivery_postcodes']) ? [] : explode(",", $option['wednesday_delivery_postcodes']);
    //     $thursday = empty($option['thursday_delivery_postcodes']) ? [] : explode(",", $option['thursday_delivery_postcodes']);
    //     $friday = empty($option['friday_delivery_postcodes']) ? [] : explode(",", $option['friday_delivery_postcodes']);
    //     $saturday = empty($option['saturday_delivery_postcodes']) ? [] : explode(",", $option['saturday_delivery_postcodes']);

    //     $week_days = [1, 2, 3, 4, 5, 6, 7];
    //     foreach ($monday as $value) {
    //         $specific_postcodes[$value][] = 1;
    //     }
    //     foreach ($tuesday as $value) {
    //         $specific_postcodes[$value][] = 2;
    //     }
    //     foreach ($wednesday as $value) {
    //         $specific_postcodes[$value][] = 3;
    //     }
    //     foreach ($thursday as $value) {
    //         $specific_postcodes[$value][] = 4;
    //     }
    //     foreach ($friday as $value) {
    //         $specific_postcodes[$value][] = 5;
    //     }
    //     foreach ($saturday as $value) {
    //         $specific_postcodes[$value][] = 6;
    //     }
    //     foreach ($sunday as $value) {
    //         $specific_postcodes[$value][] = 7;
    //     }
    //     if(empty($specific_postcodes[$postcode])) {
    //         return false;
    //     }
    //     return $specific_postcodes[$postcode];
    // }

    // private function _get_postcode_disabled_dates($postcode_days, &$disabled_dates) {
    //     for($i=1; $i<=60; $i++){
    //         $day_no = date("N", strtotime($i." days"));
    //         if(!in_array($day_no, $postcode_days)) {
    //             $disabled_dates[] = date("Y-m-d", strtotime($i." days"));
    //         }
    //     }
    // }

    // private function _get_address_postcode($address) {
    //     $address = str_replace(", ", "+", $address);
    //     $address = str_replace(" ", "+", $address);
    //     $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyAYyxEGB5J4cZGFf9TWu4N0gy0KQDWtFeI";
    //     $data = json_decode(file_get_contents($url), true);
    //     $data = end($data['results'][0]['address_components']);
    //     return $data['short_name'];
    // }

    // private function _get_pickup_disabled_dates($is_meal) {
    //     if(!$is_meal){
    //         $dates = [date("Y-m-d"), date("Y-m-d", strtotime("+1 Days")),];
    //     }
    //     $disable_days = ["sunday"];
    //     for($i=0; $i<=60; $i++){
    //         $day = strtolower(date("l", strtotime($i." days")));
    //         if(in_array($day, $disable_days)) {
    //             $dates[] = date("Y-m-d", strtotime($i." days"));
    //         }
    //     }
    //     array_push($dates,"2022-11-11", "2022-11-14", "2022-11-21", "2022-11-28", "2022-12-05", "2022-12-12", "2022-12-19", "2022-12-26", "2022-12-27", "2023-01-02", "2023-01-03");
    //     return $dates;
    // }

    public function handle_custom_query_var( $query, $query_vars ) {
        $query['meta_query'][] = [
            'key' => '_hidden_on_app',
            'value' => '1',
            'compare' => '!='
        ];
        return $query;
    }
}