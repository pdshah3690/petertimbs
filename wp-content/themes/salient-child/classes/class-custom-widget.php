<?php
// Creating the widget
class cw_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
        // Base ID of your widget
            'cw_widget',
        // Widget name will appear in UI
            __('Meals Filter Widget', 'cw_widget_domain'),
        // Widget description
            array(
            'description' => __('Custom Meals Filter Widget', 'cw_widget_domain')
        ));
    }
    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        // before and after widget arguments are defined by themes
        
        echo $args['before_widget'];
        if (!empty($title))
        // echo $args['before_title'] . $title . $args['after_title'];

        // This is where you run the code and display the output
        $includes = ["deli-products", "grazing-boxes", "pies", "ready-to-eat-meals", "ready-to-heat-meals", "salads"];
        $terms_slug = get_queried_object()->slug;
        $taxonomy_slug = get_query_var( 'taxonomy' );
        if( is_admin() || in_array( $terms_slug, $includes ) || $taxonomy_slug == 'meals_tag' ){
            $title_meals_search = "SEARCH MEALS";
            echo $args['before_title'] . $title_meals_search . $args['after_title'];        

            $html = '<div id="woocommerce_product_search-2" class="widget woocommerce widget_product_search">
                        <div class="widget_search">
                            <form role="search" method="get" class="woocommerce-product-search search-form" action="'.site_url().'">
                                <label class="screen-reader-text" for="woocommerce-product-search-field-0">Search for:</label>
                                <input type="search" id="woocommerce-product-search-field-0" class="search-field" placeholder="Search Meals…" value="" name="s">
                                <button type="submit" class="search-widget-btn"><span class="normal icon-salient-search" aria-hidden="true"></span></button>
                                <input type="hidden" name="post_type" value="product">  
                            </form>
                        </div>
                    </div>';

            echo __($html, 'cw_widget_domain');

            $title_meals_type = "FILTER BY TYPES OF MEALS";
            echo $args['before_title'] . $title_meals_type . $args['after_title'];
            
            
            $html = '<ul class="product-categories">';
            foreach($includes as $slug) {
                $term = get_term_by( "slug", $slug, "product_cat" );
                $html .= '<li class="cat-item cat-item-44"><a href="'.site_url().'/product-category/'.$term->slug.'">'.ucwords($term->name).'</a></li>';
            }
            $html .= '</ul>';
            echo __($html, 'cw_widget_domain');

            $title_meals_tags = "Meal Tags";
            $meal_tags = get_terms( 'meals_tag');

            echo $args['before_title'] . $title_meals_tags . $args['after_title'];
            $html = '<ul class="product-categories">';
            foreach($meal_tags as $tag) {
                $html .= '<li class="cat-item cat-item-44"><a href="'.site_url().'/meals_tag/'.$tag->slug.'">'.ucwords($tag->name).'</a></li>';
            }
            $html .= '</ul>';
            echo __($html, 'cw_widget_domain');
        }
        
        
        // echo __($title, 'cw_widget_domain');
        echo $args['after_widget'];
    }
    // Widget Backend
    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'cw_widget_domain');
        }
      // Widget admin form
        ?>
        <label for="<?php
                echo $this->get_field_id('title');
        ?>"><?php
                _e('Title:');
        ?>
        <input class="widefat" id="<?php
                echo $this->get_field_id('title');
        ?>" name="<?php
                echo $this->get_field_name('title');
        ?>" type="text" value="<?php
                echo esc_attr($title);
        ?>" />
        <?php
    }
    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance          = array();
        $instance['title'] = (!empty($new_instance['title'])) 
                              ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
} // Class cw_widget ends here
// Register and load the widget
function cw_load_widget()
{
    register_widget('cw_widget');
}
add_action('widgets_init', 'cw_load_widget');