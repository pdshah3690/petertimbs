<div class="wrap wpurp-import">

    <div id="icon-themes" class="icon32"></div>
    <h2><?php _e( 'View Meal Planner by User', 'wp-ultimate-recipe' ); ?></h2>
    <form method="POST" action="<?php echo admin_url( 'edit.php?post_type=recipe&page=wpurp_view_meal_plan' ); ?>">
        <?php
        $user_id = isset( $_POST['user'] ) ? intval( $_POST['user'] ) : 0;

        $args = array(
            'show' => 'user_login',
            'selected' => $user_id,
        );
        wp_dropdown_users( $args );
        submit_button( __( 'View Meal Planner', 'wp-ultimate-recipe' ), 'primary', 'submit', false );
        ?>
    </form>

    <?php
    if ( $user_id ) :
        $user = get_user_by( 'id', $user_id );
    ?>
    <h3><?php echo __( 'Meal Planner for', 'wp-ultimate-recipe' ) . ' ' . $user->user_login . ' - #' . $user->ID; ?></h3>

    <?php
    wp_localize_script( 'wpurp-meal-planner', 'wpurp_meal_planner_grid',
        array(
            'slug' => '',
            'view_meal_plan' => $user_id,
        )
    );
    if( !class_exists( 'WPURP_Mobile_Detect' ) ) {
        include_once( WPUltimateRecipePremium::get()->premiumDir . '/vendor/Mobile_Detect.php' );
    }
    $detect = new WPURP_Mobile_Detect;

    if( $detect->isMobile() ) {
        $class = 'wpurp-meal-plan-mobile';
    } else {
        $class = 'wpurp-meal-plan-desktop';
    }
    ?>

    <div class="wpurp-view-meal-plan <?php echo $class; ?>" data-id="<?php echo esc_attr( $user_id ); ?>">
        <div class="wpurp-meal-plan-calendar-container">
            <?php
            $view_meal_plan = $user_id;
            $meal_plan_admin = false;
            $meal_plan_id = 0;
            $start_date = new DateTime( 'today', WPUltimateRecipe::get()->timezone() );
            $end_date = false;
            include( $this->addonDir . '/templates/calendar.php' );
            ?>
        </div>
    </div>
    
    <?php endif; ?>
</div>