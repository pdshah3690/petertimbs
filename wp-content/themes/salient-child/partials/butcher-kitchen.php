<?php 
	$includes = ["deli-products", "grazing-boxes", "pies", "ready-to-eat-meals", "ready-to-heat-meals", "salads"];
	$meal_tags = get_terms( 'meals_tag');
?>
<div class="butcher_kitchen_product_list">
	<div id="sidebar" class="col span_3 left-sidebar butcher_kitchen_left_side">
	    <div id="woocommerce_product_search-2" class="widget woocommerce widget_product_search">
	        <h4>SEARCH PRODUCTS</h4>
	        <div class="widget_search">
	            <form role="search" method="get" class="woocommerce-product-search search-form" action="http://www.petertimbs.jadecreative.co.nz/">
	                <label class="screen-reader-text" for="woocommerce-product-search-field-0">Search for:</label>
	                <input type="search" id="woocommerce-product-search-field-0" class="search-field" placeholder="Search products…" value="" name="s">
	                <button type="submit" class="search-widget-btn"><span class="normal icon-salient-search" aria-hidden="true"></span></button>
	                <input type="hidden" name="post_type" value="product">	
	            </form>
	        </div>
	    </div>
	    <div id="woocommerce_product_categories-2" class="widget woocommerce widget_product_categories">
	        <h4>FILTER BY TYPES OF MEALS</h4>
	        <ul class="product-categories">
	        	<?php 
	        		foreach($includes as $slug) {
						$term = get_term_by( "slug", $slug, "product_cat" );
						// echo $term->count;
						?>
	            		<li class="cat-item cat-item-44"><a href="<?php echo site_url(); ?>/product-category/<?php echo $term->slug; ?>"><?php echo ucwords($term->name); ?> </a></li>
						<?php
					}
	        	?>
	        </ul>
	        <h4>Meal Tags</h4>
	        <ul class="product-categories">
	        	<?php 
	        		foreach($meal_tags as $tag) {
						?>
	            		<li class="cat-item cat-item-44"><a href="<?php echo site_url(); ?>/meals_tag/<?php echo $tag->slug; ?>"><?php echo ucwords($tag->name); ?> </a></li>
						<?php
					}
	        	?>
	        </ul>
	    </div>
	</div>

	<div class="butcher_kitchen_right_side">
		<?php get_sidebar(); ?>
		<div class="woocommerce columns-<?php echo esc_attr( $columns ); ?>">
			<?php 
				if ( $loop->have_posts() ) {
					do_action( 'woocommerce_before_shop_loop' );
					woocommerce_product_loop_start();
					// if ( wc_get_loop_prop( 'total' ) ) {
						while ( $loop->have_posts() ) {
							$loop->the_post();
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						}
					// }
					wp_reset_postdata();
					woocommerce_product_loop_end();
					do_action( 'woocommerce_after_shop_loop' );
				} else {
					do_action( 'woocommerce_no_products_found' );
				}
				$total_pages = $loop->max_num_pages;

			    if ($total_pages > 1){

			        $current_page = max(1, get_query_var('paged'));
			        ?> <nav class="woocommerce-pagination"> <?php
			        echo paginate_links(array(
			            'base' => get_pagenum_link(1) . '%_%',
			            'format' => '/page/%#%',
			            'current' => $current_page,
			            'total' => $total_pages,
			            'end_size'           => 3,
 						'mid_size'           => 3,
			            'prev_text'    => __('Previous'),
			            'next_text'    => __('Next'),
			            'type' => 'list',
			        ));
			        ?>
			    	</nav> <?php 
			    }
			?>
		</div>	
	</div>
</div>