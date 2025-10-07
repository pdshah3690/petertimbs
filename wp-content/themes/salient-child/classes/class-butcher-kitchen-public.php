<?php 
defined( 'ABSPATH' ) || exit;

Class Butcher_Kitchen_Public {
	public function __construct() {
		add_shortcode('render_butcher_kitchen', [$this, 'render_butcher_kitchen_func']);
	}

	public function render_butcher_kitchen_func() {
		global $post;
		$page = empty(get_query_var('paged')) ? 1 : get_query_var('paged');
		$args = [
            "post_type" => "product",
            "post_status" => "publish",
            "posts_per_page" => 12,
            "paged" => $page,
            "order" => "ASC",
            "orderby"   => "menu_order",
            'tax_query' => array(
                array(
                    'taxonomy'  => 'product_cat',
                    'field'     => 'slug',
                    'terms'     => array('meals'),
                )
            )
        ];

		$loop = new WP_Query($args);
		wc_set_loop_prop( 'columns', 3 );
		$columns = wc_get_loop_prop( 'columns' );

		ob_start();
        include_once dirname( __FILE__ ).( '/../partials/butcher-kitchen.php');
        wp_reset_postdata();
        return ob_get_clean();
	}
}
$butcher_box_public = new Butcher_Kitchen_Public();