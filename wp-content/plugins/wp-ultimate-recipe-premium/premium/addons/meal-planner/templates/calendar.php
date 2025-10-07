<?php
$view_meal_plan = isset( $view_meal_plan ) ? intval( $view_meal_plan ) : false;
$meal_plan = $this->get_meal_plan( $meal_plan_id, $meal_plan_admin, $view_meal_plan );
$time_in_day = 24*60*60;

if( !class_exists( 'WPURP_Mobile_Detect' ) ) {
    include_once( WPUltimateRecipePremium::get()->premiumDir . '/vendor/Mobile_Detect.php' );
}
$detect = new WPURP_Mobile_Detect;

if( $detect->isTablet() ) {
    $nbr_of_days = intval( WPUltimateRecipe::option( 'meal_planner_days_tablet', 3 ) );
} elseif( $detect->isMobile() ) {
    $nbr_of_days = intval( WPUltimateRecipe::option( 'meal_planner_days_mobile', 1 ) );
} else {
    $nbr_of_days = intval( WPUltimateRecipe::option( 'meal_planner_days_desktop', 5 ) );
}

$dates = array();
$courses = $meal_plan['courses'];
?>

<table class="wpurp-meal-plan-calendar" data-admin="<?php echo $meal_plan_admin ? 'true' : 'false'; ?>" data-meal-plan-id="<?php echo $meal_plan_id; ?>" data-start-date="<?php echo $start_date->format( 'Ymd' ); ?>" data-end-date="<?php echo $end_date ? $end_date->format( 'Ymd' ) : ''; ?>" data-nbr-days="<?php echo $nbr_of_days; ?>">
    <thead>
    <tr>
        <?php
        // Adjust columns shown if there's an end date
        if( $end_date ) {
            $date_diff = date_diff( $end_date, $start_date );
            $date_diff = $date_diff->days + 1;
            if( $nbr_of_days > $date_diff ) {
                $nbr_of_days = $date_diff;
            }
        }

        for( $i = 0; $i < $nbr_of_days; $i++ ) {
            // Get Recipes for Date
            $date_key = intval( $start_date->format( 'Ymd' ) );
            $dates[$i] = isset( $meal_plan['dates'][$date_key] ) ? $meal_plan['dates'][$date_key] : array();

            // Get Courses
            foreach( $dates[$i] as $course => $recipes ) {
                if( !in_array( $course, $courses ) ) {
                    $courses[] = $course;
                }
            }

            // Output Header
            $class = $start_date->format( 'N' ) >= 6 ? 'wpurp-meal-plan-date-weekend' : 'wpurp-meal-plan-date-weekday';

            echo '<th class="' . $class . '" width="' . (100.0/$nbr_of_days) . '%">' ;

            $date_format = WPUltimateRecipe::option( 'meal_planner_date_format', '%B %e' );
            if(strpos($date_format,'%') === false) {
                $date_format = $this->date_format_to( $date_format, 'strf' );
            }
            echo '<div class="wpurp-meal-plan-date">' . strftime( $date_format, $start_date->getTimestamp() ) . '</div>';
            echo '<div class="wpurp-meal-plan-date-readable">';
            if( $i == 0 && $start_date == new DateTime( 'today', WPUltimateRecipe::get()->timezone() ) ) {
                _e( 'Today', 'wp-ultimate-recipe' );
            } elseif( $i == 1 && $start_date == new DateTime( 'tomorrow', WPUltimateRecipe::get()->timezone() ) ) {
                _e( 'Tomorrow', 'wp-ultimate-recipe' );
            } else {
                $day_of_week = $start_date->format( 'N' );
                switch( $day_of_week ) {
                    case 1:
                        _e( 'Monday' );
                        break;
                    case 2:
                        _e( 'Tuesday' );
                        break;
                    case 3:
                        _e( 'Wednesday' );
                        break;
                    case 4:
                        _e( 'Thursday' );
                        break;
                    case 5:
                        _e( 'Friday' );
                        break;
                    case 6:
                        _e( 'Saturday' );
                        break;
                    case 7:
                        _e( 'Sunday' );
                        break;
                }
            }
            echo '</div>';

            // Header in day numbers from 2000
            $date_diff = date_diff( new DateTime( '2000-01-01', WPUltimateRecipe::get()->timezone() ), $start_date );
            $date_diff = $date_diff->days + 1;

            echo '<div class="wpurp-meal-plan-date-readable wpurp-meal-plan-date-readable-numbers">';
            echo __( 'Day', 'wp-ultimate-recipe' ) . ' ' . $date_diff;
            echo '</div>';

            if( $i == 0 ) {
                if( $meal_plan_id == 0 || $start_date != new DateTime( '2000-01-01', WPUltimateRecipe::get()->timezone() ) ) {
                    echo '<i class="fa fa-chevron-left wpurp-meal-plan-date-change wpurp-meal-plan-date-prev"></i>';
                }
            }
            if( $i == $nbr_of_days-1 ) {
                if( !$end_date || $end_date > $start_date ) {
                    echo '<i class="fa fa-chevron-right wpurp-meal-plan-date-change wpurp-meal-plan-date-next"></i>';
                }
            }

            echo '</th>';

            $start_date->modify( '+1 day' );
        }
        ?>
    </tr>
    </thead>
    <?php
    foreach( $courses as $index => $course ) {
        $up_disabled = $index == 0 ? ' wpurp-disabled' : '';
        $down_disabled = $index == count( $courses ) - 1 ? ' wpurp-disabled' : '';

        echo '<tbody class="wpurp-meal-plan-course">';
        echo '<tr class="wpurp-meal-plan-header">';
        echo '<td colspan="' . $nbr_of_days . '"><span class="wpurp-meal-plan-course-name">' . $course . '</span><span class="wpurp-meal-plan-actions"><i class="fa fa-arrow-down wpurp-course-move wpurp-course-down' . $down_disabled . '"></i><i class="fa fa-arrow-up wpurp-course-move wpurp-course-up' . $up_disabled . '"></i><i class="fa fa-pencil wpurp-course-edit"></i><i class="fa fa-trash wpurp-course-delete"></i></span></td>';
        echo '</tr>';

        echo '<tr class="wpurp-meal-plan-recipes">';
        foreach( $dates as $date => $recipes ) {
            echo '<td class="wpurp-meal-plan-recipe-list">';

            if( isset( $recipes[$course] ) ) {
                foreach( $recipes[$course] as $recipe ) {
                    $type = isset( $recipe['type'] ) ? $recipe['type'] : 'recipe';
                    $nutrition_fields = WPUltimateRecipe::option( 'meal_plan_nutrition_facts_fields', array( 'calories', 'fat', 'carbohydrate', 'protein' ) );

                    if( $type == 'recipe' ) {
                        $recipe_obj = new WPURP_Recipe( $recipe['id'] );

                        $nutritional_facts = array();
                        foreach( $nutrition_fields as $nutrition_field ) {
                            $nutritional_facts[] = $recipe_obj->nutritional( $nutrition_field );
                        }

                        $title = $recipe_obj->title();
                    } else {
                        $ingredient = get_term_by( 'id', $recipe['id'], 'ingredient' );
                        $ingredient_nutrition = WPUltimateRecipe::addon('nutritional-information')->get( $recipe['id'] );

                        $nutritional_facts = array();
                        if( $ingredient_nutrition ) {
                            foreach( $nutrition_fields as $nutrition_field ) {
                                $nutritional_facts[] = $ingredient_nutrition[ $nutrition_field ];
                            }
                        }

                        $title = $ingredient->name;

                        // Ingredient Name Display
                        switch( WPUltimateRecipe::option( 'meal_plan_ingredients_display', 'name' ) ) {
                            case 'name_metric':
                                $title = $ingredient_nutrition && $ingredient_nutrition['_meta'] ? $ingredient_nutrition['_meta']['serving_quantity'] . $ingredient_nutrition['_meta']['serving_unit'] . ' ' . $title : $title;
                                break;
                            case 'name_reference':
                                $title = $ingredient_nutrition && $ingredient_nutrition['_meta'] ? $ingredient_nutrition['_meta']['serving'] . ' ' . $title : $title;
                                break;
                            // Default just leave $title
                        }
                    }

                    $nutritional_facts = array_map( 'floatval', $nutritional_facts );
                    $nutritional = ' data-nutrition="' . implode( ';', $nutritional_facts ) . '"';

                    $quantity = isset( $recipe['quantity'] ) ? $recipe['quantity'] : '1';
                    $leftovers = isset( $recipe['leftovers'] ) && $recipe['leftovers'] ? 'true' : 'false';
                    $title = $leftovers == 'true' ? $title . ' (' . __( 'leftovers', 'wp-ultimate-recipe' ) . ')' : $title;

                    echo '<div class="wpurp-meal-plan-recipe" data-recipe="' . $recipe['id'] . '" data-type="' . $type . '" data-quantity="' . $quantity . '" data-servings="' . $recipe['servings'] . '"' . $nutritional . ' data-leftovers="' . $leftovers . '">';

                    if( $type == 'ingredient') {
                        echo '<span class="wpurp-meal-plan-recipe-quantity">' . $recipe['quantity'] . '</span>x ';
                    } elseif( $recipe_obj->image_url( 'thumbnail' ) ) {
                        echo '<img src="' . $recipe_obj->image_url( 'thumbnail' ) .'">';
                    }

                    echo '<span class="wpurp-meal-plan-recipe-title">' . $title . '</span>  <span class="wpurp-meal-plan-recipe-servings"> (' . $recipe['servings'] . ')</span>';
                    echo '</div>';
                }
            }

            echo '</td>';
        }
        echo '</tr>';
        echo '</tbody>';
    }
    ?>
    <tbody class="wpurp-meal-plan-course-placeholder">
    <tr class="wpurp-meal-plan-header">
        <td colspan="<?php echo $nbr_of_days; ?>"><span class="wpurp-meal-plan-course-name"></span><span class="wpurp-meal-plan-actions"><i class="fa fa-arrow-down wpurp-course-move wpurp-course-down"></i><i class="fa fa-arrow-up wpurp-course-move wpurp-course-up"></i><i class="fa fa-pencil wpurp-course-edit"></i><i class="fa fa-trash wpurp-course-delete"></i></span></td>
    </tr>
    <tr class="wpurp-meal-plan-recipes">
        <?php
        for( $i = 0; $i < $nbr_of_days; $i++ ) {
            echo '<td class="wpurp-meal-plan-recipe-list"></td>';
        }
        ?>
    </tr>
    </tbody>
    <?php if( WPUltimateRecipe::option( 'meal_plan_nutrition_facts', '0' ) == '1') { ?>
    <tbody class="wpurp-meal-plan-nutrition">
    <tr class="wpurp-meal-plan-header">
        <?php if( WPUltimateRecipe::option( 'meal_plan_nutrition_facts_total', '0' ) == '1') { ?>
        <td colspan="<?php echo $nbr_of_days; ?>"><?php _e( 'Nutrition Facts', 'wp-ultimate-recipe' ); ?></td>
        <?php } else { ?>
        <td colspan="<?php echo $nbr_of_days; ?>"><?php _e( 'Nutrition Facts Per Serving', 'wp-ultimate-recipe' ); ?></td>
        <?php } ?>
    </tr>
    <?php
        $nutrition_fact_labels = array(
            'calories'              => array( __( 'Calories', 'wp-ultimate-recipe' ), 'kcal'),
            'fat'                   => array( __( 'Fat', 'wp-ultimate-recipe' ), 'g'),
            'carbohydrate'          => array( __( 'Carbohydrates', 'wp-ultimate-recipe' ), 'g'),
            'protein'               => array( __( 'Protein', 'wp-ultimate-recipe' ), 'g'),
            'saturated_fat'         => array( __( 'Saturated Fat', 'wp-ultimate-recipe' ), 'g'),
            'polyunsaturated_fat'   => array( __( 'Polyunsaturated Fat', 'wp-ultimate-recipe' ), 'g'),
            'monounsaturated_fat'   => array( __( 'Monounsaturated Fat', 'wp-ultimate-recipe' ), 'g'),
            'trans_fat'             => array( __( 'Trans Fat', 'wp-ultimate-recipe' ), 'g'),
            'cholesterol'           => array( __( 'Cholesterol', 'wp-ultimate-recipe' ), 'mg'),
            'sodium'                => array( __( 'Sodium', 'wp-ultimate-recipe' ), 'mg'),
            'potassium'             => array( __( 'Potassium', 'wp-ultimate-recipe' ), 'mg'),
            'fiber'                 => array( __( 'Fiber', 'wp-ultimate-recipe' ), 'g'),
            'sugar'                 => array( __( 'Sugar', 'wp-ultimate-recipe' ), 'g'),
            'vitamin_a'             => array( __( 'Vitamin A', 'wp-ultimate-recipe' ), '%'),
            'vitamin_c'             => array( __( 'Vitamin C', 'wp-ultimate-recipe' ), '%'),
            'calcium'               => array( __( 'Calcium', 'wp-ultimate-recipe' ), '%'),
            'iron'                  => array( __( 'Iron', 'wp-ultimate-recipe' ), '%'),
        );

        if( WPUltimateRecipe::option( 'nutritional_information_unit', 'calories' ) == 'kilojoules') {
            $nutrition_fact_labels['calories'] = array( __( 'Energy', 'wp-ultimate-recipe' ), 'kJ');
        }

        $nutrition_fields = WPUltimateRecipe::option( 'meal_plan_nutrition_facts_fields', array( 'calories', 'fat', 'carbohydrate', 'protein' ) );
        foreach( $nutrition_fields as $nutrition_field ) {
            $nutrition_fact_label = $nutrition_fact_labels[ $nutrition_field ][0];
            $nutrition_fact_unit = $nutrition_fact_labels[ $nutrition_field ][1];
    ?>
    <tr class="wpurp-meal-plan-nutrition-facts wpurp-meal-plan-nutrition-facts-<?php echo $nutrition_field; ?>">
        <td>
            <span class="wpurp-meal-plan-nutrition-type"><?php echo $nutrition_fact_label; ?></span>
            <span class="wpurp-meal-plan-nutrition-unit">(<?php echo $nutrition_fact_unit; ?>)</span>
            <span class="wpurp-meal-plan-nutrition-value"></span>
        </td>
        <?php
        for( $i = 1; $i < $nbr_of_days; $i++ ) {
            echo '<td><span class="wpurp-meal-plan-nutrition-value"></span></td>';
        }
        ?>
    </tr>
    <?php } ?>
    <tr class="wpurp-meal-plan-nutrition-facts wpurp-meal-plan-nutrition-facts-missing">
        <?php
        for( $i = 0; $i < $nbr_of_days; $i++ ) {
            echo '<td><span class="wpurp-meal-plan-nutrition-missing">' . __( 'Recipes without nutrition data:', 'wp-ultimate-recipe' ) . ' <span class="wpurp-meal-plan-nutrition-missing-value"></span></span></td>';
        }
        ?>
    </tr>
    </tbody>
    <?php } ?>
    <tbody class="wpurp-meal-plan-selected-recipe">
    <tr class="wpurp-meal-plan-header">
        <td colspan="<?php echo $nbr_of_days; ?>">
            <span class="recipe-not-selected"><?php _e( 'Click on a recipe for more details', 'wp-ultimate-recipe' ); ?></span>
            <div class="recipe-details-loader wpurp-loader"><div></div><div></div><div></div></div>
            <span class="recipe-selected"><?php _e( 'Selected Recipe:', 'wp-ultimate-recipe' ); ?> <span class="recipe-title"></span><span class="wpurp-meal-plan-actions"><i class="fa fa-close wpurp-recipe-close"></i><i class="fa fa-trash wpurp-recipe-delete"></i></span></span>
            <span class="recipe-selected-multiple"><?php _e( 'Selected Recipes:', 'wp-ultimate-recipe' ); ?> <span class="selected-number"></span><span class="wpurp-meal-plan-actions"><i class="fa fa-cutlery wpurp-recipe-edit-multiple"></i><i class="fa fa-trash wpurp-recipe-delete-multiple"></i></span></span>
        </td>
    </tr>
    <tr class="wpurp-meal-plan-selected-recipe-details">
        <td colspan="<?php echo $nbr_of_days; ?>">
            <div class="wpurp-meal-plan-selected-recipe-details-container"></div>
        </td>
    </tr>
    </tbody>
</table>
<div class="wpurp-meal-plan-footer-actions">
    <?php if( $meal_plan_id > 0 && WPUltimateRecipe::option( 'saved_meal_plan_add_to_meal_planner', '1' ) == '1' && ( WPUltimateRecipe::option( 'meal_planner_enable', 'guests' ) == 'guests' || is_user_logged_in() ) ) { ?>
    <button type="button" class="wpurp-meal-plan-button wpurp-meal-plan-add-meal-planner"><?php _e( 'Save to Meal Planner', 'wp-ultimate-recipe' ); ?></button>
    <?php } ?>
    <button type="button" class="wpurp-meal-plan-button wpurp-meal-plan-add-course"><?php _e( 'Add Course', 'wp-ultimate-recipe' ); ?></button>
    <div class="wpurp-meal-plan-footer-actions-right">
        <?php if ( WPUltimateRecipe::option( 'meal_planner_print_recipes_button', '1' ) == '1' ) : ?>
        <button type="button" class="wpurp-meal-plan-button wpurp-meal-plan-print-recipes" onclick="RecipeMealPlanner.printRecipes(this)"><?php _e( 'Print Recipes', 'wp-ultimate-recipe' ); ?></button>
        <?php endif; ?>
        <?php if ( WPUltimateRecipe::option( 'meal_planner_print_plan_button', '1' ) == '1' ) : ?>
        <button type="button" class="wpurp-meal-plan-button wpurp-meal-plan-print" onclick="RecipeMealPlanner.printPlan(this)"><?php _e( 'Print Plan', 'wp-ultimate-recipe' ); ?></button>
        <?php endif; ?>
        <button type="button" class="wpurp-meal-plan-button wpurp-meal-plan-shopping-list"><?php _e( 'Generate Shopping List', 'wp-ultimate-recipe' ); ?></button>
    </div>
</div>