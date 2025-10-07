<?php

class WPURP_Recipe_Cloner {

    private $fields_to_clone = array(
        'recipe_title',
        'recipe_description',
        'recipe_rating',
        'recipe_servings',
        'recipe_servings_normalized',
        'recipe_servings_type',
        'recipe_prep_time',
        'recipe_prep_time_text',
        'recipe_cook_time',
        'recipe_cook_time_text',
        'recipe_passive_time',
        'recipe_passive_time_text',
        'recipe_instructions',
        'recipe_notes',
    );

    public function __construct()
    {
        add_action( 'init', array( $this, 'assets' ) );

        add_action( 'wp_ajax_clone_recipe', array( $this, 'ajax_clone_recipe' ) );
        add_action( 'wp_ajax_clone_meal_plan', array( $this, 'ajax_clone_meal_plan' ) );
    }

    public function assets()
    {
        WPUltimateRecipe::get()->helper( 'assets' )->add(
            array(
                'file' => '/js/recipe_cloner.js',
                'premium' => true,
                'admin' => true,
                'page' => 'admin_posts_overview',
                'deps' => array(
                    'jquery',
                ),
                'data' => array(
                    'name' => 'wpurp_recipe_cloner',
                    'ajax_url' => WPUltimateRecipe::get()->helper('ajax')->url(),
                    'nonce' => wp_create_nonce( 'clone_recipe' )
                )
            )
        );
    }

    public function ajax_clone_recipe()
    {
        $recipe_id = intval( $_POST['recipe'] );

        if( check_ajax_referer( 'clone_recipe', 'security', false ) && 'recipe' == get_post_type( $recipe_id ) )
        {
            $recipe = new WPURP_Recipe( $recipe_id );

            $post = array(
                'post_title' => $recipe->title(),
                'post_type'	=> 'recipe',
                'post_status' => 'draft',
                'post_author' => get_current_user_id(),
            );

            // Necessary to set the post terms correctly in recipe_save.
            $_POST['recipe_ingredients'] = get_post_meta( $recipe->ID(), 'recipe_ingredients', true );

            $post_id = wp_insert_post($post);

            // Clone recipe fields.
            foreach( $this->fields_to_clone as $field ) {
                $val = get_post_meta( $recipe->ID(), $field, true );
                update_post_meta( $post_id, $field, $val );
            }

            // Clone recipe taxonomies.
            $taxonomies = WPUltimateRecipe::get()->tags();
            unset( $taxonomies['ingredient'] );
            $taxonomies['category'] = true;
            $taxonomies['post_tag'] = true;

            foreach( $taxonomies as $taxonomy => $options ) {
                $terms = get_the_terms( $recipe->ID(), $taxonomy );

                if (!is_wp_error($terms) && $terms) {
                    $term_ids = array();

                    foreach ($terms as $term) {
                        $term_ids[] = $term->term_id;
                    }

                    wp_set_post_terms( $post_id, $term_ids, $taxonomy );
                }
            }

            $url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
            echo json_encode( array( 'redirect' => $url ) );
        }
        die();
    }

    public function ajax_clone_meal_plan()
    {
        $meal_plan_id = intval( $_POST['meal_plan'] );

        if( check_ajax_referer( 'clone_recipe', 'security', false ) && WPURP_MEAL_PLAN_POST_TYPE == get_post_type( $meal_plan_id ) )
        {
            $meal_plan = get_post( $meal_plan_id );

            $post = array(
                'post_title' => $meal_plan->post_title . ' - Clone',
                'post_type'	=> WPURP_MEAL_PLAN_POST_TYPE,
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
            );

            $post_id = wp_insert_post( $post );

            // Clone Meal Plan.
            $meal_plan = get_post_meta( $meal_plan_id, 'wpurp_meal_plan', true );
            update_post_meta( $post_id, 'wpurp_meal_plan', $meal_plan );

            $url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
            echo json_encode( array( 'redirect' => $url ) );
        }
        die();
    }
}