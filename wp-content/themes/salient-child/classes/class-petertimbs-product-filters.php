<?php
defined( 'ABSPATH' ) || exit;

class Petertimbs_product_filters
{
	function __construct()
	{
		add_filter('woocommerce_shortcode_products_query', [$this, 'petertimbs_home_page_filter'], 9999, 3);
		add_filter('woocommerce_shortcode_products_query', [$this, 'petertimbs_hide_product']);
		add_action( 'woocommerce_product_query', [$this, 'hide_meals_from_site'] );
		add_filter('woocommerce_related_products', [$this, 'hide_meals_from_relative_posts'], 99, 1);
		add_filter( 'get_terms', [$this, 'exclude_category'], 10, 3 );
	}

	function petertimbs_home_page_filter($args, $atts, $type) {
	    global $post;
	    $args['meta_query'] = [
	        'key' => '_hidden_on_site',
	        'value' => '1',
	        'compare' => '!='
	    ];
	    $ids = get_hidden_product_ids();
	    $args['post__not_in'] = $ids;
	    if($post->post_name != 'meals')
	    {
	        $args['tax_query'] = [
	            [
	    	           'taxonomy' => 'product_cat',
	    	           'field'    => 'slug',
	    	           'terms'    => 'meals',
	    	           'operator' => 'NOT IN'
	    	    ]
	    	];
	    }
	    $args['berocket_filtered'] = 0;
	    return $args;
	}

	function petertimbs_hide_product($args) {
	    global $post;
	    $ids = get_hidden_product_ids();
	    $args['post__not_in'] = $ids;
	    if($post->post_name != 'meals')
	    {
	        $args['tax_query'] = array(
	            [
	    	           'taxonomy' => 'product_cat',
	    	           'field'    => 'slug',
	    	           'terms'    => 'meals',
	    	           'operator' => 'NOT IN'
	    	           ]
	    	    );
	    }
	    return $args;
	}

	function hide_meals_from_site( $q ) {
	    $category = get_queried_object();
	    $ids = [];
	    if($category->slug == 'meals') {
			$ids = get_hidden_product_ids(false);	    	
	    } else {
		    $ids = get_hidden_product_ids();
	    }
	    // if (is_shop() || is_product_category()) {
	    // 	set_query_var( 'post__not_in', $ids );
	    // }
	    if (is_shop() || is_product_category()) {
	    	set_query_var( 'post__not_in', $ids );
	    }
	    // if($category->slug != 'meals'){
	    //     $tax_query = (array) $q->get( 'tax_query' );
	        
	    //     $tax_query[] = array(
	    //            'taxonomy' => 'product_cat',
	    //            'field'    => 'slug',
	    //            'terms'    => array('meals'),
	    //            'operator' => 'NOT IN'
	    //     );
	    //     $q->set( 'tax_query', $tax_query );
	    // }
	    if(is_shop()){
	    	
		    if($category->slug != 'meals'){
		        $tax_query = (array) $q->get( 'tax_query' );
		        
		        $tax_query[] = array(
		               'taxonomy' => 'product_cat',
		               'field'    => 'slug',
		               'terms'    => array('meals'),
		               'operator' => 'NOT IN'
		        );
		        $q->set( 'tax_query', $tax_query );
		    }
	    }



	}

	function hide_meals_from_relative_posts($args)
	{
		$ids = get_hidden_product_ids();
	    foreach($args as $product_id)
	    {
	        $is_meals = has_term('meals', 'product_cat', $product_id);
	        if($is_meals == true){
	            $meal_array[] = $product_id;
	        }
	    }
	    if(empty($meal_array))
	    {
	    	$args = array_diff($args, $ids);
	        return $args;
	    }
	    else{
	    	$args = array_diff($args, $ids);
	        $args = array_diff($args, $meal_array);
	        return $args;    
	    }
	        
	}

	function exclude_category( $terms, $taxonomies, $args )
	{
		$ignore = ["meals", "butchers-box", "deli-products", "grazing-boxes", "pies", "ready-to-eat-meals", "ready-to-heat-meals", "salads"];
		$new_terms = array();
		// if ( in_array( 'product_cat', $taxonomies ) && is_shop() ) {
		// 	foreach ( $terms as $key => $term ) {
		//     	if ( ! in_array( $term->slug, $ignore ) ) {
		//         	$new_terms[] = $term;
		//       	}
		//     }
		//     $terms = $new_terms;
		// }
		$terms_slug = get_queried_object()->slug;
		if ( in_array( 'product_cat', $taxonomies ) && is_shop() || is_product_category()  || is_product_tag() ) {
			// if( in_array( $terms_slug, $ignore ) ){
			// 	foreach ( $terms as $key => $term ) {
			//     	if ( in_array( $term->slug, $ignore ) ) {
			//     		$new_terms[] = $term;
			//       	}
			//     }
			// }else{
				foreach ( $terms as $key => $term ) {
			    	if ( ! in_array( $term->slug, $ignore ) ) {
			        	$new_terms[] = $term;
			      	}
			    }
			// }
	    	$terms = $new_terms;
		}
		return $terms;
	}

}

$petertimbs_product_filters = new Petertimbs_product_filters;