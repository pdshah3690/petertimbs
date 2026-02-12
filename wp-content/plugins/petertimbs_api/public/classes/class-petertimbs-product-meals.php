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

class Petertimbs_Product_Meals {

	public function index($request) {
     	$this->user = pt_validate_request($request);

     	remove_action('woocommerce_product_query', 'petertimbs_site_product_filter');

     	$page = empty($request->get_param('page')) ? 1 : $request->get_param('page');
     	$limit = empty($request->get_param('limit')) ? 10 : $request->get_param('limit');

        $args = [
            "post_type" => "product",
            "post_status" => "publish",
            "posts_per_page" => $limit,
            "paged" => $page,
            // "order" => "DESC",
            // "orderby"   => "ID",
            "order" => "ASC",
            "orderby"   => "menu_order",
            "fields" => "ids",
		    "paging" => true
        ];

        $args['meta_query']['relation'] = 'AND';
        $args['meta_query'][] = [
            'key' => '_hidden_on_app',
            'value' => '1',
            'compare' => '!='
        ];

        $args['tax_query'][] = [
        	'taxonomy' => 'product_cat',
        	'field' => 'slug',
        	'terms' => 'meals',
        	'operator' => 'IN',
        ];

        if(!empty($request->get_param('meat_cut'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "pa_meat-types",
                'field' => 'slug',
                'terms' => $request->get_param('meat_cut'),
                'operator' => 'AND'
            ];
        }

		if(!empty($request->get_param('meat_types'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "product_cat",
                'field' => 'slug',
                'terms' => $request->get_param('meat_types'),
                'operator' => 'AND'
            ];
        }
        if(!empty($request->get_param('filter_by')) && $request->get_param('filter_by') == "meals" ) {

			$args['tax_query']['relation'] = 'AND';
			$args['tax_query'][] = [
				'taxonomy' => "product_cat",
				'field' => 'slug',
				'terms' => 'meals',
				'operator' => 'AND'
			];
		} else {
			$args['tax_query']['relation'] = 'AND';
			$args['tax_query'][] = [
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => 'meals',
				'operator' => 'IN',
			];
		}

		if(!empty($request->get_param('meal_types'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "product_cat",
                'field' => 'slug',
                'terms' => $request->get_param('meal_types'),
                'operator' => 'IN'
            ];
        }

		if(!empty($request->get_param('meals_tag'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "meals_tag",
                'field' => 'slug',
                'terms' => $request->get_param('meals_tag'),
                'operator' => 'IN'
            ];
        }



        $data = new WP_Query($args);
    	$response['success'] = true;
    	$response['total_products'] = $data->found_posts;
    	$response['max_num_pages'] = $data->max_num_pages;
    	$response['meals'] = [];

    	foreach($data->posts as $d) {
    		$product = wc_get_product($d);
            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_ID() ), 'woocommerce_gallery_thumbnail' );
            $image = empty($image) ? "" : $image[0];
            $stock_status = $product->get_stock_status();
            $price = html_entity_decode(strip_tags($product->get_price_html()));
			$sale_price = empty($product->get_sale_price()) ? "" : html_entity_decode(get_woocommerce_currency_symbol().$product->get_sale_price());
            $is_meal = false;
			if (has_term( 'meals', 'product_cat',  $product->get_ID())){
				$is_meal = true;
			}
			$tags = get_the_terms($product->get_ID(),'meals_tag');

			foreach($tags as $tag){
				$list[] = [
					"name" => $tag->name,
					"slug" => $tag->slug,
				];
			}


    		$response['meals'][] = [
    			"ID"   => $product->get_ID(),
    			"name" => $product->get_title(),
    			"price" => $price,
    			"sale_price" => $sale_price,
    			"image" => $image,
    			"description" => wp_strip_all_tags($product->get_description()),
    			"stock_status" => $stock_status,
    			"is_meal" => $is_meal,
    			"tag" => $list,
    		];
    		$list= [];
    	}

        wp_send_json($response, 200);
        wp_die();
	}

	public function filters($request)
	{
		pt_validate_request($request);

		$response['success'] = true;
		$meat_cut = get_terms([
			'taxonomy'	 => 'pa_meat-types'
		]);

		$includes = ["deli-products", "grazing-boxes", "pies", "ready-to-eat-meals", "ready-to-heat-meals", "salads"];

		foreach($includes as $slug) {
			$term = get_term_by( "slug", $slug, "product_cat" );
			$meat_types_list[] = [
				"name" => ucwords($term->name),
				"slug" => $term->slug,
			];
		}

		$tags = get_tags(array(
		  	'taxonomy' => 'meals_tag',
		  	'orderby' => 'name',
		  	'hide_empty' => false
		));
		foreach($tags as $tag) {
			$meals_tags[] = [
				"name" => $tag->name,
				"slug" => $tag->slug,
			];
		}

		$response['filters'][] = [
			"name" => "Meal Types",
			"key"  => "meal_types",
			"list" => $meat_types_list,
		];
		$response['filters'][] = [
			"name" => "Tags",
			"key"  => "meals_tag",
			"list" => $meals_tags,
		];

		wp_send_json($response, 200);
		wp_die();
	}

	public function view($request) {
		pt_validate_request($request);
		$product = wc_get_product($request->get_param("id"));
		$variations = [];
		if($product->get_type() === "variable") {
			$attributes = new WC_Product_Variable($product);
			foreach ($attributes->get_available_variations() as $v) {
				$is_hidden = get_post_meta($v['variation_id'], '_hidden_on_app', true);
                $shipping_type = get_post_meta($v['variation_id'], "_shipping_type", true);
				$shipping_type = empty($shipping_type) ? "both" : $shipping_type;
				if($is_hidden == 1) {
					continue;
				}
				$variations[] = [
					"variation_id" => $v['variation_id'],
					"attributes" => $v['attributes'],
					"display_price" => "$". $v['display_price'],
					"display_regular_price" => "$".$v['display_regular_price'],
					"image"	=> $v['image']['gallery_thumbnail_src'],
					"max_qty" => $v['max_qty'],
					"min_qty" => $v['min_qty'],
					"shipping_type" => $shipping_type
				];
			}
		}
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_ID() ), 'woocommerce_gallery_thumbnail' );
        $image = empty($image) ? "" : $image[0];

        $is_purchasable = true;
		$from_date = get_post_meta($product->get_ID(), 'date_product_from', true);
		$start_date = strtotime(date("Y-m-d 00:00:00", strtotime($from_date)));
        $to_date = get_post_meta($product->get_ID(), "date_product_to", true);
        $end_date = strtotime(date('Y-m-d 23:59:59', strtotime($to_date)));
        $today = strtotime('now');
        $message = "";
        if (!empty($from_date)) {
        	if ($today <= $end_date && $today >= $start_date) {
	        	$message = 'This item can be picked up from '.$from_date.' to '.$to_date.'.';
	        } else {
	        	$message = 'This item is no longer for sale.';
	        	$is_purchasable = false;
	        }
        }

        $shipping_type = get_post_meta($product->get_ID(), "_shipping_type", true);
        $shipping_type = empty($shipping_type) ? "both" : $shipping_type;
    	$response['success'] = true;
		$response['product'] = [
			"ID"   => $product->get_ID(),
			"name" => $product->get_title(),
			"type" => $product->get_type(),
			"price" => "$".$product->get_price(),
			"sale_price" => "$".$product->get_sale_price(),
			"image" => $image,
			"description" => strip_tags($product->get_description()),
			"variations"  => $variations,
			"message" => $message,
			"is_purchasable" => $is_purchasable,
		];
		if(empty($variations)) {
			$response['product']['shipping_type'] = $shipping_type;
		}
		$response['product']['is_meal'] = true;
        wp_send_json($response, 200);
        wp_die();
	}
}