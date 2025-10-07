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
    var variation_select2 = $("#buthcer-box-variation").select2();
    var butcherbox_product = $("#butcherbox_product").select2();
    var product_variations;
    var previous;
    $("#buthcer-box-variation").on("select2:opening", function(){
        previous = $('#buthcer-box-variation').val();
    }).on("select2:select", function(){
        var size = $('#buthcer-box-variation').val();
        var price = $('#buthcer-box-variation').find(":selected").data("price");
        var left_qty = calculate_left_qty();
        if(size > 0) {
            $(".select-product").removeClass("hide");
            $(".butcherbox-price").html(price);
            if(left_qty == 0) {
                var product_id      = $("#product_id").val();
                var variation_id    = $('#buthcer-box-variation').find(":selected").data("variation_id");
                var cart_item_data = get_added_bb_products();
                update_butcher_box_products(product_id, variation_id, cart_item_data, false);

                $(".single_add_to_cart_button").attr("disabled", "disabled");
            }
            if(left_qty < 0) {
                var total_qty = $('#buthcer-box-variation').val();
                var x = total_qty - left_qty;
                $(".woocommerce-notices-wrapper").html("<div class='woocommerce-message'>You currently have "+x+" items in your butchers box, please remove some before you can select a new butchers box size</div>");
                window.scrollTo(0, 0);
                $('#buthcer-box-variation').val(previous);
                variation_select2.trigger('change');
                previous = '';
                return false;
            }
        } else {
            $(".bb_variation_wrap").addClass("hide");
            $(".select-product").addClass("hide");
        }
    });

    $("#butcherbox_product").on("select2:select", function(){
        var product_id = $('#butcherbox_product').val();
        var is_variable = $('#butcherbox_product').find(":selected").data("is_variable");
        if(product_id > 0) {
            $(".bb_variation_wrap").removeClass("hide");
            if(is_variable > 0 ) {
                $(".single_add_to_cart_button").attr("disabled", "disabled");
                get_product_butcherbox_variation(product_id);
            } else {
                $('#bb-product-variation-1').select2('destroy');
                $('.product-variations-tr-1').addClass('hide');
                $('#bb-product-variation-2').select2('destroy');
                $('.product-variations-tr-2').addClass('hide');
            }
        }
    });

    $("#bb-product-variation-1").on("select2:select", function(e){
        var attr1 = $(this).val();
        if(attr1 != "") {
            if(product_variations[attr1][0]['variation'] != null) {
                var attr2 = product_variations[attr1][0]['attr_1'];
                $(".product-variations-tr-2").removeClass("hide");
                var html = "<option value='0'>Choose a "+attr2+"</option>";
                $.each(product_variations[attr1], function(k, v){
                    html = html + "<option value='"+ v.variation_id +"'>"+ v.variation +"</option>";
                });
                $('#bb-product-variation-2').html(html);
                $('#bb-product-variation-2').select2();
                $(".select2-container", $(".product-variations-tr-2")).attr("style", "width: 100%;");
            } else {
                $('#bb-product-variation-2').html("");
                $(".product-variations-tr-2").addClass("hide");
            }
            $(".single_add_to_cart_button").removeAttr("disabled");
        } else {
            $(".product-variations-tr-2").addClass("hide");
            $(".single_add_to_cart_button").attr("disabled", "disabled");
        }
    });

    $(".bb_variation_wrap .plus").on("click", function() {
        var left_qty = calculate_left_qty();
        var qty = $("#quantity_bb").val();
        qty++;
        if(qty <= left_qty) {
            $("#quantity_bb").val(qty);
        }
    });

    $(".bb_variation_wrap .minus").on("click", function() {
        var qty = $("#quantity_bb").val();
        if(qty > 1) {
            qty--;
            $("#quantity_bb").val(qty);
        }
    });

    $(".bb_variation_wrap .single_add_to_cart_button").click(function() {
        var product_id      = $("#product_id").val();
        var variation_id    = $('#buthcer-box-variation').find(":selected").data("variation_id");
        var bb_product_id   = $('#butcherbox_product').val();
        var bb_qty          = $("#quantity_bb").val();
        var is_variable     = $('#butcherbox_product').find(":selected").data("is_variable");
        var bb_variation_id = 0;
        if(bb_product_id == "" || bb_product_id == null || bb_product_id == undefined) {
            return false;
        }
        if(is_variable == 1) {
            bb_variation_id = $("#bb-product-variation-2").val();
            var attr_1 = $("#bb-product-variation-1").val();
            if(product_variations[attr_1][0]['variation'] == null) {
                bb_variation_id = product_variations[attr_1][0]['variation_id'];
            }
            console.log(bb_variation_id);
        }

        if(bb_variation_id == 0 && is_variable == 1) {
            $(this).attr("disabled", "disabled");
            return false;
        }
        if($("#bb_product_" + bb_product_id + "_" + bb_variation_id).length > 0) {
            $(".bb_qty_text", $("#bb_product_" + bb_product_id + "_" + bb_variation_id)).val(bb_qty);
        }
        var cart_item_data = get_added_bb_products();
        if($("#bb_product_" + bb_product_id + "_" + bb_variation_id).length == 0) {
            cart_item_data.push({
                "product_id"    : bb_product_id,
                "qty"           : bb_qty,
                "variation_id"  : bb_variation_id,
                "is_render"     : true
            });
        }
        $("#quantity_bb").val(1);
        update_butcher_box_products(product_id, variation_id, cart_item_data);
    });

    $(document).on("click", "a.remove-bb-item", function(e) {
        $(this).closest(".bb_item").remove();

        var product_id      = $("#product_id").val();
        var variation_id    = $('#buthcer-box-variation').find(":selected").data("variation_id");
        var cart_item_data = get_added_bb_products();
        update_butcher_box_products(product_id, variation_id, cart_item_data, false);

        var left_qty = calculate_left_qty();
        $(".single_add_to_cart_button").removeAttr("disabled");
    });

    $(document).on("click", ".bb_item .minus", function(e) {
        var product_id = $(this).data("product_id");
        var variation_id = $(this).data("variation_id");
        var tr = $("#bb_product_"+product_id+"_"+variation_id);
        var qty = $(".bb_qty_text", tr).val();
        if(qty > 1) {
            qty--;
            $(".bb_qty_text", tr).val(qty);
            var left_qty = calculate_left_qty();
            $(".single_add_to_cart_button").removeAttr("disabled");

            var product_id      = $("#product_id").val();
            var variation_id    = $('#buthcer-box-variation').find(":selected").data("variation_id");
            var cart_item_data = get_added_bb_products();
            update_butcher_box_products(product_id, variation_id, cart_item_data, false);
        }
    });

    $(document).on("click", ".bb_item .plus", function(e) {
        var left_qty = calculate_left_qty();
        if(left_qty > 0) {
            var product_id = $(this).data("product_id");
            var variation_id = $(this).data("variation_id");
            var tr = $("#bb_product_"+product_id+"_"+variation_id);
            var qty = $(".bb_qty_text", tr).val();
            qty++;
            $(".bb_qty_text", tr).val(qty);

            var left_qty = calculate_left_qty();
            var product_id      = $("#product_id").val();
            var variation_id    = $('#buthcer-box-variation').find(":selected").data("variation_id");
            var cart_item_data = get_added_bb_products();
            update_butcher_box_products(product_id, variation_id, cart_item_data, false);
        }
    });

    var get_product_butcherbox_variation = function (product_id) {
        $.ajax({
            url : wc_add_to_cart_params.ajax_url,
            type : "post",
            dataType : "json",
            data : {
                product_id : product_id,
                action : "get_product_variations"
            },
            success : function(response) {
                product_variations = response['variations'];
                var html = "<option value='0'>Choose a "+response['attr_0']+"</option>";
                $.each(response['variations'], function(k, v){
                    html = html + "<option value='"+  k +"'>" + k + "</option>";
                });
                $("#bb-product-variation-1").html(html);
                $("#bb-product-variation-1").select2();
                $(".product-variations-tr-1").removeClass("hide");
                $(".select2-container", $(".product-variations-tr-1")).removeAttr("style");
                $(".select2-container", $(".product-variations-tr-1")).attr("style", "width: 100%;");
                $(".single_add_to_cart_button").attr("disabled", "disabled");
            }
        });
    }

    var update_butcher_box_products = function (product_id, variation_id, cart_item_array, is_render = true) {
        if(is_render) {
            $( '.product_cat-butchers-box' ).block({ message: null, overlayCSS: { background: '#000', opacity: 0.6 } });
            render_butcher_box_products(cart_item_array);
        }
        $.ajax({
            url : wc_add_to_cart_params.ajax_url,
            type : "post",
            dataType : "json",
            data : {
                product_id : product_id,
                variation_id : variation_id,
                cart_item : cart_item_array,
                action : "add_products_to_butcherbox"
            },
            success : function(response) {
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
            }
        });
    }

    var calculate_left_qty = function () {
        var total_qty = $('#buthcer-box-variation').val();

        var bb_item_qty = 0;
        $("tr.bb_item, div.bb_item").each(function() {
            var bb_qty = $(".bb_qty_text", $(this)).val();
            bb_item_qty = parseInt(bb_item_qty) + parseInt(bb_qty);
        });
        var left_qty = total_qty - bb_item_qty;

        if(left_qty == 0 && total_qty > 0) {
            $(".single_add_to_cart_button").attr("disabled", "disabled");
            $(".woocommerce-notices-wrapper").html('<div class="woocommerce-message"><a href="http://www.petertimbs.jadecreative.co.nz/cart/" tabindex="1" class="button wc-forward">View cart</a> Your butchers box is full</div>');
            $(".left-qty").html("&nbsp;&nbsp;Your butchers box is full");
            window.scrollTo(0, 0);
        } else {
            $(".woocommerce-notices-wrapper").html("");
            $(".single_add_to_cart_button").removeAttr("disabled");
            if(left_qty > 0) {
                $(".left-qty").html(" / add "+left_qty+" more items to your box");
            }
        }
        return left_qty;
    }

    var render_butcher_box_products = function (bb_added_items) {
        $.ajax({
            url : wc_add_to_cart_params.ajax_url,
            type : "post",
            data : {
                action : "get_bb_added_products",
                cart_item : bb_added_items
            },
            success : function(html) {
                $('.product_cat-butchers-box').unblock();
                $(".added-products").removeClass("hide");
                $(".added-products .box-main-div").append(html);
                var left_qty = calculate_left_qty();
            }
        });
    }

    var get_added_bb_products = function () {
        var bb_item = [];
        $(".bb_item").each(function() {
            var bb_product_id = $(this).data("product_id");
            var bb_variation_id = $(this).data("variation_id");
            var bb_qty = $(".bb_qty_text", $(this)).val();
            bb_item.push({
                "product_id"    : bb_product_id,
                "qty"           : bb_qty,
                "variation_id"  : bb_variation_id
            });
        });
        return bb_item;
    }

    $(document).ready(function() {
        $("#buthcer-box-variation").trigger("select2:select");
    });
})(jQuery);