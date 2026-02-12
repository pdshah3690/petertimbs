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

class Petertimbs_Order {



	public function index($request) {

		$this->user = pt_validate_request($request);

		$post = $request->get_json_params();



     	$page = empty($post['page']) ? 1 : $post['page'];

     	$limit = empty($post['limit']) ? 10 : $post['limit'];



        $args = [

            "post_type" => "shop_order",

            "post_status" => ["wc-pending", "wc-on-hold", "wc-processing", "wc-failed", "wc-completed"],

            "posts_per_page" => $limit,

            "paged" => $page,

            "order" => "DESC",

            "orderby"   => "ID",

            "fields" => "ids",

		    'meta_key' => '_customer_user',

		    'meta_value' => $this->user->user_id,

		    'meta_compare' => '=',

		    "paging" => true

        ];

        $data = new WP_Query($args);

        $orders = [];

		foreach($data->posts as $order_id) {

			$order = wc_get_order($order_id);

			$products = [];

			foreach ($order->get_items() as $item) {

				$product = wc_get_product(empty($item->get_variation_id()) ? $item->get_product_id() : $item->get_variation_id());

		        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_product_id() ), 'woocommerce_gallery_thumbnail' );

		        $image = empty($image) ? "" : $image[0];

	            $attr = "";

	            if(!empty($product) && $product->get_type() == "variation") {

	                $attr = array_values($product->get_attributes());

	                $attr = implode(", ", $attr);

	            }

				$products[] = [

					'product_id' 	=> $item->get_product_id(),

					'variation_id' 	=> $item->get_variation_id(),

					'qty' 	=> $item->get_quantity(),

					'price' => empty($product) ? "$0" : "$".round($item->get_total()/$item->get_quantity(), 2),

					'name' 	=> empty($product) ? "Remove Product" : $product->get_title(),

					'variation' => $attr,

					'total' => empty($product) ? "$0" : "$".$item->get_total(),

					'image' => $image

				];

			}

			$order_type = get_post_meta($order->get_id(), '_order_type', true);

			if($order_type != "pick_up"){

				$delivery_charge_fee = 0;

	        	foreach( $order->get_items('fee') as $item_id => $item_fee ) {

					$delivery_charge = "$".$item_fee->get_total()." Includes delivery, an ice pack and packaging";

					$delivery_charge_fee = $delivery_charge_fee + $item_fee->get_total();

	            }

	            $total = $order->get_total();

	            $total = $total - $delivery_charge_fee;

	        }else{

	        	$delivery_charge = 'Store Collection';

	        	$total = $order->get_total();

	        }





			// $total = $order->get_total();

			$gst = round((($total*15) / 115), 2);

	        $subtotal = $total - $gst;

	        // $order_type = get_post_meta($order->get_id(), '_order_type', true);

	        if($order_type == "pick_up") {

	        	$order_ready_date = get_post_meta($order->get_ID(), '_order_preparation_time', 1);

	        	$order_ready_date = date("d/m/y h:i a", strtotime($order_ready_date));

	        } else {

	        	$order_ready_date = get_post_meta($order->get_ID(), '_order_delivery_date', 1);

	        	$order_ready_date = date("d/m/y", strtotime($order_ready_date));

	        }

	        $date = $order->get_date_created()->date('Y-m-d H:i:s');

	        $date = date("d/m/y", strtotime($date))." at ".date("h:i a", strtotime($date));

	        // $delivery_charge = ($order_type == "pick_up") ? "Store Collection" : "$".Delivery_Fee." Includes delivery, an ice pack and packaging";

	        $method = ($order->get_payment_method() == "stripe") ? "Credit/Debit Card" : "Cash on delivery";



	        $delivery_address = $order->get_address('billing');

	        $address = [];

	        if($order_type == 'delivery'){

	        	$address = [

					'address_1' 	=> $delivery_address['address_1'],

					'address_2' 	=> $delivery_address['address_2'],

					'city' 	=> $delivery_address['city'],

					'state' 	=> $delivery_address['state'],

					'postcode' 	=> $delivery_address['postcode'],

					'country' 	=> $delivery_address['country'],

					'phone' 	=> $delivery_address['phone'],

				];



				$orders[] = [

					'id' => $order_id,

					'total' => "$".$order->get_total(),

					'sub_total' => "$".$subtotal,

					'gst' => "$".$gst,

					'delivery_charge' => $delivery_charge,

					'order_type' => $order_type,

					'order_ready_date' => $order_ready_date,

					'status' => ucfirst($order->get_status()),

					'date'  => $date,

					'method' => $method,

					'products' => $products,

					'delivery_address' => $address

				];

	        }else{

	        	$orders[] = [

					'id' => $order_id,

					'total' => "$".$order->get_total(),

					'sub_total' => "$".$subtotal,

					'gst' => "$".$gst,

					'delivery_charge' => $delivery_charge,

					'order_type' => $order_type,

					'order_ready_date' => $order_ready_date,

					'status' => ucfirst($order->get_status()),

					'date'  => $date,

					'method' => $method,

					'products' => $products,

					'store_address' => get_post_meta($order->get_id(), '_store_collection', 1)

				];

	        }

		}

		$response = [

			"success" => true,

			"orders"  => $orders

		];

        $response['max_num_pages'] = $data->max_num_pages;

        wp_send_json($response, 200);

        wp_die();

	}



	public function create($request) {

		$peter_timbs = get_option("peter_timbs");
		$this->user = pt_validate_request($request);
		$post = $request->get_json_params();

		$status = 400;
		$response = [];

		if(empty($post['cart'])) {
			$response['success'] = false;
			$response['message'] = "Cart is empty!";
		}

        if(empty($post['stripe_source']) && $post['payment_method'] == 'stripe') {
			$response['success'] = false;
			$response['message'] = "Provide valid stripe source";
		}

		$post['billing_address_1'] = $post["tempAdd"]["billing_address_1"];
		$post['billing_address_2'] = $post["tempAdd"]["billing_address_2"];
		$post['billing_city'] = $post["tempAdd"]["billing_city"];
		$post['billing_postcode'] = $post["tempAdd"]["billing_postcode"];
		$post['billing_state'] = $post["tempAdd"]["billing_state"];

		if($post['order_type'] == 'delivery') {

			if(empty($post['billing_address_1'])) {
	            $response['success'] = false;
	            $response['message'] = "Provide Billing Address 1";
	        }

	        if(empty($post['billing_city'])) {
	            $response['success'] = false;
	            $response['message'] = "Provide Billing City";
	        }

	        if(empty($post['billing_state'])) {
	            $response['success'] = false;
	            $response['message'] = "Provide Billing State";
	        }

	        if(empty($post['billing_postcode'])) {
	            $response['success'] = false;
	            $response['message'] = "Provide Billing PostCode";
	        }
       	}

		$stripe_customer_id = get_user_meta( $this->user->user_id, "wp__stripe_customer_id", true );
        wp_set_current_user($this->user->user_id);
		$order_total = 0;
		$total_prep_time = 0;

	    foreach ($request->get_param('cart') as $item) {



	    	$product = wc_get_product(empty($item['variation_id']) ? $item['product_id'] : $item['variation_id']);

	    	$order_total = $order_total + ($product->get_price()*$item['qty']);



	    	$current_product_cat = has_term('meals', 'product_cat', $item['product_id']);

	    	$post_id = empty($item['variation_id']) ? $item['product_id'] : $item['variation_id'];

            $preparation_time = get_post_meta($post_id, "preparation_time", true);

            if(!empty($preparation_time)) {

                $total_prep_time = $total_prep_time + ($preparation_time * $item['qty']);

            }

	    }



	    if($current_product_cat == 1){

	    	$today = date("Y-m-d");

        	$pickup_date_time = date("Y-m-d", strtotime($post['pickup_datetime']));

        	$time = date('H', strtotime($post['pickup_datetime']));



        	if($today == $pickup_date_time && date("H") > 13) {

        		$response['success'] = false;

				$response['message'] = "Meals need to be ordered before 1pm for same day pickup.";

				$status = 200;

            	wp_send_json($response, $status);

    			wp_die();

	        }elseif($time < 16 || $time > 18 ){

	        	$response['success'] = false;

				$response['message'] = "Meals are only available for pickup between 4pm - 6pm.";

				$status = 200;

            	wp_send_json($response, $status);

    			wp_die();

	        }

	    }



	    $total_prep_time = $total_prep_time + $peter_timbs['preparation_buffer_time'];

	    $total_prep_time = date("H", strtotime($total_prep_time));



	    if(empty($current_product_cat) && $post['order_type'] == 'pick_up'){

	    	if(date("H") > 13 || $total_prep_time > 13 ){

	    		$response['success'] = false;

				$response['message'] = "Meat need to be ordered before 1pm.";

				$status = 200;

            	wp_send_json($response, $status);

    			wp_die();	

	    	}

	    }



		if(empty($response)) {

		    $order = wc_create_order(array('customer_id' => $this->user->user_id));

		    update_post_meta( $order->get_id(), "_is_order", "App Order" );

		    update_post_meta( $order->get_id(), "_stripe_source_id", $post['stripe_source'] );

		    update_post_meta( $order->get_id(), "_stripe_customer_id", $stripe_customer_id );

	        $address = array(

		        'address_1'  => $post['billing_address_1'],

		        'address_2'  => $post['billing_address_2'],

		        'city'       => $post['billing_city'],

		        'state'      => $post['billing_state'],

		        'postcode'   => $post['billing_postcode'],

		        'country'    => 'NZ'

		    );



		    $order->set_address( $address, 'billing' );

		    $order->set_billing_first_name( $post['first_name'] );

		    $order->set_billing_last_name( $post['last_name'] );

		    $order->set_billing_email( $post['email'] );

		    foreach ($request->get_param('cart') as $item) {

		    	$product = wc_get_product(empty($item['variation_id']) ? $item['product_id'] : $item['variation_id']);

			    $order->add_product( $product, $item['qty'], []);



			    $is_merchandise = has_term( 'merchandise', 'product_cat', $product);

		    }



            if($post['order_type'] == 'delivery') {

                $calculate_tax_for = array(

                    'country' => "NZ",

                    'state' => $post['billing_state'],

                    'postcode' => $post['billing_postcode'],

                    'city' => $post['billing_city'],

                );



             //    $item_fee = new WC_Order_Item_Fee();

             //    $item_fee->set_name( "Delivery fee: Includes delivery, an ice pack and packaging" );

             //    $item_fee->set_amount(Delivery_Fee);

             //    $item_fee->set_tax_class( '' );

             //    $item_fee->set_tax_status( 'none' );

             //    $item_fee->set_total(Delivery_Fee);

             //    // Calculating Fee taxes

             //    $item_fee->calculate_taxes( $calculate_tax_for );

            	// // Add Fee item to the order

            	// $order->add_item( $item_fee );



            	// 12-05-22 zone postcode fee add start





            	$option = get_option("peter_timbs");

	            $zone_price = 0;

	            $zone_one = empty($option['zone_one']) ? [] : explode(",", $option['zone_one']['post_code']);

				$zone_two = empty($option['zone_two']) ? [] : explode(",", $option['zone_two']['post_code']);

				$zone_three = empty($option['zone_three']) ? [] : explode(",", $option['zone_three']['post_code']);

				$zone_four = empty($option['zone_four']) ? [] : explode(",", $option['zone_four']['post_code']);

				$north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);

				$south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);



				if(in_array($post['billing_postcode'],$zone_one)) {

					$min_cart_amount = $option['zone_one']['min_cart_amount'];

					$discount = $option['zone_one']['discount'];

					$discount_type = $option['zone_one']['discount_type'];

					$delivery_fee = $option['zone_one']['delivery_fee'];

				}

				elseif(in_array($post['billing_postcode'],$zone_two)) {

					$min_cart_amount = $option['zone_two']['min_cart_amount'];

					$discount = $option['zone_two']['discount'];

					$discount_type = $option['zone_two']['discount_type'];

					$delivery_fee = $option['zone_two']['delivery_fee'];

				}

				elseif(in_array($post['billing_postcode'],$zone_three)) {

					$min_cart_amount = $option['zone_three']['min_cart_amount'];

					$discount = $option['zone_three']['discount'];

					$discount_type = $option['zone_three']['discount_type'];

					$delivery_fee = $option['zone_three']['delivery_fee'];

				}

				elseif(in_array($post['billing_postcode'],$zone_four)) {

					$min_cart_amount = $option['zone_four']['min_cart_amount'];

					$discount = $option['zone_four']['discount'];

					$discount_type = $option['zone_four']['discount_type'];

					$delivery_fee = $option['zone_four']['delivery_fee'];



				}

				elseif(in_array($post['billing_postcode'],$north_island_postcodes) && $is_merchandise == true) {

					$min_cart_amount = $option['north_island_postcodes']['min_cart_amount'];

					$discount = $option['north_island_postcodes']['discount'];

					$discount_type = $option['north_island_postcodes']['discount_type'];

					$delivery_fee = $option['north_island_postcodes']['delivery_fee'];

				}

				elseif(in_array($post['billing_postcode'],$south_island_postcodes) && $is_merchandise == true) {

					$min_cart_amount = $option['south_island_postcodes']['min_cart_amount'];

					$discount = $option['south_island_postcodes']['discount'];

					$discount_type = $option['south_island_postcodes']['discount_type'];

					$delivery_fee = $option['south_island_postcodes']['delivery_fee'];

				}else{

					$delivery_fee = Delivery_Fee;

				}



				if(!empty($discount)){

					// $amount = empty($option['minimum_delivery_amount']) ? 100 : $option['minimum_delivery_amount'];

					// $amount = Delivery_Fee;

					// $delivery_fee = Delivery_Fee;



					$cart_total = $order_total;



					if($min_cart_amount > $cart_total ){

						$zone_price = $delivery_fee;

					}else{

						if($discount_type == 'percentage'){

							$cal = $delivery_fee * $discount / 100;

							$zone_price = $delivery_fee - $cal;

						}else{

							$zone_price = $delivery_fee - $discount;

						}

					}



					// if($discount_type == 'percentage'){

					// 	$cal = $amount * $discount / 100;

					// 	$zone_price = $amount - $cal;

					// }else{

					// 	$zone_price = $amount - $discount;

					// }



				}else{

					$zone_price = 0;

				}





            	// $order_items = wc_get_order($order->get_id());



	            foreach ($request->get_param('cart') as $cart_item)

	            {

					$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];



					$product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);



					$is_merchandise = has_term( 'merchandise', 'product_cat', $post_id);



				    if(!empty($product_free_delivery)){

					    $free_delivery[] = $product_free_delivery;

					}

	            }

	            if(!empty($discount) && !empty($free_delivery)){

	            	$zone_fee = new WC_Order_Item_Fee();

	            	if($is_merchandise){

	            		$zone_fee->set_name( "Delivery fee:" );

	            	}else{

	            		$zone_fee->set_name( "Delivery fee: Includes delivery, an ice pack and packaging" );

	            	}

	                $zone_fee->set_amount(0);

	                $zone_fee->set_tax_class( '' );

	                $zone_fee->set_tax_status( 'none' );

	                $zone_fee->set_total(0);

	                // Calculating Fee taxes

	                $zone_fee->calculate_taxes( $calculate_tax_for );

	            	// Add Fee item to the order

	            	$order->add_item( $zone_fee );

	            }elseif(!empty($discount)){

	            	if($zone_price > 0){

						$zone_price = $zone_price;

					}else{

						$zone_price = 0;

					}

					$zone_fee = new WC_Order_Item_Fee();

					if($is_merchandise){

	            		$zone_fee->set_name( "Delivery fee:" );

	            	}else{

	            		$zone_fee->set_name( "Delivery fee: Includes delivery, an ice pack and packaging" );

	            	}

	                $zone_fee->set_amount($zone_price);

	                $zone_fee->set_tax_class( '' );

	                $zone_fee->set_tax_status( 'none' );

	                $zone_fee->set_total($zone_price);

	                // Calculating Fee taxes

	                $zone_fee->calculate_taxes( $calculate_tax_for );

	            	// Add Fee item to the order

	            	$order->add_item( $zone_fee );

	            }elseif(!empty($free_delivery)){

	            	$item_fee = new WC_Order_Item_Fee();

	            	if($is_merchandise){

	            		$item_fee->set_name( "Delivery fee:" );

	            	}else{

	            		$item_fee->set_name( "Delivery fee: Includes delivery, an ice pack and packaging" );

	            	}

	                $item_fee->set_amount(0);

	                $item_fee->set_tax_class( '' );

	                $item_fee->set_tax_status( 'none' );

	                $item_fee->set_total(0);

	                // Calculating Fee taxes

	                $item_fee->calculate_taxes( $calculate_tax_for );

	            	// Add Fee item to the order

	            	$order->add_item( $item_fee );

	            }else{

	            	$item_fee = new WC_Order_Item_Fee();

	            	if($is_merchandise){

	            		$item_fee->set_name( "Delivery fee:" );

	            	}else{

	            		$item_fee->set_name( "Delivery fee: Includes delivery, an ice pack and packaging" );

	            	}

	                $item_fee->set_amount(Delivery_Fee);

	                $item_fee->set_tax_class( '' );

	                $item_fee->set_tax_status( 'none' );

	                $item_fee->set_total(Delivery_Fee);

	                // Calculating Fee taxes

	                $item_fee->calculate_taxes( $calculate_tax_for );

	            	// Add Fee item to the order

	            	$order->add_item( $item_fee );

	            }



            }





	        $this_order = new WC_Order($order->get_id());

		    $total = $order->calculate_totals();

	        $this_order->set_total($total);





	        $response = [];

            $store_address = empty($post["store_address"]) ? "Edgeware" : $post["store_address"];

            update_post_meta($order->get_id(), '_store_collection', $store_address);

            if($post['order_type'] == 'pick_up') {
	            update_post_meta($order->get_id(), '_order_preparation_time', date("Y-m-d H:i:s", strtotime($post['pickup_datetime'])));
	            update_post_meta($order->get_id(), '_order_type', 'pick_up');
            }

            if($post['order_type'] == 'delivery') {
	            update_post_meta($order->get_id(), '_order_delivery_date', date("Y-m-d", strtotime($post['delivery_date'])));
	            update_post_meta($order->get_id(), '_order_type', 'delivery');
            }

            if($post['payment_method'] == 'stripe') {
                $_POST['stripe_source'] = $post['stripe_source'];
                add_filter( 'wc_stripe_use_elements_checkout_form', '__return_false' );
                $wc_gateway_stripe = new WC_Gateway_Stripe();
            	$payment_result = $wc_gateway_stripe->process_payment( $order->get_id() );
            	update_post_meta($order->get_id(), "_payment_method_title", "Credit Card (Stripe)");
                update_post_meta($order->get_id(), "_payment_method", "stripe");
                if($payment_result['result'] == 'success') {
                    update_post_meta($order->get_id(), "_stripe_currency", "nzd");
                    update_post_meta($order->get_id(), "_stripe_source_id", $_POST['stripe_source']);
                    $order_handler = new WC_Stripe_Order_Handler();
                    $order_handler->capture_payment($order->get_id());
                    $this_order->payment_complete();
                }else{
                	$response['success'] = false;
					$response['message'] = "Sorry, your order has failed can you please check your payment method.";
					$status = 200;
                	wp_send_json($response, $status);
        			wp_die();
                }
            }

            if($post['payment_method'] == 'cod') {
                update_post_meta($order->get_id(), "_payment_method_title", "Cash on pick up");
                update_post_meta($order->get_id(), "_payment_method", "cod");
                $this_order->update_status( 'processing' );
                $this_order->save();
            }

		    $response['success'] = true;
			$response['order'] = [
				'order_id' => $order->get_id()
			];

			$status = 200;
			$gss = new PT_GSS_Api();
			$order = wc_get_order($order->get_id());
			$gss_order = $gss->create_customer_order($order);

		}

        wp_send_json($response, $status);

        wp_die();

	}



	public function view($request) {

		$this->user = pt_validate_request($request);

		$order = wc_get_order($request->get_param("id"));

		if(!empty($order)) {

			$data = [

				"order_id" => $order->get_id(),

				'total' => "$".$order->get_total(),

				'sub_total' => "$".$order->get_subtotal(),

				'tax' => "$".$order->get_total_tax(),

				'status' => ucwords(str_replace("-", " ", $order->get_status())),

				'date'  => $order->get_date_created()->date('Y-m-d H:i:s'),

				'store' => get_post_meta($order->get_id(), "_store_collection", 1),

			];

			// foreach( $order->get_items('fee') as $item_id => $item_fee ) {

   //              $data['item_fee_name'] = $item_fee->get_name();

			// 	$data['item_fee_amount'] = $item_fee->get_total();

   //          }

			foreach ($order->get_items() as $item) {

		        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_product_id() ), 'woocommerce_gallery_thumbnail' );

		        $image = empty($image) ? "" : $image[0];



				$data['products'][] = [

					'product_id' 	=> $item->get_product_id(),

					'variation_id' 	=> $item->get_variation_id(),

					'qty' 	=> $item->get_quantity(),

					'price' => "$".round($item->get_total()/$item->get_quantity(), 2),

					'name' 	=> $item->get_name(),

					'total' => "$".$item->get_total(),

					'image' => $image

				];

			}

			$status = 200;

			$response['success'] = true;

			$response['order'] = $data;

		} else {

			$status = 400;

			$response['success'] = false;

			$response['message'] = "Invalid access!";

		}



        wp_send_json($response, $status);

        wp_die();

	}



	public function _get_stripe_source() {

		$fields = [

			'type' => 'card',

			'currency' => 'nzd',

			'amount' => 13965,

			'owner' => [

				'email' => $this->user->email

			],

			'card' => [

				'exp_month' => 2,

				'exp_year'	=> 25,

				'number'    => '4242424242424242',

				'cvc' 		=> 123

			]

		];

		$response = WC_Stripe_API::request( $fields, 'sources' );

		wp_send_json($response);

		wp_die();

		// return $response->id;

	}



	public function repeat($request) {

		$this->user = pt_validate_request($request);

		// get product by order id

		$order = wc_get_order($request->get_param("id"));

		foreach ( $order->get_items() as $item ) {



			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_product_id() ), 'woocommerce_gallery_thumbnail' );

		    $image = empty($image) ? "" : $image[0];

			$data['products'][] = [

				'product_id' 	=> $item->get_product_id(),

				'variation_id' 	=> $item->get_variation_id(),

				'qty' 	=> $item->get_quantity(),

				'price' => "$".round($item->get_total()/$item->get_quantity(), 2),

				'name' 	=> $item->get_name(),

				'total' => "$".$item->get_total(),

				'image' => $image

			];

		}

		

		// cart logic

		$options = get_option("peter_timbs");

		$full_address = $order->billing_address_1." ".$order->billing_address_2." ".$order->billing_city." ".$order->billing_state." ".$order->billing_postcode." ".$order->billing_country;



		$postcode = _get_address_postcode($full_address);



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

        

        foreach ($data['products'] as $item) {

        	$product_check = wc_get_product(empty($item['variation_id']) ? $item['product_id'] : $item['variation_id']);  	

        	if(!empty($product_check)){

        		$stock_status = $product_check->get_stock_status();

        		if($stock_status == 'instock'){

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

		                $merchandise = $item['product_id'];

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

        

        foreach ($data['products'] as $item) {

            $product_check = wc_get_product(empty($item['variation_id']) ? $item['product_id'] : $item['variation_id']);

        	

        	if(!empty($product_check)){

        		$stock_status = $product_check->get_stock_status();

        		if($stock_status == 'instock'){

        			$item_cat = has_term('meals', 'product_cat', $item['product_id']);

		            $item_cat = $item_cat == 1 ? 'true' : 'false' ; 

		            $item_cat_merchandise = has_term('merchandise', 'product_cat', $item['product_id']);

		            $item_cat_merchandise = $item_cat_merchandise == 1 ? 'true' : 'false' ; 

		            if($current_product_cat != $item_cat) {

		                if($current_product_cat == 'true') {

		                    $status = 200;

		                    $response = [

		                        "success" => false,

		                        "message" => "You currently have a meal (or meals) in your cart, meals cannot be purchased with other items."

		                    ];

		                } else {

		                    $status = 200;

		                    $response = [

		                        "success" => false,

		                        "message" => "Sorry, meals cannot be purchased with other items and need to be ordered separately."

		                    ];

		                }

		                wp_send_json($response, $status);

		                wp_die();

		            }



		            if($current_product_cat != $item_cat_merchandise) {

		                if($current_product_cat == 'true') {

		                    $status = 200;

		                    $response = [

		                        "success" => false,

		                        "message" => "You currently have a merchandise (or merchandise) in your cart, merchandise cannot be purchased with other items."

		                    ];

		                } else {

		                    $status = 200;

		                    $response = [

		                        "success" => false,

		                        "message" => "Sorry, merchandise cannot be purchased with other items and need to be ordered separately."

		                    ];

		                }

		                wp_send_json($response, $status);

		                wp_die();

		            }



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

        	}     

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



        if(empty($postcode_days)) {

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

					$min_cart_amount = $option['north_island_postcodes']['min_cart_amount'];

					$discount = $option['north_island_postcodes']['discount'];

					$discount_type = $option['north_island_postcodes']['discount_type'];

					$delivery_fee = $option['north_island_postcodes']['delivery_fee'];

				}

				elseif(in_array($postcode,$south_island_postcodes) && $is_merchandise == true ) {

					$min_cart_amount = $option['south_island_postcodes']['min_cart_amount'];

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

        $response['pickup_total'] = ($cart_shipping_type != "delivery") ? $total : 0;

        $response['delivery_total'] = ($cart_shipping_type != "pickup") ? $total+$dis_cal: 0;



        // 17-05-22 zone postcode fee add start

        if(($dis_cal >= 0) && ($cart_shipping_type != 'pick_up') ){

            $response['delivery_actual_fee'] = $delivery_actual_fee;

            $response['delivery_discount'] = $cal;

            $response['delivery_charge'] = $dis_cal;

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





		wp_send_json($response, $status);

        wp_die();

	}

}