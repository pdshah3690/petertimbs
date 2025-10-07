jQuery(document).ready(function() {
    jQuery('.clone-recipe').on('click', function() {
        var recipe = jQuery(this).data('recipe');
        var nonce = jQuery(this).data('nonce');

        var data = {
            action: 'clone_recipe',
            security: wpurp_recipe_cloner.nonce,
            recipe_meta_box_nonce: nonce,
            recipe: recipe
        };

        jQuery.post(wpurp_recipe_cloner.ajax_url, data, function(out) {
            window.location = out.redirect;
        }, 'json');
    });

    jQuery('.clone-meal-plan').on('click', function() {
        var meal_plan = jQuery(this).data('meal-plan');

        var data = {
            action: 'clone_meal_plan',
            security: wpurp_recipe_cloner.nonce,
            meal_plan: meal_plan
        };

        jQuery.post(wpurp_recipe_cloner.ajax_url, data, function(out) {
            window.location = out.redirect;
        }, 'json');
    });
});