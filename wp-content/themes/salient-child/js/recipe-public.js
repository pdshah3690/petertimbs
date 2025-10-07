(function( $ ) {
    'use strict';
    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(document).on("click", "#recipe-master-ingredient-tag .tag-cloud-link", function(e) {
        console.log("master ingredient tags");
        // $("#recipe-meat-tag .tag-cloud-link").removeClass("active");
        // $("#recipe-difficulty-tag .tag-cloud-link").removeClass("active");
        $(".tag-cloud-link span").removeClass("active");
        $(this).toggleClass("active");
        $("span", $(this)).addClass("active");
        var term_ids = [];
        $("#recipe-master-ingredient-tag .tag-cloud-link.active").each(function(e){
            var term_id = $(this).data("term_id");
            term_ids.push(term_id);
        });
        get_recipes_by_filters(term_ids, 'ingredient');
    });
    $(document).on("click", "#recipe-meat-tag .tag-cloud-link", function(e) {
        $(this).toggleClass("active");
        get_recipes_by_filters();
    });
    $(document).on("click", "#recipe-difficulty-tag .tag-cloud-link", function(e) {
        $(this).toggleClass("active");
        get_recipes_by_filters();
    });
    $(document).on("click", "#recipe-allergen-tag .tag-cloud-link", function(e) {
        $(this).toggleClass("active");
        $("span", $(this)).toggleClass("active");
        $(".tag-cloud-link span, .tag-cloud-link .recipe-icon", $(this)).toggleClass("active");
        $(".recipe-icon", $(this)).toggleClass("active");
        get_recipes_by_filters();
    });

    $("#ingredient-no-filter").on("select2:select", function(e){
        get_recipes_by_filters();
    });
    $("#serving-size-filter").on("select2:select", function(){
        get_recipes_by_filters();
    });
    $("#cook-time-filter").on("select2:select", function(){
        get_recipes_by_filters();
    });

    $(document).on("hover", ".wpurp-recipe-stars i", function(e){
        var star = $(this).data("value");
        var i = 1;
        $(".wpurp-recipe-stars i").each(function(k, v){
            if(i <= star) {
                $(v).removeClass("fa-star-o");
                $(v).removeClass("fa-star-half-o");
                $(v).addClass("fa-star");
            } else {
                $(v).removeClass("fa-star");
                $(v).removeClass("fa-star-half-o");
                $(v).addClass("fa-star-o");
            }
            i++;
        });
    });
    $(document).on("click", ".wpurp-recipe-stars i", function(e){
        var stars = $(this).data("value");
        $.ajax({
            url : wpurp_user_ratings.ajax_url,
            data : {
                action: 'rate_recipe',
                security: wpurp_user_ratings.nonce,
                stars: stars,
                recipe: nectarLove.postID                
            },
            dataType : "json",
            type : "post",
            success : function(response) {
                var html = "";
                for(var i=1; i<=5; i++) {
                    if( i <= response['stars'] ) {
                        var icon = "fa-star";
                    } else if( i-1 == response['stars'] && response['half_star'] == true ) {
                        var icon = "fa-star-half-o";
                    }  else {
                        var icon = "fa-star-o";
                    }
                    html = html + '<i data-value="' + i + '" class="wpurp-star fa '+ icon +'" data-original="'+ icon +'"></i>';
                }
                $(".wpurp-recipe-stars").html(html);
            }
        })
    });
    $(document).on("change", ".rating-filter", function(e){
        get_recipes_by_filters();
    });

    $('.rating-filter').rating({
        defaultCaption : '',
        filledStar: '<i class="fa fa-star"></i>',
        emptyStar: '<i class="fa fa-star-o"></i>'
    });

    $(document).on("mouseout", ".wpurp-recipe-stars", function(e){
        $(".wpurp-recipe-stars i").each(function(k, v){
            var style = $(v).data("original");
            $(v).removeAttr("class");
            $(v).attr("class", "fa "+style);
        });
    });

    $(document).on("click", "#recipe-tabs a", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $(".tab").removeClass("active");
        $(this).parent().addClass("active");
        $(".panel.entry-content").addClass("hide");
        $(id).removeClass("hide");
    });

    function get_recipes_by_filters() {
        var allergens = [];
        var meat = []; 
        var difficulty = [];
        var ingredient_no = $("#ingredient-no-filter").val();
        var serving_size =  $("#serving-size-filter").val();
        var cook_time = $("#cook-time-filter").val();
        var ratings = $('.rating-filter').val();
        $("#recipe-allergen-tag .tag-cloud-link.active").each(function(e){
            var term_id = $(this).data("term_id");
            allergens.push(term_id);
        });
        $("#recipe-meat-tag .tag-cloud-link.active").each(function(e){
            var term_id = $(this).data("term_id");
            meat.push(term_id);
        });
        $("#recipe-difficulty-tag .tag-cloud-link.active").each(function(e){
            var term_id = $(this).data("term_id");
            difficulty.push(term_id);
        });

        jQuery.ajax({
            url : wc_add_to_cart_params.ajax_url,
            type : "post",
            data : {
                action : "get_recipes_by_filters",
                allergens : allergens,
                meat : meat,
                difficulty : difficulty,
                ingredient_no : ingredient_no,
                serving_size : serving_size,
                cook_time : cook_time,
                ratings : ratings
            },
            success : function(recipes) {
                jQuery("#recipe-container .span_9 .posts-container").html(recipes);
            }
        });
    }
 })(jQuery);