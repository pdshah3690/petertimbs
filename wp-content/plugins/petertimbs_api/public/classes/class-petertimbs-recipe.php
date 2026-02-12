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

class Petertimbs_Recipe {

	public function index($request) {
     	$this->user = pt_validate_request($request);

     	$page = empty($request->get_param('page')) ? 1 : $request->get_param('page');
     	$limit = empty($request->get_param('limit')) ? 10 : $request->get_param('limit');

        $args = [
            "post_type" => "recipe",
            "post_status" => "publish",
            "posts_per_page" => $limit,
            "paged" => $page,
            "order" => "DESC",
            "orderby"   => "ID",
            "paging" => true
        ];
        if(!empty($request->get_param('allergens'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "allergen",
                'field' => 'slug',
                'terms' => $request->get_param('allergens'),
                'operator' => 'AND'
            ];
        }
        if(!empty($request->get_param('meat'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "meat",
                'field' => 'slug',
                'terms' => $request->get_param('meat'),
                'operator' => 'AND'
            ];
        }
        if(!empty($request->get_param('difficulty'))) {
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][] = [
                'taxonomy' => "difficulty",
                'field' => 'slug',
                'terms' => $request->get_param('difficulty'),
                'operator' => 'AND'
            ];
        }
        if(!empty($request->get_param('serving_size'))) {
            $count = explode("-", $request->get_param('serving_size'));
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
        if(!empty($request->get_param('cook_time'))) {
            $count = explode("-", $request->get_param('cook_time'));
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
        if(!empty($request->get_param('ratings'))) {
            $half = $request->get_param('ratings') + 0.49;
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = [
                'key'     => 'recipe_user_ratings_rating',
                'value'   => [$request->get_param('ratings'), $half],
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        }

        if(!empty($request->get_param('ingredient_no'))) {
            $count = explode("-", $request->get_param('ingredient_no'));
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

        $recipes = new WP_Query($args);
        $response['success'] = true;
        $response['max_num_pages'] = $recipes->max_num_pages;
        $response['recipes'] = [];
        foreach ($recipes->posts as $r) {
            $difficulty = get_the_terms($r->ID, "difficulty");
            $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;

            $servings = get_post_meta($r->ID, "recipe_servings", true);
            $cook_time = get_post_meta($r->ID, "recipe_cook_time", true)." ".get_post_meta($r->ID, "recipe_cook_time_text", true);
            $rating = get_post_meta($r->ID, "recipe_user_ratings_rating", true);

        	$response['recipes'][] = [
				"ID"   => $r->ID,
				"name" => $r->post_title,
				"image" => wp_get_attachment_url(get_post_thumbnail_id($r->ID)),
				"difficulty" => $difficulty,
				"servings"   => $servings,
				"cook_time"  => $cook_time,
				"rating"	 => $rating
        	];
        }
        wp_reset_postdata();
        wp_send_json($response, 200);
        wp_die();
    }

    public function filters($request) {
     	$this->user = pt_validate_request($request);

        $meats = get_terms([
            'taxonomy' => 'meat',
            'public' => true
        ]);

        $difficulty = get_terms([
            'taxonomy' => 'difficulty',
            'public' => true
        ]);

        $allergens = get_terms([
            'taxonomy' => 'allergen',
            'public' => true
        ]);

        $response['success'] = true;
        $difficulty_list = $allergen_list = $meat_list = [];
        foreach ($meats as $m) {
        	$meat_list[] = [
        		"name" => $m->name,
        		"slug" => $m->slug,
        	];
        }
        $response['filters'][] = [
            "name" => "MEAT TYPE",
            "key"  => "meat",
            "list" => $meat_list
        ];

        foreach ($allergens as $a) {
            $icon = get_field("icon", "allergen_".$a->term_id);
        	$allergen_list[] = [
        		"name" => html_entity_decode($a->name),
        		"slug" => $a->slug,
                "icon" => $icon['url']
        	];
        }
        $response['filters'][] = [
            "name" => "DIETARY NEEDS",
            "key"  => "allergens",
            "list" => $allergen_list
        ];

        foreach ($difficulty as $d) {
        	$difficulty_list[] = [
        		"name" => $d->name,
        		"slug" => $d->slug,
        	];
        }
        $response['filters'][] = [
            "name" => 'NUMBER OF INGREDIENTS',
            "key"  => 'ingredient_no',
            "list" => [
            	["slug" => "1-5", "name" => "1-5",],
    			["slug" => "6-10", "name" => "6-10",],
    			["slug" => "11-15", "name" => "11-15",],
    			["slug" => "16-20", "name" => "16-20",],
    			["slug" => "21-25", "name" => "21-25",],
    			["slug" => "26-", "name" => "26+",]
            ]
        ];
		$response['filters'][] = [
            "name" => 'COOK TIME',
            "key" =>  'cook_time',
            "list" => [
    			["slug" => "10-45", "name" => "10-45 Min."],
    			["slug" => "45-60", "name" => "45Min. - 1 Hour"],
    			["slug" => "60-180", "name" => "1-3 Hours"],
    			["slug" => "240-600", "name" => "4-10 Hours"]
            ]
		];
        $response['filters'][] = [
            "name"  => "SERVING SIZE",
            "key"   => "serving_size",
            "list"  => [
                ["slug" => "1-4", "name" => "1-4"],
                ["slug" => "5-8", "name" => "5-8"],
                ["slug" => "9-12", "name" => "9-12"],
                ["slug" => "13-20", "name" => "13-20"]
            ]
        ];
        $response['filters'][] = [
            "name"  => "RATINGS",
            "key"   => "ratings",
            "list"  => [
                ["slug" => "1", "name" => "1"],
                ["slug" => "2", "name" => "2"],
                ["slug" => "3", "name" => "3"],
                ["slug" => "4", "name" => "4"],
                ["slug" => "4", "name" => "5 out of 5"],
            ]
        ];
        $response['filters'][] = [
            "name" => "DIFFICULTY",
            "key"  => "difficulty",
            "list" => $difficulty_list
        ];
        wp_send_json($response, 200);
        wp_die();
    }

    public function view($request) {
        pt_validate_request($request);
        $recipe = get_post($request->get_param("id"));
        $recipe_details = get_post_meta($recipe->ID);

        $difficulty = get_the_terms($recipe->ID, "difficulty");
        $difficulty = empty($difficulty) ? "" : $difficulty[0]->name;

        $allergen_data = get_the_terms($recipe->ID, "allergen");
        $allergens = [];
        foreach ($allergen_data as $a) {
            $allergen_icon = get_field("icon_light", "allergen_".$a->term_id);
            $allergens[] = [
                "icon" => $allergen_icon['url'],
                "name" => trim(html_entity_decode(str_ireplace("free", "", $a->name))),
            ];
        }

        $instruction_arr = empty($recipe_details['recipe_instructions']) ? [] : unserialize($recipe_details['recipe_instructions'][0]);

        $ingredients = empty($recipe_details['recipe_ingredients']) ? [] : unserialize($recipe_details['recipe_ingredients'][0]);

        $serving_size = $recipe_details['recipe_servings'][0]; // ." ".$recipe_details['recipe_servings_type'][0];
        $cook_time = $recipe_details['recipe_cook_time'][0]." ".$recipe_details['recipe_cook_time_text'][0];
        $response['success'] = true;
        $response['recipe'] = [
            "ID" => $recipe->ID,
            "name" => $recipe->post_title,
            "difficulty" => $difficulty,
            "servings" => $serving_size,
            "cook_time" => $cook_time,
            "rating" => $recipe_details['recipe_user_ratings_rating'][0],
            "method" => $instruction_arr,
            "ingredients" => $ingredients,
            "allergens" => $allergens
        ];

        wp_send_json($response, 200);
        wp_die();
    }
}