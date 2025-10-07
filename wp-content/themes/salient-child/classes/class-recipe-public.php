<?php 
defined( 'ABSPATH' ) || exit;

Class Recipe_Public {

	public function __construct() {
        $this->recipe_page_handler();
	}

    public function recipe_page_handler() {
        add_action('wp_enqueue_scripts', [$this, 'recipe_public_script']);
        add_shortcode('render_recipes', [$this, 'render_recipes_func']);
        add_action('get_recipe_sidebar', [$this, 'get_recipe_sidebar']);
        add_action('get_recipe_ratings_html', [$this, 'get_recipe_ratings_html'], 10, 1);
        $this->_recipe_ajax_handler();
    }

    private function _recipe_ajax_handler()
    {
        add_action('wp_ajax_get_recipes_by_filters', [$this, 'get_recipes_by_filters']);
        add_action('wp_ajax_nopriv_get_recipes_by_filters', [$this, 'get_recipes_by_filters']);
    }

	public function render_recipes_func() {
        $page = empty(get_query_var('paged')) ? 1 : get_query_var('paged');
        $args = [
            "post_type" => "recipe",
            "post_status" => "publish",
            "posts_per_page" => 9,
            "paged" => $page,
            "order" => "DESC",
            "orderby"   => "ID"
        ];
        $recipes = new WP_Query($args);
        ob_start();
        include_once dirname( __FILE__ ).( '/../partials/recipe-template.php');
        wp_reset_postdata();
        return ob_get_clean();
    }

    public function get_recipe_sidebar() {
        $allergens = get_terms([
            'taxonomy' => 'allergen',
            'public' => true
        ]);
        $args = array(
            'hide_empty' => true, // also retrieve terms which are not used yet
            'meta_query' => array(
                array(
                   'key'       => 'is_master_ingredient',
                   'value'     => '1'
                )
            ),
            'taxonomy'  => 'ingredient',
        );
        $ingredients = get_terms( $args );

        $meats = get_terms([
            'taxonomy' => 'meat',
            'public' => true
        ]);

        $difficulties = get_terms([
            'taxonomy' => 'difficulty',
            'public' => true
        ]);

        ob_start();
        include_once dirname( __FILE__ ).( '/../partials/recipe-sidebar.php');
        echo ob_get_clean();
    }

    public function recipe_public_script() {
        wp_enqueue_style( 'star-ratings', get_stylesheet_directory_uri() . '/css/star-rating.min.css', '', $nectar_theme_version );
        wp_enqueue_script('star-ratings', get_stylesheet_directory_uri() . '/js/star-rating.min.js', array('jquery'), null, true);
        wp_enqueue_script('recipe-public', get_stylesheet_directory_uri()."/js/recipe-public.js", array('jquery'), null, true);
    }

    public function get_recipes_by_filters() {
        $args = [
            'post_type'    => 'recipe',
            'post_status'  => 'publish',
            'fields'       => 'ids',
            'posts_per_page' => -1
        ];
        if(!empty($_POST['allergens'])) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "allergen",
                'field' => 'id',
                'terms' => $_POST['allergens'],
                'operator' => 'AND'
            ];
        }
        if(!empty($_POST['meat'])) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "meat",
                'field' => 'id',
                'terms' => $_POST['meat'],
                'operator' => 'AND'
            ];
        }
        if(!empty($_POST['difficulty'])) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "difficulty",
                'field' => 'id',
                'terms' => $_POST['difficulty'],
                'operator' => 'AND'
            ];
        }
        if(!empty($_POST['serving_size'])) {
            $count = explode("-", $_POST['serving_size']);
            $min = $count[0];
            $max = $count[1];
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = [
                'key'     => 'recipe_servings',
                'value'   => array( $min, $max ),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        }
        if(!empty($_POST['cook_time'])) {
            $count = explode("-", $_POST['cook_time']);
            $min = $count[0];
            $max = $count[1];
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = [
                'key'     => 'recipe_cook_time',
                'value'   => array( $min, $max ),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        }
        if(!empty($_POST['ratings'])) {
            $half = $_POST['ratings'] + 0.49;
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = [
                'key'     => 'recipe_user_ratings_rating',
                'value'   => [$_POST['ratings'], $half],
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
                'nopaging' => true
            ];
        }
        if(!empty($_POST['ingredient_no'])) {
            $count = explode("-", $_POST['ingredient_no']);
            $min = $count[0];
            $max = $count[1];
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = [
                'key'     => 'no_of_ingredients',
                'value'   => array( $min, $max ),
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        }

        $recipes = get_posts($args);
        $html = $this->_render_recipe_list($recipes);
        echo $html;
        die();
    }

    private function _render_recipe_list($recipes)
    {
        $html = "";
        foreach ($recipes as $r) {
            $difficulty = get_the_terms($r, "difficulty");
            $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;

            $servings = get_post_meta($r, "recipe_servings", true);
            $cook_time = get_post_meta($r, "recipe_cook_time", true)." mins";

            $rating_obj = new WPURP_User_Ratings;
            $rating = $rating_obj->get_recipe_rating($r);

            $rat_html = '<div class="recipe-ratings-wrapper">';
            for($i=1; $i<=5; $i++) {
                if( $i <= $rating['stars'] ) {
                    $icon = "fa-star";
                } else if( $i-1 == $rating['stars'] && $rating['half_star'] == true ) {
                    $icon = "fa-star-half-o";
                }  else {
                    $icon = "fa-star-o";
                }

                $rat_html .= '<i data-value="'.$i.'" class="wpurp-star fa ' . esc_attr( $icon ) . '" data-original="' . esc_attr( $icon ) . '"></i>';
            }
            $rat_html .= '</div>';
            $html .= '<article id="post-'.$r.'" class=" masonry-blog-item post-'.$r.' post type-post status-publish format-standard has-post-thumbnail category-general">  
                      <div class="inner-wrap">
                        <div class="post-content">
                          <div class="content-inner">
                            <a class="entire-meta-link" href="'.get_permalink($r).'"></a>        
                            <span class="post-featured-img" style="background-image: url('.wp_get_attachment_url(get_post_thumbnail_id($r)).');"></span>
                            <div class="article-content-wrap">
                                <div class="post-header">
                                    <h3 class="title"><a href="'.get_permalink($r).'">'.get_the_title($r).'</a></h3>
                                </div>
                                '.$rat_html.'
                                <div class="recipe-meta-details">
                                    <span><img class="recipe-detail-icon" src="'.get_stylesheet_directory_uri().'/img/serving-size-light.png">'.$servings.'</span>
                                    <span><img class="recipe-detail-icon" src="'.get_stylesheet_directory_uri().'/img/cook-time-light.png">'.$cook_time.'</span>
                                </div>
                                <div class="recipe-meta-details">
                                    <span><img class="recipe-detail-icon" src="'.get_stylesheet_directory_uri().'/img/difficulty-light.png">'.$difficulty.'</span>
                                </div>
                            </div><!--article-content-wrap-->
                          </div><!--/content-inner-->
                        </div><!--/post-content-->
                      </div><!--/inner-wrap-->
                    </article>';
        }
        if(empty($html)) {
            $html = "<p>Sorry, there are no recipes that match your preferences, please try again.</p>";
        }
        return $html;
    }

    public function get_recipe_ratings_html($recipe_id) {
        $rating_obj = new WPURP_User_Ratings;
        $rating = $rating_obj->get_recipe_rating($recipe_id);
        echo '<div class="recipe-ratings-wrapper">';
        for($i=1; $i<=5; $i++) {
            if( $i <= $rating['stars'] ) {
                $icon = "fa-star";
            } else if( $i-1 == $rating['stars'] && $rating['half_star'] == true ) {
                $icon = "fa-star-half-o";
            }  else {
                $icon = "fa-star-o";
            }

            echo '<i data-value="'.$i.'" class="wpurp-star fa ' . esc_attr( $icon ) . '" data-original="' . esc_attr( $icon ) . '"></i>';
        }
        echo '</div>';
    }
}
new Recipe_Public();