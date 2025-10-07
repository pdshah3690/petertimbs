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
     jQuery("#customer_product_sale_table").dataTable({
        "aaSorting": [],
        "pageLength": 100,
        searching: false,
        dom: 'Bfrtip',
        buttons: [
        'csv', 'excel'
        ]
    });
     jQuery(".notification-table").dataTable({
        "aaSorting": [],
        "pageLength": 100,
    });
    jQuery(document).ready(function(e) {
        $("select#order_type").on("change", function() {
            $("#order_type_form").submit();
        });
    });
    jQuery(document).ready(function(e) {
        $("select#select_store").on("change", function() {
            $("#order_type_form").submit();
        });
    });

    if($("#selected_user").length){
        $("#selected_user").select2({
          multiple: true,
        });    
    }
    if($("#selected_zones").length){
        $("#selected_zones").select2({
          multiple: false,
        });    
    }
    if($("#selected_product").length){
        $("#selected_product").select2({
          multiple: false,
        });    
    }
    $("input:radio[name=notification_type]").change(function(e){
        var type = $(this).val();
        $("#type_schedule").hide();
        if(type == 'schedule'){
            $("#type_schedule").show();
        }

    });
    $('#all_user').change(function() {
        let isChecked = $(this).is(':checked');
        $("#selected_all_user").removeClass('hide');
        $("#selected_all_zones").removeClass('hide');
        if(isChecked == true){
            $("#selected_all_user").addClass('hide');
            $("#selected_all_zones").addClass('hide');
        }
    });
    $('#selected_zones').change(function() {
        $("#select_check").addClass('hide');
        $("#selected_all_user").addClass('hide');
    });
    $('#selected_all_user').change(function() {
        $("#select_check").addClass('hide');
        $("#selected_all_zones").addClass('hide');
    });
    $("#send_notification").click(function(e){
		$("#notification-response-message").html("");
		$("#notification-response-message").hide();
        var userId = $("#selected_user").val();
        var title = $("#title").val();
        var body = $("#body").val();
        var schedule_at = $("#schedule_at").val();
        var type = $("input:radio[name=notification_type]:checked").val();
        var all_user = $("#all_user").is(':checked');
        var zone = $("#selected_zones").val();
        var product_id = $("#selected_product").val();
        // console.log(all_user);
        $.ajax({
            url: ajaxurl,
            type: "post",
            data: {
                action : 'send_notification',
                userId  : userId,
                title : title,
                body : body,
                schedule_at: schedule_at,
                type: type,
                all_user: all_user,
                zone: zone,
                product_id: product_id,
            },
            success : function(response){
                // alert(response.message);
                $("#notification-response-message").html("Notification sent successfully!!!");
				$("#notification-response-message").show();
                $("#send_notification_list").html(response.list);
                // $("#myModal").css("display","block");
                $("#selected_user").val('').trigger("change");
                $("#selected_zones").val('').trigger("change");
                $("#select_check").removeClass('hide');
                $("#selected_all_user").removeClass('hide');
                $("#selected_all_zones").removeClass('hide');
                $("#selected_product").val('').trigger("change");
                $('#success_notification').append('');
                $('#success_notification').append(response.message);
                $("#title").val('');
                $("#body").val('');
                $("#schedule_at").val("");
                $(".notification_type").prop('checked', false);
                $("#all_user").prop('checked', false);
            }
        });
    });

    $(".view_notification").click(function(e){
        var time_stamp = $(this).data('id');

        $.ajax({
            url: ajaxurl,
            type: "post",
            data: {
                action : 'view_notification',
                time_stamp  : time_stamp,
            },
            success : function(response){
                $("#view_notification_list").html(response.list);
                $("#delete_modal").css("display","block");
                // alert(response.message);
                // location.reload();
            }
        });
    });
    
    $(document).on("click",".all_delete_notification",function(e){
        var notification_id = $(this).data('id');
        var time_stamp = $("."+notification_id).val();
        if (confirm("Are you sure you want to delete!") == true) {
            $.ajax({
                url: ajaxurl,
                type: "post",
                data: {
                    action : 'delete_notification',
                    notification_id  : notification_id,
                    time_stamp : time_stamp,
                },
                success : function(response){
                    if(response.success == true){
                        $("#delete_modal").css("display","none");
                        location.reload();
                    }
                }
            });
        }
        
    });

    $(document).on("click",".delete_notification",function(e){
        
        var notification_id = $(this).data('id');
        var time_stamp = '';
        
        if (confirm("Are you sure you want to delete!") == true) {
            $.ajax({
                url: ajaxurl,
                type: "post",
                data: {
                    action : 'delete_notification',
                    notification_id  : notification_id,
                    time_stamp : time_stamp,
                },
                success : function(response){
                    if(response.success == true){
                        $("#delete_modal").css("display","none");
                        location.reload();
                    }
                }
            });
        }        
    });

    // $(".all_delete_notification").click(function(e){
    //     var notification_id = $(this).data('id');
    //     var time_stamp = $("."+notification_id).val();
    //     console.log(time_stamp);
    // });
    // $(".delete_notification").click(function(e){
    //     var notification_id = $(this).data('id');
    //     // var time_stamp = $("."+notification_id).val();
    //     console.log(notification_id);
    //     $.ajax({
    //         url: ajaxurl,
    //         type: "post",
    //         data: {
    //             // action : 'delete_notification',
    //             time_stamp  : time_stamp,
    //         },
    //         success : function(response){
    //             $("#delete_modal").css("display","none");
    //             // alert(response.message);
    //             location.reload();
    //         }
    //     });
    // });

    $(document).on("click",".close",function(e){
        $("#myModal").css("display","none");
        $("#delete_modal").css("display","none");
    });


    $("#schedule_at").datetimepicker();

})(jQuery);