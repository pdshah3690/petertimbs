<?php
define( 'WPURP_MEAL_PLAN_POST_TYPE', 'meal_plan' );

class WPURP_Meal_Planner extends WPURP_Premium_Addon {

    public function __construct( $name = 'meal-planner' ) {
        parent::__construct( $name );

        // Actions
        add_action( 'init', array( $this, 'assets' ) );
        add_action( 'init', array( $this, 'assets_admin' ) );
        add_action( 'init', array( $this, 'post_type' ) );
        add_action( 'admin_init', array( $this, 'admin_meta_box' ) );
        add_action( 'manage_posts_extra_tablenav', array( $this, 'view_meal_plan_link' ) );
        add_action( 'admin_menu', array( $this, 'view_meal_plan_page' ) );
        add_action( 'template_redirect', array( $this, 'mobile_shopping_list' ), 1 );

        // Filters
        add_filter( 'manage_edit-' . WPURP_MEAL_PLAN_POST_TYPE . '_columns', array( $this, 'admin_columns') );
        add_filter( 'manage_' . WPURP_MEAL_PLAN_POST_TYPE . '_posts_custom_column' , array( $this, 'admin_columns_content'), 10, 2 );

        // Ajax
        add_action( 'wp_ajax_get_recipe_meal_plan_data', array( $this, 'ajax_get_recipe_meal_plan_data' ) );
        add_action( 'wp_ajax_nopriv_get_recipe_meal_plan_data', array( $this, 'ajax_get_recipe_meal_plan_data' ) );
        add_action( 'wp_ajax_get_ingredient_meal_plan_data', array( $this, 'ajax_get_ingredient_meal_plan_data' ) );
        add_action( 'wp_ajax_nopriv_get_ingredient_meal_plan_data', array( $this, 'ajax_get_ingredient_meal_plan_data' ) );
        add_action( 'wp_ajax_meal_planner_groupby', array( $this, 'ajax_groupby' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_groupby', array( $this, 'ajax_groupby' ) );
        add_action( 'wp_ajax_meal_planner_save', array( $this, 'ajax_save' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_save', array( $this, 'ajax_save' ) );
        add_action( 'wp_ajax_meal_planner_change_date', array( $this, 'ajax_change_date' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_change_date', array( $this, 'ajax_change_date' ) );
        add_action( 'wp_ajax_meal_planner_shopping_list', array( $this, 'ajax_shopping_list' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_shopping_list', array( $this, 'ajax_shopping_list' ) );
        add_action( 'wp_ajax_meal_planner_shopping_list_save', array( $this, 'ajax_shopping_list_save' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_shopping_list_save', array( $this, 'ajax_shopping_list_save' ) );
        add_action( 'wp_ajax_meal_planner_button', array( $this, 'ajax_add_to_meal_plan_button' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_button', array( $this, 'ajax_add_to_meal_plan_button' ) );
        add_action( 'wp_ajax_meal_planner_add_from_meal_plan', array( $this, 'ajax_add_to_meal_planner' ) );
        add_action( 'wp_ajax_nopriv_meal_planner_add_from_meal_plan', array( $this, 'ajax_add_to_meal_planner' ) );
        add_action( 'wp_ajax_shopping_list_mobile_mail', array( $this, 'ajax_shopping_list_mobile_mail' ) );
        add_action( 'wp_ajax_nopriv_shopping_list_mobile_mail', array( $this, 'ajax_shopping_list_mobile_mail' ) );

        // Shortcodes
        add_shortcode( 'ultimate-recipe-meal-planner', array( $this, 'meal_planner_shortcode' ) );
        add_shortcode( 'ultimate-recipe-meal-plan', array( $this, 'meal_plan_shortcode' ) );
    }

    public function assets() {
        global $wp_locale;

        WPUltimateRecipe::get()->helper( 'assets' )->add(
            array(
                'file' => WPUltimateRecipe::get()->coreUrl . '/vendor/select2/select2.css',
                'direct' => true,
                'public' => true,
            ),
            array(
                'name' => 'jquery-ui',
                'file' => $this->addonPath . '/vendor/jquery-ui-smoothness/jquery-ui.css',
                'premium' => true,
                'public' => true,
            ),
            array(
                'file' => $this->addonPath . '/css/admin.css',
                'premium' => true,
                'admin' => true,
            ),
            array(
                'file' => $this->addonPath . '/css/meal-planner.css',
                'premium' => true,
                'public' => true,
            ),
            array(
                'file' => $this->addonPath . '/css/meal-plan.css',
                'premium' => true,
                'public' => true,
            ),
            array(
                'file' => $this->addonPath . '/css/add-to-meal-plan.css',
                'premium' => true,
                'public' => true,
            ),
            array(
                'name' => 'select2wpurp',
                'file' => '/vendor/select2/select2.min.js',
                'public' => true,
                'deps' => array(
                    'jquery',
                ),
            ),
            array(
                'name' => 'jquery-ui-touch-punch',
                'file' => WPUltimateRecipePremium::get()->premiumUrl . '/vendor/jQuery-UI-Touch-Punch/jquery.ui.touch-punch.min.js',
                'direct' => true,
                'public' => true,
                'deps' => array(
                    'jquery-ui-sortable',
                    'jquery-ui-draggable',
                    'jquery-ui-droppable',
                ),
            ),
            array(
                'name' => 'jquery-touch-swipe',
                'file' => WPUltimateRecipePremium::get()->premiumUrl . '/vendor/jQuery-touchSwipe/jquery.touchSwipe.min.js',
                'direct' => true,
                'public' => true,
                'deps' => array(
                    'jquery',
                ),
            ),
            array(
                'name' => 'wpurp-meal-planner',
                'file' => $this->addonPath . '/js/meal-planner.js',
                'premium' => true,
                'public' => true,
                'deps' => array(
                    'jquery',
                    'wpurp-unit-conversion',
                    'js-quantities',
                    'jquery-ui-sortable',
                    'jquery-ui-datepicker',
                    'jquery-ui-touch-punch',
                    'jquery-touch-swipe',
                    'select2wpurp',
                ),
                'data' => array(
                    'name' => 'wpurp_meal_planner',
                    'ajaxurl' => WPUltimateRecipe::get()->helper('ajax')->url(),
                    'addonUrl' => $this->addonUrl,
                    'printUrl' => site_url( '/wpurp_print/' ),
                    'nonce' => wp_create_nonce( 'wpurp_meal_planner' ),
                    'nonce_print' => wp_create_nonce( 'wpurp_meal_planner_print' ),
                    'nonce_admin' => '',
                    'textLeftovers' => __( 'leftovers', 'wp-ultimate-recipe' ),
                    'textQuantity' => __( 'Quantity', 'wp-ultimate-recipe' ),
                    'textServings' => __( 'Servings', 'wp-ultimate-recipe' ),
                    'textDeleteCourse' => __( 'Do you want to remove this entire course?', 'wp-ultimate-recipe' ),
                    'textDeleteRecipe' => __( 'Do you want to remove this recipe from the menu?', 'wp-ultimate-recipe' ),
                    'textDeleteRecipes' => __( 'Do you want to remove all these recipes from the menu?', 'wp-ultimate-recipe' ),
                    'textAddToMealPlan' => WPUltimateRecipe::option( 'added_to_meal_plan_tooltip_text', __('This recipe has been added to your Meal Plan', 'wp-ultimate-recipe') ),
                    'nutrition_facts_fields' => WPUltimateRecipe::option( 'meal_plan_nutrition_facts_fields', array( 'calories', 'fat', 'carbohydrate', 'protein' ) ),
                    'nutrition_facts_calories_type' => WPUltimateRecipe::option( 'nutritional_information_unit', 'calories' ),
                    'nutrition_facts_total' => WPUltimateRecipe::option( 'meal_plan_nutrition_facts_total', '0' ),
                    'adjustable_system' => WPUltimateRecipe::option( 'meal_planner_dynamic_unit_system', '1' ),
                    'default_unit_system' => WPUltimateRecipe::option( 'meal_planner_default_unit_system', '0' ),
                    'consolidate_ingredients' => WPUltimateRecipe::option( 'meal_plan_shopping_list_consolidate_ingredients', '1' ),
                    'checkboxes' => WPUltimateRecipe::option( 'meal_plan_shopping_list_checkboxes', '1' ),
                    'fractions' => WPUltimateRecipe::option( 'recipe_adjustable_servings_fractions', '0' ) == '1' ? true : false,
                    'print_shoppinglist_style' => WPUltimateRecipe::option( 'custom_code_print_shoppinglist_css', '' ),
                    'datepicker' => array(
                        'dateFormat'        => 'yy-mm-dd',
                        'monthNames'        => array_values( $wp_locale->month ),
                        'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
                        'dayNames'          => array_values( $wp_locale->weekday ),
                        'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
                        'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
                        'firstDay'          => get_option( 'start_of_week' ),
                        'isRTL'             => $wp_locale->is_rtl(),
                    ),
                )
            ),
            array(
                'file' => $this->addonPath . '/js/add-to-meal-plan.js',
                'premium' => true,
                'public' => true,
                'deps' => array(
                    'jquery',
                    'jquery-ui-datepicker',
                ),
                'data' => array(
                    'name' => 'wpurp_add_to_meal_plan',
                    'ajaxurl' => WPUltimateRecipe::get()->helper('ajax')->url(),
                    'nonce' => wp_create_nonce( 'wpurp_add_to_meal_plan' ),
                    'datepicker' => array(
                        'dateFormat'        => 'yy-mm-dd',
                        'monthNames'        => array_values( $wp_locale->month ),
                        'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
                        'dayNames'          => array_values( $wp_locale->weekday ),
                        'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
                        'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
                        'firstDay'          => get_option( 'start_of_week' ),
                        'isRTL'             => $wp_locale->is_rtl(),
                    ),
                )
            )
        );
    }

    public function assets_admin() {
        global $wp_locale;

        WPUltimateRecipe::get()->helper( 'assets' )->add(
            array(
                'file' => WPUltimateRecipe::get()->coreUrl . '/vendor/font-awesome/css/font-awesome.min.css',
                'direct' => true,
                'admin' => true,
                'page' => 'meal_plan_form',
            ),
            array(
                'file' => WPUltimateRecipe::get()->coreUrl . '/vendor/select2/select2.css',
                'direct' => true,
                'admin' => true,
                'page' => 'meal_plan_form',
            ),
            array(
                'name' => 'select2wpurp',
                'file' => '/vendor/select2/select2.min.js',
                'admin' => true,
                'page' => 'meal_plan_form',
                'deps' => array(
                    'jquery',
                ),
            ),
            array(
                'file' => '/js/print_button.js',
                'admin' => true,
                'page' => 'meal_plan_form',
                'deps' => array(
                    'jquery',
                ),
                'data' => array(
                    'name' => 'wpurp_print',
                    'ajaxurl' => WPUltimateRecipe::get()->helper('ajax')->url(),
                    'nonce' => wp_create_nonce( 'wpurp_print' ),
                    'custom_print_css' => WPUltimateRecipe::option( 'custom_code_print_css', '' ),
                    'coreUrl' => WPUltimateRecipe::get()->coreUrl,
                    'premiumUrl' => WPUltimateRecipe::is_premium_active() ? WPUltimateRecipePremium::get()->premiumUrl : false,
                    'title' => WPUltimateRecipe::option( 'print_template_title_text', get_bloginfo('name') ),
                ),
            ),
            array(
                'file' => $this->addonPath . '/css/meal-planner.css',
                'premium' => true,
                'admin' => true,
                'page' => 'meal_plan_form',
            ),
            array(
                'name' => 'jquery-touch-swipe',
                'file' => WPUltimateRecipePremium::get()->premiumUrl . '/vendor/jQuery-touchSwipe/jquery.touchSwipe.min.js',
                'direct' => true,
                'admin' => true,
                'deps' => array(
                    'jquery',
                ),
            ),
            array(
                'file' => '/js/adjustable_servings.js',
                'admin' => true,
                'deps' => array(
                    'jquery',
                    'fraction',
                ),
                'data' => array(
                    'name' => 'wpurp_servings',
                    'precision' => WPUltimateRecipe::option( 'recipe_adjustable_servings_precision', 2 ),
                    'decimal_character' => WPUltimateRecipe::option( 'recipe_decimal_character', '.' ),
                ),
            ),
            array(
                'name' => 'wpurp-meal-planner',
                'file' => $this->addonPath . '/js/meal-planner.js',
                'premium' => true,
                'admin' => true,
                'page' => 'meal_plan_form',
                'deps' => array(
                    'jquery',
                    'jquery-ui-sortable',
                    'jquery-touch-swipe',
                    'select2wpurp',
                ),
                'data' => array(
                    'name' => 'wpurp_meal_planner',
                    'ajaxurl' => WPUltimateRecipe::get()->helper('ajax')->url(),
                    'addonUrl' => $this->addonUrl,
                    'printUrl' => site_url( '/wpurp_print/' ),
                    'nonce' => wp_create_nonce( 'wpurp_meal_planner' ),
                    'nonce_print' => wp_create_nonce( 'wpurp_meal_planner_print' ),
                    'nonce_admin' => wp_create_nonce( 'wpurp_meal_planner_admin' ),
                    'textLeftovers' => __( 'leftovers', 'wp-ultimate-recipe' ),
                    'textQuantity' => __( 'Quantity', 'wp-ultimate-recipe' ),
                    'textServings' => __( 'Servings', 'wp-ultimate-recipe' ),
                    'textDeleteCourse' => __( 'Do you want to remove this entire course?', 'wp-ultimate-recipe' ),
                    'textDeleteRecipe' => __( 'Do you want to remove this recipe from the menu?', 'wp-ultimate-recipe' ),
                    'textDeleteRecipes' => __( 'Do you want to remove all these recipes from the menu?', 'wp-ultimate-recipe' ),
                    'textAddToMealPlan' => WPUltimateRecipe::option( 'added_to_meal_plan_tooltip_text', __('This recipe has been added to your Meal Plan', 'wp-ultimate-recipe') ),
                    'nutrition_facts_fields' => WPUltimateRecipe::option( 'meal_plan_nutrition_facts_fields', array( 'calories', 'fat', 'carbohydrate', 'protein' ) ),
                    'nutrition_facts_calories_type' => WPUltimateRecipe::option( 'nutritional_information_unit', 'calories' ),
                    'nutrition_facts_total' => WPUltimateRecipe::option( 'meal_plan_nutrition_facts_total', '0' ),
                    'datepicker' => array(
                        'dateFormat'        => 'yy-mm-dd',
                        'monthNames'        => array_values( $wp_locale->month ),
                        'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
                        'dayNames'          => array_values( $wp_locale->weekday ),
                        'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
                        'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
                        'firstDay'          => get_option( 'start_of_week' ),
                        'isRTL'             => $wp_locale->is_rtl(),
                    ),
                )
            )
        );
    }

    public function post_type()
    {
        $name = __( 'Meal Plans', 'wp-ultimate-recipe' );
        $singular = __( 'Meal Plan', 'wp-ultimate-recipe' );

        $args = apply_filters( 'wpurp_register_meal_plan_post_type',
            array(
                'labels' => array(
                    'name' => $name,
                    'singular_name' => $singular,
                    'add_new' => __( 'Add New', 'wp-ultimate-recipe' ),
                    'add_new_item' => __( 'Add New', 'wp-ultimate-recipe' ) . ' ' . $singular,
                    'edit' => __( 'Edit', 'wp-ultimate-recipe' ),
                    'edit_item' => __( 'Edit', 'wp-ultimate-recipe' ) . ' ' . $singular,
                    'new_item' => __( 'New', 'wp-ultimate-recipe' ) . ' ' . $singular,
                    'view' => __( 'View', 'wp-ultimate-recipe' ),
                    'view_item' => __( 'View', 'wp-ultimate-recipe' ) . ' ' . $singular,
                    'search_items' => __( 'Search', 'wp-ultimate-recipe' ) . ' ' . $name,
                    'not_found' => __( 'No', 'wp-ultimate-recipe' ) . ' ' . $name . ' ' . __( 'found.', 'wp-ultimate-recipe' ),
                    'not_found_in_trash' => __( 'No', 'wp-ultimate-recipe' ) . ' ' . $name . ' ' . __( 'found in trash.', 'wp-ultimate-recipe' ),
                    'parent' => __( 'Parent', 'wp-ultimate-recipe' ) . ' ' . $singular,
                ),
                'public' => false,
                'show_ui' => true,
                'menu_position' => 5,
                'supports' => array( 'title', 'author' ),
                'taxonomies' => array( '' ),
                'menu_icon' =>  WPUltimateRecipe::get()->coreUrl . '/img/icon_16.png',
                'has_archive' => false,
                'rewrite' => false,
                'show_in_menu' => 'edit.php?post_type=' . WPURP_POST_TYPE,
            )
        );

        register_post_type( WPURP_MEAL_PLAN_POST_TYPE, $args );
    }

    public function admin_columns( $columns ) {
        $columns['meal_plan_actions'] = __( 'Actions', 'wp-ultimate-recipe' );
        return $columns;
    }

    public function admin_columns_content( $column, $post_ID ) {
        switch( $column ) {
            case 'meal_plan_actions':
                echo '<a href="#" class="clone-meal-plan" data-meal-plan="' . $post_ID . '">' . __( 'Clone Meal Plan', 'wp-ultimate-recipe' ) . '</a>';
                break;
        }
    }

    public function admin_meta_box()
    {
        add_meta_box(
            'meal_plan_meta_box',
            __( 'Meal Plan', 'wp-ultimate-recipe' ),
            array( $this, 'meal_plan_meta_box' ),
            WPURP_MEAL_PLAN_POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'meal_plan_shortcode_meta_box',
            __( 'Shortcode', 'wp-ultimate-recipe' ),
            array( $this, 'meal_plan_shortcode_meta_box' ),
            WPURP_MEAL_PLAN_POST_TYPE,
            'side',
            'high'
        );
    }

    public function meal_plan_meta_box()
    {
        wp_localize_script( 'wpurp-meal-planner', 'wpurp_meal_planner_grid',
            array(
                'slug' => '',
            )
        );

        if( get_post_status() == 'publish' ) {
            include( $this->addonDir . '/templates/meal-planner.php' );
        } else {
            _e( 'Publish Meal Plan to add recipes to it. Publishing will not make it show up on your website.', 'wp-ultimate-recipe' );
        }
    }

    public function meal_plan_shortcode_meta_box()
    {
        if( get_post_status() == 'publish' ) {
            $post = get_post();
            echo '<strong>' . __( 'Use the following shortcode to display the Meal Plan', 'wp-ultimate-recipe' ) . ':</strong><br/><br/>';
            echo '[ultimate-recipe-meal-plan id="' . $post->post_name . '"]';
        } else {
            _e( 'You have to publish the Meal Plan first.', 'wp-ultimate-recipe' );
        }
    }

    public function mobile_shopping_list()
    {
        // Keyword to check for in URL
        $keyword = urlencode( WPUltimateRecipe::option( 'meal_plan_shopping_list_keyword', 'shopping-list' ) );
        if( strlen( $keyword ) <= 0 ) {
            $keyword = 'shopping-list';
        }

        // Current URL
        $url = $_SERVER['REQUEST_URI'];
        $prefix = wp_make_link_relative( get_home_url() );

        if( $prefix && substr( $url, 0, strlen( $prefix ) ) == $prefix ) {
            $url = substr( $url, strlen( $prefix ) );
        }

        // Check if URL starts with /shopping-list/
        if( strpos( $url, '/' . $keyword . '/' ) === 0 && strlen( $url ) > ( strlen( $keyword ) + 2 + 8 ) ) {
            $hash = substr( $url, strlen( '/' . $keyword . '/' ) );

            include( $this->addonDir . '/templates/shopping-list-mobile.php' );
            exit();
        }
    }

    public function ajax_get_recipe_meal_plan_data()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $recipe_id = intval( $_POST['recipe_id'] );
            $recipe = new WPURP_Recipe( $recipe_id );

            $servings = $recipe->servings_normalized();
            if( $servings < 1 ) {
                $servings = 1;
            }

            // Get nutrition data
            $nutrition_fields = WPUltimateRecipe::option( 'meal_plan_nutrition_facts_fields', array( 'calories', 'fat', 'carbohydrate', 'protein' ) );
            $nutritional_facts = array();
            foreach( $nutrition_fields as $nutrition_field ) {
                $nutritional_facts[] = $recipe->nutritional( $nutrition_field );
            }

            $recipe_data = array(
                'id' => $recipe->ID(),
                'name' => $recipe->title(),
                'link' => $recipe->link(),
                'image' =>$recipe->image_url( 'thumbnail' ),
                'nutrition' => implode( ';', $nutritional_facts ),
                'servings_original' => $servings,
            );

            wp_send_json_success( array(
				'recipe' => $recipe_data,
			) );
        }

        wp_die();
    }

    public function ajax_get_ingredient_meal_plan_data()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $ingredient_id = intval( $_POST['ingredient_id'] );
            $ingredient = get_term_by( 'id', $ingredient_id, 'ingredient' );
            $name = $ingredient->name;

            // Get nutrition data
            $nutritional = WPUltimateRecipe::addon('nutritional-information')->get( $ingredient_id );

            $nutrition_fields = WPUltimateRecipe::option( 'meal_plan_nutrition_facts_fields', array( 'calories', 'fat', 'carbohydrate', 'protein' ) );
            $nutritional_facts = array();
            if( $nutritional ) {
                foreach( $nutrition_fields as $nutrition_field ) {
                    $nutritional_facts[] = $nutritional[ $nutrition_field ];
                }
            }
            
            // Ingredient Name Display
            switch( WPUltimateRecipe::option( 'meal_plan_ingredients_display', 'name' ) ) {
                case 'name_metric':
                    $name = $nutritional && $nutritional['_meta'] ? $nutritional['_meta']['serving_quantity'] . $nutritional['_meta']['serving_unit'] . ' ' . $name : $name;
                    break;
                case 'name_reference':
                    $name = $nutritional && $nutritional['_meta'] ? $nutritional['_meta']['serving'] . ' ' . $name : $name;
                    break;
                // Default just leave $name
            }

            $ingredient_data = array(
                'id' => $ingredient->term_id,
                'name' => $name,
                'nutrition' => implode( ';', $nutritional_facts ),
                'servings_original' => 1,
            );

            wp_send_json_success( array(
				'ingredient' => $ingredient_data,
			) );
        }

        wp_die();
    }

    public function ajax_groupby()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $groupby = $_POST['groupby'];
            $slug = $_POST['grid'];

            $groups = $this->get_recipes_grouped_by( $groupby, $slug );
            echo $this->get_select_options( $groups );
        }

        die();
    }

    public function ajax_change_date()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $start_date = DateTime::createFromFormat( 'Ymd', $_POST['start_date'], WPUltimateRecipe::get()->timezone() );
            $start_date->setTime(0,0);

            if( !isset( $_POST['end_date'] ) || !$_POST['end_date'] ) {
                $end_date = false;
            } else {
                $end_date = DateTime::createFromFormat( 'Ymd', $_POST['end_date'], WPUltimateRecipe::get()->timezone() );
                $end_date->setTime(0,0);
            }

            if( WPUltimateRecipe::option( 'meal_planner_dates_change', 'shown' ) == 'shown' ) {
                $nbr_days = intval( $_POST['nbr_days'] );
            } else {
                $nbr_days = intval( WPUltimateRecipe::option( 'meal_planner_dates_change', 'shown' ) );
            }

            $going_back = !$_POST['going_back'] || $_POST['going_back'] == 'false' ? false : true;

            if( $going_back ) {
                $start_date->modify( '-' . $nbr_days . ' day' );
            } else {
                $start_date->modify( '+' . $nbr_days . ' day' );
            }

            $meal_plan_admin = $_POST['admin'] == 'true' ? true : false;
            $meal_plan_id = $_POST['id'];
            $view_meal_plan = $_POST['view_meal_plan'];
            include( $this->addonDir . '/templates/calendar.php' );
        }

        die();
    }

    public function ajax_shopping_list()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $id = intval( $_POST['id'] );
            $from_date = DateTime::createFromFormat( 'Y-m-d', $_POST['from'], WPUltimateRecipe::get()->timezone() );
            $to_date = DateTime::createFromFormat( 'Y-m-d', $_POST['to'], WPUltimateRecipe::get()->timezone() );
            $unit_system = intval( $_POST['unit_system'] );

            $recipes = array();

            if( $from_date && $to_date && $from_date <= $to_date ) {
                $from_date->setTime(0,0);
                $to_date->setTime(0,0);

                $meal_plan = $this->get_meal_plan( $id );

                for( $i = 0; $i < 366; $i++ ) {
                    $date_key = $from_date->format( 'Ymd' );
                    if( isset( $meal_plan['dates'][$date_key] ) ) {
                        foreach( $meal_plan['dates'][$date_key] as $course => $date_recipes ) {
                            $recipes = array_merge( $recipes, $date_recipes );
                        }
                    }

                    if($from_date == $to_date) break;
                    $from_date->modify( '+1 day' );
                }
            }

            $ingredients = $this->get_shopping_list_ingredients( $recipes );

            echo json_encode(array(
                'ingredients' => $ingredients,
                'unit_system' => $unit_system,
            ));
        }

        die();
    }

    public function ajax_add_to_meal_planner()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $id = intval( $_POST['id'] );
            $date = DateTime::createFromFormat( 'Y-m-d', $_POST['date'], WPUltimateRecipe::get()->timezone() );

            if( $date ) {
                $date->setTime(0,0);
                $this->add_meal_plan_to_meal_planner( $id, $date );
            }
        }

        die();
    }

    public function ajax_shopping_list_mobile_mail()
    {
        if( check_ajax_referer( 'wpurp_shopping_list_mobile', 'security', false ) )
        {
            $email = explode( ';', $_POST['email'] );
            $link = $_POST['link'];

            $subject = htmlspecialchars_decode( get_bloginfo( 'name' ) ) . ' - ' . __( 'Shopping List', 'wp-ultimate-recipe' );
            $content = __( 'You can access the shopping list over here:', 'wp-ultimate-recipe' );
            $content .= "\n" . $link;

            if( WPUltimateRecipe::option( 'meal_plan_shopping_list_email', '0' ) == '1' ) {
                wp_mail( $email, $subject, $content);
            }
        }

        die();
    }

    public function ajax_shopping_list_save()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) )
        {
            $shopping_list = $_POST['shopping_list'];

            $today = new DateTime( 'today', WPUltimateRecipe::get()->timezone() );
            $today = intval( $today->format( 'Ymd' ) );

            $delete_date = new DateTime( 'today', WPUltimateRecipe::get()->timezone() );
            $delete_date = $delete_date->modify( '-7 days' );
            $delete_date = intval( $delete_date->format( 'Ymd' ) );

            $shopping_lists = get_option( 'wpurp_meal_plan_shopping_lists', array() );
            $new_shopping_lists = array();

            // Only keep shopping lists from the last 7 days
            foreach( $shopping_lists as $date => $date_lists ) {
                if( $date >= $delete_date ) {
                    $new_shopping_lists[$date] = $date_lists;
                }
            }

            // Make sure today exists
            if( !isset( $new_shopping_lists[$today] ) ) {
                $new_shopping_lists[$today] = array();
            }

            // Save current shopping list
            do {
                $meal_plan_id = mt_rand();
            } while( isset( $new_shopping_lists[$today][$meal_plan_id] ) );

            $new_shopping_lists[$today][$meal_plan_id] = $shopping_list;
            update_option( 'wpurp_meal_plan_shopping_lists', $new_shopping_lists, false );

            // Link to new shopping list
            $keyword = urlencode( WPUltimateRecipe::option( 'meal_plan_shopping_list_keyword', 'shopping-list' ) );
            if( strlen( $keyword ) <= 0 ) {
                $keyword = 'shopping-list';
            }
            $link = site_url( '/' . $keyword . '/' . $today . $meal_plan_id );

            echo $link;
        }

        die();
    }

    public function get_shopping_list_ingredients( $recipes )
    {
        $ingredients = array();

        $setting_show_ingredient_notes = WPUltimateRecipe::option( 'meal_plan_shopping_list_ingredient_notes', '0' ) == '1' ? true : false;

        foreach( $recipes as $recipe_data ) {
            if( isset( $recipe_data['leftovers'] ) && $recipe_data['leftovers'] ) {
                continue;
            }

            if( $recipe_data['type'] == 'ingredient') {
                $ingredient = get_term_by( 'id', $recipe_data['id'], 'ingredient' );

                $plural = WPURP_Taxonomy_MetaData::get( 'ingredient', $ingredient->slug, 'plural' );
                if( ! $plural || is_array( $plural ) ) {
                    $plural = $ingredient->name;
                }

                $group = WPURP_Taxonomy_MetaData::get( 'ingredient', $ingredient->slug, 'group' );
                if( !$group ) {
                    $group = __( 'Other', 'wp-ultimate-recipe' );
                }

                // Ingredient Name Display
                $name = $ingredient->name;
                $ingredient_nutrition = WPUltimateRecipe::addon('nutritional-information')->get( $recipe_data['id'] );

                switch( WPUltimateRecipe::option( 'meal_plan_ingredients_display', 'name' ) ) {
                    case 'name_metric':
                        $name = $ingredient_nutrition && $ingredient_nutrition['_meta'] ? $ingredient_nutrition['_meta']['serving_quantity'] . $ingredient_nutrition['_meta']['serving_unit'] . ' ' . $name : $name;
                        $plural = $ingredient_nutrition && $ingredient_nutrition['_meta'] ? $ingredient_nutrition['_meta']['serving_quantity'] . $ingredient_nutrition['_meta']['serving_unit'] . ' ' . $plural : $plural;
                        break;
                    case 'name_reference':
                        $name = $ingredient_nutrition && $ingredient_nutrition['_meta'] ? $ingredient_nutrition['_meta']['serving'] . ' ' . $name : $name;
                        $plural = $ingredient_nutrition && $ingredient_nutrition['_meta'] ? $ingredient_nutrition['_meta']['serving'] . ' ' . $plural : $plural;
                        break;
                    // Default just leave $title
                }

                $ingredients[] = array(
                    'id' => $ingredient->term_id,
                    'group' => $group,
                    'name' => $name,
                    'plural' => $plural,
                    'unit' => '',
                    'amount' => $recipe_data['quantity'],
                );
            } else {
                $recipe = new WPURP_Recipe( $recipe_data['id'] );

                $servings_original = $recipe->servings_normalized();
                $servings_ratio = $recipe_data['servings'] / $servings_original;

                foreach( $recipe->ingredients() as $ingredient ) {
                    $term = get_term( $ingredient['ingredient_id'], 'ingredient' );

                    if ( WPUltimateRecipe::option( 'ignore_ingredient_ids', '' ) != '1' && $term !== null && !is_wp_error( $term ) ) {
                        $ingredient['ingredient'] = $term->name;
                    }

                    $plural = WPURP_Taxonomy_MetaData::get( 'ingredient', $term->slug, 'plural' );
                    if( $plural && !is_array( $plural ) ) {
                        $ingredient['plural'] = $plural;
                    } else {
                        $ingredient['plural'] = $ingredient['ingredient'];
                    }

                    // Show Ingredient Notes?
                    if( $setting_show_ingredient_notes && $ingredient['notes'] ) {
                        $ingredient['ingredient'] .= ' (' . $ingredient['notes'] . ')';
                        $ingredient['plural'] .= ' (' . $ingredient['notes'] . ')';
                    }

                    $ingredient['group'] = WPURP_Taxonomy_MetaData::get( 'ingredient', $term->slug, 'group' );
                    if( !$ingredient['group'] ) {
                        $ingredient['group'] = __( 'Other', 'wp-ultimate-recipe' );
                    }

                    $ingredients[] = array(
                        'id' => $ingredient['ingredient_id'],
                        'group' => $ingredient['group'],
                        'name' => $ingredient['ingredient'],
                        'plural' => $ingredient['plural'],
                        'unit' => $ingredient['unit'],
                        'amount' => $servings_ratio * $ingredient['amount_normalized'],
                    );
                }
            }
        }

        return $ingredients;
    }

    public function ajax_add_to_meal_plan_button()
    {
        if(check_ajax_referer( 'wpurp_add_to_meal_plan', 'security', false ) )
        {
            $recipe_id = intval( $_POST['recipe_id'] );
            $servings_wanted = floatval( $_POST['servings_wanted'] );
            $course = $_POST['course'];

            $date = DateTime::createFromFormat( 'Y-m-d', $_POST['date'], WPUltimateRecipe::get()->timezone() );
            $date = $date->format( 'Ymd' );


            $recipe = new WPURP_Recipe( $recipe_id );
            $servings_wanted = $servings_wanted < 1 ? $recipe->servings_normalized() : $servings_wanted;

            $meal_plan = $this->get_meal_plan();

            if( !isset( $meal_plan['dates'][$date] ) ) {
                $meal_plan['dates'][$date] = array();
            }

            if( !isset( $meal_plan['dates'][$date][$course] ) ) {
                $meal_plan['dates'][$date][$course] = array();
            }

            $meal_plan['dates'][$date][$course][] = array(
                'id' => $recipe_id,
                'servings' => $servings_wanted,
            );

            $this->save_meal_plan( 0, $meal_plan );
        }

        die();
    }

    public function ajax_save()
    {
        if( check_ajax_referer( 'wpurp_meal_planner', 'security', false ) ) {
            $menu = $_POST['menu'];

            $dates = array();
            $courses = array();

            if( isset( $menu['start_date'] ) && isset( $menu['nbr_days'] ) ) {
                $start_date = DateTime::createFromFormat( 'Ymd', $menu['start_date'], WPUltimateRecipe::get()->timezone() );
                $start_date->setTime(0,0);

                // Init Dates
                for( $i = 0; $i < $menu['nbr_days']; $i++ ) {
                    $date = clone $start_date;
                    $date->modify( '+' . $i . ' day' );
                    $date = $date->format( 'Ymd' );

                    $dates[$date] = array();
                }

                if( isset( $menu['courses'] ) ) {
                    foreach ($menu['courses'] as $course_index => $course ) {
                        $course_name = $course['name'];
                        $courses[] = $course_name;

                        if( isset( $course['days'] ) ) {
                            $dates[$date][$course_name] = array();

                            foreach( $course['days'] as $day_index => $recipes ) {
                                $date = clone $start_date;
                                $date->modify( '+' . $day_index . ' day' );
                                $date = $date->format( 'Ymd' );

                                foreach( $recipes as $recipe_index => $recipe ) {
                                    $dates[$date][$course_name][$recipe_index] = array(
                                        'type' => $recipe['type'],
                                        'id' => intval( $recipe['id'] ),
                                        'quantity' => floatval( $recipe['quantity'] ),
                                        'servings' => floatval( $recipe['servings'] ),
                                        'leftovers' => $recipe['leftovers'] == 'true' ? true : false,
                                    );
                                }
                            }
                        }
                    }
                }
            }

            $id = isset( $menu['id'] ) ? intval( $menu['id'] ) : 0;

            // Update Meal Plan for all when admin nonce is present
            $admin_version = check_ajax_referer( 'wpurp_meal_planner_admin', 'security_admin', false );

            $this->update_meal_plan( $id, $dates, $courses, $admin_version );
        }

        die();
    }

    public function add_meal_plan_to_meal_planner( $id, $start_date )
    {
        $meal_plan = $this->get_meal_plan( $id );
        $meal_planner = $this->get_meal_plan();

        foreach( $meal_plan['dates'] as $date => $courses ) {
            $meal_plan_date = DateTime::createFromFormat( 'Ymd', $date, WPUltimateRecipe::get()->timezone() );
            $meal_plan_date->setTime(0,0);

            $date_diff = date_diff( new DateTime( '2000-01-01', WPUltimateRecipe::get()->timezone() ), $meal_plan_date );
            $date_diff = $date_diff->days;

            $actual_date = clone $start_date;
            $actual_date->modify( '+' . $date_diff . ' day' );
            $date_key = $actual_date->format( 'Ymd' );

            if( isset( $meal_planner['dates'][$date_key] ) ) {
                $date_courses = $meal_planner['dates'][$date_key];
                foreach( $courses as $course => $recipes ) {
                    if( isset( $meal_planner['dates'][$date_key][$course] ) ) {
                        $date_courses[$course] = array_merge( $meal_planner['dates'][$date_key][$course], $recipes );
                    } else {
                        $date_courses[$course] = $recipes;
                    }
                }
            } else {
                $date_courses = $courses;
            }

            $meal_planner['dates'][$date_key] = $date_courses;
        }

        $meal_planner['courses'] = array_unique( array_merge( $meal_plan['courses'], $meal_planner['courses'] ) );

        $this->save_meal_plan( 0, $meal_planner );
    }

    public function update_meal_plan( $id, $dates, $courses, $admin_version )
    {
        $meal_plan = $this->get_meal_plan( $id );

        $meal_plan['courses'] = array_unique( $courses );
        foreach( $dates as $date => $date_courses ) {
            $nbr_recipes = 0;
            foreach( $date_courses as $recipes ) {
                $nbr_recipes += count( $recipes );
            }

            if( $nbr_recipes == 0 ) {
                unset( $meal_plan['dates'][$date] );
            } else {
                $meal_plan['dates'][$date] = $date_courses;
            }
        }

        $this->save_meal_plan( $id, $meal_plan, $admin_version );
    }

    public function get_meal_plan( $id = 0, $admin_version = false, $view_meal_plan = false )
    {
        if( $id > 0 ) {
            // Admin Meal Plan
            $user_id = 0;
            $meal_plan = get_post_meta( $id, 'wpurp_meal_plan', true );

            // Personalised Meal Plan
            if( !$admin_version && isset( $_COOKIE['WPURP_Meal_Plan_ID_' . $id ] ) ) {
                $cookie = explode( ';', $_COOKIE['WPURP_Meal_Plan_ID_' . $id ] );
                $last_update = array_shift( $cookie );

                if( intval( $last_update ) > $meal_plan['last_update'] ) {
                    $i = 0;
                    foreach( $meal_plan['dates'] as $date => $courses ) {
                        foreach( $courses as $course => $recipes ) {
                            foreach( $recipes as $recipe_id => $recipe ) {
                                $meal_plan['dates'][$date][$course][$recipe_id]['servings'] = intval( $cookie[$i] );
                                $i++;
                            }
                        }
                    }
                }
            }
        } else {
            // Front-end Meal Planner
            $user_id = get_current_user_id();

            if( $user_id !== 0 ) {
                // Logged In

                if( $view_meal_plan && current_user_can( 'manage_options' ) ) {
                    $meal_plan = get_user_meta( $view_meal_plan, 'wpurp_meal_plan', true );
                    $meal_plan = is_array( $meal_plan ) ? $meal_plan : false;
                } else {
                    $meal_plan = get_user_meta( $user_id, 'wpurp_meal_plan', true );
                    $meal_plan = is_array( $meal_plan ) ? $meal_plan : false;
                }
            } else {
                // Not Logged In
                if( isset( $_COOKIE['WPURP_Meal_Plan_ID'] ) ) {
                    $guest_meal_plans = get_option( 'wpurp_guest_meal_plans', array() );
                    $meal_plan_id = intval( $_COOKIE['WPURP_Meal_Plan_ID'] );

                    $meal_plan = isset( $guest_meal_plans[$meal_plan_id] ) ? $guest_meal_plans[$meal_plan_id] : false;
                } else {
                    $meal_plan = false;
                }
            }
        }

        if( !isset( $meal_plan ) || !$meal_plan ) {
            $meal_plan = array(
                'user' => $user_id,
                'courses' => array(),
                'dates' => array(),
            );
        }

        // Default Courses
        if( empty( $meal_plan['courses'] ) ) {
            if( WPUltimateRecipe::option( 'meal_plan_courses_use_custom', '0' ) == '1' ) {
                for( $i = 1; $i <= 8; $i++ ) {
                    $course = WPUltimateRecipe::option( 'meal_plan_courses_custom_' . $i, '' );
                    if( trim( $course ) !== '' ) {
                        $meal_plan['courses'][] = __( $course, 'wp-ultimate-recipe' );
                    }
                }
            } else {
                $meal_plan['courses'] = WPUltimateRecipe::option( 'meal_planner_default_courses', array(
                    __( 'Breakfast', 'wp-ultimate-recipe' ),
                    __( 'Lunch', 'wp-ultimate-recipe' ),
                    __( 'Dinner', 'wp-ultimate-recipe' ),
                ));
            }
        }

        return $meal_plan;
    }

    public function save_meal_plan( $id, $meal_plan, $admin_version = false )
    {
        $meal_plan['last_update'] = time();

        if( $id > 0 ) {
            if( $admin_version && current_user_can( 'edit_posts' ) ) {
                // Admin Meal Plan
                update_post_meta( $id, 'wpurp_meal_plan', $meal_plan );
            } else {
                // Personalised Meal Plan is saved as a cookie
                $cookie_array = array( time() );

                foreach( $meal_plan['dates'] as $courses ) {
                    foreach( $courses as $recipes ) {
                        foreach( $recipes as $recipe ) {
                            $cookie_array[] = $recipe['servings'];
                        }
                    }
                }

                $cookie = implode( ';', $cookie_array );
                setcookie( 'WPURP_Meal_Plan_ID_' . $id, $cookie, time()+60*60*24*30, '/' );
            }
        } else {
            // Front-end Meal Planner
            $user_id = get_current_user_id();

            if( $user_id !== 0 ) {
                // Logged In
                update_user_meta( $user_id, 'wpurp_meal_plan', $meal_plan );
            } else {
                // Not Logged In
                $guest_meal_plans = get_option( 'wpurp_guest_meal_plans', array() );

                if( isset( $_COOKIE['WPURP_Meal_Plan_ID'] ) ) {
                    $meal_plan_id = intval( $_COOKIE['WPURP_Meal_Plan_ID'] );
                } else {
                    do {
                        $meal_plan_id = mt_rand();
                    } while( isset( $guest_meal_plans[$meal_plan_id] ) );
                }

                // Refresh cookie expiration
                setcookie( 'WPURP_Meal_Plan_ID', $meal_plan_id, time()+60*60*24*30, '/' );

                $guest_meal_plans[$meal_plan_id] = $meal_plan;
                update_option( 'wpurp_guest_meal_plans', $guest_meal_plans, false );
            }
        }
    }

    public function get_cached_recipes( $admin )
    {
        $recipes_grouped = array();
        $recipes = WPUltimateRecipe::get()->helper( 'cache' )->get( 'recipes_by_title' );

        $current_letter = '';
        $current_recipes = array();

        foreach( $recipes as $recipe ) {
            $letter = strtoupper( mb_substr( $recipe['label'], 0, 1 ) );

            if( $letter != $current_letter ) {
                if($current_letter != '') {
                    $recipes_grouped[] = array(
                        'group_name' => $current_letter,
                        'recipes' => $current_recipes
                    );
                }

                $current_letter = $letter;
                $current_recipes = array();
            }

            // Only show published recipes on frontend.
            if ( $admin || (
                ' (future)' !== mb_substr( $recipe['label'], -9 )
                && ' (draft)' !== mb_substr( $recipe['label'], -8 )
                && ' (pending)' !== mb_substr( $recipe['label'], -10 )
                && ' (private)' !== mb_substr( $recipe['label'], -10 ) ) ) {
                $current_recipes[] = new WPURP_Recipe( $recipe['value'] );
            }
        }

        if( count( $current_recipes ) > 0 ) {
            $recipes_grouped[] = array(
                'group_name' => $current_letter,
                'recipes' => $current_recipes
            );
        }

        return $recipes_grouped;
    }

    public function get_recipes_grouped_by( $groupby, $slug = false )
    {
        $post_ids = false;
        if( $slug ) {
            $post = get_page_by_path( $slug, OBJECT, WPUPG_POST_TYPE );

            if ( !is_null( $post ) ) {
                $grid = new WPUPG_Grid( $post );
                $posts = $grid->posts();
                $post_ids = $posts['all'];
            }
        }

        $post_ids = apply_filters( 'wpurp_meal_planner_recipes_post_ids', $post_ids, $groupby, $slug );

        $recipes_grouped = array();
        switch( $groupby ) {

            case 'a-z':
                $recipes = WPUltimateRecipe::get()->query()->ids( $post_ids )->order_by( 'title' )->order( 'ASC' )->get();

                $current_letter = '';
                $current_recipes = array();

                foreach( $recipes as $recipe ) {
                    $letter = strtoupper( mb_substr( $recipe->title(), 0, 1 ) );

                    if( $letter != $current_letter ) {
                        if($current_letter != '') {
                            $recipes_grouped[] = array(
                                'group_name' => $current_letter,
                                'recipes' => $current_recipes
                            );
                        }

                        $current_letter = $letter;
                        $current_recipes = array();
                    }

                    $current_recipes[] = $recipe;
                }

                if( count( $current_recipes ) > 0 ) {
                    $recipes_grouped[] = array(
                        'group_name' => $current_letter,
                        'recipes' => $current_recipes
                    );
                }

                break;

            default:
                $terms = get_terms( $groupby );

                foreach( $terms as $term ) {
                    $recipes_grouped[] = array(
                        'group_name'    => $term->name,
                        'recipes' => WPUltimateRecipe::get()->query()->ids( $post_ids )->order_by( 'title' )->order( 'ASC' )->taxonomy( $groupby )->term( $term->slug )->get(),
                    );
                }
                break;
        }

        return apply_filters( 'wpurp_meal_planner_recipes', $recipes_grouped, $groupby, $slug );
    }

    public function get_select_options($recipe_groups)
    {
        $recipe_cache = WPUltimateRecipe::get()->helper( 'cache' )->get( 'recipes_with_data' );
        $out = '<option></option>';

        foreach( $recipe_groups as $group )
        {
            $out .= '<optgroup label="' . esc_attr( $group['group_name'] ) . '">';

            foreach( $group['recipes'] as $recipe )
            {
                if( isset( $recipe_cache[$recipe->ID()] ) ) {
                    $title = $recipe_cache[$recipe->ID()]['title'];
                } else {
                    $title = $recipe->title();
                    if ( 'publish' !== $recipe->post_status() ) {
                        $title .= ' (' . $recipe->post_status() . ')';
                    }
                }

                $out .= '<option value="' . $recipe->ID() . '">' . $title . '</option>';
            }

            $out .= '</optgroup>';
        }

        return $out;
    }

    public function view_meal_plan_link() {
        $screen = get_current_screen();

        if ( WPURP_MEAL_PLAN_POST_TYPE === $screen->post_type ) {
            echo '<a href="' . admin_url( 'edit.php?post_type=recipe&page=wpurp_view_meal_plan' ) . '" class="button" style="display: inline-block;">' . __( 'View Meal Planner by User', 'wp-ultimate-recipe' ) . '</a>';
        }
    }

    public function view_meal_plan_page() {
        add_submenu_page( null, __( 'View Meal Planner by User', 'wp-ultimate-recipe' ), __( 'View Meal Planner by User', 'wp-ultimate-recipe' ), 'manage_options', 'wpurp_view_meal_plan', array( $this, 'view_meal_plan_page_content' ) );
    }

    public function view_meal_plan_page_content() {
        if ( !current_user_can('manage_options') ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        require( $this->addonDir. '/templates/view_meal_plan.php' );
    }

    public function meal_planner_shortcode( $options )
    {
        $options = shortcode_atts( array(
            'posts_from_grid' => '',
        ), $options );

        switch( WPUltimateRecipe::option( 'meal_planner_enable', 'guests' ) ) {

            case 'off':
                return WPUltimateRecipe::option( 'meal_planner_disabled_text', '' );
                break;

            case 'registered':
                if( !is_user_logged_in() ) {
                    return WPUltimateRecipe::option( 'meal_planner_disabled_text', '' );
                }
            // Logged in? Fall through!
            case 'guests':
                // Posts from Grid
                $posts_from_grid = strtolower( trim( $options['posts_from_grid'] ) );

                $script_name = WPUltimateRecipe::option( 'assets_use_minified', '1' ) == '1' ? 'wpurp_script_minified' : 'wpurp-meal-planner';
                wp_localize_script( $script_name, 'wpurp_meal_planner_grid',
                    array(
                        'slug' => $posts_from_grid,
                    )
                );

                // Include template
                ob_start();
                include( $this->addonDir . '/templates/meal-planner.php' );
                $meal_planner = ob_get_contents();
                ob_end_clean();

                return apply_filters( 'wpurp_meal_planner', $meal_planner, $options );
                break;
        }

    }

    public function meal_plan_shortcode( $options )
    {
        $slug = strtolower( trim( $options['id'] ) );

        if( $slug ) {
            $post = get_page_by_path($slug, OBJECT, WPURP_MEAL_PLAN_POST_TYPE);

            if ( !is_null( $post ) ) {
                ob_start();
                include( $this->addonDir . '/templates/meal-plan.php' );
                $meal_plan = ob_get_contents();
                ob_end_clean();

                return apply_filters( 'wpurp_meal_planner_shortcode', $meal_plan, $options );
            }
        }
    }

    /**
    * Source: https://gist.github.com/mcaskill/02636e5970be1bb22270
    * Convert date/time format between `date()` and `strftime()`
    *
    * Timezone conversion is done for Unix. Windows users must exchange %z and %Z.
    *
    * Unsupported date formats : S, n, t, L, B, G, u, e, I, P, Z, c, r
    * Unsupported strftime formats : %U, %W, %C, %g, %r, %R, %T, %X, %c, %D, %F, %x
    *
    * @example Convert `%A, %B %e, %Y, %l:%M %P` to `l, F j, Y, g:i a`, and vice versa for "Saturday, March 10, 2001, 5:16 pm"
    * @link http://php.net/manual/en/function.strftime.php#96424
    *
    * @param string $format The format to parse.
    * @param string $syntax The format's syntax. Either 'strf' for `strtime()` or 'date' for `date()`.
    * @return bool|string Returns a string formatted according $syntax using the given $format or `false`.
    */
    function date_format_to( $format, $syntax )
    {
        // http://php.net/manual/en/function.strftime.php
        $strf_syntax = array(
            // Day - no strf eq : S (created one called %O)
            '%O', '%d', '%a', '%e', '%A', '%u', '%w', '%j',
            // Week - no date eq : %U, %W
            '%V',
            // Month - no strf eq : n, t
            '%B', '%m', '%b', '%-m',
            // Year - no strf eq : L; no date eq : %C, %g
            '%G', '%Y', '%y',
            // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
            '%P', '%p', '%l', '%I', '%H', '%M', '%S',
            // Timezone - no strf eq : e, I, P, Z
            '%z', '%Z',
            // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
            '%s'
        );

        // http://php.net/manual/en/function.date.php
        $date_syntax = array(
            'S', 'd', 'D', 'j', 'l', 'N', 'w', 'z',
            'W',
            'F', 'm', 'M', 'n',
            'o', 'Y', 'y',
            'a', 'A', 'g', 'h', 'H', 'i', 's',
            'O', 'T',
            'U'
        );

        switch ( $syntax ) {
            case 'date':
                $from = $strf_syntax;
                $to   = $date_syntax;
                break;

            case 'strf':
                $from = $date_syntax;
                $to   = $strf_syntax;
                break;

            default:
                return false;
        }

        $pattern = array_map( array( $this, 'date_format_to_map' ), $from );
        return preg_replace( $pattern, $to, $format );
    }

    function date_format_to_map( $s ) {
        return '/(?<!\\\\|\%)' . $s . '/';
    }
}

WPUltimateRecipe::loaded_addon( 'meal-planner', new WPURP_Meal_Planner() );