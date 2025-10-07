<?php

class Petertimbs_meals_filter
{
public 	function __construct() {
		add_action( 'woocommerce_before_single_product', [$this, 'check_product_availability_during_set_date'], 10);
		add_action( "nectar_quick_view_summary_content", [$this, "remove_quick_view"], 1 );
		add_filter( "woocommerce_product_categories_widget_args", [$this, "hide_meal_category"] );
	}

	public function check_product_availability_during_set_date() {
		global $post;
		$today = strtotime('now');

		$date_set_from = get_post_meta( $post->ID, 'date_product_from', true);
		$start_date = strtotime(date("Y-m-d 00:00:00", strtotime($date_set_from)));

		$date_set_to = get_post_meta( $post->ID, 'date_product_to', true);
		$end_date = strtotime(date("Y-m-d 23:59:59", strtotime($date_set_to)));

		if (!empty($date_set_from)) {
			if ($today >= $start_date && $today <= $end_date) {

			} else {
				if ($start_date > $today) {
					?>
					<div class="woocommerce-message custom-woocommerce-message-height" role="alert">
						This item can be picked up from <?php echo $date_set_from ?> to <?php echo $date_set_to ?>.
					</div>
					<?php 
					// remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
				}
				if($end_date < $today){
					?>
					<div class="woocommerce-message custom-woocommerce-message-height" role="alert">
						This item is no longer for sale.
					</div>
					<?php
					remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
				}
			}
		}
	}

	public function remove_quick_view() {
	    global $post;
	    $today = strtotime('now');
	    $date_set_from = get_post_meta( $post->ID, 'date_product_from', true);
	    $start_date = strtotime(date("Y-m-d 00:00:00", strtotime($date_set_from)));

	    $date_set_to = get_post_meta( $post->ID, 'date_product_to', true);
	    $end_date = strtotime(date("Y-m-d 23:59:59", strtotime($date_set_to)));

	    if(!empty($date_set_from)) {
			if ($today >= $start_date && $today <= $end_date) {
	            remove_action('nectar_quick_view_summary_content','woocommerce_template_single_title');
	            add_action('nectar_quick_view_summary_content','woocommerce_template_single_title', 8);
	        } else {
	            remove_action('nectar_quick_view_summary_content','woocommerce_template_single_title');
	            add_action('nectar_quick_view_summary_content','woocommerce_template_single_title', 8);
	            if($today < $start_date) {
		            add_action('nectar_quick_view_summary_content', [$this, 'display_date_notice_available'], 9);
	            }
	            if($today > $end_date) {
	            	add_action('nectar_quick_view_summary_content', [$this, 'display_date_notice_unavailable'], 9);
		            remove_action('nectar_quick_view_summary_content','woocommerce_template_single_add_to_cart');
	            }
	        }   
	    }
	}

	public function display_date_notice_unavailable() {
	    ?>
	    <span style="color: #3452FF">This item is no longer for sale.</span>
	    <?php
	}

	public function display_date_notice_available() {
	    global $post;
	    $date_set_from = get_post_meta( $post->ID, 'date_product_from');
	    $date_set_to = get_post_meta( $post->ID, 'date_product_to');
	    ?>
	    <span style="color: #3452FF">This item can be picked up from <?php echo $date_set_from[0] ?> to <?php echo $date_set_to[0] ?>.</span>
	    <?php
	}

	public function hide_meal_category($args) {
		$meal = get_term_by( "slug", "meals", "product_cat" );
		$args['exclude'] = $meal->term_id;
		return $args;
	}
}

$petertimbs_meals_filter = new Petertimbs_meals_filter;