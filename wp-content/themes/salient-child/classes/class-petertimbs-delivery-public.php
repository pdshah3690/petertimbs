<?php 

defined('ABSPATH') || exit;

// woocommerce_before_calculate_totals
Class Delivery_Public {
    public function __construct() {
    	add_action('wp_ajax_add_cart_fee', [$this, 'add_cart_fee']);
    	add_action('wp_ajax_nopriv_add_cart_fee', [$this, 'add_cart_fee']);
		add_action('woocommerce_checkout_update_order_review', [$this, 'set_delivery_type_radio']);
		add_action('woocommerce_cart_calculate_fees', [$this, 'add_delivery_fee'], 9999, 1);

    	add_action('wp_ajax_get_distance_from_store', [$this, 'get_distance_from_store']);
    	add_action('wp_ajax_nopriv_get_distance_from_store', [$this, 'get_distance_from_store']);
    	add_action('woocommerce_cart_contents', [$this, 'remove_delivery_fees']);

		add_filter('woocommerce_add_to_cart_validation', [$this, 'delivery_product_check'], 20, 3);
		add_action('woocommerce_before_checkout_form', [$this, 'display_order_amount_note'] );
		add_action('woocommerce_cart_is_empty', [$this, 'reset_cart_type_on_empty']);
		add_action( 'woocommerce_cart_item_removed', [$this, 'check_cart_type_on_remove'], 10, 2 );
    }

	public function set_delivery_type_radio($posted_data) {
	    parse_str($posted_data, $output);
	    if (isset($output['delivery_type'])){
	        WC()->session->set('delivery_type', $output['delivery_type']);
	    }
	}

	public function add_cart_fee() {
		
		if(wp_doing_ajax()) {
			WC()->session->set('delivery_type', $_POST['type']);
			WC()->cart->calculate_fees();
			WC()->cart->calculate_totals();
        	// Get order review fragment.
	        ob_start();
	        woocommerce_order_review();
	        $woocommerce_order_review = ob_get_clean();
	        wp_send_json(
	            array(
	                'result'    => empty($messages) ? 'success' : 'failure',
	                'fragments' => apply_filters(
	                    'woocommerce_update_order_review_fragments',
	                    array(
	                        '.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
	                   	)
	               	),
	           	)
	       	);
		}
	}

	public function add_delivery_fee($cart) {

		$option = get_option("peter_timbs");
		$billing_postcode = WC()->customer->get_billing_postcode();

		$zone_one = empty($option['zone_one']) ? [] : explode(",", $option['zone_one']['post_code']);
		$zone_two = empty($option['zone_two']) ? [] : explode(",", $option['zone_two']['post_code']);
		$zone_three = empty($option['zone_three']) ? [] : explode(",", $option['zone_three']['post_code']);
		$zone_four = empty($option['zone_four']) ? [] : explode(",", $option['zone_four']['post_code']);
		$north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);
		$south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);

		foreach( WC()->cart->get_cart() as $cart_item ){		
			$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];	
			$check_is_merchandise = has_term('merchandise', 'product_cat', $post_id);
		}
		
		if(in_array($billing_postcode,$zone_one)) {
			$min_cart_amount = $option['zone_one']['min_cart_amount'];
			$discount = $option['zone_one']['discount'];
			$discount_type = $option['zone_one']['discount_type'];
			$delivery_fee = $option['zone_one']['delivery_fee'];
		}
		elseif(in_array($billing_postcode,$zone_two)) {
			$min_cart_amount = $option['zone_two']['min_cart_amount'];
			$discount = $option['zone_two']['discount'];
			$discount_type = $option['zone_two']['discount_type'];
			$delivery_fee = $option['zone_two']['delivery_fee'];
		}
		elseif(in_array($billing_postcode,$zone_three)) {
			$min_cart_amount = $option['zone_three']['min_cart_amount'];
			$discount = $option['zone_three']['discount'];
			$discount_type = $option['zone_three']['discount_type'];
			$delivery_fee = $option['zone_three']['delivery_fee'];
		}
		elseif(in_array($billing_postcode,$zone_four)) {
			$min_cart_amount = $option['zone_four']['min_cart_amount'];
			$discount = $option['zone_four']['discount'];
			$discount_type = $option['zone_four']['discount_type'];
			$delivery_fee = $option['zone_four']['delivery_fee'];
		}
		elseif(in_array($billing_postcode,$north_island_postcodes) && $check_is_merchandise == true) {
			$min_cart_amount = $option['north_island_postcodes']['min_cart_amount'];
			$discount = $option['north_island_postcodes']['discount'];
			$discount_type = $option['north_island_postcodes']['discount_type'];
			$delivery_fee = $option['north_island_postcodes']['delivery_fee'];
		}
		elseif(in_array($billing_postcode,$south_island_postcodes) && $check_is_merchandise == true) {
			$min_cart_amount = $option['south_island_postcodes']['min_cart_amount'];
			$discount = $option['south_island_postcodes']['discount'];
			$discount_type = $option['south_island_postcodes']['discount_type'];
			$delivery_fee = $option['south_island_postcodes']['delivery_fee'];
		}
		else{
			$delivery_fee = Delivery_Fee;
		}


		
		if($discount >= 0){
			// $amount = empty($option['minimum_delivery_amount']) ? 100 : $option['minimum_delivery_amount'];
			// $amount = Delivery_Fee;

			$cart_total = WC()->cart->get_subtotal();

			if($min_cart_amount > $cart_total ){
				$dis_cal = $delivery_fee;
			}else{
				if($discount_type == 'percentage'){
					$cal = $delivery_fee * $discount / 100;
					$dis_cal = $delivery_fee - $cal;
				}else{
					$dis_cal = $delivery_fee - $discount;	
				}	
			}
				
			// if($discount_type == 'percentage'){
			// 	$cal = $delivery_fee * $discount / 100;
			// 	$dis_cal = $delivery_fee - $cal;
			// }else{
			// 	$dis_cal = $delivery_fee - $discount;
			// }

		}else{
			$dis_cal = 0;
		}


		if(!empty($_POST['delivery_type'])) {
			if($_POST['delivery_type'] == 'delivery') {
				// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", Delivery_Fee);


				foreach( WC()->cart->get_cart() as $cart_item ){
					
					$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
					
					$product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);

					$check_is_merchandise = has_term('merchandise', 'product_cat', $post_id);
					

				    if(!empty($product_free_delivery)){
					    $free_delivery[] = $product_free_delivery;
					}

				}

				if(!empty($free_delivery)){
					if( $check_is_merchandise ) 
					{
						WC()->cart->add_fee("Delivery fee", "Free Delivery");
					}else{
						WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", "Free Delivery");
					}
					// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", "Free Delivery");

				}else{
					if($discount >= 0){
						if( $check_is_merchandise ) 
						{
							WC()->cart->add_fee("Delivery fee", $dis_cal);
						}else{
							WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", $dis_cal);
						}
						// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", $dis_cal);
					}else{
						if( $check_is_merchandise ) 
						{
							WC()->cart->add_fee("Delivery fee", Delivery_Fee);
						}else{
							WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", Delivery_Fee);
						}
						// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", Delivery_Fee);

					}
				}
			}
			if($_POST['delivery_type'] == 'pick_up') {
				WC()->cart->fees_api()->remove_all_fees();
			}
		} else {
			if(WC()->session->get('delivery_type') == 'delivery') {
				// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", Delivery_Fee);
				
				foreach( WC()->cart->get_cart() as $cart_item ){
					
					$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
					
					$product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);

					$check_is_merchandise = has_term('merchandise', 'product_cat', $post_id);

				    if(!empty($product_free_delivery)){
					    $free_delivery[] = $product_free_delivery;
					}

				}

				if(!empty($free_delivery)){
					if( $check_is_merchandise ) 
					{
						WC()->cart->add_fee("Delivery fee", "Free Delivery");
					}else{
						WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", "Free Delivery");
					}
					// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", "Free Delivery");

				}else{
					if($discount >= 0){
						if( $check_is_merchandise ) 
						{
							WC()->cart->add_fee("Delivery fee", $dis_cal);
						}else{
							WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", $dis_cal);
						}
						// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", $dis_cal);
					}else{
						if( $check_is_merchandise ) 
						{
							WC()->cart->add_fee("Delivery fee", Delivery_Fee);
						}else{
							WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", Delivery_Fee);
						}
						// WC()->cart->add_fee("Delivery fee: Includes delivery, an ice pack and packaging", Delivery_Fee);

					}
				}
				
			}
			if(WC()->session->get('delivery_type') == 'pick_up') {
				WC()->cart->fees_api()->remove_all_fees();
			}
		}
	}

	public function get_distance_from_store() {
		if(wp_doing_ajax()) {
			$dest = $_POST['address_1'];
			$dest .= empty($_POST['address_2']) ? "+".$_POST['address_2'] : "";
			$dest .= "+".$_POST['city']."+".$_POST['postcode']."+NZ";
			$dest = str_replace(", ", "+", $dest);
			$dest = str_replace(" ", "+", $dest);
			$dest = str_replace("++", "+", $dest);

			// $is_local = measure_distance_from_store($dest);
			$is_local = 0;
			$user_id = get_current_user_id();
			$option = get_option("peter_timbs");
			$is_specific_postcode = 0;
			$zone_price = 0;
			$days = [];
			$remove_delivery_date = false;

			foreach( WC()->cart->get_cart() as $cart_item ){		
				$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];	
				$check_is_merchandise = has_term('merchandise', 'product_cat', $post_id);
			}

			if(!$is_local) {
				$days = $this->_check_specific_delivery_postcodes($_POST['postcode']);
				$north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);
				$south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);
				if(!empty($days)) {
					$zone_one = empty($option['zone_one']) ? [] : explode(",", $option['zone_one']['post_code']);
					$zone_two = empty($option['zone_two']) ? [] : explode(",", $option['zone_two']['post_code']);
					$zone_three = empty($option['zone_three']) ? [] : explode(",", $option['zone_three']['post_code']);
					$zone_four = empty($option['zone_four']) ? [] : explode(",", $option['zone_four']['post_code']);
					
					if(in_array($_POST['postcode'],$zone_one)) {
						$zone_price = $option['zone_one']['min_cart_amount'];
					}
					elseif(in_array($_POST['postcode'],$zone_two)) {
						$zone_price = $option['zone_two']['min_cart_amount'];
					}
					elseif(in_array($_POST['postcode'],$zone_three)) {
						$zone_price = $option['zone_three']['min_cart_amount'];
					}
					elseif(in_array($_POST['postcode'],$zone_four)) {
						$zone_price = $option['zone_four']['min_cart_amount'];
					}
					elseif(in_array($_POST['postcode'],$north_island_postcodes)) {
						$zone_price = $option['north_island_postcodes']['min_cart_amount'];
					}
					elseif(in_array($_POST['postcode'],$south_island_postcodes)) {
						$zone_price = $option['south_island_postcodes']['min_cart_amount'];
					}
					

					$is_local = 1;
					$is_specific_postcode = 1;
					update_user_meta($user_id, "is_under_range", 1);
					
				} else {
					if(in_array($_POST['postcode'],$north_island_postcodes,true) || in_array($_POST['postcode'],$south_island_postcodes,true) ){
						$is_local = 1;
						$is_specific_postcode = 1;
						update_user_meta($user_id, "is_under_range", 1);
						if($check_is_merchandise == false){
							$remove_delivery_date = true;
						}
					}
					WC()->session->set('delivery_type', '');
					update_user_meta($user_id, "is_under_range", 0);
					WC()->cart->calculate_fees();
					WC()->cart->calculate_totals();
				}
			} else {
				update_user_meta($user_id, "is_under_range", 1);
			}
			foreach( WC()->cart->get_cart() as $cart_item ){
					
				$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
				
				$product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);

			    if(!empty($product_free_delivery)){
				    $free_delivery[] = $product_free_delivery;
				}

			}
			if(!empty($free_delivery)){
				$zone_price = 0;
			}else{
				$zone_price = $zone_price;
			}

			$cart_total = WC()->cart->cart_contents_total;
			wp_send_json([
				"success" => true,
				"is_local" => $is_local,
				"is_specific_postcode" => $is_specific_postcode,
				"days" => $days,
				"zone_fee" => $zone_price,
				"cart_total" => $cart_total,
				"remove_delivery_date" => $remove_delivery_date,
			]);
			wp_die();
		}
	}

	public function remove_delivery_fees() {
		WC()->session->set('delivery_type', '');
		WC()->cart->calculate_fees();
		WC()->cart->calculate_totals();
	}

	public function delivery_product_check($passed, $product_id, $quantity) {
	    // Getting the product categories term slugs in an array for the current product
	    $product_id = empty($_POST['variation_id']) ? $product_id : $_POST['variation_id'];
	    $shipping_type = get_post_meta($product_id, "_shipping_type", 1);
	    $shipping_type = empty($shipping_type) ? "both" : $shipping_type;

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
		            wc_add_notice(__('Sorry, only specific items are available for pickup and only these items can be included in the same order.'), 'error');
		            return false;
			    }
			    if($cart_type != $shipping_type && $shipping_type == "pick_up") {
		            wc_add_notice(__('Sorry, only specific items are available for delivery and only these items can be included in the same order.'), 'error');
		            return false;
			    }
		    }
	        WC()->session->set('cart_type', $shipping_type);
	    }
	    if(WC()->cart->is_empty()) {
	        WC()->session->set('cart_type', $shipping_type);	    	
	    }
	    return $passed;
	}

	public function display_order_amount_note() {
		if(WC()->session->get('cart_type') == 'delivery') {
		    $option = get_option("peter_timbs");
		    // $amount = empty($option['minimum_delivery_amount']) ? 100 : $option['minimum_delivery_amount'];
		    foreach( WC()->cart->get_cart() as $cart_item ){
					
				$post_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
				
				$product_free_delivery = get_post_meta($post_id, "product_free_delivery", true);

			    if(!empty($product_free_delivery)){
				    $free_delivery[] = $product_free_delivery;
				}

			}
			if(!empty($free_delivery)){
				$amount = 0;
			}else{
				$amount = empty($option['minimum_delivery_amount']) ? 100 : $option['minimum_delivery_amount'];
			}

		    $cart_total = WC()->cart->get_totals();
		    if($cart_total['subtotal'] < $amount) {
		        echo '<div class="woocommerce-info">Sorry, your order total must be $'.$amount.' or more to be eligible for discount.</div>
		            <p class="return-to-shop"><a class="button wc-backward" href="/shop">Continue Shopping</a></p>';

		        // echo '<style>.woocommerce-form-login-toggle{display: none;}</style>';
		        // echo '<div class="woocommerce-info">Sorry, your order total must be $'.$amount.' or more to be eligible for delivery</div>
		        //     <p class="return-to-shop"><a class="button wc-backward" href="/shop">Continue Shopping</a></p>';
		    }
		}
	}

	public function reset_cart_type_on_empty() {
        WC()->session->set('cart_type', "");
        WC()->session->set('is_meal', 0);
	}

	public function check_cart_type_on_remove($cart_item_key, $cart) {
		if(!$cart->is_empty()) {
			$cart_type = WC()->session->get('cart_type');
			$i = 0;
			foreach ($cart->get_cart() as $key => $cart_item) {
		    	$item_id = empty($cart_item['variation_id']) ? $cart_item['product_id'] : $cart_item['variation_id'];
		    	$item_type = get_post_meta($item_id, "_shipping_type", 1);
		    	if($item_type != "both") {
		    		$i = $i+1;
		    		break;
		    	}
			}
			if($i == 0) {
				WC()->session->set('cart_type', 'both');
			}
		}
	}

	private function _check_specific_delivery_postcodes($postcode) {
		$option = get_option("peter_timbs");
		$specific_postcodes = [];
		$sunday = empty($option['sunday_delivery_postcodes']) ? [] : explode(",", $option['sunday_delivery_postcodes']);
		$monday = empty($option['monday_delivery_postcodes']) ? [] : explode(",", $option['monday_delivery_postcodes']);
		$tuesday = empty($option['tuesday_delivery_postcodes']) ? [] : explode(",", $option['tuesday_delivery_postcodes']);
		$wednesday = empty($option['wednesday_delivery_postcodes']) ? [] : explode(",", $option['wednesday_delivery_postcodes']);
		$thursday = empty($option['thursday_delivery_postcodes']) ? [] : explode(",", $option['thursday_delivery_postcodes']);
		$friday = empty($option['friday_delivery_postcodes']) ? [] : explode(",", $option['friday_delivery_postcodes']);
		$saturday = empty($option['saturday_delivery_postcodes']) ? [] : explode(",", $option['saturday_delivery_postcodes']);
		
		$north_island_postcodes = empty($option['north_island_postcodes']) ? [] : explode(",", $option['north_island_postcodes']['post_code']);
		$south_island_postcodes = empty($option['south_island_postcodes']) ? [] : explode(",", $option['south_island_postcodes']['post_code']);

		$week_days = [0, 1, 2, 3, 4, 5, 6];
		foreach ($sunday as $value) {
			$specific_postcodes[$value][] = 0;
		}
		foreach ($monday as $value) {
			$specific_postcodes[$value][] = 1;
		}
		foreach ($tuesday as $value) {
			$specific_postcodes[$value][] = 2;
		}
		foreach ($wednesday as $value) {
			$specific_postcodes[$value][] = 3;
		}
		foreach ($thursday as $value) {
			$specific_postcodes[$value][] = 4;
		}
		foreach ($friday as $value) {
			$specific_postcodes[$value][] = 5;
		}
		foreach ($saturday as $value) {
			$specific_postcodes[$value][] = 6;
		}
		$final_data = [];
		foreach ($specific_postcodes as $key => $days) {
			$final_data[$key] = array_values( array_diff($week_days, $days) );
		}

		// if(empty($final_data[$postcode])){
		// 	if( in_array($postcode,$north_island_postcodes) || in_array($postcode,$south_island_postcodes) )
		// 	{   
		// 	    $final_data[$postcode] = $postcode;
		// 	}
		// }
		$return = empty($final_data[$postcode]) ? [] : $final_data[$postcode];
		return $return;
	}
}

$delivery_public = new Delivery_Public();
