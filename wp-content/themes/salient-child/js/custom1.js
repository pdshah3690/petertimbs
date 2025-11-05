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
    $(document).ready(function (e) {
        var minDate = $("#hidden_prep_time").val();
        var disabledDates = pt_obj.disabled_dates;
        var pickup_disabled_dates = pt_obj.pickup_disabled_dates;
        var maxDate = pt_obj.max_date;
        if(pt_obj.is_disabled == 1) {
            // disabledDates.push(pt_obj.next_day);
        }
        if(pt_obj.pick_up_range_from.length > 0) {
            minDate = pt_obj.pick_up_range_from;
            maxDate = pt_obj.pick_up_range_to;
        }

        $("#pickup_date_time").datetimepicker({
            timepicker:true,
            format : 'Y-m-d H:i',
            disabledWeekDays: [0, 1],
            disabledDates: pickup_disabled_dates,
            minDate : minDate,
            maxDate : maxDate,
            defaultDate: minDate,
            defaultTime: "08:00",
            allowTimes:[
                '08:00', '08:15', '08:30', '08:45',
                '09:00', '09:15', '09:30', '09:45',
                '10:00', '10:15', '10:30', '10:45',
                '11:00', '11:15', '11:30', '11:45',
                '12:00', '12:15', '12:30', '12:45',
                '13:00', '13:15', '13:30', '13:45',
                '14:00', '14:15', '14:30', '14:45',
                '15:00', '15:15', '15:30', '15:45',
                '16:00', '16:15', '16:30', '16:45',
                '17:00'
            ]
        });
        $("#pickup_date_time").on("keyup", function() {
            $(this).val("");
            jQuery('#pickup_date_time').datetimepicker('show');
        });
        $('.woocommerce-account .woocommerce > #customer_login .nectar-form-controls .control:nth-child(2)').trigger('click');
        $("#delivery_date").datetimepicker({
            timepicker:false,
            format: 'Y-m-d',
            disabledWeekDays: [0, 1],
            disabledDates: disabledDates,
            minDate: pt_obj.today,
            maxDate: pt_obj.max_date
        });
        $("#delivery_date").on("keyup", function() {
            $(this).val("");
            jQuery('#delivery_date').datetimepicker('show');
        });


        $("#billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode").on("change", function() {
            measure_distance();
        });

        var zone_fee = 0;
        var cart_fee = 0;
        var measure_distance = function () {
            if($("#woocommerce-register-nonce").length > 0) {
                return false;
            }
            if($("#delivery_date").length == 0) {
                return false;
            }
            var delivery_type = $("input[name='delivery_type']:checked").val();
            $(".delivery-note").remove();
            var address_1 = $("#billing_address_1").val().trim();
            var address_2 = $("#billing_address_2").val().trim();
            var city = $("#billing_city").val().trim();
            var state = $("#billing_state").val();
            var postcode = $("#billing_postcode").val().trim();
            if(address_1 != "" && city != '' && state != '' && postcode != '') {
                if($("input[name='delivery_type']").length > 0) {
                    $.ajax({
                        url : woocommerce_params.ajax_url,
                        data : {
                            action : "get_distance_from_store",
                            address_1 : address_1,
                            address_2 : address_2,
                            city : city,
                            state : state,
                            postcode : postcode,
                        },
                        type : "post",
                        dataType : "json",
                        success : function(response) {
                            zone_fee = response.zone_fee;
                            cart_fee = response.cart_total;
                            if((zone_fee >= 0) && (parseInt(zone_fee) > parseInt(response.cart_total))){

                                var html = '<div class="woocommerce-info" id="order_note">Sorry, your order total must be $' + zone_fee +' or more to be eligible for discount.</div><p class="return-to-shop"><a class="button wc-backward" href="/shop">Continue Shopping</a></p>';
                                if($('#order_note').length == 0){
                                    $('#delivery_type_field').before(html);
                                }
                                // $('#place_order').attr('disabled' , 'true');
                            }
                            if(response.is_local) {
                                if(response.is_specific_postcode) {
                                    $('#delivery_date').datetimepicker('setOptions', {disabledWeekDays: response.days});
                                } else {
                                    $('#delivery_date').datetimepicker('setOptions', {disabledWeekDays: [0, 1]});
                                }

                                $(".delivery_type_radio").removeClass("hide");
                                $(".only_delivery_field").removeClass("hide");
                                $("#pickup_date_time_field").addClass("hide");
                                if(response.remove_delivery_date == true){
                                    $(".delivery_type_radio").addClass("hide");
                                    $(".only_delivery_field").addClass("hide");
                                    $("#pickup_date_time_field").removeClass("hide");
                                    $("#delivery_type_pick_up").prop('checked', true);
                                }
                                // $("#place_order").removeAttr("disabled");
                                // $("#delivery_date_field").removeClass("hide");
                                // $(".only_delivery_field").removeClass("hide");
                                // $("#pickup_date_time_field").addClass("hide");
                                if($(".only_delivery_type_radio").length > 0) {
                                    $("#delivery_type_pick_up").prop('checked', false);
                                    $("#delivery_type_delivery").prop('checked', true);
                                    add_cart_fee('delivery');
                                }
                            } else {
                                // $("#place_order").attr("disabled", "disabled");
                                var html = '<div class="woocommerce-info delivery-note">Sorry, right now we are not delivering to your area but we are adding more territories, stay up-to-date <a target="_blank" href="/covid-19-update-page">here</a></div>';
                                $(".delivery_type_radio").addClass("hide");
                                $(".only_delivery_field").addClass("hide");
                                $("#pickup_date_time_field").removeClass("hide");
                                if($(".only_delivery_type_radio").length > 0 || jQuery("input[name='delivery_type']").length > 0) {
                                    $("#delivery_type_pick_up").prop('checked', true);
                                    $("#delivery_type_delivery").prop('checked', false);
                                    add_cart_fee('pick_up');
                                }
                                if($(".delivery-note").length > 0) {
                                    $(".delivery-note").html(html);
                                } else {
                                    $("#billing_postcode").parent().parent().after(html);
                                }
                            }
                        }
                    });
                }
            }
        }

        $("input[name='delivery_type']").on("change", function(e) {
            var type = $("input[name='delivery_type']:checked").val();
            $(".delivery-note").remove();
            if(type == "delivery") {
                measure_distance();
                $("#pickup_date_time").parent().parent().addClass("hide");
                $("#delivery_date").parent().parent().removeClass("hide");
            } else {
                $("#delivery_date").parent().parent().addClass("hide");
                $("#pickup_date_time").parent().parent().removeClass("hide");
            }
            // zone_fee = 10;
            // if((zone_fee >= 0) && (parseInt(zone_fee) > parseInt(cart_fee))){
                // $('#place_order').attr('disabled' , 'true');
            // }
            add_cart_fee(type);
        });

        var add_cart_fee = function(type) {
            if(zone_fee != 0) {
                zone_fee = zone_fee;
            }
            $.ajax({
                url : woocommerce_params.ajax_url,
                data: {
                    action : "add_cart_fee",
                    type : type,
                    zone_fee : zone_fee,
                },
                type : "post",
                dataType : "json",
                success : function(response) {
                    $('body').trigger('updated_checkout');
                    $.each(response.fragments, function(k, v) {
                        $(k).html(v);
                    });
                }
            });
        }
        var check_distance = function (response, status) {
            var is_under_range = false;
            if(response.rows[0].elements[0].status == "OK") {
                var distance = response.rows[0].elements[0].distance.value;
                distance = distance/1000;
                is_under_range = (distance <= pt_obj.delivery_distance) ? true : false;
            }
            if(is_under_range) {
                $("#is_under_range").val(1);
                var html = 'Yes, we can deliver to you. <a target="_blank" style="color: #fff;" href="/covid-19-update-page">Read what this means</a>';
            } else {
                var html = 'Sorry, right now we are not delivering to your area but we are adding more territories, stay up-to-date <a target="_blank" style="color: #fff;" href="/covid-19-update-page">here</a>.';
            }
            $("#delivery-note li").html(html);
            $("#delivery-note").removeClass("hide");
        }

        /* Check address range during signup */
        $(".delivery_field").on("change", function(e) {
            var service = new google.maps.DistanceMatrixService();
            var address_1 = $("#billing_address_1").val().trim();
            var address_2 = $("#billing_address_2").val().trim();
            var city = $("#billing_city").val().trim();
            var postcode = $("#billing_postcode").val().trim();

            if(address_1 != '' && city != '' && postcode != '') {
                var dest = address_1.replace(", ", "+");
                if(address_2 != '') {
                    address_2 = address_2.replace(", ", "+");
                    dest = dest+"+"+address_2;
                }
                dest = dest + "+" + city + "+" + postcode + "+NZ";
                dest.replace(" ", "+");
                dest.replace("++", "+");
                service.getDistanceMatrix({
                    origins: ["70+Edgeware+Rd+Edgeware+Christchurch+NZ"],
                    destinations: [dest],
                    travelMode: 'DRIVING',
                }, check_distance);
            }
        });
        if($("#billing_address_1").length > 0) {
            var address_1 = $("#billing_address_1").val().trim();
            var address_2 = $("#billing_address_2").val().trim();
            var city = $("#billing_city").val().trim();
            var state = $("#billing_state").val();
            var postcode = $("#billing_postcode").val().trim();
            if(address_1 != "" && city != '' && state != '' && postcode != '') {
                $("#billing_postcode").trigger("change");
            }
        }

        // Click event
        // validation input text ma value 6 k nai
        // If inserted ajax call
        //

        var add_log_card = function() {
            var card_number = $("#loyalty_card_number").val();
            $.ajax({
                url : woocommerce_params.ajax_url,
                data: {
                    action : "add_log_card",
                    card_number : card_number
                },
                type: "post",
                dataType: "json",
                success: function(response) {
                    if(response.success) {
                        $(".card-response").text(response.message);
                    }
                }
            });
        }
        $("#submitme").on("click", function() {
            add_log_card();
        })
    });
})(jQuery);