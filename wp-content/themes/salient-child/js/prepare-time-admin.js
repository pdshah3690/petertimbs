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


    var _preparation_time_settings = function(type = "variable") {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_product_prepare_time',
                type : type,
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.prepare-time-container').html(html);
            }
        });
    }

    var _site_product_settings = function(type = "variable") {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_site_product',
                type : type,
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.site-product-container').html(html);
            }
        });
    }

    var _shipping_type_settings = function(type = "variable") {
        console.log("_shipping_type_settings");
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_shipping_type',
                type : type,
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.shipping-type-container').html(html);
            }
        });
    }

    var _date_product_settings = function() {
        console.log('before ajax');
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_date_product',
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.date-product-container').html(html);
                $("#date_product_from").datepicker({ dateFormat: 'dd-mm-yy', minDate: 0 });
                $("#date_product_to").datepicker({ dateFormat: 'dd-mm-yy', minDate: 0 });
            }
        });
    }

    var _product_free_delivery_settings = function(type = "variable") {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_product_free_delivery',
                type : type,
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.product-free-delivery-container').html(html);
            }
        });
    }

    var _get_butchers_kitchen_img_settings = function(type = "variable") {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action : 'get_butchers_kitchen_img',
                type : type,
                product_id  : woocommerce_admin_meta_boxes.post_id
            },
            success : function(html){
                $('.butchers-kitchen-img-container').html(html);
            }
        });
    }

    $(document.body).on('woocommerce_variations_saved woocommerce_variations_removed', function(){
        _preparation_time_settings();
        _site_product_settings();
        _shipping_type_settings();
        _date_product_settings();
        _product_free_delivery_settings();
        _get_butchers_kitchen_img_settings();
    });
    $(document).on("change", "#product-type", function(){
        var type = $(this).val();
        _preparation_time_settings(type);
        _site_product_settings(type);
        _shipping_type_settings(type);
        _date_product_settings();
        _product_free_delivery_settings(type);
        _get_butchers_kitchen_img_settings(type);
    });

    $(document).on( 'click', '.upload_image_button', upload_image_button )
        .on( 'click', '.remove_image_button', remove_image_button );

    function upload_image_button(e) {
        e.preventDefault();
        var $this = $( e.currentTarget );
        var $input_field = $this.prev();
        var $image = $this.parent().find( '.uploaded_image' );
        var custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Add Image',
            button: {
                text: 'Add Image'
            },
            multiple: false
        });
        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
            $input_field.val( attachment.id );
            $('.uploaded_image').attr('src', attachment.url);
            $image.html( '<img src="' + attachment.url + '" />' );
        });
        custom_uploader.open();
    }

    function remove_image_button(e) {
        e.preventDefault();
        var $this = $( e.currentTarget );
        var $input_field = $this.parent().find( '.featured_image_upload' );
        var $image = $this.parent().find( '.uploaded_image' );

        $input_field.val( '' );
        $image.html( '' );
    }





 })(jQuery);